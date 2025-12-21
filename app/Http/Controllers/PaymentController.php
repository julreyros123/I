<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Models\BillingRecord;
use App\Models\PaymentRecord;
use App\Models\Register;
use App\Services\PaymentService;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $prefillAccount = $request->query('account');

        return view('payment.index', [
            'prefillAccount' => $prefillAccount,
        ]);
    }

    protected function buildPaymentSummary(array $input, array $result): string
    {
        $parts = [];
        if (!empty($input['payment_method'])) {
            $parts[] = 'Method: ' . $input['payment_method'];
        }
        if (!empty($input['reference_number'])) {
            $parts[] = 'Reference: ' . $input['reference_number'];
        }
        if (!empty($input['notes'])) {
            $parts[] = 'Notes: ' . $input['notes'];
        }
        $details = $result['payment_details'] ?? [];
        if (is_array($details)) {
            if (isset($details['overpayment']) && $details['overpayment'] > 0) {
                $parts[] = 'Overpayment: ₱' . number_format((float) $details['overpayment'], 2);
            }
            if (isset($details['bills_paid']) && $details['bills_paid'] > 0) {
                $parts[] = sprintf('Bills settled: %d', (int) $details['bills_paid']);
            }
        }

        return $parts ? implode(' • ', $parts) : '—';
    }

    public function searchCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'account_no' => ['required', 'string', 'max:50']
        ]);

        try {
            $inputAcct = (string) $request->account_no;
            $normalized = preg_replace('/[^A-Za-z0-9]/', '', $inputAcct);
            $customer = Customer::query()
                ->where('account_no', $inputAcct)
                ->orWhereRaw("REPLACE(REPLACE(account_no,'-',''),' ','') = ?", [$normalized])
                ->first();

            if (!$customer && str_contains($inputAcct, '-')) {
                $withoutCheck = preg_replace('/-([A-Za-z0-9])$/', '', $inputAcct);
                if ($withoutCheck) {
                    $customer = Customer::query()
                        ->where('account_no', $withoutCheck)
                        ->orWhereRaw("REPLACE(REPLACE(account_no,'-',''),' ','') = ?", [preg_replace('/[^A-Za-z0-9]/', '', $withoutCheck)])
                        ->first();
                }
            }

            if (!$customer) {
                $customer = Customer::query()
                    ->whereRaw("REPLACE(REPLACE(account_no,'-',''),' ','') LIKE ?", ["{$normalized}%"])
                    ->orderByRaw('LENGTH(account_no) ASC')
                    ->first();
            }

            if (!$customer) {
                return response()->json([
                    'error' => 'Customer not found'
                ], 404);
            }

            // Use the stored account number for consistency in downstream queries
            $acct = $customer->account_no;
            $normalizedAcct = preg_replace('/[^A-Za-z0-9]/', '', $acct ?? '') ?: null;

            $applyAccountFilter = function ($query) use ($acct, $normalizedAcct, $inputAcct) {
                $query->where('account_no', $inputAcct);
                if ($acct && $acct !== $inputAcct) {
                    $query->orWhere('account_no', $acct);
                }
                if ($normalizedAcct) {
                    $query->orWhereRaw("REPLACE(REPLACE(account_no,'-',''),' ','') = ?", [$normalizedAcct]);
                }
            };

            if (strcasecmp((string) ($customer->status ?? ''), 'Active') !== 0) {
                return response()->json([
                    'error' => 'Customer status is not Active. Only active accounts can make payments.',
                ], 422);
            }

            // Get all unpaid bills for this customer
            $allBills = BillingRecord::where(function ($query) use ($applyAccountFilter) {
                    $applyAccountFilter($query);
                })
                ->withSum('paymentRecords as amount_paid_sum', 'amount_paid')
                ->orderByDesc('created_at')
                ->get();

            $unpaidBills = $allBills->filter(function ($bill) {
                $paidSum = (float) ($bill->amount_paid_sum ?? 0);
                return ($bill->total_amount - $paidSum) > 0.01;
            })->values();

            // Calculate total outstanding amount
            $totalOutstanding = $unpaidBills->reduce(function ($carry, $bill) {
                $paidSum = (float) ($bill->amount_paid_sum ?? 0);
                return $carry + max(0, (float) $bill->total_amount - $paidSum);
            }, 0.0);

            if ($totalOutstanding <= 0.01) {
                return response()->json([
                    'error' => 'This customer has no unpaid bills to settle.',
                ], 422);
            }

            // Get the latest unpaid bill for detailed information
            $latestBill = $unpaidBills->sortByDesc('created_at')->first();

            // Check if customer has any overdue bills (Notice of Disconnection)
            $overdueBills = $unpaidBills->filter(function ($bill) {
                return $bill->bill_status === 'Notice of Disconnection';
            })->values();

            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'account_no' => $customer->account_no,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'meter_no' => $customer->meter_no,
                    'meter_size' => $customer->meter_size,
                    'previous_reading' => $customer->previous_reading,
                    'classification' => optional(Register::where('account_no', $customer->account_no)->first())->connection_classification,
                ],
                'unpaid_bills' => $unpaidBills->map(function ($bill) {
                    $paidSum = (float) ($bill->amount_paid_sum ?? 0);
                    $outstanding = max(0, (float) $bill->total_amount - $paidSum);
                    return [
                        'id' => $bill->id,
                        'billing_date' => $bill->created_at->format('Y-m-d'),
                        'previous_reading' => $bill->previous_reading,
                        'current_reading' => $bill->current_reading,
                        'consumption' => $bill->consumption_cu_m,
                        'subtotal' => $bill->consumption_cu_m * $bill->base_rate,
                        'maintenance_charge' => $bill->maintenance_charge,
                        'advance_payment' => $bill->advance_payment,
                        'overdue_penalty' => $bill->overdue_penalty,
                        'total_amount' => $outstanding,
                        'bill_status' => $bill->bill_status,
                        'formatted_total' => '₱' . number_format($outstanding, 2),
                        'date_from' => $bill->date_from,
                        'date_to' => $bill->date_to,
                        'base_rate' => $bill->base_rate,
                    ];
                }),
                'latest_bill' => $latestBill ? [
                    'id' => $latestBill->id,
                    'billing_date' => $latestBill->created_at->format('Y-m-d'),
                    'previous_reading' => $latestBill->previous_reading,
                    'current_reading' => $latestBill->current_reading,
                    'consumption' => $latestBill->consumption_cu_m,
                    'subtotal' => $latestBill->consumption_cu_m * $latestBill->base_rate,
                    'maintenance_charge' => $latestBill->maintenance_charge,
                    'advance_payment' => $latestBill->advance_payment,
                    'overdue_penalty' => $latestBill->overdue_penalty,
                    'total_amount' => max(0, (float) $latestBill->total_amount - (float) ($latestBill->amount_paid_sum ?? 0)),
                    'bill_status' => $latestBill->bill_status,
                    'formatted_total' => '₱' . number_format(max(0, (float) $latestBill->total_amount - (float) ($latestBill->amount_paid_sum ?? 0)), 2),
                    'date_from' => $latestBill->date_from,
                    'date_to' => $latestBill->date_to,
                    'base_rate' => $latestBill->base_rate,
                    'delivery_date' => $latestBill->date_to ? $latestBill->date_to->format('Y-m-d') : null,
                ] : null,
                'overdue_bills' => $overdueBills->map(function ($bill) {
                    $paidSum = (float) ($bill->amount_paid_sum ?? 0);
                    $outstanding = max(0, (float) $bill->total_amount - $paidSum);
                    return [
                        'id' => $bill->id,
                        'bill_status' => $bill->bill_status,
                        'total_amount' => $outstanding,
                        'formatted_total' => '₱' . number_format($outstanding, 2),
                        'billing_date' => $bill->created_at->format('Y-m-d'),
                    ];
                }),
                'total_outstanding' => $totalOutstanding,
                'formatted_total_outstanding' => '₱' . number_format($totalOutstanding, 2),
                'has_overdue' => $overdueBills->isNotEmpty(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to search customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function quickSearch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim($data['q']);
        $nameFilter = isset($data['name']) ? trim($data['name']) : '';
        $normalizedQuery = preg_replace('/[^A-Za-z0-9]/', '', $query);

        // Flexible search for customers by account, name, or address
        $paymentSums = DB::table('payment_records')
            ->select('billing_record_id', DB::raw('SUM(amount_paid) as paid_total'))
            ->groupBy('billing_record_id');

        $billingSummary = DB::table('billing_records')
            ->select('account_no')
            ->selectRaw(
                "SUM(CASE WHEN COALESCE(billing_records.bill_status, '') <> 'Paid' THEN GREATEST(total_amount - COALESCE(pr.paid_total, 0), 0) ELSE 0 END) as outstanding_total"
            )
            ->selectRaw(
                "SUM(CASE WHEN COALESCE(billing_records.bill_status, '') <> 'Paid' THEN 1 ELSE 0 END) as outstanding_count"
            )
            ->leftJoinSub($paymentSums, 'pr', 'pr.billing_record_id', '=', 'billing_records.id')
            ->groupBy('account_no');

        $results = Customer::query()
            ->leftJoinSub($billingSummary, 'billing_summary', 'billing_summary.account_no', '=', 'customers.account_no')
            ->where('customers.status', 'Active')
            ->where(function ($qb) use ($query, $nameFilter, $normalizedQuery) {
                $qb->where(function ($inner) use ($query) {
                        $inner->where('customers.account_no', 'like', "%{$query}%")
                              ->orWhere('customers.name', 'like', "%{$query}%");
                    });

                if ($normalizedQuery !== '') {
                    $qb->orWhere(function ($inner) use ($normalizedQuery) {
                        $inner->whereRaw("REPLACE(REPLACE(customers.account_no,'-',''),' ','') LIKE ?", ["%{$normalizedQuery}%"]);
                    });
                }

                if ($nameFilter !== '') {
                    $qb->orWhere(function ($inner) use ($nameFilter) {
                        $inner->where('customers.name', 'like', "%{$nameFilter}%");
                    });
                }
            })
            ->orderByRaw(
                "CASE 
                    WHEN customers.account_no LIKE ? THEN 0 
                    WHEN customers.name LIKE ? THEN 1 
                    ELSE 2 
                END, COALESCE(billing_summary.outstanding_total, 0) DESC, customers.name ASC",
                ["{$query}%", ($nameFilter !== '' ? "{$nameFilter}%" : "{$query}%")]
            )
            ->limit(10)
            ->get([
                'customers.id',
                'customers.account_no',
                'customers.name',
                'customers.address',
                'customers.meter_no',
                'customers.classification',
                'customers.status',
                DB::raw('COALESCE(billing_summary.outstanding_total, 0) as outstanding_total'),
                DB::raw('COALESCE(billing_summary.outstanding_count, 0) as outstanding_count'),
            ])
            ->map(function ($customer) {
                $outstandingTotal = (float) ($customer->outstanding_total ?? 0);
                return [
                    'id' => $customer->id,
                    'account_no' => $customer->account_no,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'meter_no' => $customer->meter_no,
                    'classification' => $customer->classification,
                    'status' => $customer->status,
                    'unpaid_count' => (int) ($customer->outstanding_count ?? 0),
                    'unpaid_total' => $outstandingTotal,
                    'formatted_unpaid_total' => '₱' . number_format($outstandingTotal, 2),
                ];
            });

        return response()->json([
            'results' => $results,
        ]);
    }

    public function processPayment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_no' => ['required', 'string', 'max:50'],
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'latest_only' => ['nullable', 'boolean'],
            'bill_ids' => ['nullable', 'array'],
            'bill_ids.*' => ['integer'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            // Prevent duplicate full payments: compute true outstanding balance including partially paid bills
            $billingRecords = BillingRecord::where('account_no', $data['account_no'])
                ->withSum('paymentRecords as amount_paid_sum', 'amount_paid')
                ->get();

            $totalOutstanding = $billingRecords->reduce(function ($carry, $bill) {
                $paid = (float) ($bill->amount_paid_sum ?? 0);
                $due = max(0, (float) $bill->total_amount - $paid);
                return $carry + $due;
            }, 0.0);

            if ($totalOutstanding <= 0.0) {
                return response()->json([
                    'success' => false,
                    'error' => 'This customer has no outstanding balance. Payment is not allowed.',
                    'code' => 'ALREADY_PAID'
                ], 400);
            }

            $paymentService = new PaymentService();
            // Include selection controls for service to handle bill application
            $result = $paymentService->processCustomerPayment($data);

            // Activity log: payment processed
            ActivityLog::create([
                'user_id' => optional($request->user())->id,
                'module' => 'Payments',
                'action' => 'PAYMENT_PROCESSED',
                'description' => sprintf(
                    'Processed payment of ₱%s for account %s',
                    number_format($data['amount_paid'], 2),
                    $data['account_no']
                ),
                'target_type' => PaymentRecord::class,
                'target_id' => $result['payment_record_id'] ?? null,
                'meta' => [
                    'account_no' => $data['account_no'],
                    'amount_paid' => $data['amount_paid'],
                    'payment_summary' => $this->buildPaymentSummary($data, $result ?? []),
                    'payment_details' => $result['payment_details'] ?? null,
                    'auto_archived' => $result['auto_archived'] ?? null,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'payment_record_id' => $result['payment_record_id'],
                'payment_details' => $result['payment_details'],
                'auto_archived' => $result['auto_archived'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getPaymentReceipt($paymentRecordId)
    {
        try {
            $paymentRecord = PaymentRecord::with([
                    'customer',
                    'billingRecord' => function ($query) {
                        $query->withTrashed();
                    }
                ])
                ->findOrFail($paymentRecordId);

            return view('payment.receipt', compact('paymentRecord'));
        } catch (\Exception $e) {
            abort(404, 'Payment receipt not found');
        }
    }

    public function printReceipt($paymentRecordId)
    {
        try {
            $paymentRecord = PaymentRecord::with([
                    'customer',
                    'billingRecord' => function ($query) {
                        $query->withTrashed();
                    }
                ])
                ->findOrFail($paymentRecordId);

            return view('payment.print-receipt', compact('paymentRecord'));
        } catch (\Exception $e) {
            abort(404, 'Payment receipt not found');
        }
    }
}