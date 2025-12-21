<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use App\Models\BillingRecord;
use App\Models\MeterAudit;
use App\Models\Report;
use App\Models\PaymentRecord;
use App\Models\ActivityLog;
use App\Models\CustomerApplication;
use App\Models\Register;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\TransferReconnectAudit;
use App\Models\MeterAssignment;
use App\Models\Meter;

class AdminController extends Controller
{
    public function index()
    {
        // Simple role check; assumes 'role' column on users with value 'admin'
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $periodStart = now()->startOfMonth();
        $periodEnd = now();

        $monthBilled = (float) BillingRecord::whereBetween('created_at', [$periodStart, $periodEnd])->sum('total_amount');
        $monthCollected = (float) PaymentRecord::whereBetween('created_at', [$periodStart, $periodEnd])->sum('amount_paid');
        $collectionRate = $monthBilled > 0 ? ($monthCollected / $monthBilled) : 0.0;
        $unpaidTotal = (float) BillingRecord::where('bill_status', '!=', 'Paid')->sum('total_amount');
        $newCustomers = Customer::where('created_at', '>=', $periodStart)->count();

        $stats = [
            'users' => User::count(),
            'customers' => Customer::count(),
            'billings' => BillingRecord::count(),
            'today_billings' => BillingRecord::whereDate('created_at', today())->count(),
            'month_billed' => $monthBilled,
            'month_collected' => $monthCollected,
            'collection_rate' => $collectionRate,
            'unpaid_total' => $unpaidTotal,
            'new_customers' => $newCustomers,
        ];

        $pendingGenerationCount = 0;
        $pendingGenerationAmount = 0.0;
        $pendingGenerationList = collect();

        if (\Illuminate\Support\Facades\Schema::hasColumn('billing_records', 'is_generated')) {
            $pendingBase = BillingRecord::with('customer')
                ->where('is_generated', false);

            $pendingGenerationCount = (int) $pendingBase->count();
            $pendingGenerationAmount = (float) (clone $pendingBase)->sum('total_amount');
            $pendingGenerationList = (clone $pendingBase)
                ->orderByDesc('created_at')
                ->take(5)
                ->get();
        }

        // Admin task insights (applications awaiting approval/installation)
        $pendingApprovalQuery = CustomerApplication::query()
            ->with('customer')
            ->whereIn('status', ['registered', 'pending', 'inspected'])
            ->orderByDesc('created_at');

        $pendingInstallQuery = CustomerApplication::query()
            ->with('customer')
            ->whereIn('status', ['scheduled', 'installing'])
            ->orderByRaw('schedule_date is null')
            ->orderBy('schedule_date')
            ->orderByDesc('created_at');

        $applicationsPendingApprovalCount = (int) (clone $pendingApprovalQuery)->count();
        $applicationsPendingInstallationCount = (int) (clone $pendingInstallQuery)->count();

        $applicationsPendingApprovalList = (clone $pendingApprovalQuery)
            ->take(6)
            ->get(['id', 'customer_id', 'applicant_name', 'address', 'status', 'created_at']);

        $applicationsPendingInstallationList = (clone $pendingInstallQuery)
            ->take(6)
            ->get(['id', 'customer_id', 'applicant_name', 'address', 'status', 'schedule_date', 'created_at']);

        // Connection analytics (classification mix)
        $connectionBreakdown = Register::query()
            ->select('connection_classification', DB::raw('COUNT(*) as total'))
            ->groupBy('connection_classification')
            ->orderByDesc('total')
            ->get();

        if ($connectionBreakdown->isEmpty()) {
            $connectionBreakdown = collect([
                (object) ['connection_classification' => 'Residential', 'total' => 0],
                (object) ['connection_classification' => 'Commercial', 'total' => 0],
                (object) ['connection_classification' => 'Industrial', 'total' => 0],
            ]);
        }

        $connectionAnalyticsTotal = (int) $connectionBreakdown->sum('total');

        $connectionAnalytics = $connectionBreakdown->map(function ($row) use ($connectionAnalyticsTotal) {
            $label = $row->connection_classification ?: 'Unspecified';
            $count = (int) $row->total;
            $percentage = $connectionAnalyticsTotal > 0
                ? round(($count / $connectionAnalyticsTotal) * 100, 1)
                : 0;

            return [
                'label' => $label,
                'count' => $count,
                'percentage' => $percentage,
            ];
        })->values();

        if ($connectionAnalytics->sum('count') <= 0) {
            $connectionAnalytics = collect([
                ['label' => 'Residential', 'count' => 0, 'percentage' => 0],
                ['label' => 'Commercial', 'count' => 0, 'percentage' => 0],
                ['label' => 'Industrial', 'count' => 0, 'percentage' => 0],
            ]);
            $connectionAnalyticsTotal = 0;
        }

        $connectionAnalyticsLabels = $connectionAnalytics->pluck('label')->toArray();
        $connectionAnalyticsCounts = $connectionAnalytics->pluck('count')->toArray();
        $connectionColorPalette = ['#2563eb', '#38bdf8', '#1e3a8a', '#0ea5e9', '#22d3ee', '#3b82f6'];

        // Monthly revenue (current year, 12 points)
        $byMonth = BillingRecord::selectRaw('MONTH(created_at) as m, SUM(total_amount) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthlyRevenue = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyRevenue[] = (float) ($byMonth[$i] ?? 0);
        }

        // Daily revenue for the last 30 days
        $startDay = now()->copy()->subDays(29)->startOfDay();
        $endDay = now()->endOfDay();
        $byDay = BillingRecord::selectRaw('DATE(created_at) as d, SUM(total_amount) as total')
            ->whereBetween('created_at', [$startDay, $endDay])
            ->groupBy('d')
            ->pluck('total', 'd');
        $dailyRevenue = [];
        for ($i = 0; $i < 30; $i++) {
            $date = $startDay->copy()->addDays($i)->toDateString();
            $dailyRevenue[] = (float) ($byDay[$date] ?? 0);
        }

        // Yearly revenue for last 5 years including current
        $currentYear = now()->year;
        $years = range($currentYear - 4, $currentYear);
        $byYear = BillingRecord::selectRaw('YEAR(created_at) as y, SUM(total_amount) as total')
            ->whereBetween('created_at', [now()->copy()->subYears(4)->startOfYear(), now()->endOfYear()])
            ->groupBy('y')
            ->pluck('total', 'y');
        $yearlyRevenue = [];
        foreach ($years as $y) { $yearlyRevenue[] = (float) ($byYear[$y] ?? 0); }

        $defaultRangeStart = $periodStart->copy()->toDateString();
        $defaultRangeEnd = $periodEnd->copy()->toDateString();

        return view('admin.dashboard', compact(
            'stats',
            'monthlyRevenue',
            'dailyRevenue',
            'startDay',
            'yearlyRevenue',
            'pendingGenerationCount',
            'pendingGenerationAmount',
            'pendingGenerationList',
            'applicationsPendingApprovalCount',
            'applicationsPendingInstallationCount',
            'applicationsPendingApprovalList',
            'applicationsPendingInstallationList',
            'connectionAnalytics',
            'connectionAnalyticsLabels',
            'connectionAnalyticsCounts',
            'connectionAnalyticsTotal',
            'connectionColorPalette',
            'defaultRangeStart',
            'defaultRangeEnd'
        ));
    }

