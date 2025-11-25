<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillEventController extends Controller
{
    public function generate(Request $request)
    {
        $data = $request->validate([
            'bill_ids' => 'required|array|min:1',
            'bill_ids.*' => 'integer|min:1',
            'note' => 'nullable|string'
        ]);
        $userId = Auth::id();

        DB::transaction(function () use ($data, $userId) {
            $bills = Bill::whereIn('id', $data['bill_ids'])->get();
            foreach ($bills as $bill) {
                $bill->status = 'generated';
                $bill->generated_at = now();
                $bill->staff_id = $userId;
                $bill->save();
                BillEvent::create([
                    'bill_id' => $bill->id,
                    'staff_id' => $userId,
                    'type' => 'generated',
                    'note' => $data['note'] ?? null,
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function deliver(Request $request)
    {
        $data = $request->validate([
            'bill_id' => 'required|integer|min:1',
            'delivered_at' => 'nullable|date',
            'note' => 'nullable|string'
        ]);
        $userId = Auth::id();

        $bill = Bill::findOrFail($data['bill_id']);
        $bill->status = 'delivered';
        $bill->delivered_at = $data['delivered_at'] ?? now();
        $bill->staff_id = $userId;
        $bill->save();

        BillEvent::create([
            'bill_id' => $bill->id,
            'staff_id' => $userId,
            'type' => 'delivered',
            'note' => $data['note'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }
}
