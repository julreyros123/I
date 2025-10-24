<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BillingRecord;
use App\Models\Customer;

class RecordController extends Controller
{
    public function billing(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $records = BillingRecord::with('customer')
            ->when($q, function($query) use ($q) {
                $query->where('account_no', 'like', "%{$q}%")
                      ->orWhereHas('customer', function($sub) use ($q){
                          $sub->where('name', 'like', "%{$q}%");
                      });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('records.billing', compact('records', 'q'));
    }

    public function payments()
    {
        $q = trim((string) request()->get('q', ''));
        $payments = BillingRecord::with('customer')
            ->when($q, function($query) use ($q) {
                $query->where('account_no', 'like', "%{$q}%")
                      ->orWhereHas('customer', function($sub) use ($q){
                          $sub->where('name', 'like', "%{$q}%");
                      });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('records.payments', [
            'payments' => $payments,
            'q' => $q,
        ]);
    }

    public function reports()
    {
        return view('records.reports'); // Points to resources/views/records/reports.blade.php
    }

    public function history()
    {
        return view('records.history'); // Points to resources/views/records/history.blade.php
    }

    public function historyApi(Request $request)
    {
        $request->validate(['account_no' => ['required','string','max:50']]);
        $acct = $request->string('account_no')->toString();
        $rows = BillingRecord::with('customer')
            ->where('account_no', $acct)
            ->orderByDesc('created_at')
            ->take(100)
            ->get()
            ->map(function($r){
                return [
                    'date' => optional($r->created_at)->format('Y-m-d'),
                    'previous' => (float) $r->previous_reading,
                    'current' => (float) $r->current_reading,
                    'maintenance' => (float) $r->maintenance_charge,
                    'service_fee' => (float) $r->service_fee,
                    'amount_paid' => (float) $r->total_amount,
                    'consumption' => (float) $r->consumption_cu_m,
                    'name' => $r->customer->name ?? null,
                    'address' => $r->customer->address ?? null,
                ];
            });
        return response()->json(['ok' => true, 'history' => $rows]);
    }
}