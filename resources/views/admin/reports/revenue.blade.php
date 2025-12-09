@extends('layouts.admin')

@section('title', 'Admin • Revenue Report')

@php
    $activeView = $activeView ?? 'payments';
    $isPayments = $activeView === 'payments';
    $isIssues = $activeView === 'issues';
    $isPrint = $activeView === 'print';
    $queryExceptView = request()->except('view');
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 font-[Poppins] space-y-6 lg:space-y-8 print:px-0">
    <div class="rounded-3xl bg-gradient-to-r from-blue-600 via-blue-500 to-sky-500 text-white p-6 shadow-xl print:hidden">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold">
                    @if($isPayments)
                        Customer Payment Report
                    @elseif($isIssues)
                        Issue Report
                    @else
                        Printable Reports
                    @endif
                </h1>
                <p class="text-sm/relaxed text-white/80">
                    @if($isPayments)
                        Monitor collections, compare billed against paid invoices, and drill into customer payments.
                    @elseif($isIssues)
                        Track reported customer issues, priorities, and resolutions across the selected period.
                    @else
                        Generate printer-friendly revenue summaries and operational metrics for archival use.
                    @endif
                </p>
            </div>
            <div class="inline-flex items-center gap-2 text-xs bg-white/10 px-3 py-1 rounded-xl">
                <x-heroicon-o-banknotes class="w-4 h-4" /> Reports Dashboard
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.reports.revenue', array_merge($queryExceptView, ['view' => 'payments'])) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition {{ $isPayments ? 'bg-white text-blue-600 shadow' : 'bg-white/15 text-white hover:bg-white/20' }}">
                <x-heroicon-o-banknotes class="w-4 h-4" /> Customer Payment Report
            </a>
            <a href="{{ route('admin.reports.revenue', array_merge($queryExceptView, ['view' => 'issues'])) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition {{ $isIssues ? 'bg-white text-blue-600 shadow' : 'bg-white/15 text-white hover:bg-white/20' }}">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4" /> Issue Report
            </a>
            <a href="{{ route('admin.reports.revenue', array_merge($queryExceptView, ['view' => 'print'])) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition {{ $isPrint ? 'bg-white text-blue-600 shadow' : 'bg-white/15 text-white hover:bg-white/20' }}">
                <x-heroicon-o-printer class="w-4 h-4" /> Print Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    @if($isPayments)
    <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6 print:hidden">
        <form method="GET" class="w-full space-y-5">
            <input type="hidden" name="view" value="payments">
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] gap-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">From</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full h-[44px] rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">To</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full h-[44px] rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Group by</label>
                        <select name="group_by" class="w-full h-[44px] rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100">
                            <option value="day" @selected(($filters['group_by'] ?? '')==='day')>Daily</option>
                            <option value="month" @selected(($filters['group_by'] ?? 'month')==='month')>Monthly</option>
                            <option value="year" @selected(($filters['group_by'] ?? '')==='year')>Yearly</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="relative">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Customer (optional)</label>
                        <input type="text" name="customer" value="{{ $filters['customer'] ?? '' }}" placeholder="Search customer name" class="w-full pl-10 pr-3 h-[44px] rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-100" />
                        <x-heroicon-o-magnifying-glass aria-hidden="true" class="pointer-events-none w-5 h-5 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" data-range="30" class="rev-quick-range px-3 py-1.5 rounded-lg text-xs bg-blue-50 text-blue-600 hover:bg-blue-100">Last 30 days</button>
                        <button type="button" data-range="90" class="rev-quick-range px-3 py-1.5 rounded-lg text-xs bg-blue-50 text-blue-600 hover:bg-blue-100">Last 90 days</button>
                        <button type="button" data-range="365" class="rev-quick-range px-3 py-1.5 rounded-lg text-xs bg-blue-50 text-blue-600 hover:bg-blue-100">Last 12 months</button>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">Tip: Use quick ranges for rolling analysis, then refine using exact dates.</p>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center px-5 h-[42px] rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500 transition">Apply filters</button>
                    <a href="{{ route('admin.reports.revenue', ['view' => 'payments']) }}" class="inline-flex items-center justify-center px-5 h-[42px] rounded-xl bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-sm text-gray-700 dark:text-gray-100">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Insights & Snapshots -->
    <section class="space-y-5 print:space-y-3">
        <!-- Collections Insight -->
        <div class="space-y-5 print:space-y-3">
            <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6 print:border-gray-300 print:shadow-none">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Collections overview</h2>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Billed versus collected for the selected period.</p>
                    </div>
                    <div class="print:hidden flex items-center gap-2">
                        <button onclick="window.print()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-200 hover:border-blue-400 hover:text-blue-600">Print summary</button>
                        <a href="{{ route('admin.reports.revenue', array_merge(request()->all(), ['export' => 'csv'])) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-200 hover:border-blue-400 hover:text-blue-600 transition">Export CSV</a>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-gray-700 dark:text-gray-200">
                    <div class="p-4 rounded-2xl bg-blue-600 text-white">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Collected</p>
                        <p class="mt-1 text-2xl font-semibold">₱{{ number_format($summary['total_paid'] ?? 0, 2) }}</p>
                        <p class="text-[11px] text-white/70">Receipts within the range</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/25">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-blue-600 dark:text-blue-200">Total billed</p>
                        <p class="mt-1 text-xl font-semibold text-blue-700 dark:text-blue-100">₱{{ number_format($summary['total_billed'] ?? 0, 2) }}</p>
                        <p class="text-[11px] text-blue-500 dark:text-blue-200">Invoices generated</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20">
                        @php($__diff = ($summary['total_billed'] ?? 0) - ($summary['total_paid'] ?? 0))
                        <p class="text-[11px] uppercase tracking-[0.2em] text-amber-600 dark:text-amber-200">Variance</p>
                        <p class="mt-1 text-xl font-semibold {{ $__diff > 0 ? 'text-amber-600 dark:text-amber-200' : 'text-green-600 dark:text-green-200' }}">₱{{ number_format($__diff, 2) }}</p>
                        <p class="text-[11px] text-amber-500 dark:text-amber-200">Outstanding vs paid</p>
                    </div>
                </div>
                <div class="mt-6">
                    <div id="revChart" class="h-64"></div>
                </div>
            </article>
        </div>
    </section>

    <!-- Detailed Tables -->
    <section class="space-y-5 print:space-y-3">
        <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6 print:border-gray-300 print:shadow-none">
            <div class="flex items-center justify-between mb-4 print:mb-2">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Revenue breakdown</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Grouped by {{ $filters['group_by'] ?? 'month' }}.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800/70 text-gray-600 dark:text-gray-300 uppercase text-[11px] tracking-wide">
                        <tr>
                            <th class="px-4 lg:px-5 py-3 text-left">Period</th>
                            <th class="px-4 lg:px-5 py-3 text-left">Bills</th>
                            <th class="px-4 lg:px-5 py-3 text-left">Paid</th>
                            <th class="px-4 lg:px-5 py-3 text-left">Unpaid</th>
                            <th class="px-4 lg:px-5 py-3 text-left">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse(($breakdown ?? []) as $row)
                            <tr>
                                <td class="px-4 lg:px-5 py-3">{{ $row['period'] }}</td>
                                <td class="px-4 lg:px-5 py-3">{{ number_format($row['bills']) }}</td>
                                <td class="px-4 lg:px-5 py-3">₱{{ number_format($row['paid'], 2) }}</td>
                                <td class="px-4 lg:px-5 py-3">₱{{ number_format($row['unpaid'], 2) }}</td>
                                <td class="px-4 lg:px-5 py-3 font-semibold">₱{{ number_format($row['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 lg:px-5 py-10 text-center text-gray-500">
                                    <div class="max-w-sm mx-auto space-y-2">
                                        <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-gray-300" />
                                        <p class="font-medium">No data for the selected filters.</p>
                                        <p class="text-xs">Try expanding the date range or clearing the customer filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

    </section>
    @elseif($isIssues)
    <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6 print:hidden">
        <form method="GET" class="space-y-4">
            <input type="hidden" name="view" value="issues">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs_anchor">From</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full h-[44px] rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100" />
                </div>
                <div>
                    <label class="block text-xs_anchor">To</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full h-[44px] rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100" />
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">Filter the reported issues by date range to track workload.</p>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center px-5 h-[42px] rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500 transition">Apply filters</button>
                    <a href="{{ route('admin.reports.revenue', ['view' => 'issues']) }}" class="inline-flex items-center justify-center px-5 h-[42px] rounded-xl bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-sm text-gray-700 dark:text-gray-100">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <section class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @php($issueSummary = $issueSummary ?? ['total' => 0, 'priority' => 0, 'completed' => 0])
            <div class="p-5 rounded-2xl bg-blue-600 text-white shadow">
                <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Total issues</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($issueSummary['total'] ?? 0) }}</p>
                <p class="text-xs text-white/80">Submitted within range</p>
            </div>
            <div class="p-5 rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200 shadow">
                <p class="text-[11px] uppercase tracking-[0.2em]">Priority</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($issueSummary['priority'] ?? 0) }}</p>
                <p class="text-xs">Flagged as urgent</p>
            </div>
            <div class="p-5 rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200 shadow">
                <p class="text-[11px] uppercase tracking-[0.2em]">Completed</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($issueSummary['completed'] ?? 0) }}</p>
                <p class="text-xs">Resolved tickets</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">Issues by status</h2>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    @forelse(($issueByStatus ?? []) as $row)
                        <li class="flex items-center justify-between">
                            <span>{{ $row['label'] }}</span>
                            <span class="font-semibold">{{ number_format($row['total']) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-400">No issue data for the selected range.</li>
                    @endforelse
                </ul>
            </article>
            <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">Issues by category</h2>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    @forelse(($issueByCategory ?? []) as $row)
                        <li class="flex items-center justify-between">
                            <span>{{ $row['label'] }}</span>
                            <span class="font-semibold">{{ number_format($row['total']) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-400">No categorized issues found.</li>
                    @endforelse
                </ul>
            </article>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">Issue timeline</h2>
                <div class="overflow-x-auto max-h-64">
                    <table class="min-w-full text-xs md:text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800/70 text-gray-600 dark:text-gray-300 uppercase tracking-[0.2em]">
                            <tr>
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-left">Issues</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse(($issueTimeline ?? []) as $row)
                                <tr>
                                    <td class="px-4 py-2">{{ $row['period'] }}</td>
                                    <td class="px-4 py-2 font-semibold">{{ number_format($row['total']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-6 text-center text-gray-400">No timeline data for the selected dates.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
            <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">Recent issue submissions</h2>
                <ul class="space-y-3 max-h-72 overflow-y-auto pr-1 text-xs text-gray-600 dark:text-gray-300">
                    @forelse(($recentIssues ?? []) as $issue)
                        <li class="rounded-xl bg-gray-50 dark:bg-gray-800 px-3 py-2">
                            <div class="flex items-center justify-between text-[11px] text-gray-400 dark:text-gray-500">
                                <span>{{ optional($issue->created_at)->format('M d, Y') }}</span>
                                <span class="uppercase font-semibold">{{ $issue->status ?? 'open' }}</span>
                            </div>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $issue->category ?? 'General' }}</p>
                            @if($issue->other_problem)
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $issue->other_problem }}</p>
                            @endif
                            @if($issue->message)
                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($issue->message, 120) }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-gray-400">No recent issues submitted within this range.</li>
                    @endforelse
                </ul>
            </article>
        </div>
    </section>
    @endif

    @if($isPrint)
    <section id="printable-reports" class="space-y-3">
        <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6 print:border-gray-300 print:shadow-none">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Printable Reports</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Generate a print-friendly snapshot of current billing and operational metrics.</p>
                </div>
                <button onclick="window.print()" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-blue-500 text-blue-600 text-xs font-semibold hover:bg-blue-50 print:hidden">
                    <x-heroicon-o-printer class="w-4 h-4" /> Print Reports
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs md:text-sm">
                    <thead class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-100 uppercase tracking-[0.2em]">
                        <tr>
                            <th class="px-4 py-2 text-left">Metric</th>
                            <th class="px-4 py-2 text-left">Value</th>
                            <th class="px-4 py-2 text-left">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr><td class="px-4 py-2">Total billed</td><td class="px-4 py-2">₱{{ number_format($summary['total_billed'] ?? 0, 2) }}</td><td class="px-4 py-2">Invoices generated in period</td></tr>
                        <tr><td class="px-4 py-2">Total collected</td><td class="px-4 py-2">₱{{ number_format($summary['total_paid'] ?? 0, 2) }}</td><td class="px-4 py-2">Paid receipts</td></tr>
                        <tr><td class="px-4 py-2">Bills created</td><td class="px-4 py-2">{{ number_format($operationalMetrics['bills_created'] ?? 0) }}</td><td class="px-4 py-2">Billing records counted</td></tr>
                        <tr><td class="px-4 py-2">New customers</td><td class="px-4 py-2">{{ number_format($operationalMetrics['registered_customers'] ?? 0) }}</td><td class="px-4 py-2">Registrations added</td></tr>
                        <tr><td class="px-4 py-2">Issue reports</td><td class="px-4 py-2">{{ number_format($operationalMetrics['issue_reports'] ?? 0) }}</td><td class="px-4 py-2">Filed by teams</td></tr>
                        <tr><td class="px-4 py-2">Meter replacements</td><td class="px-4 py-2">{{ number_format($operationalMetrics['meter_replacements'] ?? 0) }}</td><td class="px-4 py-2">Recorded in audits</td></tr>
                        <tr><td class="px-4 py-2">Meter damages</td><td class="px-4 py-2">{{ number_format($operationalMetrics['meter_damages'] ?? 0) }}</td><td class="px-4 py-2">Damage-related actions</td></tr>
                        <tr><td class="px-4 py-2">Disconnections</td><td class="px-4 py-2">{{ number_format($operationalMetrics['disconnection_events'] ?? 0) }}</td><td class="px-4 py-2">Performed actions</td></tr>
                        <tr><td class="px-4 py-2">Reconnactions</td><td class="px-4 py-2">{{ number_format($operationalMetrics['reconnection_events'] ?? 0) }}</td><td class="px-4 py-2">Restored accounts</td></tr>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
    @endif

</div>

<style>
@media print {
  body { background: #fff; }
  .print\:hidden { display: none !important; }
  .print\:px-0 { padding-left: 0 !important; padding-right: 0 !important; }
}
</style>
@if($isPayments)
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const raw = @json($breakdown ?? []);
  const data = Array.isArray(raw) && raw.length ? raw : [{ period: 'No data', paid: 0 }];
  const labels = data.map((r) => r.period || '');
  const paid = data.map((r) => Number(r.paid ?? 0));
  const lastVal = paid.length ? paid[paid.length - 1] : 0;
  const maxPaid = paid.length ? Math.max(...paid) : 0;
  let yMax = Math.max(maxPaid, (lastVal || 0) * 1.25);
  if (!isFinite(yMax) || yMax <= 0) yMax = 5000;
  const el = document.getElementById('revChart');
  if(!el) return;
  const isDark = document.documentElement.classList.contains('dark');
  const opts = {
    chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: 'Poppins, sans-serif' },
    series: [{ name: 'Revenue collected', data: paid }],
    xaxis: { categories: labels, labels: { style: { colors: isDark ? '#cbd5e1' : '#4b5563' } } },
    yaxis: { min: 0, max: yMax, tickAmount: 5, labels: { style: { colors: isDark ? '#cbd5e1' : '#4b5563' } } },
    stroke: { width: 3, curve: 'smooth' },
    fill: { type: 'gradient', gradient: { shadeIntensity: 0.5, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] } },
    colors: ['#10b981'],
    grid: { borderColor: isDark ? '#334155' : '#e5e7eb', strokeDashArray: 4 },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: function(val){ return '₱' + (Number(val||0)).toLocaleString(undefined,{ minimumFractionDigits:2, maximumFractionDigits:2 }); } } },
    noData: { text: 'No data for selected filters', style: { color: isDark ? '#cbd5e1' : '#64748b' } }
  };
  try { new ApexCharts(el, opts).render(); } catch(e) {}

  document.querySelectorAll('.rev-quick-range').forEach(function(btn){
    btn.addEventListener('click', function(){
      const days = Number(this.dataset.range || 0);
      if (!days) return;
      const toInput = document.querySelector('input[name="to"]');
      const fromInput = document.querySelector('input[name="from"]');
      const now = new Date();
      const past = new Date();
      past.setDate(now.getDate() - (days - 1));
      const fmt = (date) => date.toISOString().slice(0,10);
      if (toInput) toInput.value = fmt(now);
      if (fromInput) fromInput.value = fmt(past);
    });
  });
});
</script>
@endif
@endsection
