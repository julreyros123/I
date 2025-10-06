<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BillingRecord;
use App\Models\Customer;

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
        $maintenance = (float)($data['maintenance_charge'] ?? 0);
        $service = (float)($data['service_fee'] ?? 25); // default 25 but editable
        $baseRate = (float)($data['base_rate'] ?? 25); // per m³ default

        $used = max(0, $current - $previous);
        $subtotal = ($used * $baseRate) + $maintenance + $service;
        $vat = $subtotal * 0.12;
        $total = $subtotal + $vat;

        $peso = fn(float $n) => '₱' . number_format($n, 2);

        return response()->json([
            'consumption_cu_m' => round($used, 2),
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
            'formatted' => [
                'vat' => $peso($vat),
                'total' => $peso($total),
                'service_fee' => $peso($service),
                'maintenance_charge' => $peso($maintenance),
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
            'date_from' => ['nullable','date'],
            'date_to' => ['nullable','date'],
        ]);

        $customer = Customer::where('account_no', $data['account_no'])->first();

        $record = BillingRecord::create([
            'customer_id' => $customer?->id,
            'account_no' => $data['account_no'],
            'previous_reading' => (float)($data['previous_reading'] ?? 0),
            'current_reading' => (float)($data['current_reading'] ?? 0),
            'consumption_cu_m' => (float)($data['consumption_cu_m'] ?? 0),
            'base_rate' => (float)($data['base_rate'] ?? 25),
            'maintenance_charge' => (float)($data['maintenance_charge'] ?? 0),
            'service_fee' => (float)($data['service_fee'] ?? 25),
            'vat' => (float)($data['vat'] ?? 0),
            'total_amount' => (float)($data['total_amount'] ?? 0),
            'date_from' => $data['date_from'] ?? null,
            'date_to' => $data['date_to'] ?? null,
        ]);

        // Optionally update customer's previous reading to current
        if ($customer) {
            $customer->previous_reading = (float)($data['current_reading'] ?? $customer->previous_reading);
            $customer->save();
        }

        return response()->json(['ok' => true, 'id' => $record->id]);
    }
}


