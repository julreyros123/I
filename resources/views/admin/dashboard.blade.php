@extends('layouts.admin')

@section('content')
<div class="w-full mx-auto px-4 sm:px-6 py-4 sm:py-5 lg:py-6 font-[Poppins] space-y-4 lg:space-y-6">

    <!-- KPI block moved below as its own row -->
    <div class="grid grid-cols-12 gap-6 mt-2 md:mt-3">
        <div class="col-span-12 grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
            <!-- Billed (This Month) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-4 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="shrink-0 kpi-icon rounded-full flex items-center justify-center bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300" style="--kpi-icon-size: 3rem;">
                            <x-heroicon-o-document-text class="w-full h-full" />
                        </div>
                        <div class="min-w-0 text-left space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Billed (This Month)</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">This month so far</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                            ₱{{ number_format($stats['month_billed'] ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Collected (This Month) -->
            <a href="{{ route('records.payments') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-4 hover:shadow-lg transition-shadow duration-200 focus:ring-2 focus:ring-blue-500 outline-none">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="shrink-0 kpi-icon rounded-full flex items-center justify-center bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300" style="--kpi-icon-size: 3rem;">
                            <x-heroicon-o-banknotes class="w-full h-full" />
                        </div>
                        <div class="min-w-0 text-left space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Collected (This Month)</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Go to payments</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-semibold text-green-600">
                            ₱{{ number_format($stats['month_collected'] ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Customers -->
            <a href="{{ route('admin.customers') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-4 hover:shadow-lg transition-shadow duration-200 focus:ring-2 focus:ring-blue-500 outline-none">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="shrink-0 kpi-icon rounded-full flex items-center justify-center bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300" style="--kpi-icon-size: 3rem;">
                            <x-heroicon-o-users class="w-full h-full" />
                        </div>
                        <div class="min-w-0 text-left space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Customers</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Manage customers</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                            {{ number_format($stats['customers'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Collection Rate -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-4 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="shrink-0 kpi-icon rounded-full flex items-center justify-center bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300" style="--kpi-icon-size: 3rem;">
                            <x-heroicon-o-arrow-trending-up class="w-full h-full" />
                        </div>
                        <div class="min-w-0 text-left space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Collection Rate</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Compared to last period</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                            {{ number_format(($stats['collection_rate'] ?? 0) * 100, 1) }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top row: Revenue Growth (full width) -->
    <div class="grid grid-cols-12 gap-4 lg:gap-5">
        <!-- Revenue Growth (much larger) -->
        <div class="col-span-12 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-2 lg:p-3">
            <div class="flex items-center justify-between mb-0">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Insights</h2>
                <div class="flex items-center gap-3 text-sm">
                    <div class="inline-flex items-center gap-1">
                        <span class="text-gray-500 dark:text-gray-300">Metric:</span>
                        <button type="button" id="metricRevenue" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-500">Revenue</button>
                        <button type="button" id="metricCustomers" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 border border-transparent dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700">Customers</button>
                        <button type="button" id="metricUsage" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 border border-transparent dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700">Avg Usage</button>
                    </div>
                    <div class="inline-flex items-center gap-1">
                        <span class="text-gray-500 dark:text-gray-300">Range:</span>
                        <button type="button" id="btnMonth" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-500">Monthly</button>
                        <button type="button" id="btnYear" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 border border-transparent dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700">Yearly</button>
                    </div>
                    <div class="hidden">
                        <span class="text-gray-500 dark:text-gray-300">Type:</span>
                        <button type="button" id="btnLine" class="px-2.5 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-500">Line</button>
                        <button type="button" id="btnBar" class="px-2.5 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 border border-transparent dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700">Bar</button>
                    </div>
                </div>
            </div>
            <div class="h-72 md:h-80 lg:h-[22rem] xl:h-[24rem] 2xl:h-[28rem]">
                <div class="relative w-full h-full overflow-hidden bg-gray-50 dark:bg-white/5 rounded-md">
                    <div id="revChart" class="absolute inset-0 w-full h-full"></div>
                </div>
            </div>
@php
// Labels
$monthsShort = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$labelsMonth = $monthsShort;
$currentYear = (int) date('Y');
$labelsYear = array(); for ($y=$currentYear-4; $y<=$currentYear; $y++) { $labelsYear[] = (string)$y; }

// Revenue (default)
$seriesMonthRevenue = isset($monthlyRevenue) ? (array)$monthlyRevenue : array();
if (count($seriesMonthRevenue) !== 12) { $seriesMonthRevenue = array_pad($seriesMonthRevenue, 12, 0); }
$hasM = false; foreach ($seriesMonthRevenue as $v) { if ((float)$v > 0) { $hasM = true; break; } }
if (!$hasM) { $seriesMonthRevenue = array(8,9,10,11,12,13,14,15,16,18,19,20); }
$currentYear = (int) date('Y');
$seriesYearRevenue = isset($yearlyRevenue) ? (array)$yearlyRevenue : array();
if (count($seriesYearRevenue) !== 5) { $seriesYearRevenue = array_pad($seriesYearRevenue, 5, 0); }
$hasY = false; foreach ($seriesYearRevenue as $v) { if ((float)$v > 0) { $hasY = true; break; } }
if (!$hasY) { $seriesYearRevenue = array(120,135,150,142,160); }

// Customers Added (demo until wired)
$seriesMonthCustomers = isset($monthlyCustomers) ? (array)$monthlyCustomers : array(3,5,4,8,6,7,9,11,10,12,9,8);
if (count($seriesMonthCustomers) !== 12) { $seriesMonthCustomers = array_pad($seriesMonthCustomers, 12, 0); }
$seriesYearCustomers = isset($yearlyCustomers) ? (array)$yearlyCustomers : array(60,72,81,95,110);
if (count($seriesYearCustomers) !== 5) { $seriesYearCustomers = array_pad($seriesYearCustomers, 5, 0); }

// Average Usage (m³) (demo until wired)
$seriesMonthUsage = isset($monthlyUsage) ? (array)$monthlyUsage : array(12,11,13,12,14,15,16,15,14,13,12,11);
if (count($seriesMonthUsage) !== 12) { $seriesMonthUsage = array_pad($seriesMonthUsage, 12, 0); }
$seriesYearUsage = isset($yearlyUsage) ? (array)$yearlyUsage : array(150,155,160,162,170);
if (count($seriesYearUsage) !== 5) { $seriesYearUsage = array_pad($seriesYearUsage, 5, 0); }
@endphp
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
            (function(){
              var el = document.getElementById('revChart');
              if (!el) return;
              var mode = 'month';
              var labelsMonth = @json($labelsMonth);
              var labelsYear = @json($labelsYear);
              var metric = 'revenue';
              // datasets
              var monthRevenue = @json($seriesMonthRevenue);
              var yearRevenue  = @json($seriesYearRevenue);
              var monthCustomers = @json($seriesMonthCustomers);
              var yearCustomers  = @json($seriesYearCustomers);
              var monthUsage = @json($seriesMonthUsage);
              var yearUsage  = @json($seriesYearUsage);

              function peso(v){
                if (v >= 1e9) return '₱'+(v/1e9).toFixed(1)+'B';
                if (v >= 1e6) return '₱'+(v/1e6).toFixed(1)+'M';
                if (v >= 1e3) return '₱'+(v/1e3).toFixed(1)+'K';
                return '₱'+Math.round(v).toLocaleString();
              }
              function num(v){ if (v>=1e6) return (v/1e6).toFixed(1)+'M'; if (v>=1e3) return (v/1e3).toFixed(1)+'K'; return (''+Math.round(v)); }
              function m3(v){ return num(v)+' m³'; }
              function isDark(){ return document.documentElement.classList.contains('dark'); }
              function activeData(){
                var d, l = (mode==='year')?labelsYear:labelsMonth;
                if (metric==='revenue') d = (mode==='year')?yearRevenue:monthRevenue;
                else if (metric==='customers') d = (mode==='year')?yearCustomers:monthCustomers;
                else d = (mode==='year')?yearUsage:monthUsage;
                return {d:d,l:l};
              }
              function yFormatter(){
                if (metric==='revenue') return function(v){ return peso(v); };
                if (metric==='customers') return function(v){ return num(v); };
                return function(v){ return m3(v); };
              }
              function palette(){
                // Tri-blue palette: dark blue, blue, sky blue
                var darkBlue = '#1e3a8a';
                var blue = '#2563eb';
                var sky = '#38bdf8';
                if (isDark()) return { dark:darkBlue, blue:'#60a5fa', sky:sky, grid:'rgba(255,255,255,0.10)', tick:'#cbd5e1', bg:'#0b1f3a' };
                return { dark:darkBlue, blue:blue, sky:sky, grid:'#E5E7EB', tick:'#6B7280', bg:'#F3F4F6' };
              }
              function buildOptions(){
                var pal = palette();
                var pack = activeData();
                var name = (mode==='year') ? (metric==='revenue'?'Yearly Revenue':(metric==='customers'?'Yearly Customers':'Yearly Avg Usage')) : (metric==='revenue'?'Monthly Revenue':(metric==='customers'?'Monthly Customers':'Monthly Avg Usage'));
                return {
                  chart: { type: 'area', height: '100%', toolbar: { show: false }, animations: { enabled: true }, dropShadow: { enabled: true, top: 6, left: 0, blur: 6, color: pal.blue, opacity: 0.35 } },
                  theme: { mode: isDark() ? 'dark' : 'light' },
                  series: [{ name: name, data: pack.d }],
                  xaxis: { categories: pack.l, labels: { style: { colors: pal.tick } }, axisBorder: { color: pal.grid }, axisTicks: { color: pal.grid } },
                  yaxis: { labels: { formatter: yFormatter(), style: { colors: pal.tick } } },
                  grid: { borderColor: pal.grid },
                  stroke: { curve: 'smooth', width: 2, colors: [pal.blue] },
                  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.08, stops: [0, 60, 100], gradientToColors: [pal.sky] } },
                  colors: [pal.dark, pal.blue, pal.sky].slice(1,2),
                  dataLabels: { enabled: false },
                  tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: yFormatter() } }
                };
              }

              var chart = new ApexCharts(el, buildOptions());
              chart.render();

              function setActive(btnOn, btnOff){
                btnOn.classList.add('bg-blue-600','text-white');
                btnOn.classList.remove('bg-gray-100','text-gray-700','dark:bg-gray-700','dark:text-gray-200');
                btnOff.classList.remove('bg-blue-600','text-white');
                btnOff.classList.add('bg-gray-100','text-gray-700','dark:bg-gray-700','dark:text-gray-200');
              }
              function setMetricActive(activeBtn, otherBtn1, otherBtn2){
                activeBtn.classList.add('bg-blue-600','text-white');
                activeBtn.classList.remove('bg-gray-100','text-gray-700','dark:bg-gray-700','dark:text-gray-200');
                [otherBtn1, otherBtn2].forEach(function(btn){
                  btn.classList.remove('bg-blue-600','text-white');
                  btn.classList.add('bg-gray-100','text-gray-700','dark:bg-gray-700','dark:text-gray-200');
                });
              }

              function updateChart(){
                var pal = palette();
                var pack = activeData();
                var name = (mode==='year') ? (metric==='revenue'?'Yearly Revenue':(metric==='customers'?'Yearly Customers':'Yearly Avg Usage')) : (metric==='revenue'?'Monthly Revenue':(metric==='customers'?'Monthly Customers':'Monthly Avg Usage'));
                chart.updateOptions({
                  chart: { dropShadow: { enabled: true, top: 6, left: 0, blur: 6, color: pal.blue, opacity: 0.35 } },
                  theme: { mode: isDark() ? 'dark' : 'light' },
                  xaxis: { categories: pack.l, labels: { style: { colors: pal.tick } }, axisBorder: { color: pal.grid }, axisTicks: { color: pal.grid } },
                  yaxis: { labels: { formatter: yFormatter(), style: { colors: pal.tick } } },
                  grid: { borderColor: pal.grid },
                  stroke: { colors: [pal.blue] },
                  colors: [pal.dark, pal.blue, pal.sky].slice(1,2),
                  tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: yFormatter() } }
                });
                chart.updateSeries([{ name: name, data: pack.d }]);
              }

              // Bind toggles
              var bM = document.getElementById('btnMonth');
              var bY = document.getElementById('btnYear');
              var mR = document.getElementById('metricRevenue');
              var mC = document.getElementById('metricCustomers');
              var mU = document.getElementById('metricUsage');
              if (bM && bY){
                bM.onclick = function(){ mode='month'; setActive(bM,bY); updateChart(); };
                bY.onclick = function(){ mode='year'; setActive(bY,bM); updateChart(); };
              }
              if (mR && mC && mU){
                mR.onclick = function(){ metric='revenue'; setMetricActive(mR,mC,mU); updateChart(); };
                mC.onclick = function(){ metric='customers'; setMetricActive(mC,mR,mU); updateChart(); };
                mU.onclick = function(){ metric='usage'; setMetricActive(mU,mR,mC); updateChart(); };
              }

              // Observe theme changes to update colors
              var mo = new MutationObserver(function(){ updateChart(); });
              mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            })();
            </script>
        </div>

    </div>

    <!-- Second row: Staff Reminders (8) + Analytics donut (4) -->
    <div class="grid grid-cols-12 gap-6">
        <!-- Staff Reminders -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Staff Reminders</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Reminders for staff to generate and deliver up-to-date bills.</p>
                </div>
                <a href="{{ route('records.billing') }}" class="text-sm text-blue-600 hover:text-blue-700">Go to billing</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/50 rounded-xl p-3 flex items-center gap-3">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-200 p-2">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-blue-700 dark:text-blue-200">Bills Pending Generation</p>
                        <p class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                            {{ number_format($pendingGenerationCount ?? 0) }} bills
                        </p>
                    </div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 rounded-xl p-3 flex items-center gap-3">
                    <div class="rounded-full bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-200 p-2">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-red-700 dark:text-red-200">Amount Pending Generation</p>
                        <p class="text-lg font-semibold text-red-900 dark:text-red-100">
                            ₱{{ number_format($pendingGenerationAmount ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <p class="text-xs uppercase tracking-wide text-gray-400">Bills to generate and deliver</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-2 text-left">Account</th>
                                <th class="px-4 py-2 text-left">Customer</th>
                                <th class="px-4 py-2 text-left">Bill Date</th>
                                <th class="px-4 py-2 text-left">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-100">
                            @forelse($pendingGenerationList as $bill)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $bill->account_no }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $bill->customer->name ?? '—' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ optional($bill->created_at)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm font-medium text-red-600 dark:text-red-400">
                                        ₱{{ number_format($bill->total_amount ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">No bills pending generation or delivery.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Analytics donut -->
        <div class="col-span-12 lg:col-span-4 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-100">Analytics</h2>
                <span class="text-[11px] lg:text-xs text-gray-500">Connection Types</span>
            </div>
            <div class="h-[12rem] md:h-[14rem] lg:h-[18rem] flex items-center justify-center">
                <div class="w-40 h-40 md:w-48 md:h-48 lg:w-56 lg:h-56 relative">
                    <svg viewBox="0 0 100 100" class="w-full h-full rotate-[-90deg]">
                        <!-- Background track -->
                        <circle cx="50" cy="50" r="40" fill="none" stroke="currentColor" class="text-gray-200 dark:text-slate-600" stroke-width="12" />
                        <!-- Segments: 60% blue, 25% green, 15% red -->
                        <!-- circumference ≈ 251.33 -->
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#1e3a8a" stroke-width="12" stroke-linecap="butt" stroke-dasharray="150.8 1000" stroke-dashoffset="0" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#2563eb" stroke-width="12" stroke-linecap="butt" stroke-dasharray="62.8 1000" stroke-dashoffset="-150.8" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#38bdf8" stroke-width="12" stroke-linecap="butt" stroke-dasharray="37.7 1000" stroke-dashoffset="-213.6" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center rotate-0">
                        <div class="inline-flex flex-row items-center gap-1 whitespace-nowrap w-max min-w-[110px] md:min-w-[130px] tracking-tight text-gray-800 dark:text-gray-100">
                            <span class="text-base md:text-lg font-semibold">—%</span>
                            <span class="text-sm md:text-base text-gray-500">Residential</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center text-[11px] md:text-xs">
                <div class="space-y-1">
                    <div class="flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full" style="background-color:#1e3a8a"></span><span>Residential</span></div>
                    <div class="font-semibold">—</div>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full" style="background-color:#2563eb"></span><span>Commercial</span></div>
                    <div class="font-semibold">—</div>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full" style="background-color:#38bdf8"></span><span>Industrial</span></div>
                    <div class="font-semibold">—</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection