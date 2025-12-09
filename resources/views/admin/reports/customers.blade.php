@extends('layouts.admin')

@section('title', 'Admin • Customer Data Report')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 font-[Poppins] space-y-6 lg:space-y-8">
    <div class="rounded-3xl bg-gradient-to-r from-sky-600 via-blue-500 to-indigo-500 text-white p-6 shadow-xl">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold">Customer Data Report</h1>
                <p class="text-sm/relaxed text-white/80">Understand customer growth, classifications, and connection health over time.</p>
            </div>
            <div class="inline-flex items-center gap-2 text-xs bg-white/10 px-3 py-1 rounded-xl">
                <x-heroicon-o-user-group class="w-4 h-4" /> Customer intelligence
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">From</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full h-[44px] rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">To</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full h-[44px] rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100" />
            </div>
            <div class="flex gap-2 items-end">
                <button type="submit" class="inline-flex items-center justify-center px-5 h-[44px] rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500 transition">Apply filters</button>
                <a href="{{ route('admin.reports.customers') }}" class="inline-flex items-center justify-center px-5 h-[44px] rounded-xl bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-sm text-gray-700 dark:text-gray-100 transition">Reset</a>
            </div>
        </form>
    </div>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
            <header>
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Overall status</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Active connections snapshot</p>
            </header>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-admin.metric-card label="Total" :value="number_format($overallCounts['total'] ?? 0)" icon="users" tone="blue" />
                <x-admin.metric-card label="Active" :value="number_format($overallCounts['active'] ?? 0)" icon="check-circle" tone="emerald" />
                <x-admin.metric-card label="Inactive" :value="number_format($overallCounts['inactive'] ?? 0)" icon="pause-circle" tone="amber" />
                <x-admin.metric-card label="Disconnected" :value="number_format($overallCounts['disconnected'] ?? 0)" icon="bolt-slash" tone="rose" />
            </div>
        </article>
        <article class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">New registrations</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $filters['from'] ?? '' }} → {{ $filters['to'] ?? '' }}</p>
                </div>
                <span class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-200 px-3 py-1 rounded-full">{{ number_format($newCustomers) }} new</span>
            </header>
            <div class="space-y-3">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500 mb-2">By status</p>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-300">
                        @forelse($statusBreakdown as $row)
                            <li class="flex items-center justify-between"><span>{{ $row->status ?? 'Unspecified' }}</span><span class="font-semibold">{{ number_format($row->total ?? 0) }}</span></li>
                        @empty
                            <li class="text-xs text-gray-400">No status breakdown available.</li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500 mb-2">By classification</p>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-300">
                        @forelse($classificationBreakdown as $row)
                            <li class="flex items-center justify-between"><span>{{ $row->classification ?? 'Unspecified' }}</span><span class="font-semibold">{{ number_format($row->total ?? 0) }}</span></li>
                        @empty
                            <li class="text-xs text-gray-400">No classification data.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </article>
    </section>

    <section class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
        <header class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Recent customer registrations</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Latest 20 entries</p>
            </div>
        </header>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-800/70 text-gray-600 dark:text-gray-300 uppercase text-[11px] tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Account No.</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Classification</th>
                        <th class="px-4 py-3 text-left">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentCustomers as $customer)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $customer->name }}</td>
                            <td class="px-4 py-3">{{ $customer->account_no }}</td>
                            <td class="px-4 py-3">{{ $customer->status ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $customer->classification ?? '—' }}</td>
                            <td class="px-4 py-3">{{ optional($customer->created_at)->format('M d, Y • h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-gray-300" />
                                    <p class="font-medium">No registrations for the chosen range.</p>
                                    <p class="text-xs">Try broadening the filter window.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">30-day registration trend</h3>
            <div class="h-64" id="customerTrendChart"></div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const el = document.getElementById('customerTrendChart');
    if(!el) return;

    const labels = @json($trendLabels ?? []);
    const counts = @json($trendCounts ?? []);
    const isDark = document.documentElement.classList.contains('dark');

    const options = {
        chart: {
            type: 'area',
            height: 260,
            toolbar: { show: false },
            fontFamily: 'Poppins, sans-serif'
        },
        series: [{
            name: 'Registrations',
            data: counts
        }],
        xaxis: {
            categories: labels,
            labels: {
                rotate: -45,
                style: { colors: isDark ? '#cbd5e1' : '#4b5563' }
            }
        },
        yaxis: {
            min: 0,
            tickAmount: 5,
            labels: { style: { colors: isDark ? '#cbd5e1' : '#4b5563' } }
        },
        stroke: { width: 3, curve: 'smooth' },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 0.5,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        colors: ['#38bdf8'],
        grid: {
            borderColor: isDark ? '#334155' : '#e5e7eb',
            strokeDashArray: 4
        },
        dataLabels: { enabled: false },
        tooltip: {
            y: { formatter: value => `${value} registrations` }
        },
        noData: {
            text: 'No registration data',
            style: { color: isDark ? '#cbd5e1' : '#64748b' }
        }
    };

    const chart = new ApexCharts(el, options);
    chart.render();
});
</script>
@endpush
@endsection
