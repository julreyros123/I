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
            $customer = Customer::where('account_no', $paymentData['account_no'])->first();
            
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            // Get all unpaid bills for this customer
            $unpaidBills = BillingRecord::where('account_no', $paymentData['account_no'])
                ->whereDoesntHave('paymentRecords')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($unpaidBills->isEmpty()) {
                throw new \Exception('No outstanding bills found for this customer');
            }

            // Check if any bills are already paid (prevent duplicate payments)
            $paidBills = BillingRecord::where('account_no', $paymentData['account_no'])
                ->whereHas('paymentRecords', function($query) {
                    $query->where('payment_status', 'paid');
                })
                ->get();

            if ($paidBills->isNotEmpty()) {
                throw new \Exception('Some bills for this customer are already paid. Cannot process duplicate payment.');
            }

            $amountPaid = (float) $paymentData['amount_paid'];
            $totalOutstanding = $unpaidBills->sum('total_amount');
            $remainingAmount = $amountPaid;
            $paymentRecords = [];
            $overpayment = 0;

            // Process payments for each bill
            foreach ($unpaidBills as $bill) {
                if ($remainingAmount <= 0) break;

                $billAmount = $bill->total_amount;
                $paymentForThisBill = min($remainingAmount, $billAmount);
                $paymentStatus = 'paid';

                // Check if this bill is fully paid
                if ($paymentForThisBill < $billAmount) {
                    $paymentStatus = 'partial';
                }

                // Create payment record for this bill
                $paymentRecord = PaymentRecord::create([
                    'customer_id' => $customer->id,
                    'billing_record_id' => $bill->id,
                    'account_no' => $paymentData['account_no'],
                    'bill_amount' => $billAmount,
                    'amount_paid' => $paymentForThisBill,
                    'overpayment' => 0,
                    'credit_applied' => 0,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $paymentData['payment_method'] ?? 'cash',
                    'reference_number' => $paymentData['reference_number'] ?? null,
                    'notes' => $paymentData['notes'] ?? null,
                ]);

                // Update bill status to Paid if fully paid
                if ($paymentForThisBill >= $billAmount) {
                    $bill->update(['bill_status' => 'Paid']);
                }

                $paymentRecords[] = $paymentRecord;
                $remainingAmount -= $paymentForThisBill;
            }

            // Handle overpayment
            if ($remainingAmount > 0) {
                $overpayment = $remainingAmount;
            }

            return [
                'success' => true,
                'payment_record_id' => $paymentRecords[0]->id, // Return first payment record ID
                'message' => $this->generateCustomerPaymentMessage(0, $overpayment, count($paymentRecords)),
                'payment_details' => [
                    'total_outstanding' => $totalOutstanding,
                    'amount_paid' => $amountPaid,
                    'credit_applied' => 0,
                    'overpayment' => $overpayment,
                    'bills_paid' => count($paymentRecords),
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

