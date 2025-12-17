<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\BillingRecord;
use App\Models\PaymentRecord;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Process a payment and handle overpayments as credits
     */
    public function processPayment(array $paymentData): array
    {
        return DB::transaction(function () use ($paymentData) {
            $customer = Customer::where('account_no', $paymentData['account_no'])->first();
            
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            $billAmount = (float) $paymentData['total_amount'];
            $amountPaid = (float) $paymentData['amount_paid'] ?? $billAmount;
            $overpayment = 0;
            $paymentStatus = 'paid';

            // Calculate overpayment
            if ($amountPaid > $billAmount) {
                $overpayment = $amountPaid - $billAmount;
                $paymentStatus = 'overpaid';
            }

            // Create billing record (existing logic)
            $billingRecord = BillingRecord::create([
                'customer_id' => $customer->id,
                'account_no' => $paymentData['account_no'],
                'previous_reading' => (float)($paymentData['previous_reading'] ?? 0),
                'current_reading' => (float)($paymentData['current_reading'] ?? 0),
                'consumption_cu_m' => (float)($paymentData['consumption_cu_m'] ?? 0),
                'base_rate' => (float)($paymentData['base_rate'] ?? 25),
                'maintenance_charge' => 0.0,
                'vat' => 0.0,
                'total_amount' => $billAmount,
                'date_from' => $paymentData['date_from'] ?? null,
                'date_to' => $paymentData['date_to'] ?? null,
            ]);

            // Create payment record
            $paymentRecord = PaymentRecord::create([
                'customer_id' => $customer->id,
                'billing_record_id' => $billingRecord->id,
                'account_no' => $paymentData['account_no'],
                'bill_amount' => $billAmount,
                'amount_paid' => $amountPaid,
                'overpayment' => $overpayment,
                'credit_applied' => 0,
                'payment_status' => $paymentStatus,
                'notes' => $overpayment > 0 ? "₱" . number_format($overpayment, 2) . " overpayment" : 'Standard payment',
            ]);

            // Update customer's previous reading
            if (isset($paymentData['current_reading'])) {
                $customer->previous_reading = (float) $paymentData['current_reading'];
                $customer->save();
            }

            return [
                'success' => true,
                'billing_record_id' => $billingRecord->id,
                'payment_record_id' => $paymentRecord->id,
                'payment_status' => $paymentStatus,
                'credit_applied' => 0,
                'overpayment' => $overpayment,
                'remaining_credit' => 0,
                'message' => $this->generatePaymentMessage($paymentStatus, 0, $overpayment),
            ];
        });
    }

    /**
     * Get customer's payment history with credit information
     */
    public function getCustomerPaymentHistory(string $accountNo): array
    {
        $customer = Customer::where('account_no', $accountNo)->first();
        
        if (!$customer) {
            return ['error' => 'Customer not found'];
        }

        $payments = PaymentRecord::with('billingRecord')
            ->where('account_no', $accountNo)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => $payment->created_at->format('Y-m-d'),
                    'bill_amount' => $payment->bill_amount,
                    'amount_paid' => $payment->amount_paid,
                    'credit_applied' => $payment->credit_applied,
                    'overpayment' => $payment->overpayment,
                    'payment_status' => $payment->payment_status,
                    'notes' => $payment->notes,
                    'consumption' => $payment->billingRecord->consumption_cu_m ?? 0,
                ];
            });

        return [
            'customer' => [
                'account_no' => $customer->account_no,
                'name' => $customer->name,
                'address' => $customer->address,
            ],
            'payments' => $payments,
        ];
    }

    /**
     * Generate payment notes based on overpayment
     */
    private function generatePaymentNotes(float $overpayment): string
    {
        if ($overpayment > 0) {
            return "₱" . number_format($overpayment, 2) . " overpayment";
        }
        
        return 'Standard payment';
    }

    /**
     * Process customer payment for existing bills
     */
    public function processCustomerPayment(array $paymentData): array
    {
        return DB::transaction(function () use ($paymentData) {
            $accountNo = $paymentData['account_no'];
            $amountPaid = (float) ($paymentData['amount_paid'] ?? 0);
            $latestOnly = (bool) ($paymentData['latest_only'] ?? false);
            $selectedBillIds = collect($paymentData['bill_ids'] ?? [])
                ->filter(fn ($id) => $id !== null)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $customer = Customer::where('account_no', $accountNo)->first();

            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            if ($amountPaid <= 0) {
                throw new \RuntimeException('Amount paid must be greater than zero.');
            }

            $baseQuery = BillingRecord::where('account_no', $accountNo)
                ->where(function ($qb) {
                    $qb->whereNull('bill_status')
                        ->orWhere('bill_status', '!=', 'Paid');
                });

            if ($latestOnly) {
                $bills = (clone $baseQuery)
                    ->orderByDesc('created_at')
                    ->limit(1)
                    ->lockForUpdate()
                    ->get();
            } elseif (!empty($selectedBillIds)) {
                $bills = (clone $baseQuery)
                    ->whereIn('id', $selectedBillIds)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();
            } else {
                $bills = (clone $baseQuery)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();
            }

            if ($bills->isEmpty()) {
                throw new \RuntimeException('No unpaid bills available for this account.');
            }

            $billsOutstandingAmounts = $bills->map(function ($bill) {
                $alreadyPaid = (float) $bill->paymentRecords()->sum('amount_paid');
                return max(0, (float) $bill->total_amount - $alreadyPaid);
            });

            $totalDue = $billsOutstandingAmounts->sum();
            if ($totalDue <= 0) {
                throw new \RuntimeException('The selected bills are already paid.');
            }

            if ($amountPaid + 0.01 < $totalDue) {
                throw new \RuntimeException('Amount paid is insufficient to cover the selected bills.');
            }

            $remainingAmount = $amountPaid;
            $paymentRecords = [];
            $processedBillIds = [];
            $billsPaid = 0;

            foreach ($bills as $bill) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $billAmount = (float) $bill->total_amount;
                $alreadyPaid = (float) $bill->paymentRecords()->sum('amount_paid');
                if ($alreadyPaid + 0.01 >= $billAmount) {
                    continue; // this bill is already settled
                }

                $remainingForBill = max(0, $billAmount - $alreadyPaid);
                if ($remainingForBill <= 0) {
                    continue;
                }

                $amountForBill = min($remainingAmount, $remainingForBill);

                $record = PaymentRecord::create([
                    'customer_id' => $customer->id,
                    'billing_record_id' => $bill->id,
                    'account_no' => $accountNo,
                    'bill_amount' => $billAmount,
                    'amount_paid' => $amountForBill,
                    'overpayment' => 0,
                    'credit_applied' => 0,
                    'payment_status' => 'partial',
                    'notes' => 'Partial payment',
                ]);

                $remainingAmount -= $amountForBill;
                $paymentRecords[] = $record;
                $processedBillIds[] = $bill->id;

                $newPaidTotal = $alreadyPaid + $amountForBill;
                if ($newPaidTotal + 0.01 >= $billAmount) {
                    $bill->bill_status = 'Paid';
                    $billsPaid++;
                    $record->payment_status = 'paid';
                    $record->notes = 'Standard payment';
                    $record->save();
                } else {
                    $bill->bill_status = 'Outstanding Payment';
                }

                $bill->save();
            }

            $overpayment = max(0, $remainingAmount);
            if (!empty($paymentRecords)) {
                $lastRecord = end($paymentRecords);
                if ($overpayment > 0) {
                    $lastRecord->overpayment = $overpayment;
                    $lastRecord->payment_status = 'overpaid';
                    $lastRecord->notes = $this->generatePaymentNotes($overpayment);
                }
                $lastRecord->save();
            }

            // Re-sync bill statuses to reflect their latest outstanding balance
            if (!empty($processedBillIds)) {
                BillingRecord::whereIn('id', $processedBillIds)
                    ->withSum('paymentRecords as amount_paid_sum', 'amount_paid')
                    ->get()
                    ->each(function (BillingRecord $bill) {
                        $paid = (float) ($bill->amount_paid_sum ?? 0);
                        $outstanding = max(0, (float) $bill->total_amount - $paid);

                        if ($outstanding <= 0.01) {
                            $bill->bill_status = 'Paid';
                        } elseif ($paid > 0 && $bill->bill_status === 'Pending') {
                            $bill->bill_status = 'Outstanding Payment';
                        }

                        $bill->save();
                    });
            }

            $totalOutstanding = BillingRecord::where('account_no', $accountNo)
                ->withSum('paymentRecords as amount_paid_sum', 'amount_paid')
                ->get()
                ->sum(function ($bill) {
                    $paid = (float) ($bill->amount_paid_sum ?? 0);
                    return max(0, (float) $bill->total_amount - $paid);
                });

            return [
                'success' => true,
                'payment_record_id' => $paymentRecords[0]->id,
                'message' => $this->generateCustomerPaymentMessage(0, $overpayment, $billsPaid),
                'payment_details' => [
                    'total_outstanding' => $totalOutstanding,
                    'amount_paid' => $amountPaid,
                    'credit_applied' => 0,
                    'overpayment' => $overpayment,
                    'bills_paid' => $billsPaid,
                    'remaining_credit' => 0,
                ],
            ];
        });
    }

    /**
     * Generate user-friendly payment message
     */
    private function generatePaymentMessage(string $status, float $creditApplied, float $overpayment): string
    {
        $message = "Payment processed successfully!";
        
        if ($overpayment > 0) {
            $message .= " ₱" . number_format($overpayment, 2) . " overpayment.";
        }
        
        return $message;
    }

    /**
     * Generate customer payment message
     */
    private function generateCustomerPaymentMessage(float $creditApplied, float $overpayment, int $billsPaid): string
    {
        $message = "Payment processed successfully! Paid {$billsPaid} bill(s).";
        
        if ($overpayment > 0) {
            $message .= " ₱" . number_format($overpayment, 2) . " overpayment.";
        }
        
        return $message;
    }
}

