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
            $creditApplied = 0;
            $overpayment = 0;
            $paymentStatus = 'paid';

            // Check if customer has existing credit
            $availableCredit = $customer->credit_balance;
            
            // If customer has credit and amount paid is less than bill amount
            if ($availableCredit > 0 && $amountPaid < $billAmount) {
                $creditToApply = min($availableCredit, $billAmount - $amountPaid);
                $creditApplied = $creditToApply;
                $amountPaid += $creditToApply;
                
                // Update customer's credit balance
                $customer->credit_balance -= $creditToApply;
                $customer->save();
            }

            // Calculate overpayment
            if ($amountPaid > $billAmount) {
                $overpayment = $amountPaid - $billAmount;
                $paymentStatus = 'overpaid';
                
                // Add overpayment to customer's credit balance
                $customer->credit_balance += $overpayment;
                $customer->save();
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
                'service_fee' => 0.0,
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
                'credit_applied' => $creditApplied,
                'payment_status' => $paymentStatus,
                'notes' => $this->generatePaymentNotes($creditApplied, $overpayment),
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
                'credit_applied' => $creditApplied,
                'overpayment' => $overpayment,
                'remaining_credit' => $customer->credit_balance,
                'message' => $this->generatePaymentMessage($paymentStatus, $creditApplied, $overpayment),
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
                'credit_balance' => $customer->credit_balance,
                'formatted_credit_balance' => $customer->getFormattedCreditBalance(),
            ],
            'payments' => $payments,
        ];
    }

    /**
     * Generate payment notes based on credit and overpayment
     */
    private function generatePaymentNotes(float $creditApplied, float $overpayment): string
    {
        $notes = [];
        
        if ($creditApplied > 0) {
            $notes[] = "Applied ₱" . number_format($creditApplied, 2) . " credit from previous overpayment";
        }
        
        if ($overpayment > 0) {
            $notes[] = "₱" . number_format($overpayment, 2) . " overpayment added to account credit";
        }
        
        return implode('. ', $notes) ?: 'Standard payment';
    }

    /**
     * Generate user-friendly payment message
     */
    private function generatePaymentMessage(string $status, float $creditApplied, float $overpayment): string
    {
        $message = "Payment processed successfully!";
        
        if ($creditApplied > 0) {
            $message .= " Applied ₱" . number_format($creditApplied, 2) . " from your account credit.";
        }
        
        if ($overpayment > 0) {
            $message .= " ₱" . number_format($overpayment, 2) . " overpayment has been added to your account credit for future bills.";
        }
        
        return $message;
    }
}
