<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BillingRecord;
use App\Models\Customer;
use App\Models\PaymentRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use App\Models\ActivityLog;

class RecordController extends Controller
{
    public function billing(Request $request)
    {
        $this->applyOverdueRules();
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status', '');
        $generated = $request->get('generated', ''); // '' | '0' | '1'
        $issueFrom = $request->filled('issue_from') ? Carbon::parse($request->get('issue_from'))->startOfDay() : null;
        $issueTo = $request->filled('issue_to') ? Carbon::parse($request->get('issue_to'))->endOfDay() : null;
        
        $records = BillingRecord::with('customer', 'paymentRecords')
            ->when($q, function($query) use ($q) {
                $query->where('account_no', 'like', "%{$q}%")
                      ->orWhereHas('customer', function($sub) use ($q){
                          $sub->where('name', 'like', "%{$q}%")
                              ->orWhere('address', 'like', "%{$q}%");
                      });
            })
            ->when($status, function($query) use ($status) {
                $query->where('bill_status', $status);
            })
            ->when($generated !== '' && \Schema::hasColumn('billing_records','is_generated'), function($query) use ($generated){
                $query->where('is_generated', $generated === '1');
            })
            ->when($issueFrom, function($query) use ($issueFrom) {
                if (Schema::hasColumn('billing_records', 'issued_at')) {
                    $query->where(function($inner) use ($issueFrom) {
                        $inner->whereNotNull('issued_at')->where('issued_at', '>=', $issueFrom)
                            ->orWhere(function($fallback) use ($issueFrom) {
                                $fallback->whereNull('issued_at')->where('created_at', '>=', $issueFrom);
                            });
                    });
                } else {
                    $query->where('created_at', '>=', $issueFrom);
                }
            })
            ->when($issueTo, function($query) use ($issueTo) {
                if (Schema::hasColumn('billing_records', 'issued_at')) {
                    $query->where(function($inner) use ($issueTo) {
                        $inner->whereNotNull('issued_at')->where('issued_at', '<=', $issueTo)
                            ->orWhere(function($fallback) use ($issueTo) {
                                $fallback->whereNull('issued_at')->where('created_at', '<=', $issueTo);
                            });
                    });
                } else {
                    $query->where('created_at', '<=', $issueTo);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Get statistics (generation-focused) with guard if column not migrated yet
        if (Schema::hasColumn('billing_records', 'is_generated')) {
            $stats = [
                'pending_generate' => BillingRecord::where('is_generated', false)->count(),
                'generated' => BillingRecord::where('is_generated', true)->count(),
            ];
        } else {
            $stats = [
                'pending_generate' => BillingRecord::count(),
                'generated' => 0,
            ];
        }

        return view('records.billing', compact('records', 'q', 'status', 'stats', 'generated', 'issueFrom', 'issueTo'));
    }

    public function archive($id): RedirectResponse
    {
        $record = BillingRecord::findOrFail($id);

        // Only allow archiving fully paid bills
        if ($record->bill_status !== 'Paid') {
            return back()->with('error', 'Only fully paid bills can be archived.');
        }

        $record->delete();

        ActivityLog::create([
            'user_id' => optional(request()->user())->id,
            'module' => 'Billing',
            'action' => 'BILL_ARCHIVED',
            'description' => sprintf('Archived bill #%d for account %s', $record->id, $record->account_no),
            'target_type' => BillingRecord::class,
            'target_id' => $record->id,
            'meta' => [
                'account_no' => $record->account_no,
                'total_amount' => $record->total_amount,
                'bill_status' => $record->bill_status,
            ],
        ]);

        return back()->with('status', 'Record archived');
    }

    public function restore($id): RedirectResponse
    {
        $record = BillingRecord::onlyTrashed()->findOrFail($id);
        $record->restore();

        ActivityLog::create([
            'user_id' => optional(request()->user())->id,
            'module' => 'Billing',
            'action' => 'BILL_RESTORED',
            'description' => sprintf('Restored bill #%d for account %s', $record->id, $record->account_no),
            'target_type' => BillingRecord::class,
            'target_id' => $record->id,
            'meta' => [
                'account_no' => $record->account_no,
                'total_amount' => $record->total_amount,
                'bill_status' => $record->bill_status,
            ],
        ]);

        return back()->with('status', 'Record restored');
    }

    public function forceDelete($id): RedirectResponse
    {
        $record = BillingRecord::onlyTrashed()->findOrFail($id);
        $record->forceDelete();
        return back()->with('status', 'Record permanently deleted');
    }

    public function billingManagement(Request $request)
    {
        $this->applyOverdueRules();
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status', '');
        $generated = $request->get('generated', ''); // '' | '0' | '1'
        
        $records = BillingRecord::with('customer', 'paymentRecords')
            ->when($q, function($query) use ($q) {
                $query->where('account_no', 'like', "%{$q}%")
                      ->orWhereHas('customer', function($sub) use ($q){
                          $sub->where('name', 'like', "%{$q}%")
                              ->orWhere('address', 'like', "%{$q}%");
                      });
            })
            ->when($status, function($query) use ($status) {
                $query->where('bill_status', $status);
            })
            ->when($generated !== '' && \Schema::hasColumn('billing_records','is_generated'), function($query) use ($generated){
                $query->where('is_generated', $generated === '1');
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Get statistics (generation-focused) with guard if column not migrated yet
        if (Schema::hasColumn('billing_records', 'is_generated')) {
            $stats = [
                'pending_generate' => BillingRecord::where('is_generated', false)->count(),
                'generated' => BillingRecord::where('is_generated', true)->count(),
            ];
        } else {
            // Fallback: treat all as pending when the is_generated column is not yet available
            $stats = [
                'pending_generate' => BillingRecord::count(),
                'generated' => 0,
            ];
        }

        // Quick totals cards (no schema changes)
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();
        $startOfMonth = Carbon::now()->startOfMonth();
        $now = Carbon::now();

        $collectionsToday = (float) PaymentRecord::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('amount_paid');
        $collectionsMonth = (float) PaymentRecord::whereBetween('created_at', [$startOfMonth, $now])->sum('amount_paid');
        $overdueQuery = BillingRecord::whereIn('bill_status', ['Overdue', 'Notice of Disconnection', 'Disconnected'])
            ->whereDoesntHave('paymentRecords');
        $overdueCount = (int) $overdueQuery->count();
        $overdueAmount = (float) $overdueQuery->sum('total_amount');
        $outstandingAmount = (float) BillingRecord::where('bill_status', '!=', 'Paid')->sum('total_amount');

        $quick = [
            'collections_today' => $collectionsToday,
            'collections_month' => $collectionsMonth,
            'overdue_count' => $overdueCount,
            'overdue_amount' => $overdueAmount,
            'outstanding_amount' => $outstandingAmount,
        ];

        // Pending list for generation (limit to show in UI)
        $pending = BillingRecord::with('customer')
            ->when(Schema::hasColumn('billing_records','is_generated'), function($qb){ $qb->where('is_generated', false); })
            ->where(function($qb){ $qb->whereNull('bill_status')->orWhere('bill_status','!=','Paid'); })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id','account_no','total_amount','date_from','date_to','created_at']);

        return view('records.billing-management', compact('records', 'q', 'status', 'stats', 'generated', 'quick', 'pending'));
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

    public function archivedBilling(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $records = BillingRecord::onlyTrashed()
            ->with('customer')
            ->when($q, function($query) use ($q) {
                $query->where('account_no', 'like', "%{$q}%")
                      ->orWhereHas('customer', function($sub) use ($q){
                          $sub->where('name', 'like', "%{$q}%")
                              ->orWhere('address', 'like', "%{$q}%");
                      });
            })
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('records.archived-billing', compact('records', 'q'));
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

    public function billingStats(Request $request)
    {
        if (Schema::hasColumn('billing_records', 'is_generated')) {
            $stats = [
                'pending_generate' => BillingRecord::where('is_generated', false)->count(),
                'generated' => BillingRecord::where('is_generated', true)->count(),
            ];
        } else {
            $stats = [ 'pending_generate' => BillingRecord::count(), 'generated' => 0 ];
        }
        return response()->json(['ok' => true] + $stats);
    }

    public function generateBill($id)
    {
        $billingRecord = BillingRecord::with('customer')->findOrFail($id);
        
        return view('records.bill-details', compact('billingRecord'));
    }

    public function updateBillStatus(Request $request, $id)
    {
        $request->validate([
            'bill_status' => 'required|in:Outstanding Payment,Overdue,Paid,Notice of Disconnection,Disconnected',
            'notes' => 'nullable|string|max:500'
        ]);

        $billingRecord = BillingRecord::findOrFail($id);
        if ($billingRecord->is_generated) {
            return response()->json([
                'success' => false,
                'message' => 'This bill is locked and cannot be modified after generation.'
            ], 403);
        }
        $fromStatus = $billingRecord->bill_status;
        $billingRecord->update([
            'bill_status' => $request->bill_status,
            'notes' => $request->notes
        ]);

        ActivityLog::create([
            'user_id' => optional($request->user())->id,
            'module' => 'Billing',
            'action' => 'BILL_STATUS_UPDATED',
            'description' => sprintf(
                'Updated bill #%d for account %s from %s to %s',
                $billingRecord->id,
                $billingRecord->account_no,
                $fromStatus ?? 'N/A',
                $request->bill_status
            ),
            'target_type' => BillingRecord::class,
            'target_id' => $billingRecord->id,
            'meta' => [
                'account_no' => $billingRecord->account_no,
                'from_status' => $fromStatus,
                'to_status' => $request->bill_status,
                'notes' => $request->notes,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bill status updated successfully!'
        ]);
    }

    public function printBill($id)
    {
        $billingRecord = BillingRecord::with('customer')->findOrFail($id);

        $usageSeries = $this->getLastFiveMonthsUsage($billingRecord->account_no);
        return view('records.bill-print', compact('billingRecord', 'usageSeries'));
    }

    public function bulkGenerate(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required','array','min:1'],
            'ids.*' => ['integer','distinct']
        ]);

        $ids = $data['ids'];

        $finalIds = $ids;
        if (Schema::hasColumn('billing_records', 'is_generated')) {
            // Filter out already generated records only if column exists
            $records = BillingRecord::whereIn('id', $ids)->get();
            $toGenerate = $records->where('is_generated', false)->pluck('id')->all();

            if (!empty($toGenerate)) {
                BillingRecord::whereIn('id', $toGenerate)->update([
                    'is_generated' => true,
                    'generated_at' => now(),
                ]);
            }

            $finalIds = BillingRecord::whereIn('id', $ids)->pluck('id')->all();
        }

        $idsQuery = implode(',', $finalIds);
        return redirect()->route('records.billing.print-batch', ['ids' => $idsQuery]);
    }

    public function printBatch(Request $request)
    {
        $idsParam = (string) $request->get('ids', '');
        $ids = array_values(array_filter(array_map('intval', explode(',', $idsParam))));
        abort_if(empty($ids), 404);

        $records = BillingRecord::with('customer')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        // Pre-compute usage per account_no
        $usageByAccount = [];
        $accounts = $records->pluck('account_no')->unique()->values();
        foreach ($accounts as $acct) {
            $usageByAccount[$acct] = $this->getLastFiveMonthsUsage($acct);
        }

        return view('records.bill-print-batch', compact('records', 'usageByAccount'));
    }

    private function getLastFiveMonthsUsage(string $accountNo): array
    {
        $rows = BillingRecord::where('account_no', $accountNo)
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(function($r){
                return [
                    'label' => optional($r->created_at)->format('M Y'),
                    'value' => (float) ($r->consumption_cu_m ?? 0),
                ];
            })
            ->reverse()
            ->values()
            ->all();
        return $rows;
    }

    /**
     * Apply overdue rules to unpaid bills without payment records.
     * Flow: Outstanding Payment (new) -> Overdue (>=1 month past due) ->
     *       Notice of Disconnection (>=2 months past due) -> Disconnected (>=3 months past due)
     * Penalty: simple monthly rate applied to base total (excludes previously applied penalty).
     */
    private function applyOverdueRules(): void
    {
        $monthlyPenaltyRate = (float) env('BILLING_MONTHLY_PENALTY_RATE', 0.05); // default 5%/month
        $now = Carbon::now();

        $candidates = BillingRecord::with(['paymentRecords', 'customer'])
            ->whereNotNull('due_date')
            ->whereIn('bill_status', ['Outstanding Payment', 'Overdue', 'Notice of Disconnection'])
            ->get();

        foreach ($candidates as $bill) {
            // Skip bills that already have any payment recorded
            if ($bill->paymentRecords && $bill->paymentRecords->count() > 0) {
                continue;
            }
            if (!$bill->due_date) continue;
            if ($now->lessThan($bill->due_date)) continue;

            $days = $bill->due_date->diffInDays($now);
            $monthsOverdue = max(0, (int) floor($days / 30));

            // Base total excludes previously applied penalties
            $currentPenalty = (float) ($bill->overdue_penalty ?? 0);
            $baseTotal = max(0.0, (float) $bill->total_amount - $currentPenalty);

            $newPenalty = round($baseTotal * $monthlyPenaltyRate * $monthsOverdue, 2);

            $newStatus = 'Outstanding Payment';
            if ($monthsOverdue >= 3) {
                $newStatus = 'Disconnected';
                if ($bill->customer && $bill->customer->status !== 'Disconnected') {
                    $bill->customer->status = 'Disconnected';
                    $bill->customer->save();
                }
            } elseif ($monthsOverdue >= 2) {
                $newStatus = 'Notice of Disconnection';
            } elseif ($monthsOverdue >= 1) {
                $newStatus = 'Overdue';
            }

            $updates = [];
            if (abs($newPenalty - $currentPenalty) > 0.009) {
                $updates['overdue_penalty'] = $newPenalty;
                $updates['total_amount'] = round($baseTotal + $newPenalty, 2);
            }
            if ($newStatus !== $bill->bill_status) {
                $updates['bill_status'] = $newStatus;
            }
            if (!empty($updates)) {
                $bill->fill($updates);
                $bill->save();
            }
        }
    }
}