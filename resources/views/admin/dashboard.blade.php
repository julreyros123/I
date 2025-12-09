@extends('layouts.admin')

@section('content')
<div class="w-full mx-auto px-4 sm:px-6 py-4 sm:py-5 lg:py-6 font-[Poppins] space-y-4 lg:space-y-6">

    <!-- KPI block moved below as its own row -->
    <div class="grid grid-cols-12 gap-6 mt-2 md:mt-3">
        <div class="col-span-12 grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
            <!-- Billed (This Month) -->
            <div class="group relative overflow-hidden rounded-2xl shadow-lg p-4 lg:p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 bg-blue-600 text-white">
                <div class="relative flex h-full flex-col justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20">
                            <x-heroicon-o-document-text class="w-6 h-6" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Billed revenue</p>
                            <p class="text-sm font-semibold text-white">Invoices this month</p>
                        </div>
                    </div>
                    <div class="text-right space-y-1 pt-6">
                        <p class="text-3xl font-semibold">₱{{ number_format($stats['month_billed'] ?? 0, 2) }}</p>
                        <p class="text-[11px] text-white/70">Live total billed</p>
                    </div>
                </div>
            </div>

            <!-- Collected (This Month) -->
            <a href="{{ route('records.payments') }}" class="group relative overflow-hidden rounded-2xl shadow-lg p-4 lg:p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500 bg-blue-600 text-white">
                <div class="relative flex h-full flex-col justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20">
                            <x-heroicon-o-banknotes class="w-6 h-6" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Collections</p>
                            <p class="text-sm font-semibold text-white">Posted receipts</p>
                        </div>
                    </div>
                    <div class="text-right space-y-1 pt-6">
                        <p class="text-3xl font-semibold">₱{{ number_format($stats['month_collected'] ?? 0, 2) }}</p>
                        <p class="text-[11px] text-white/70">Settled ledger payments</p>
                    </div>
                </div>
            </a>

            <!-- Customers -->
            <a href="{{ route('admin.customers') }}" class="group relative overflow-hidden rounded-2xl shadow-lg p-4 lg:p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500 bg-blue-600 text-white">
                <div class="relative flex h-full flex-col justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20">
                            <x-heroicon-o-users class="w-6 h-6" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Active customers</p>
                            <p class="text-sm font-semibold text-white">Accounts in good standing</p>
                        </div>
                    </div>
                    <div class="text-right space-y-1 pt-6">
                        <p class="text-4xl font-semibold">{{ number_format($stats['customers'] ?? 0) }}</p>
                        <p class="text-[11px] text-white/70">Profiles in good standing</p>
                    </div>
                </div>
            </a>

            <!-- Collection Rate -->
            <div class="group relative overflow-hidden rounded-2xl shadow-lg p-4 lg:p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 bg-blue-600 text-white">
                <div class="relative flex h-full flex-col justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20">
                            <x-heroicon-o-arrow-trending-up class="w-6 h-6" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Collection rate</p>
                            <p class="text-sm font-semibold text-white">Paid vs billed</p>
                        </div>
                    </div>
                    <div class="text-right space-y-1 pt-6">
                        <p class="text-3xl font-semibold">{{ number_format(($stats['collection_rate'] ?? 0) * 100, 1) }}%</p>
                        <p class="text-[11px] text-white/70">Target ≥ 90%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top row: Revenue Growth (full width) -->
    <div class="grid grid-cols-12 gap-4 lg:gap-5">
        <!-- Revenue Growth (much larger) -->
        <div class="col-span-12 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-3 lg:p-4">
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 mb-4 lg:mb-5">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Insights</h2>
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <div class="inline-flex flex-wrap items-center gap-1">
                        <span class="text-gray-500 dark:text-gray-300">Metric:</span>
                        <button type="button" id="metricRevenue" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-500">Revenue</button>
                        <button type="button" id="metricCustomers" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 border border-transparent dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700">Customers</button>
                        <button type="button" id="metricUsage" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 border border-transparent dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700">Avg Usage</button>
                    </div>
                    <div class="inline-flex flex-wrap items-center gap-1">
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

    <!-- Second row: Admin Tasks (8) + Analytics (4) -->
    <div class="grid grid-cols-12 gap-6">
        <!-- Admin Tasks -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Admin Tasks</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Stay on top of new applications that still need approvals or installations.</p>
                </div>
                <a href="{{ route('admin.applicants.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                    View applications
                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <a href="{{ route('admin.applicants.index', ['status' => 'pending']) }}" class="group relative overflow-hidden rounded-xl border border-amber-200 dark:border-amber-500/40 bg-amber-50 dark:bg-amber-900/10 p-4">
                    <div class="absolute inset-y-0 left-0 w-1 bg-amber-500/80 group-hover:bg-amber-500 transition-all"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-200 p-2">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-amber-700 dark:text-amber-200 tracking-wide uppercase">Need approval</p>
                            <p class="text-2xl font-semibold text-amber-900 dark:text-amber-50">{{ number_format($applicationsPendingApprovalCount ?? 0) }}</p>
                            <p class="text-[11px] text-amber-700/80 dark:text-amber-100/70">Applications awaiting review</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.applicants.index', ['status' => 'scheduled']) }}" class="group relative overflow-hidden rounded-xl border border-sky-200 dark:border-sky-500/40 bg-sky-50 dark:bg-sky-900/10 p-4">
                    <div class="absolute inset-y-0 left-0 w-1 bg-sky-500/80 group-hover:bg-sky-500 transition-all"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="rounded-full bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-200 p-2">
                            <x-heroicon-o-wrench-screwdriver class="w-5 h-5" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-sky-700 dark:text-sky-200 tracking-wide uppercase">Need installation</p>
                            <p class="text-2xl font-semibold text-sky-900 dark:text-sky-50">{{ number_format($applicationsPendingInstallationCount ?? 0) }}</p>
                            <p class="text-[11px] text-sky-700/80 dark:text-sky-100/70">Jobs scheduled or in-progress</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Awaiting approval</h3>
                        <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-200 px-2 py-0.5 text-[11px] font-medium">{{ number_format($applicationsPendingApprovalCount ?? 0) }}</span>
                    </div>
                    <ul class="space-y-3">
                        @forelse($applicationsPendingApprovalList as $application)
                            <li class="rounded-xl border border-gray-100 dark:border-gray-700/70 bg-white dark:bg-gray-900/40 px-3 py-2.5 shadow-sm">
                                @php($statusLabel = \Illuminate\Support\Str::headline($application->status ?? 'pending'))
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $application->applicant_name ?? 'Unnamed applicant' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $application->address ?? 'No address provided' }}</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Filed {{ optional($application->created_at)->diffForHumans() ?? '—' }}</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="inline-flex items-center rounded-full bg-amber-100/80 dark:bg-amber-500/30 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:text-amber-200">{{ $statusLabel }}</span>
                                        <a href="{{ route('admin.applicants.show', $application->id) }}" class="text-xs font-medium text-blue-600 hover:text-blue-500">Review</a>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500 dark:text-gray-400">No applications awaiting approval.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Awaiting installation</h3>
                        <span class="inline-flex items-center rounded-full bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-200 px-2 py-0.5 text-[11px] font-medium">{{ number_format($applicationsPendingInstallationCount ?? 0) }}</span>
                    </div>
                    <ul class="space-y-3">
                        @forelse($applicationsPendingInstallationList as $application)
                            <li class="rounded-xl border border-gray-100 dark:border-gray-700/70 bg-white dark:bg-gray-900/40 px-3 py-2.5 shadow-sm">
                                @php($statusLabel = \Illuminate\Support\Str::headline($application->status ?? 'scheduled'))
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $application->applicant_name ?? 'Unnamed applicant' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $application->address ?? 'No address provided' }}</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Schedule {{ optional($application->schedule_date)->format('M d, Y') ?? 'Not set' }}</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="inline-flex items-center rounded-full bg-sky-100/80 dark:bg-sky-500/30 px-2 py-0.5 text-[11px] font-medium text-sky-700 dark:text-sky-200">{{ $statusLabel }}</span>
                                        <a href="{{ route('admin.applicants.show', $application->id) }}" class="text-xs font-medium text-blue-600 hover:text-blue-500">Open</a>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500 dark:text-gray-400">No installations awaiting action.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Analytics donut -->
        <div class="col-span-12 lg:col-span-4 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-100">Connection Analytics</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Live mix of connection classifications.</p>
                </div>
                <div class="inline-flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 text-[11px] font-medium overflow-hidden">
                    <button type="button" data-connection-mode="count" class="px-3 py-1.5 bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-300">Count</button>
                    <button type="button" data-connection-mode="percentage" class="px-3 py-1.5 text-gray-500 dark:text-gray-400 hover:text-blue-500">Share</button>
                </div>
            </div>
            <div class="h-[12rem] md:h-[14rem] lg:h-[18rem] flex items-center justify-center">
                <div id="connectionChart" class="w-full h-full"></div>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($connectionAnalytics as $index => $entry)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $connectionColorPalette[$index % max(1, count($connectionColorPalette))] ?? '#2563eb' }}"></span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $entry['label'] }}</span>
                        </div>
                        <div class="text-right font-semibold text-gray-800 dark:text-gray-100">
                            <span data-connection-count-value="{{ $index }}">{{ number_format($entry['count']) }}</span>
                            <span class="hidden" data-connection-percentage-value="{{ $index }}">{{ number_format($entry['percentage'], 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">No connection data yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const chartEl = document.querySelector('#connectionChart');
        if (!chartEl || typeof ApexCharts === 'undefined') { return; }

        const palette = @json($connectionColorPalette ?? []);
        const labels = @json($connectionAnalyticsLabels ?? []);
        const counts = @json(array_map('intval', $connectionAnalyticsCounts ?? []));
        const percentages = @json(($connectionAnalytics ?? collect())->pluck('percentage')->map(fn($v) => (float) $v)->toArray());

        const hasData = (counts || []).some(function(value){ return Number(value) > 0; });
        const seriesCounts = hasData ? counts : labels.map(() => 0);
        const seriesPercent = hasData ? percentages : labels.map(() => 0);

        const chart = new ApexCharts(chartEl, {
            chart: {
                type: 'donut',
                height: '100%',
                toolbar: { show: false }
            },
            labels: labels,
            series: seriesCounts,
            colors: palette.length ? palette : undefined,
            legend: { show: false },
            dataLabels: {
                enabled: true,
                formatter: function(val){ return Math.round(val) + '%'; }
            },
            responsive: [{
                breakpoint: 768,
                options: { dataLabels: { enabled: false } }
            }]
        });

        chart.render();

        const toggleButtons = document.querySelectorAll('[data-connection-mode]');
        const countBadges = document.querySelectorAll('[data-connection-count-value]');
        const percentBadges = document.querySelectorAll('[data-connection-percentage-value]');

        function setMode(mode){
            toggleButtons.forEach(function(btn){
                const active = btn.getAttribute('data-connection-mode') === mode;
                btn.classList.toggle('bg-white', active);
                btn.classList.toggle('dark:bg-gray-800', active);
                btn.classList.toggle('text-blue-600', active);
                btn.classList.toggle('dark:text-blue-300', active);
                btn.classList.toggle('text-gray-500', !active);
                btn.classList.toggle('dark:text-gray-400', !active);
            });

            countBadges.forEach(function(el){ el.classList.toggle('hidden', mode !== 'count'); });
            percentBadges.forEach(function(el){ el.classList.toggle('hidden', mode !== 'percentage'); });

            if (mode === 'count') {
                chart.updateSeries(seriesCounts);
            } else {
                chart.updateSeries(seriesPercent.length ? seriesPercent : labels.map(() => 0));
            }
        }

        toggleButtons.forEach(function(btn){
            btn.addEventListener('click', function(){ setMode(btn.getAttribute('data-connection-mode')); });
        });

        setMode('count');
    });
    </script>
</div>
@endsection