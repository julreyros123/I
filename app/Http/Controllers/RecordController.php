<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\BillingRecord;
use App\Models\Customer;
use App\Models\PaymentRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecordController extends Controller
{
    public function billing(Request $request)
    {
        $this->applyOverdueRules();
        $q = trim((string) $request->get('q', ''));
        $statusOptions = ['Pending','Outstanding Payment','Overdue','Notice of Disconnection','Disconnected','Paid'];
        $statuses = array_values(array_unique(array_filter(array_map(function($value) use ($statusOptions) {
            $value = trim((string) $value);
            return in_array($value, $statusOptions, true) ? $value : null;
        }, Arr::wrap($request->input('statuses', []))))));
        $status = $request->get('status', '');
        if (empty($statuses) && $status && in_array($status, $statusOptions, true)) {
            $statuses = [$status];
        }
        $generated = $request->get('generated', ''); // '' | '0' | '1'
        $issueFrom = $request->filled('issue_from') ? Carbon::parse($request->get('issue_from'))->startOfDay() : null;
        $issueTo = $request->filled('issue_to') ? Carbon::parse($request->get('issue_to'))->endOfDay() : null;
        
        $activeCustomerConstraint = function ($customerQuery) {
            $customerQuery->where(function ($statusQuery) {
                $statusQuery->whereNull('status')
                    ->orWhere('status', '!=', 'Disconnected');
            });
        };

        $recordsQuery = BillingRecord::with('customer', 'paymentRecords')
            ->when(Schema::hasColumn('billing_records', 'deleted_at'), function ($query) {
                $query->whereNull('billing_records.deleted_at');
            })
            ->whereHas('customer', $activeCustomerConstraint)
            ->when($q, function($query) use ($q) {
                $query->where('account_no', 'like', "%{$q}%")
                      ->orWhereHas('customer', function($sub) use ($q){
                          $sub->where('name', 'like', "%{$q}%")
                              ->orWhere('address', 'like', "%{$q}%");
                      });
            })
            ->when($statuses, function($query) use ($statuses) {
                $query->whereIn('bill_status', $statuses);
            })
            ->when($generated !== '' && Schema::hasColumn('billing_records','is_generated'), function($query) use ($generated){
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
            });

        $records = (clone $recordsQuery)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // Get statistics (generation-focused) with guard if column not migrated yet
        if (Schema::hasColumn('billing_records', 'is_generated')) {
            $statsBase = BillingRecord::whereHas('customer', $activeCustomerConstraint);
            $stats = [
                'pending_generate' => (clone $statsBase)->where('is_generated', false)->count(),
                'generated' => (clone $statsBase)->where('is_generated', true)->count(),
            ];
        } else {
            $statsBase = BillingRecord::whereHas('customer', $activeCustomerConstraint);
            $stats = [
                'pending_generate' => (clone $statsBase)->count(),
                'generated' => 0,
            ];
        }

        return view('records.billing', compact('records', 'q', 'status', 'statuses', 'statusOptions', 'stats', 'generated', 'issueFrom', 'issueTo'));
    }

    public function bulkArchive(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'archive_ids' => ['required', 'array'],
            'archive_ids.*' => ['integer', 'distinct'],
        ]);

        $ids = array_unique(array_map('intval', $validated['archive_ids'] ?? []));

        if (empty($ids)) {
            return back()->with('error', 'Select at least one paid record to archive.');
        }

        $records = BillingRecord::whereIn('id', $ids)
            ->where('bill_status', 'Paid')
            ->get(['id', 'account_no', 'total_amount', 'bill_status']);

        if ($records->isEmpty()) {
            return back()->with('error', 'No eligible paid records found for archiving.');
        }

        DB::transaction(function () use ($records, $request) {
            foreach ($records as $record) {
                $record->delete();

                ActivityLog::create([
                    'user_id' => optional($request->user())->id,
                    'module' => 'Billing',
                    'action' => 'BILL_ARCHIVED',
                    'description' => sprintf('Bulk archived bill #%d for account %s', $record->id, $record->account_no),
                    'target_type' => BillingRecord::class,
                    'target_id' => $record->id,
                    'meta' => [
                        'account_no' => $record->account_no,
                        'total_amount' => $record->total_amount,
                        'bill_status' => $record->bill_status,
                        'bulk' => true,
                    ],
                ]);
            }
        });

        return back()->with('status', sprintf('%d record(s) archived.', $records->count()));
    }

    public function billingCustomerSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:100'],
        ]);

        $query = trim($validated['q']);

        if ($query === '') {
            return response()->json(['results' => []]);
        }

        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $query);

        $customers = Customer::query()
            ->where(function ($qb) use ($query, $normalized) {
                $qb->where('account_no', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%");

                if ($normalized !== '' && $normalized !== $query) {
                    $qb->orWhereRaw("REPLACE(REPLACE(account_no,'-',''),' ','') LIKE ?", ["%{$normalized}%"]);
                }
            })
            ->orderByRaw(
                "CASE WHEN account_no LIKE ? THEN 0 WHEN name LIKE ? THEN 1 ELSE 2 END, name ASC",
                ["{$query}%", "{$query}%"]
            )
            ->limit(8)
            ->get(['id', 'account_no', 'name', 'address', 'status']);

        if ($customers->isEmpty()) {
            return response()->json(['results' => []]);
        }

        $accounts = $customers->pluck('account_no')->filter()->values();

        $billingSnapshots = BillingRecord::whereIn('account_no', $accounts)
            ->withSum('paymentRecords as amount_paid_sum', 'amount_paid')
            ->get(['id', 'account_no', 'total_amount', 'bill_status']);

        $grouped = $billingSnapshots->groupBy('account_no')->map(function ($records) {
            $outstanding = 0.0;
            $unpaidCount = 0;

            foreach ($records as $record) {
                $paid = (float) ($record->amount_paid_sum ?? 0);
                $due = max(0.0, (float) $record->total_amount - $paid);
                if ($due > 0.009) {
                    $unpaidCount++;
                }
                $outstanding += $due;
            }

            return [
                'outstanding' => $outstanding,
                'unpaid_count' => $unpaidCount,
            ];
        });

        $results = $customers->map(function ($customer) use ($grouped) {
            $snapshot = $grouped->get($customer->account_no, ['outstanding' => 0.0, 'unpaid_count' => 0]);
            $outstanding = (float) ($snapshot['outstanding'] ?? 0.0);
            $unpaidCount = (int) ($snapshot['unpaid_count'] ?? 0);
            return [
                'id' => $customer->id,
                'account_no' => $customer->account_no,
                'name' => $customer->name,
                'address' => $customer->address,
                'status' => $customer->status,
                'unpaid_count' => $unpaidCount,
                'formatted_unpaid_total' => '₱' . number_format($outstanding, 2),
            ];
        })->values();

        return response()->json(['results' => $results]);
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
        $generated = $request->get('generated', '');

        $activeCustomerConstraint = function ($customerQuery) {
            $customerQuery->where(function ($statusQuery) {
                $statusQuery->whereNull('status')
                    ->orWhere('status', '!=', 'Disconnected');
            });
        };

        $recordsQuery = BillingRecord::with('customer', 'paymentRecords')
            ->whereHas('customer', $activeCustomerConstraint)
            ->when($q, function ($query) use ($q) {
                $query->where('account_no', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($sub) use ($q) {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('address', 'like', "%{$q}%");
                    });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('bill_status', $status);
            })
            ->when($generated !== '' && Schema::hasColumn('billing_records', 'is_generated'), function ($query) use ($generated) {
                $query->where('is_generated', $generated === '1');
            })
            ->where(function ($query) {
                $query->whereNull('bill_status')
                    ->orWhereNotIn('bill_status', ['Disconnected']);
            })
            ->whereNull('deleted_at');

        $records = (clone $recordsQuery)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        if (Schema::hasColumn('billing_records', 'is_generated')) {
            $statsBase = BillingRecord::whereHas('customer', $activeCustomerConstraint)
                ->where(function ($query) {
                    $query->whereNull('bill_status')
                        ->orWhereNotIn('bill_status', ['Disconnected']);
                });
            $stats = [
                'pending_generate' => (clone $statsBase)->where('is_generated', false)->count(),
                'generated' => (clone $statsBase)->where('is_generated', true)->count(),
            ];
        } else {
            $statsBase = BillingRecord::whereHas('customer', $activeCustomerConstraint)
                ->where(function ($query) {
                    $query->whereNull('bill_status')
                        ->orWhereNotIn('bill_status', ['Disconnected']);
                });
            $stats = [
                'pending_generate' => (clone $statsBase)->count(),
                'generated' => 0,
            ];
        }

        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();
        $startOfMonth = Carbon::now()->startOfMonth();
        $now = Carbon::now();

        $collectionsToday = (float) PaymentRecord::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('amount_paid');
        $collectionsMonth = (float) PaymentRecord::whereBetween('created_at', [$startOfMonth, $now])->sum('amount_paid');
        $overdueQuery = BillingRecord::whereIn('bill_status', ['Overdue', 'Notice of Disconnection'])
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

        $pending = BillingRecord::with('customer')
            ->whereHas('customer', $activeCustomerConstraint)
            ->when(Schema::hasColumn('billing_records', 'is_generated'), function ($qb) {
                $qb->where('is_generated', false);
            })
            ->where(function ($qb) {
                $qb->whereNull('bill_status')
                    ->orWhereNotIn('bill_status', ['Paid', 'Disconnected']);
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'account_no', 'total_amount', 'date_from', 'date_to', 'created_at']);

        return view('records.billing-management', compact('records', 'q', 'status', 'stats', 'generated', 'quick', 'pending'));
    }

    public function printBill($id)
    {
        $billingRecord = BillingRecord::with('customer')->findOrFail($id);

        if (($billingRecord->customer && $billingRecord->customer->status === 'Disconnected')
            || $billingRecord->bill_status === 'Disconnected') {
            abort(403, 'This account is disconnected. Printing is not allowed.');
        }

        $usageSeries = $this->getLastFiveMonthsUsage($billingRecord->account_no);
        return view('records.bill-print', compact('billingRecord', 'usageSeries'));
    }

    public function downloadBillPdf($id)
    {
        $billingRecord = BillingRecord::with('customer')->findOrFail($id);

        if (($billingRecord->customer && $billingRecord->customer->status === 'Disconnected')
            || $billingRecord->bill_status === 'Disconnected') {
            abort(403, 'This account is disconnected. Printing is not allowed.');
        }

        $path = $billingRecord->pdf_path;
        if (!$path) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404);
        }

        $filename = ($billingRecord->invoice_number ?? ('INV-' . str_pad((string) $billingRecord->id, 4, '0', STR_PAD_LEFT))) . '.pdf';
        return $disk->download($path, $filename);
    }

    public function payments(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $payments = Customer::query()
            ->select('customers.*')
            ->whereHas('paymentRecords')
            ->withCount(['paymentRecords as payment_count'])
            ->withSum('paymentRecords as total_paid', 'amount_paid')
            ->addSelect([
                'latest_payment_at' => PaymentRecord::selectRaw('MAX(created_at)')
                    ->whereColumn('payment_records.customer_id', 'customers.id'),
            ])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('customers.account_no', 'like', "%{$q}%")
                          ->orWhere('customers.name', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('latest_payment_at')
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
        $statusOptions = ['Paid','Outstanding Payment','Overdue','Notice of Disconnection','Disconnected'];
        $statuses = array_values(array_unique(array_filter(array_map(function($value) use ($statusOptions) {
            $value = trim((string) $value);
            return in_array($value, $statusOptions, true) ? $value : null;
        }, Arr::wrap($request->input('statuses', []))))));
        $status = $request->get('status', '');
        if (empty($statuses) && $status && in_array($status, $statusOptions, true)) {
            $statuses = [$status];
        }

        $baseQuery = BillingRecord::onlyTrashed()
            ->with('customer')
            ->when($q, function ($query) use ($q) {
                $query->where('account_no', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($sub) use ($q) {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('address', 'like', "%{$q}%");
                    });
            })
            ->when($statuses, function ($query) use ($statuses) {
                $query->whereIn('bill_status', $statuses);
            });

        $records = (clone $baseQuery)
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        $archivedCount = (clone $baseQuery)->count();
        if ($archivedCount > 100) {
            $backupPath = $this->backupArchivedBillingRecords();
            if ($backupPath) {
                session()->flash('status', sprintf('Archived billing backup saved to storage/app/%s', $backupPath));
            } else {
                session()->flash('error', 'Archived records exceed 100 but automatic backup failed. Please check the logs.');
            }
        }

        return view('records.archived-billing', compact('records', 'q', 'archivedCount', 'statusOptions', 'statuses'));
    }

    public function exportArchivedBilling(Request $request): StreamedResponse
    {
        $q = trim((string) $request->get('q', ''));
        $statusOptions = ['Paid','Outstanding Payment','Overdue','Notice of Disconnection','Disconnected'];
        $statuses = array_values(array_unique(array_filter(array_map(function($value) use ($statusOptions) {
            $value = trim((string) $value);
            return in_array($value, $statusOptions, true) ? $value : null;
        }, Arr::wrap($request->input('statuses', []))))));
        $status = $request->get('status', '');
        if (empty($statuses) && $status && in_array($status, $statusOptions, true)) {
            $statuses = [$status];
        }

        $query = BillingRecord::onlyTrashed()
            ->with('customer')
            ->when($q, function($qb) use ($q) {
                $qb->where('account_no', 'like', "%{$q}%")
                    ->orWhereHas('customer', function($sub) use ($q) {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('address', 'like', "%{$q}%");
                    });
            })
            ->when($statuses, function ($query) use ($statuses) {
                $query->whereIn('bill_status', $statuses);
            })
            ->orderByDesc('deleted_at');

        $filename = sprintf('archived-billing-%s.csv', Carbon::now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Invoice', 'Account No', 'Customer Name', 'Total Amount', 'Status', 'Deleted At']);
            $query->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $record) {
                    fputcsv($handle, [
                        $record->id,
                        $record->invoice_number ?? sprintf('INV-%04d', $record->id),
                        $record->account_no,
                        optional($record->customer)->name,
                        number_format((float) $record->total_amount, 2, '.', ''),
                        $record->bill_status,
                        optional($record->deleted_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function history()
    {
        return view('records.history'); // Points to resources/views/records/history.blade.php
    }

    public function historyApi(Request $request)
    {
        $request->validate(['account_no' => ['required','string','max:50']]);
        $acct = $request->string('account_no')->toString();

        $payments = PaymentRecord::query()
            ->with([
                'billingRecord' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->where('account_no', $acct)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $rows = $payments->map(function (PaymentRecord $payment) {
            $bill = $payment->billingRecord;
            return [
                'date' => optional($payment->created_at)->format('Y-m-d'),
                'previous' => (float) ($bill->previous_reading ?? 0),
                'current' => (float) ($bill->current_reading ?? 0),
                'maintenance' => (float) ($bill->maintenance_charge ?? 0),
                'service_fee' => (float) ($bill->service_fee ?? 0),
                'amount_paid' => (float) $payment->amount_paid,
                'consumption' => (float) ($bill->consumption_cu_m ?? 0),
                'bill_amount' => (float) ($payment->bill_amount ?? $bill->total_amount ?? 0),
                'payment_status' => $payment->payment_status,
                'notes' => $payment->notes,
            ];
        });

        return response()->json([
            'ok' => true,
            'history' => $rows,
        ]);
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
            'bill_status' => 'required|in:Pending,Outstanding Payment,Overdue,Paid,Notice of Disconnection,Disconnected',
            'notes' => 'nullable|string|max:500'
        ]);

        $billingRecord = BillingRecord::findOrFail($id);
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill status updated successfully!'
            ]);
        }

        return redirect()
            ->route('records.billing')
            ->with('success', 'Bill status updated successfully!');
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