<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BillingRecord;
use App\Models\Customer;
use App\Services\PaymentService;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BillingController extends Controller
{
    public function compute(Request $request): JsonResponse
    {
        $data = $request->validate([
            'previous_reading' => ['nullable','numeric','min:0'],
            'current_reading' => ['nullable','numeric','min:0'],
            'maintenance_charge' => ['nullable','numeric','min:0'],
            'base_rate' => ['nullable','numeric','min:0'],
        ]);

        $previous = (float)($data['previous_reading'] ?? 0);
        $current = (float)($data['current_reading'] ?? 0);
        $maintenance = (float)($data['maintenance_charge'] ?? 0);
        $baseRate = (float)($data['base_rate'] ?? 25); // per m³ default

        $used = max(0, $current - $previous);
        $subtotal = ($used * $baseRate);
        // New rule: No VAT. Total = subtotal + charges
        $vat = 0.0;
        $total = $subtotal + $maintenance;

        $peso = fn(float $n) => '₱' . number_format($n, 2);

        return response()->json([
            'consumption_cu_m' => round($used, 2),
            'subtotal' => ($used * $baseRate),
            'vat' => $vat,
            'total' => $total,
            'formatted' => [
                'total' => $peso($total),
                'subtotal' => $peso(($used * $baseRate)),
                'maintenance_charge' => $peso($maintenance),
            ],
        ]);
    }
    
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_no' => ['required','string','max:50'],
            'invoice_number' => ['nullable','string','max:50','unique:billing_records,invoice_number'],
            'prepared_by' => ['nullable','string','max:255'],
            'issued_at' => ['nullable','date'],
            'previous_reading' => ['required','numeric','min:0'],
            'current_reading' => ['required','numeric','min:0','gt:previous_reading'],
            'consumption_cu_m' => ['required','numeric','min:0'],
            'base_rate' => ['nullable','numeric','min:0'],
            'maintenance_charge' => ['nullable','numeric','min:0'],
            'advance_payment' => ['nullable','numeric','min:0'],
            'overdue_penalty' => ['nullable','numeric','min:0'],
            'vat' => ['nullable','numeric','min:0'],
            'total_amount' => ['required','numeric','min:0'],
            'date_from' => ['nullable','date'],
            'date_to' => ['nullable','date'],
        ]);

        try {
            $targetCycleDate = Carbon::parse($data['date_to'] ?? $data['issued_at'] ?? Carbon::now());
            $monthStart = $targetCycleDate->copy()->startOfMonth();
            $monthEnd = $targetCycleDate->copy()->endOfMonth();
            $monthLabel = $monthStart->format('F Y');

            $cycleConstraint = function (Builder $query) use ($monthStart, $monthEnd) {
                $query->where(function ($cycle) use ($monthStart, $monthEnd) {
                    $cycle->whereNotNull('date_to')->whereBetween('date_to', [$monthStart, $monthEnd])
                        ->orWhere(function ($fallback) use ($monthStart, $monthEnd) {
                            $fallback->whereNull('date_to')
                                ->whereNotNull('issued_at')
                                ->whereBetween('issued_at', [$monthStart, $monthEnd]);
                        })
                        ->orWhere(function ($fallback) use ($monthStart, $monthEnd) {
                            $fallback->whereNull('date_to')
                                ->whereNull('issued_at')
                                ->whereBetween('created_at', [$monthStart, $monthEnd]);
                        });
                });
            };

            // Check for duplicate bill for the same account with same current reading
            $existingBill = BillingRecord::where('account_no', $data['account_no'])
                ->where('current_reading', $data['current_reading'])
                ->first();
                
            if ($existingBill) {
                return response()->json([
                    'ok' => false,
                    'error' => 'A bill with this current reading already exists for this account.'
                ], 400);
            }

            $pendingStatuses = ['Pending','Outstanding Payment','Overdue','Notice of Disconnection','Disconnected'];
            $existingUnpaidForCycle = BillingRecord::where('account_no', $data['account_no'])
                ->where(function ($query) use ($cycleConstraint) {
                    $cycleConstraint($query);
                })
                ->where(function ($query) use ($pendingStatuses) {
                    $query->whereNull('bill_status')->orWhereIn('bill_status', $pendingStatuses);
                })
                ->first();

            if ($existingUnpaidForCycle) {
                return response()->json([
                    'ok' => false,
                    'error' => "Cannot create a new billing record for {$monthLabel} while a previous invoice remains unpaid."
                ], 400);
            }

            $existingCycle = BillingRecord::withTrashed()
                ->where('account_no', $data['account_no'])
                ->where(function ($query) use ($cycleConstraint) {
                    $cycleConstraint($query);
                })
                ->first();

            if ($existingCycle) {
                return response()->json([
                    'ok' => false,
                    'error' => "An invoice for {$monthLabel} already exists for this account. Please update the existing bill instead of creating a duplicate."
                ], 400);
            }

            // Find customer
            $customer = Customer::where('account_no', $data['account_no'])->first();
            if (!$customer) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Customer not found.'
                ], 400);
            }

            // Create billing record
            $dueDate = null;
            if (!empty($data['date_to'])) {
                $dueDate = Carbon::parse($data['date_to']);
            } else {
                $dueDate = Carbon::now()->endOfMonth();
            }
            $billingRecord = BillingRecord::create([
                'customer_id' => $customer->id,
                'account_no' => $data['account_no'],
                'invoice_number' => $data['invoice_number'] ?? $this->generateInvoiceNumber(),
                'prepared_by' => $data['prepared_by'] ?? optional($request->user())->name,
                'previous_reading' => $data['previous_reading'],
                'current_reading' => $data['current_reading'],
                'consumption_cu_m' => $data['consumption_cu_m'],
                'base_rate' => $data['base_rate'] ?? 25,
                'maintenance_charge' => $data['maintenance_charge'] ?? 0,
                'advance_payment' => $data['advance_payment'] ?? 0,
                'overdue_penalty' => $data['overdue_penalty'] ?? 0,
                'vat' => $data['vat'] ?? 0,
                'total_amount' => $data['total_amount'],
                'bill_status' => 'Pending',
                'notes' => null,
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
                'due_date' => $dueDate,
                'issued_at' => $data['issued_at'] ? Carbon::parse($data['issued_at']) : Carbon::now(),
            ]);

            // Update customer's previous reading
            $customer->update(['previous_reading' => $data['current_reading']]);

            // Advance payment is now just a field in the billing record
            
            return response()->json([
                'ok' => true,
                'billing_record_id' => $billingRecord->id,
                'invoice_number' => $billingRecord->invoice_number,
                'message' => 'Bill saved successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function status(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_no' => ['required','string','max:50'],
            'target_date' => ['nullable','date'],
        ]);

        $accountNo = trim($data['account_no']);
        $customer = Customer::where('account_no', $accountNo)->first();

        if (!$customer) {
            return response()->json([
                'ok' => false,
                'error' => 'Customer not found for this account number.'
            ]);
        }

        $targetCycleDate = isset($data['target_date'])
            ? Carbon::parse($data['target_date'])
            : Carbon::now();

        $monthStart = $targetCycleDate->copy()->startOfMonth();
        $monthEnd = $targetCycleDate->copy()->endOfMonth();
        $monthLabel = $monthStart->format('F Y');

        $cycleConstraint = function (Builder $query) use ($monthStart, $monthEnd) {
            $query->where(function ($cycle) use ($monthStart, $monthEnd) {
                $cycle->whereNotNull('date_to')->whereBetween('date_to', [$monthStart, $monthEnd])
                    ->orWhere(function ($fallback) use ($monthStart, $monthEnd) {
                        $fallback->whereNull('date_to')
                            ->whereNotNull('issued_at')
                            ->whereBetween('issued_at', [$monthStart, $monthEnd]);
                    })
                    ->orWhere(function ($fallback) use ($monthStart, $monthEnd) {
                        $fallback->whereNull('date_to')
                            ->whereNull('issued_at')
                            ->whereBetween('created_at', [$monthStart, $monthEnd]);
                    });
            });
        };

        $pendingStatuses = ['Pending','Outstanding Payment','Overdue','Notice of Disconnection','Disconnected'];

        $cycleInvoice = BillingRecord::withTrashed()
            ->where('account_no', $accountNo)
            ->where(function ($query) use ($cycleConstraint) {
                $cycleConstraint($query);
            })
            ->orderByDesc('issued_at')
            ->orderByDesc('created_at')
            ->first();

        $unpaidForCycle = BillingRecord::where('account_no', $accountNo)
            ->where(function ($query) use ($cycleConstraint) {
                $cycleConstraint($query);
            })
            ->where(function ($query) use ($pendingStatuses) {
                $query->whereNull('bill_status')->orWhereIn('bill_status', $pendingStatuses);
            })
            ->first();

        $latestInvoice = BillingRecord::withTrashed()
            ->where('account_no', $accountNo)
            ->orderByDesc('issued_at')
            ->orderByDesc('created_at')
            ->first();

        $latestInvoiceData = null;
        if ($latestInvoice) {
            $latestInvoiceData = [
                'invoice_number' => $latestInvoice->invoice_number ?? ('INV-' . str_pad($latestInvoice->id, 4, '0', STR_PAD_LEFT)),
                'bill_status' => $latestInvoice->bill_status,
                'is_generated' => (bool) ($latestInvoice->is_generated ?? false),
                'issued_at' => optional($latestInvoice->issued_at ?? $latestInvoice->created_at)->format('M d, Y'),
                'deleted_at' => optional($latestInvoice->deleted_at)->toAtomString(),
                'amount' => $latestInvoice->total_amount,
            ];
        }

        $cycleInvoiceData = null;
        if ($cycleInvoice) {
            $cycleInvoiceData = [
                'invoice_number' => $cycleInvoice->invoice_number ?? ('INV-' . str_pad($cycleInvoice->id, 4, '0', STR_PAD_LEFT)),
                'bill_status' => $cycleInvoice->bill_status,
                'is_generated' => (bool) ($cycleInvoice->is_generated ?? false),
                'issued_at' => optional($cycleInvoice->issued_at ?? $cycleInvoice->created_at)->format('M d, Y'),
                'deleted_at' => optional($cycleInvoice->deleted_at)->toAtomString(),
                'amount' => $cycleInvoice->total_amount,
            ];
        }

        $messages = [];
        if ($unpaidForCycle && $cycleInvoiceData) {
            $messages[] = sprintf(
                'Invoice %s for %s is still marked %s. Collect payment before issuing another bill for this cycle.',
                $cycleInvoiceData['invoice_number'],
                $monthLabel,
                $cycleInvoiceData['bill_status'] ?? 'Pending'
            );
        } elseif ($cycleInvoiceData) {
            $messages[] = sprintf(
                'Invoice %s already exists for %s (status: %s).',
                $cycleInvoiceData['invoice_number'],
                $monthLabel,
                $cycleInvoiceData['bill_status'] ?? 'Pending'
            );
        } elseif ($latestInvoiceData) {
            $messages[] = sprintf(
                'Latest invoice %s was issued on %s (status: %s).',
                $latestInvoiceData['invoice_number'],
                $latestInvoiceData['issued_at'],
                $latestInvoiceData['bill_status'] ?? 'Pending'
            );
        }

        return response()->json([
            'ok' => true,
            'customer' => [
                'id' => $customer->id,
                'account_no' => $customer->account_no,
                'name' => $customer->name,
                'address' => $customer->address,
            ],
            'month_label' => $monthLabel,
            'has_unpaid_for_cycle' => (bool) $unpaidForCycle,
            'cycle_invoice' => $cycleInvoiceData,
            'latest_invoice' => $latestInvoiceData,
            'messages' => $messages,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd');
        $suffix = strtoupper(str_pad(dechex(random_int(0, 0xFFFF)), 4, '0', STR_PAD_LEFT));

        $candidate = $prefix . '-' . $suffix;
        $attempts = 0;

        while (BillingRecord::where('invoice_number', $candidate)->exists() && $attempts < 5) {
            $suffix = strtoupper(str_pad(dechex(random_int(0, 0xFFFF)), 4, '0', STR_PAD_LEFT));
            $candidate = $prefix . '-' . $suffix;
            $attempts++;
        }

        return $candidate;
    }

    public function getPaymentHistory(Request $request): JsonResponse
    {
        $request->validate([
            'account_no' => ['required', 'string', 'max:50']
        ]);

        try {
            $paymentService = new PaymentService();
            $result = $paymentService->getCustomerPaymentHistory($request->account_no);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}


