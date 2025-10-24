<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BillingRecord;
use App\Models\Customer;
use App\Services\PaymentService;

class BillingController extends Controller
{
    public function compute(Request $request): JsonResponse
    {
        $data = $request->validate([
            'previous_reading' => ['nullable','numeric','min:0'],
            'current_reading' => ['nullable','numeric','min:0'],
            'maintenance_charge' => ['nullable','numeric','min:0'],
            'service_fee' => ['nullable','numeric','min:0'],
            'base_rate' => ['nullable','numeric','min:0'],
        ]);

        $previous = (float)($data['previous_reading'] ?? 0);
        $current = (float)($data['current_reading'] ?? 0);
        // Fees removed per requirement
        $maintenance = 0.0;
        $service = 0.0;
        $baseRate = (float)($data['base_rate'] ?? 25); // per m³ default

        $used = max(0, $current - $previous);
        $subtotal = ($used * $baseRate);
        // New rule: No VAT. Total = subtotal + charges
        $vat = 0.0;
        $total = $subtotal + 0; // total will be adjusted below with charges
        // Move charges outside subtotal if desired
        $total = ($used * $baseRate);

        $peso = fn(float $n) => '₱' . number_format($n, 2);

        return response()->json([
            'consumption_cu_m' => round($used, 2),
            'subtotal' => ($used * $baseRate),
            'vat' => $vat,
            'total' => $total,
            'formatted' => [
                'total' => $peso($total),
                'subtotal' => $peso(($used * $baseRate)),
                'service_fee' => $peso(0.0),
                'maintenance_charge' => $peso(0.0),
            ],
        ]);
    }
    
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_no' => ['required','string','max:50'],
            'previous_reading' => ['nullable','numeric','min:0'],
            'current_reading' => ['nullable','numeric','min:0'],
            'consumption_cu_m' => ['nullable','numeric','min:0'],
            'base_rate' => ['nullable','numeric','min:0'],
            'maintenance_charge' => ['nullable','numeric','min:0'],
            'service_fee' => ['nullable','numeric','min:0'],
            'vat' => ['nullable','numeric','min:0'],
            'total_amount' => ['nullable','numeric','min:0'],
            'amount_paid' => ['nullable','numeric','min:0'], // New field for actual payment amount
            'date_from' => ['nullable','date'],
            'date_to' => ['nullable','date'],
        ]);

        try {
            $paymentService = new PaymentService();
            $result = $paymentService->processPayment($data);
            
            return response()->json([
                'ok' => true,
                'billing_record_id' => $result['billing_record_id'],
                'payment_record_id' => $result['payment_record_id'],
                'message' => $result['message'],
                'payment_status' => $result['payment_status'],
                'credit_applied' => $result['credit_applied'],
                'overpayment' => $result['overpayment'],
                'remaining_credit' => $result['remaining_credit'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 400);
        }
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


