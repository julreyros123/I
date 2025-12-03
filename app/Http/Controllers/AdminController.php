<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use App\Models\BillingRecord;
use App\Models\Report;
use App\Models\PaymentRecord;
use App\Models\ActivityLog;

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

        return view('admin.dashboard', compact(
            'stats',
            'monthlyRevenue',
            'dailyRevenue',
            'startDay',
            'yearlyRevenue',
            'pendingGenerationCount',
            'pendingGenerationAmount',
            'pendingGenerationList'
        ));
    }

    public function notices()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        return view('admin.notices');
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

    public function customers()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        $customers = Customer::orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.customers', compact('customers'));
    }

    public function meters()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        return view('admin.meters');
    }

    public function revenue(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $groupBy = in_array($request->get('group_by'), ['day','month','year']) ? $request->get('group_by') : 'month';
        $from = $request->get('from');
        $to = $request->get('to');
        $customer = trim((string) $request->get('customer', ''));

        // Default range: this year if grouping by month/year; last 30 days if by day
        if (!$from || !$to) {
            if ($groupBy === 'day') {
                $from = now()->copy()->subDays(29)->startOfDay();
                $to = now()->endOfDay();
            } else {
                $from = now()->copy()->startOfYear();
                $to = now()->endOfYear();
            }
        } else {
            $from = now()->parse($from)->startOfDay();
            $to = now()->parse($to)->endOfDay();
        }

        // Base queries with filters
        $billBase = BillingRecord::with('customer')
            ->whereBetween('created_at', [$from, $to])
            ->when($customer, fn($q)=>$q->whereHas('customer', fn($s)=>$s->where('name','like',"%{$customer}%")));

        $payBase = PaymentRecord::with(['customer','billingRecord'])
            ->whereBetween('created_at', [$from, $to])
            ->when($customer, fn($q)=>$q->whereHas('customer', fn($s)=>$s->where('name','like',"%{$customer}%")));

        // Summary (revenue focuses on paid bills)
        $totalBilled = (float) (clone $billBase)->sum('total_amount');
        $totalPaid = (float) (clone $payBase)->sum('amount_paid');
        $unpaid = 0.0; // revenue report focuses on paid collections

        // Breakdown
        $format = match($groupBy){
            'day' => '%Y-%m-%d',
            'year' => '%Y',
            default => '%Y-%m'
        };

        // Paid rows grouped by period (collections)
        $paidRows = (clone $payBase)
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as p, COUNT(*) as payments, SUM(amount_paid) as paid")
            ->groupBy('p')->orderBy('p')->get();

        // Build breakdown based solely on payments
        $breakdown = $paidRows->map(function($row){
            $period = $row->p;
            $payments = (int) ($row->payments ?? 0);
            $paid = (float) ($row->paid ?? 0);
            return [
                'period' => $period,
                'bills' => $payments,
                'paid' => $paid,
                'unpaid' => 0.0,
                'revenue' => $paid,
            ];
        });

        return view('admin.reports.revenue', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
                'group_by' => $groupBy,
                'customer' => $customer,
            ],
            'summary' => [
                'total_billed' => $totalBilled,
                'total_paid' => $totalPaid,
                'unpaid' => $unpaid,
            ],
            'breakdown' => $breakdown,
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