    public function dashboardStats(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $startInput = $request->query('start');
        $endInput = $request->query('end');

        try {
            $startDate = $startInput ? Carbon::parse($startInput)->startOfDay() : now()->copy()->startOfYear();
            $endDate = $endInput ? Carbon::parse($endInput)->endOfDay() : now()->copy()->endOfDay();
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid date range provided.'
            ], 422);
        }

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy(), $startDate->copy()];
        }

        $billedTotal = (float) BillingRecord::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
        $collectedTotal = (float) PaymentRecord::whereBetween('created_at', [$startDate, $endDate])->sum('amount_paid');
        $newCustomers = (int) Customer::whereBetween('created_at', [$startDate, $endDate])->count();
        $activeCustomers = (int) Customer::count();
        $collectionRate = $billedTotal > 0 ? round($collectedTotal / max($billedTotal, 0.000001), 4) : 0.0;

        return response()->json([
            'ok' => true,
            'range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'stats' => [
                'billed_total' => round($billedTotal, 2),
                'collected_total' => round($collectedTotal, 2),
                'new_customers' => $newCustomers,
                'active_customers' => $activeCustomers,
                'collection_rate' => $collectionRate,
            ],
        ]);
    }

    public function dashboardInsightsData(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $metric = $request->query('metric', 'revenue');
        if (!in_array($metric, ['revenue', 'customers', 'usage'])) {
            $metric = 'revenue';
        }

        try {
            $startDate = $request->query('start')
                ? Carbon::parse($request->query('start'))->startOfDay()
                : now()->copy()->startOfYear();
            $endDate = $request->query('end')
                ? Carbon::parse($request->query('end'))->endOfDay()
                : now()->copy()->endOfDay();
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid date range provided.'
            ], 422);
        }

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy(), $startDate->copy()];
        }

        $bucketStart = $startDate->copy()->startOfMonth();
        $bucketEnd = $endDate->copy()->startOfMonth();

        $labels = [];
        $buckets = [];
        $cursor = $bucketStart->copy();
        while ($cursor->lte($bucketEnd)) {
            $key = $cursor->format('Y-m-01');
            $labels[] = $cursor->format('M Y');
            $buckets[] = $key;
            $cursor->addMonthNoOverflow();
        }

        switch ($metric) {
            case 'customers':
                $rows = Customer::selectRaw('DATE_FORMAT(created_at, "%Y-%m-01") as bucket, COUNT(*) as total')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('bucket')
                    ->pluck('total', 'bucket');
                break;
            case 'usage':
                $rows = BillingRecord::selectRaw('DATE_FORMAT(created_at, "%Y-%m-01") as bucket, AVG(consumption_cu_m) as total')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('bucket')
                    ->pluck('total', 'bucket');
                break;
            case 'revenue':
            default:
                $rows = BillingRecord::selectRaw('DATE_FORMAT(created_at, "%Y-%m-01") as bucket, SUM(total_amount) as total')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('bucket')
                    ->pluck('total', 'bucket');
                break;
        }

        $series = [];
        foreach ($buckets as $bucketKey) {
            $value = (float) ($rows[$bucketKey] ?? 0);
            $series[] = round($value, 2);
        }

        return response()->json([
            'ok' => true,
            'range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'metric' => $metric,
            'labels' => $labels,
            'data' => $series,
        ]);
    }

    public function notices()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        return view('admin.notices');
    }

    public function customerDataReport(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : now()->copy()->subDays(89)->startOfDay();
        $to = $toInput ? Carbon::parse($toInput)->endOfDay() : now()->endOfDay();

        $filters = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];

        $overallCounts = [
            'total' => Customer::count(),
            'active' => Customer::where('status', 'Active')->count(),
            'inactive' => Customer::where('status', 'Inactive')->count(),
            'disconnected' => Customer::where('status', 'Disconnected')->count(),
        ];

        $newCustomers = Customer::whereBetween('created_at', [$from, $to])->count();

        $statusBreakdown = Customer::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $classificationBreakdown = Customer::select('classification', DB::raw('COUNT(*) as total'))
            ->groupBy('classification')
            ->orderByDesc('total')
            ->get();

        $recentCustomers = Customer::orderByDesc('created_at')
            ->take(20)
            ->get(['id', 'name', 'account_no', 'status', 'classification', 'created_at']);

        $trendWindow = $from->clone()->subDays(29);
        $trendRows = Customer::selectRaw('DATE(created_at) as date_key, COUNT(*) as total')
            ->whereBetween('created_at', [$trendWindow->startOfDay(), $to])
            ->groupBy('date_key')
            ->orderBy('date_key')
            ->get();

        $trendMap = $trendRows->keyBy('date_key');
        $trendLabels = [];
        $trendCounts = [];
        $cursor = $trendWindow->copy();
        while ($cursor->lte($to)) {
            $dateKey = $cursor->format('Y-m-d');
            $trendLabels[] = $cursor->format('M d');
            $trendCounts[] = (int) optional($trendMap->get($dateKey))->total ?? 0;
            $cursor->addDay();
        }

        return view('admin.reports.customers', [
            'filters' => $filters,
            'overallCounts' => $overallCounts,
            'newCustomers' => $newCustomers,
            'statusBreakdown' => $statusBreakdown,
            'classificationBreakdown' => $classificationBreakdown,
            'recentCustomers' => $recentCustomers,
            'trendLabels' => $trendLabels,
            'trendCounts' => $trendCounts,
        ]);
    }

    public function paymentReport(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : now()->copy()->subDays(29)->startOfDay();
        $to = $toInput ? Carbon::parse($toInput)->endOfDay() : now()->endOfDay();

        $filters = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];

        $paymentsBase = PaymentRecord::with('customer')
            ->whereBetween('created_at', [$from, $to]);

        $totalCollected = (float) (clone $paymentsBase)->sum('amount_paid');
        $transactionCount = (int) (clone $paymentsBase)->count();
        $averagePayment = $transactionCount > 0 ? $totalCollected / $transactionCount : 0.0;
        $largestPayment = (float) (clone $paymentsBase)->max('amount_paid');

        $methodBreakdown = (clone $paymentsBase)
            ->select('payment_method', DB::raw('COUNT(*) as transactions'), DB::raw('SUM(amount_paid) as total_amount'))
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $dailyBreakdown = (clone $paymentsBase)
            ->selectRaw('DATE(created_at) as date_key, COUNT(*) as transactions, SUM(amount_paid) as total_amount')
            ->groupBy('date_key')
            ->orderBy('date_key')
            ->get();

        $recentPayments = (clone $paymentsBase)
            ->orderByDesc('created_at')
            ->take(20)
            ->get(['id', 'account_no', 'amount_paid', 'payment_method', 'reference_number', 'created_at']);

        return view('admin.reports.payments', [
            'filters' => $filters,
            'summary' => [
                'total_collected' => $totalCollected,
                'transactions' => $transactionCount,
                'average_payment' => $averagePayment,
                'largest_payment' => $largestPayment,
            ],
            'methodBreakdown' => $methodBreakdown,
            'dailyBreakdown' => $dailyBreakdown,
            'recentPayments' => $recentPayments,
        ]);
    }

    public function reports()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        $priorityReports = Report::with('user')
            ->where('status', '!=', 'completed')
            ->where('is_priority', true)
            ->orderByDesc('created_at')
            ->get();
        $openReports = Report::with('user')
            ->where('status', '!=', 'completed')
            ->where('is_priority', false)
            ->orderByDesc('created_at')
            ->get();
        $completedReports = Report::with('user')
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->get();
        return view('admin.reports', compact('priorityReports', 'openReports', 'completedReports'));
    }

    public function updateReportPriority(Report $report, Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        $report->is_priority = (bool) $request->input('is_priority');
        $report->save();
        return redirect()->route('admin.reports')->with('status', 'Report updated.');
    }

    public function updateReportStatus(Report $report, Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        $status = $request->input('status', 'open');
        $report->status = $status;
        // If completed, also clear priority
        if ($status === 'completed') {
            $report->is_priority = false;
        }
        $report->save();
        return redirect()->route('admin.reports')->with('status', 'Report status updated.');
    }

    public function customers(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $query = Customer::query()->select([
            'id',
            'account_no',
            'name',
            'address',
            'contact_no',
            'classification',
            'status',
            'meter_no',
            'reconnect_requested_at',
            'created_at',
        ]);

        if ($request->get('export') === 'csv') {
            return $this->exportCustomersCsv(clone $query);
        }

        $customers = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers', [
            'customers' => $customers,
        ]);
    }

    protected function exportCustomersCsv($query)
    {
        $filename = 'customers_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'account_no',
            'name',
            'contact_no',
            'classification',
            'status',
            'created_at',
        ];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Account No.', 'Name', 'Contact', 'Connection', 'Status', 'Created At']);

            $query->orderBy('account_no')->chunk(200, function ($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    $data = [];
                    foreach ($columns as $col) {
                        $value = data_get($row, $col);
                        if ($col === 'created_at' && $value) {
                            $value = $row->created_at?->format('Y-m-d H:i');
                        }
                        $data[] = $value;
                    }
                    fputcsv($handle, $data);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function transferMeterOwnership(Customer $customer, Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $validated = $request->validate([
            'new_owner_name' => ['required','string','max:255'],
            'new_contact_no' => ['nullable','string','max:50'],
            'transfer_date' => ['nullable','date'],
            'notes' => ['nullable','string','max:1000'],
        ]);

        $transferDate = $validated['transfer_date'] ? Carbon::parse($validated['transfer_date']) : now();

        try {
            DB::transaction(function () use ($customer, $validated, $transferDate) {
                $this->applyCustomerOwnershipTransfer($customer, $validated, $transferDate);
            });
        } catch (\Throwable $e) {
            Log::error('Transfer meter ownership failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to transfer ownership. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'Meter ownership transferred successfully.',
        ]);
    }

    protected function applyCustomerOwnershipTransfer(Customer $customer, array $validated, Carbon $transferDate): void
    {
        $oldName = $customer->name;
        $oldContact = $customer->contact_no;
        $meterSerial = $customer->meter_no;

        $customer->name = $validated['new_owner_name'];
        $customer->contact_no = $validated['new_contact_no'] ?? null;
        $customer->status = 'Active';
        $customer->save();

        if ($meterSerial) {
            $meter = Meter::where('serial', $meterSerial)->first();
            if ($meter) {
                $meter->current_account_id = $customer->id;
                $meter->status = 'active';
                if (!$meter->install_date) {
                    $meter->install_date = $transferDate;
                }
                $meter->save();

                $activeAssignment = MeterAssignment::where('meter_id', $meter->id)
                    ->whereNull('unassigned_at')
                    ->latest('assigned_at')
                    ->first();

                if ($activeAssignment && $activeAssignment->account_id !== $customer->id) {
                    $activeAssignment->update([
                        'unassigned_at' => $transferDate,
                        'unassigned_by' => optional(auth()->user())->id,
                        'notes' => trim(($activeAssignment->notes ? $activeAssignment->notes . ' | ' : '') . 'Auto-closed due to ownership transfer'),
                    ]);
                }

                MeterAssignment::create([
                    'meter_id' => $meter->id,
                    'account_id' => $customer->id,
                    'assigned_at' => $transferDate,
                    'reason' => 'Ownership transfer',
                    'notes' => $validated['notes'] ?? null,
                    'assigned_by' => optional(auth()->user())->id,
                ]);
            }
        }

        Register::where('account_no', $customer->account_no)->update([
            'name' => $validated['new_owner_name'],
            'contact_no' => $validated['new_contact_no'] ?? $oldContact,
        ]);

        TransferReconnectAudit::create([
            'account_no' => $customer->account_no,
            'action' => 'transfer',
            'old_value' => trim($oldName . ($oldContact ? " ({$oldContact})" : '')),
            'new_value' => trim($validated['new_owner_name'] . ($validated['new_contact_no'] ? " ({$validated['new_contact_no']})" : '')),
            'notes' => $validated['notes'] ?? null,
            'performed_by' => optional(auth()->user())->id,
            'performed_at' => $transferDate,
        ]);
    }

    public function meters()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        return view('admin.meters');
    }

    public function revenue(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $viewParam = $request->get('view');
        $activeView = in_array($viewParam, ['payments', 'issues', 'print'], true) ? $viewParam : 'payments';
        $groupBy = in_array($request->get('group_by'), ['day', 'month', 'year'], true) ? $request->get('group_by') : 'month';

        $fromInput = $request->get('from');
        $toInput = $request->get('to');

        if (!$fromInput || !$toInput) {
            if ($groupBy === 'day') {
                $from = now()->copy()->subDays(29)->startOfDay();
                $to = now()->endOfDay();
            } else {
                $from = now()->copy()->startOfYear();
                $to = now()->endOfYear();
            }
        } else {
            $from = now()->parse($fromInput)->startOfDay();
            $to = now()->parse($toInput)->endOfDay();
        }

        $customer = $activeView === 'payments' ? trim((string) $request->get('customer', '')) : null;

        $filters = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];

        if ($activeView === 'payments') {
            $filters['group_by'] = $groupBy;
            $filters['customer'] = $customer;
        }

        $summary = [
            'total_billed' => 0.0,
            'total_paid' => 0.0,
            'unpaid' => 0.0,
        ];

        $breakdown = [];
        $operationalMetrics = [
            'registered_customers' => 0,
            'bills_created' => 0,
            'issue_reports' => 0,
            'meter_replacements' => 0,
            'meter_damages' => 0,
            'disconnected_customers' => 0,
            'disconnection_events' => 0,
            'reconnection_events' => 0,
        ];

        $meterIncidents = [];
        $recentDisconnections = [];

        $issueSummary = [
            'total' => 0,
            'priority' => 0,
            'completed' => 0,
        ];

        $issueByStatus = [];
        $issueByCategory = [];
        $recentIssues = [];
        $issueTimeline = [];

        if (in_array($activeView, ['payments', 'print'], true)) {
            $billBase = BillingRecord::with('customer')
                ->whereBetween('created_at', [$from, $to])
                ->when($customer, fn ($q) => $q->whereHas('customer', fn ($s) => $s->where('name', 'like', "%{$customer}%")));

            $payBase = PaymentRecord::with(['customer', 'billingRecord'])
                ->whereBetween('created_at', [$from, $to])
                ->when($customer, fn ($q) => $q->whereHas('customer', fn ($s) => $s->where('name', 'like', "%{$customer}%")));

            $summary['total_billed'] = (float) (clone $billBase)->sum('total_amount');
            $summary['total_paid'] = (float) (clone $payBase)->sum('amount_paid');

            $operationalMetrics['bills_created'] = (int) (clone $billBase)->count();
            $operationalMetrics['registered_customers'] = Customer::query()
                ->when($customer, fn ($q) => $q->where('name', 'like', "%{$customer}%"))
                ->whereBetween('created_at', [$from, $to])
                ->count();

            $issueSummary['total'] = Report::whereBetween('created_at', [$from, $to])->count();
            $operationalMetrics['issue_reports'] = $issueSummary['total'];

            $meterAuditBase = MeterAudit::with('meter')
                ->whereBetween('created_at', [$from, $to]);

            $operationalMetrics['meter_replacements'] = (clone $meterAuditBase)
                ->where(function ($q) {
                    $q->where('reason', 'like', '%replac%')
                      ->orWhere('action', 'like', '%replac%');
                })->count();

            $operationalMetrics['meter_damages'] = (clone $meterAuditBase)
                ->where(function ($q) {
                    $q->where('reason', 'like', '%damag%')
                      ->orWhere('action', 'like', '%damag%');
                })->count();

            $meterIncidents = (clone $meterAuditBase)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(['meter_id', 'action', 'reason', 'created_at']);

            $operationalMetrics['disconnected_customers'] = Customer::where('status', 'Disconnected')->count();
            $operationalMetrics['disconnection_events'] = TransferReconnectAudit::query()
                ->where('action', 'disconnect')
                ->whereBetween('performed_at', [$from, $to])
                ->count();
            $operationalMetrics['reconnection_events'] = TransferReconnectAudit::query()
                ->where('action', 'reconnect')
                ->whereBetween('performed_at', [$from, $to])
                ->count();

            $recentDisconnections = TransferReconnectAudit::query()
                ->whereBetween('performed_at', [$from, $to])
                ->orderByDesc('performed_at')
                ->limit(10)
                ->get(['account_no', 'action', 'performed_at', 'notes']);

            $format = match ($groupBy) {
                'day' => '%Y-%m-%d',
                'year' => '%Y',
                default => '%Y-%m',
            };

            $breakdown = (clone $payBase)
                ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period, COUNT(*) as payments, SUM(amount_paid) as paid")
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->map(fn ($row) => [
                    'period' => $row->period,
                    'bills' => (int) ($row->payments ?? 0),
                    'paid' => (float) ($row->paid ?? 0),
                    'unpaid' => 0.0,
                    'revenue' => (float) ($row->paid ?? 0),
                ])
                ->toArray();
        } else {
            $issueBase = Report::query()->with('user')
                ->whereBetween('created_at', [$from, $to]);

            $issueSummary = [
                'total' => (clone $issueBase)->count(),
                'priority' => (clone $issueBase)->where('is_priority', true)->count(),
                'completed' => (clone $issueBase)->where('status', 'completed')->count(),
            ];

            $issueByStatus = (clone $issueBase)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($row) => [
                    'label' => $row->status ?: 'Unspecified',
                    'total' => (int) ($row->total ?? 0),
                ])
                ->toArray();

            $issueByCategory = (clone $issueBase)
                ->select('category', DB::raw('COUNT(*) as total'))
                ->groupBy('category')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($row) => [
                    'label' => $row->category ?: 'Unspecified',
                    'total' => (int) ($row->total ?? 0),
                ])
                ->toArray();

            $recentIssues = (clone $issueBase)
                ->orderByDesc('created_at')
                ->limit(15)
                ->get(['id', 'category', 'status', 'created_at', 'other_problem', 'message', 'is_priority']);

            $issueTimeline = (clone $issueBase)
                ->selectRaw('DATE(created_at) as period, COUNT(*) as total')
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->map(fn ($row) => [
                    'period' => $row->period,
                    'total' => (int) ($row->total ?? 0),
                ])
                ->toArray();
        }

        return view('admin.reports.revenue', [
            'filters' => $filters,
            'activeView' => $activeView,
            'summary' => $summary,
            'breakdown' => $breakdown,
            'operationalMetrics' => $operationalMetrics,
            'issueSummary' => $issueSummary,
            'issueByStatus' => $issueByStatus,
            'issueByCategory' => $issueByCategory,
            'recentIssues' => $recentIssues,
            'issueTimeline' => $issueTimeline,
            'meterIncidents' => $meterIncidents,
            'recentDisconnections' => $recentDisconnections,
        ]);
    }

    public function billing()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        
        $periodStart = now()->startOfMonth();
        $periodEnd = now();

        $monthBilled = (float) BillingRecord::whereBetween('created_at', [$periodStart, $periodEnd])->sum('total_amount');
        $monthCollected = (float) PaymentRecord::whereBetween('created_at', [$periodStart, $periodEnd])->sum('amount_paid');
        $collectionRate = $monthBilled > 0 ? ($monthCollected / $monthBilled) : 0.0;
        $outstanding = (float) BillingRecord::where('bill_status', '!=', 'Paid')->sum('total_amount');

        $stats = [
            'month_billed' => $monthBilled,
            'month_collected' => $monthCollected,
            'collection_rate' => $collectionRate,
            'outstanding' => $outstanding
        ];
        // Recent billing records for table
        $records = \App\Models\BillingRecord::with('customer')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.billing', compact('stats', 'records'));
    }

    public function archivedBilling(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $q = trim((string) $request->get('q', ''));

        $records = BillingRecord::onlyTrashed()
            ->select([
                'id',
                'customer_id',
                'account_no',
                'total_amount',
                'bill_status',
                'deleted_at',
            ])
            ->with(['customer:id,name,address'])
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

        return view('admin.archived-billing', compact('records', 'q'));
    }

    public function activityLog(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $module = $request->get('module');
        $action = $request->get('action');
        $userId = $request->get('user_id');
        $q = trim((string) $request->get('q', ''));
        $dateFrom = $request->get('from');
        $dateTo = $request->get('to');

        $logs = ActivityLog::with('user')
            ->when($module, fn($qb) => $qb->where('module', $module))
            ->when($action, fn($qb) => $qb->where('action', $action))
            ->when($userId, fn($qb) => $qb->where('user_id', $userId))
            ->when($q, function($qb) use ($q) {
                $qb->where(function($sub) use ($q) {
                    $sub->where('description', 'like', "%{$q}%")
                        ->orWhere('module', 'like', "%{$q}%")
                        ->orWhere('action', 'like', "%{$q}%");
                });
            })
            ->when($dateFrom, fn($qb) => $qb->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($qb) => $qb->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id','name']);
        $modules = ['Payments', 'Billing'];
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action')->all();

        return view('admin.activity-log', compact('logs', 'users', 'modules', 'actions', 'module', 'action', 'userId', 'q', 'dateFrom', 'dateTo'));
    }
}


