<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BillingRecord;
use App\Models\Customer;
use App\Services\PaymentService;
use Illuminate\Support\Carbon;

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


