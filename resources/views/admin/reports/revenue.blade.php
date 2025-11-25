@extends('layouts.admin')

@section('title', 'Admin • Revenue Report')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Poppins] space-y-6 print:px-0">
    <div class="flex items-center justify-between print:hidden">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Revenue Report</h1>
        <div></div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 print:hidden">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">From</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full border rounded-lg px-3 h-[42px] dark:bg-gray-700 dark:text-white"/>
            </div>
            <div>
                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">To</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full border rounded-lg px-3 h-[42px] dark:bg-gray-700 dark:text-white"/>
            </div>
            <div>
                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Group by</label>
                <select name="group_by" class="w-full border rounded-lg px-3 h-[42px] dark:bg-gray-700 dark:text-white">
                    <option value="day" @selected(($filters['group_by'] ?? '')==='day')>Daily</option>
                    <option value="month" @selected(($filters['group_by'] ?? 'month')==='month')>Monthly</option>
                    <option value="year" @selected(($filters['group_by'] ?? '')==='year')>Yearly</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 h-[42px] rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Apply</button>
                <a href="{{ route('admin.reports.revenue') }}" class="px-4 h-[42px] inline-flex items-center justify-center rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200">Reset</a>
            </div>
        </form>
    </div>

    <!-- Chart + Summary Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 print:gap-2">
        <!-- Chart (left) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Revenue Over Time</h2>
                <span class="text-[11px] text-gray-500">Adapted to filters</span>
            </div>
            <div id="revChart" class="h-64"></div>
        </div>
        <!-- Cards (right) -->
        <div class="grid grid-cols-1 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-green-600/10 dark:bg-green-500/20 text-green-600 dark:text-green-300 flex items-center justify-center">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </div>
                    <div class="ml-auto text-right">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Paid</div>
                        <div class="text-2xl font-semibold text-green-600">₱{{ number_format($summary['total_paid'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 print:shadow-none print:border print:border-gray-300">
        <div class="flex items-center justify-between mb-3 print:mb-2">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Revenue Breakdown</h2>
            <button onclick="window.print()" class="print:hidden text-sm text-blue-600 hover:text-blue-700">Print</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2">Period</th>
                        <th class="px-4 py-2">Bills</th>
                        <th class="px-4 py-2">Paid</th>
                        <th class="px-4 py-2">Unpaid</th>
                        <th class="px-4 py-2">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse(($breakdown ?? []) as $row)
                        <tr>
                            <td class="px-4 py-2">{{ $row['period'] }}</td>
                            <td class="px-4 py-2">{{ number_format($row['bills']) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($row['paid'], 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($row['unpaid'], 2) }}</td>
                            <td class="px-4 py-2 font-semibold">₱{{ number_format($row['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No data for selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
  body { background: #fff; }
  .print\:hidden { display: none !important; }
  .print\:px-0 { padding-left: 0 !important; padding-right: 0 !important; }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const raw = @json($breakdown ?? []);
  const data = Array.isArray(raw) && raw.length ? raw : [{ period: 'No Data', paid: 0 }];
  const labels = data.map(r => r.period || '');
  const paid = data.map(r => Number(r.paid ?? 0));
  const lastVal = paid.length ? paid[paid.length - 1] : 0;
  const maxPaid = paid.length ? Math.max.apply(null, paid) : 0;
  let yMax = Math.max(maxPaid, (lastVal || 0) + 5000);
  if (yMax === 0) yMax = 5000;
  const el = document.getElementById('revChart');
  if(!el) return;
  const isDark = document.documentElement.classList.contains('dark');
  const opts = {
    chart: { type: 'line', height: 260, toolbar: { show: false } },
    series: [{ name: 'Revenue (Paid)', data: paid }],
    xaxis: { categories: labels, labels: { style: { colors: isDark ? '#cbd5e1' : '#4b5563' } } },
    yaxis: { min: 0, max: yMax, labels: { style: { colors: isDark ? '#cbd5e1' : '#4b5563' } } },
    stroke: { width: 3, curve: 'smooth' },
    colors: ['#16a34a'],
    grid: { borderColor: isDark ? '#334155' : '#e5e7eb' },
    dataLabels: { enabled: false },
    noData: { text: 'No data', style: { color: isDark ? '#cbd5e1' : '#64748b' } }
  };
  try { new ApexCharts(el, opts).render(); } catch(e) {}
});
</script>
@endsection
