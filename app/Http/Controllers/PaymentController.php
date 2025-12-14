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

class PaymentController extends Controller
{
    public function index()
    {
        return view('payment.index');
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
            
            if (!$customer) {
                return response()->json([
                    'error' => 'Customer not found'
                ], 404);
            }

            // Use the stored account number for consistency in downstream queries
            $acct = $customer->account_no;
            // Get all unpaid bills for this customer
            $allBills = BillingRecord::where('account_no', $acct)
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

        // Fast, limited search for registered (active) customers by account, name, or address
        $results = Customer::query()
            ->when(method_exists(Customer::class, 'scopeActive'), fn($qb) => $qb->active())
            ->withCount(['unpaidBillingRecords as unpaid_count'])
            ->withSum('unpaidBillingRecords as unpaid_total', 'total_amount')
            ->where(function ($qb) use ($query, $nameFilter) {
                $qb->where('account_no', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%");

                if ($nameFilter !== '') {
                    $qb->orWhere('name', 'like', "%{$nameFilter}%")
                        ->orWhere('address', 'like', "%{$nameFilter}%");
                }
            })
            ->having('unpaid_count', '>', 0)
            // Prioritize starts-with matches on account_no, then name
            ->orderByRaw(
                "CASE 
                    WHEN account_no LIKE ? THEN 0 
                    WHEN name LIKE ? THEN 1 
                    ELSE 2 
                END, unpaid_total DESC, name ASC",
                ["{$query}%", ($nameFilter !== '' ? "{$nameFilter}%" : "{$query}%")]
            )
            ->limit(10)
            ->get(['id', 'account_no', 'name', 'address', 'meter_no', 'classification'])
            ->map(function ($customer) {
                $unpaidTotal = (float) ($customer->unpaid_total ?? 0);
                return [
                    'id' => $customer->id,
                    'account_no' => $customer->account_no,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'meter_no' => $customer->meter_no,
                    'classification' => $customer->classification,
                    'unpaid_count' => (int) ($customer->unpaid_count ?? 0),
                    'unpaid_total' => $unpaidTotal,
                    'formatted_unpaid_total' => '₱' . number_format($unpaidTotal, 2),
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
            // Prevent duplicate full payments: if fully paid, block new payment
            $unpaidBills = BillingRecord::where('account_no', $data['account_no'])
                ->whereDoesntHave('paymentRecords')
                ->get();
            $totalOutstanding = $unpaidBills->sum('total_amount');
            if ($totalOutstanding <= 0) {
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
                    'payment_method' => $data['payment_method'] ?? null,
                    'reference_number' => $data['reference_number'] ?? null,
                    'payment_details' => $result['payment_details'] ?? null,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'payment_record_id' => $result['payment_record_id'],
                'payment_details' => $result['payment_details'],
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
            $paymentRecord = PaymentRecord::with(['customer', 'billingRecord'])
                ->findOrFail($paymentRecordId);

            return view('payment.receipt', compact('paymentRecord'));
        } catch (\Exception $e) {
            abort(404, 'Payment receipt not found');
        }
    }

    public function printReceipt($paymentRecordId)
    {
        try {
            $paymentRecord = PaymentRecord::with(['customer', 'billingRecord'])
                ->findOrFail($paymentRecordId);

            return view('payment.print-receipt', compact('paymentRecord'));
        } catch (\Exception $e) {
            abort(404, 'Payment receipt not found');
        }
    }
}