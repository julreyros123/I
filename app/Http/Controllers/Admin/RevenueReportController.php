<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingRecord;
use App\Models\Customer;
use App\Models\MeterAudit;
use App\Models\PaymentRecord;
use App\Models\Report;
use App\Models\TransferReconnectAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        return redirect()->route('admin.reports.revenue.collections', $request->all());
    }

    public function collections(Request $request)
    {
        $context = $this->resolveFilters($request);
        $filters = $context['filters'];
        $from = $context['from'];
        $to = $context['to'];
        $groupBy = $context['group_by'];
        $customer = $context['customer'];

        $billBase = BillingRecord::with('customer')
            ->whereBetween('created_at', [$from, $to])
            ->when($customer, fn ($q) => $q->whereHas('customer', fn ($s) => $s->where('name', 'like', "%{$customer}%")));

        $payBase = PaymentRecord::with(['customer', 'billingRecord'])
            ->whereBetween('created_at', [$from, $to])
            ->when($customer, fn ($q) => $q->whereHas('customer', fn ($s) => $s->where('name', 'like', "%{$customer}%")));

        $summary = [
            'total_billed' => (float) (clone $billBase)->sum('total_amount'),
            'total_paid' => (float) (clone $payBase)->sum('amount_paid'),
            'unpaid' => 0.0,
        ];

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
            ]);

        return view('admin.reports.revenue.collections', [
            'filters' => $filters,
            'summary' => $summary,
            'breakdown' => $breakdown,
            'navItems' => $this->navItems('collections'),
        ]);
    }

    public function issues(Request $request)
    {
        $context = $this->resolveFilters($request, includeGroupBy: false, includeCustomer: false);
        $filters = $context['filters'];
        $from = $context['from'];
        $to = $context['to'];

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
            ]);

        $issueByCategory = (clone $issueBase)
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->category ?: 'Unspecified',
                'total' => (int) ($row->total ?? 0),
            ]);

        $recentIssues = (clone $issueBase)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'category', 'status', 'created_at', 'other_problem', 'message']);

        return view('admin.reports.revenue.issues', [
            'filters' => $filters,
            'issueSummary' => $issueSummary,
            'issueByStatus' => $issueByStatus,
            'issueByCategory' => $issueByCategory,
            'recentIssues' => $recentIssues,
            'navItems' => $this->navItems('issues'),
        ]);
    }

    public function meters(Request $request)
    {
        $context = $this->resolveFilters($request, includeGroupBy: false, includeCustomer: false);
        $filters = $context['filters'];
        $from = $context['from'];
        $to = $context['to'];

        $meterBase = MeterAudit::with('meter')
            ->whereBetween('created_at', [$from, $to]);

        $meterSummary = [
            'total_events' => (clone $meterBase)->count(),
            'replacements' => (clone $meterBase)->where(function ($q) {
                $q->where('reason', 'like', '%replac%')
                  ->orWhere('action', 'like', '%replac%');
            })->count(),
            'damages' => (clone $meterBase)->where(function ($q) {
                $q->where('reason', 'like', '%damag%')
                  ->orWhere('action', 'like', '%damag%');
            })->count(),
        ];

        $recentMeterEvents = (clone $meterBase)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get(['meter_id', 'action', 'reason', 'created_at']);

        return view('admin.reports.revenue.meters', [
            'filters' => $filters,
            'meterSummary' => $meterSummary,
            'recentMeterEvents' => $recentMeterEvents,
            'navItems' => $this->navItems('meters'),
        ]);
    }

    public function connections(Request $request)
    {
        $context = $this->resolveFilters($request, includeGroupBy: false, includeCustomer: false);
        $filters = $context['filters'];
        $from = $context['from'];
        $to = $context['to'];

        $disconnectedCustomers = Customer::where('status', 'Disconnected')->count();

        $disconnectionEvents = TransferReconnectAudit::query()
            ->where('action', 'disconnect')
            ->whereBetween('performed_at', [$from, $to])
            ->count();

        $reconnectionEvents = TransferReconnectAudit::query()
            ->where('action', 'reconnect')
            ->whereBetween('performed_at', [$from, $to])
            ->count();

        $recentEvents = TransferReconnectAudit::query()
            ->whereBetween('performed_at', [$from, $to])
            ->orderByDesc('performed_at')
            ->limit(20)
            ->get(['account_no', 'action', 'performed_at', 'notes']);

        return view('admin.reports.revenue.connections', [
            'filters' => $filters,
            'summary' => [
                'disconnected_customers' => $disconnectedCustomers,
                'disconnection_events' => $disconnectionEvents,
                'reconnection_events' => $reconnectionEvents,
            ],
            'recentEvents' => $recentEvents,
            'navItems' => $this->navItems('connections'),
        ]);
    }

    private function resolveFilters(Request $request, bool $includeGroupBy = true, bool $includeCustomer = true): array
    {
        $groupBy = $includeGroupBy
            ? (in_array($request->get('group_by'), ['day', 'month', 'year'], true) ? $request->get('group_by') : 'month')
            : null;

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

        $customer = null;
        if ($includeCustomer) {
            $customer = trim((string) $request->get('customer', ''));
        }

        $filters = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];

        if ($includeGroupBy) {
            $filters['group_by'] = $groupBy;
        }

        if ($includeCustomer) {
            $filters['customer'] = $customer;
        }

        return [
            'filters' => $filters,
            'from' => $from,
            'to' => $to,
            'group_by' => $groupBy,
            'customer' => $customer,
        ];
    }

    private function navItems(string $active): array
    {
        return [
            [
                'key' => 'collections',
                'label' => 'Collections',
                'description' => 'Billed vs paid revenue',
                'route' => route('admin.reports.revenue.collections'),
                'active' => $active === 'collections',
            ],
            [
                'key' => 'issues',
                'label' => 'Issue Complaints',
                'description' => 'Staff submitted tickets',
                'route' => route('admin.reports.revenue.issues'),
                'active' => $active === 'issues',
            ],
            [
                'key' => 'meters',
                'label' => 'Meter Activity',
                'description' => 'Meter audits and incidents',
                'route' => route('admin.reports.revenue.meters'),
                'active' => $active === 'meters',
            ],
            [
                'key' => 'connections',
                'label' => 'Connection Changes',
                'description' => 'Disconnections & reconnects',
                'route' => route('admin.reports.revenue.connections'),
                'active' => $active === 'connections',
            ],
        ];
    }
}
