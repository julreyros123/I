@extends('layouts.admin')

@section('content')
<div class="w-full mx-auto px-4 sm:px-6 py-3 sm:py-4 lg:py-5 font-[Poppins] space-y-4 lg:space-y-6">

    <!-- KPI block moved below as its own row -->
    <div class="grid grid-cols-12 gap-5 mt-1 md:mt-2">
        <div class="col-span-12 flex flex-wrap items-center justify-between gap-2">
            <div class="text-xs sm:text-[13px] text-gray-600 dark:text-gray-300 font-medium">
                Synced with Insights filter · <span id="kpiRangeLabel">{{ \Illuminate\Support\Carbon::parse($defaultRangeStart ?? now()->startOfMonth())->format('M d, Y') }} – {{ \Illuminate\Support\Carbon::parse($defaultRangeEnd ?? now())->format('M d, Y') }}</span>
            </div>
            <div class="text-[11px] uppercase tracking-[0.3em] text-gray-500 dark:text-gray-400" id="kpiRangeStatus">Live</div>
        </div>
        <div id="kpiCards" class="col-span-12 grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-3.5 lg:gap-4 auto-rows-fr transition-opacity duration-200">
            <!-- Billed (This Month) -->
            <div class="group relative overflow-hidden rounded-2xl min-h-[150px] sm:min-h-[170px] shadow-lg p-3 sm:p-3.5 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 text-white" style="background-color: var(--kpi-primary);">
                <div class="relative flex h-full flex-col justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/25 text-white">
                            <x-heroicon-o-document-text class="w-4 h-4" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-white font-semibold">Billed revenue</p>
                            <p class="text-sm font-semibold text-white">Invoices this month</p>
                        </div>
                    </div>
                    <div class="space-y-1 text-right">
                        <p id="kpiBilledValue" class="text-xl sm:text-2xl font-semibold leading-tight">₱{{ number_format($stats['month_billed'] ?? 0, 2) }}</p>
                        <p class="text-[12px] sm:text-[13px] font-semibold text-white/90">Range billed total</p>
                    </div>
                </div>
            </div>

            <!-- Collected (This Month) -->
            <a href="{{ route('records.payments') }}" class="group relative overflow-hidden rounded-2xl min-h-[150px] sm:min-h-[170px] shadow-lg p-3 sm:p-3.5 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[var(--kpi-primary)] text-white" style="background-color: var(--kpi-primary);">
                <div class="relative flex h-full flex-col justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/25 text-white">
                            <x-heroicon-o-banknotes class="w-4 h-4" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-white font-semibold">Collections</p>
                            <p class="text-sm font-semibold text-white">Posted receipts</p>
                        </div>
                    </div>
                    <div class="space-y-1 text-right">
                        <p id="kpiCollectedValue" class="text-xl sm:text-2xl font-semibold leading-tight">₱{{ number_format($stats['month_collected'] ?? 0, 2) }}</p>
                        <p class="text-[12px] sm:text-[13px] font-semibold text-white/90">Settled ledger payments</p>
                    </div>
                </div>
            </a>

            <!-- Customers -->
            <a href="{{ route('admin.customers') }}" class="group relative overflow-hidden rounded-2xl min-h-[150px] sm:min-h-[170px] shadow-lg p-3 sm:p-3.5 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[var(--kpi-primary)] text-white" style="background-color: var(--kpi-primary);">
                <div class="relative flex h-full flex-col justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/25 text-white">
                            <x-heroicon-o-users class="w-4 h-4" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-white font-semibold">New registrations</p>
                            <p class="text-sm font-semibold text-white">Customers added</p>
                        </div>
                    </div>
                    <div class="space-y-1 text-right">
                        <p id="kpiCustomerValue" class="text-2xl sm:text-3xl font-semibold leading-tight">{{ number_format($stats['new_customers'] ?? 0) }}</p>
                        <p class="text-[12px] sm:text-[13px] font-semibold text-white/90">Within selected range</p>
                    </div>
                </div>
            </a>

            <!-- Collection Rate -->
            <div class="group relative overflow-hidden rounded-2xl min-h-[150px] sm:min-h-[170px] shadow-lg p-3 sm:p-3.5 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 text-white" style="background-color: var(--kpi-primary);">
                <div class="relative flex h-full flex-col justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/25 text-white">
                            <x-heroicon-o-arrow-trending-up class="w-4 h-4" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-white font-semibold">Collection rate</p>
                            <p class="text-sm font-semibold text-white">Paid vs billed</p>
                        </div>
                    </div>
                    <div class="space-y-1 text-right">
                        <p id="kpiCollectionRateValue" class="text-xl sm:text-2xl font-semibold leading-tight">{{ number_format(($stats['collection_rate'] ?? 0) * 100, 1) }}%</p>
                        <p class="text-[12px] sm:text-[13px] font-semibold text-white/90">Target ≥ 90%</p>
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
                    <div class="inline-flex flex-wrap items-center gap-2">
                        <span class="text-gray-500 dark:text-gray-300">Range:</span>
                        <input type="date" id="rangeStart" class="px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-200" />
                        <span class="text-gray-400">to</span>
                        <input type="date" id="rangeEnd" class="px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-200" />
                        <button type="button" id="applyRange" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-500">Apply</button>
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
        </div>

    </div>
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
            (function(){
              var el = document.getElementById('revChart');
              if (!el) return;

              var metric = 'revenue';
              var chart = null;
              var chartData = { labels: [], data: [] };
              var defaultRangeStart = "{{ $defaultRangeStart ?? now()->startOfMonth()->toDateString() }}";
              var defaultRangeEnd = "{{ $defaultRangeEnd ?? now()->toDateString() }}";
              var kpiEndpoint = "{{ route('admin.dashboard.stats') }}";
              var insightsEndpoint = "{{ route('admin.dashboard.insights') }}";
              var kpiEls = {
                label: document.getElementById('kpiRangeLabel'),
                status: document.getElementById('kpiRangeStatus'),
                cards: document.getElementById('kpiCards'),
                billed: document.getElementById('kpiBilledValue'),
                collected: document.getElementById('kpiCollectedValue'),
                customers: document.getElementById('kpiCustomerValue'),
                rate: document.getElementById('kpiCollectionRateValue')
              };
              var kpiState = {
                start: defaultRangeStart,
                end: defaultRangeEnd,
                loading: false
              };
              var insightsState = { loading: false };
              var dateFormatter = new Intl.DateTimeFormat(undefined, { month: 'short', day: 'numeric', year: 'numeric' });

              function pad2(n){ return String(n).padStart(2, '0'); }
              function formatDateInput(d){
                if (!d) return '';
                try {
                  var dt = (d instanceof Date) ? d : new Date(d);
                  if (!isFinite(dt)) return '';
                  return dt.getFullYear() + '-' + pad2(dt.getMonth() + 1) + '-' + pad2(dt.getDate());
                } catch (e) {
                  return '';
                }
              }
              function formatDisplayDate(iso){
                if (!iso) return '';
                try {
                  var dt = new Date(iso);
                  return isFinite(dt) ? dateFormatter.format(dt) : String(iso);
                } catch (e) {
                  return String(iso);
                }
              }
              function setMetricActive(activeBtn, inactiveBtn1, inactiveBtn2){
                var active = 'bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-500';
                var inactive = 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-transparent dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700';
                if (activeBtn){ activeBtn.className = 'px-3 py-1.5 rounded-md ' + active; }
                if (inactiveBtn1){ inactiveBtn1.className = 'px-3 py-1.5 rounded-md ' + inactive; }
                if (inactiveBtn2){ inactiveBtn2.className = 'px-3 py-1.5 rounded-md ' + inactive; }
              }

              function metricName(){
                if (metric === 'customers') return 'Customers';
                if (metric === 'usage') return 'Avg Usage';
                return 'Revenue';
              }
              function peso(v){
                if (v >= 1e9) return '₱'+(v/1e9).toFixed(1)+'B';
                if (v >= 1e6) return '₱'+(v/1e6).toFixed(1)+'M';
                if (v >= 1e3) return '₱'+(v/1e3).toFixed(1)+'K';
                return '₱'+Math.round(v).toLocaleString();
              }
              function num(v){ if (v>=1e6) return (v/1e6).toFixed(1)+'M'; if (v>=1e3) return (v/1e3).toFixed(1)+'K'; return (''+Math.round(v)); }
              function m3(v){ return num(v)+' m³'; }
              function isDark(){ return document.documentElement.classList.contains('dark'); }
              function palette(){
                var darkBlue = '#1e3a8a';
                var blue = '#2563eb';
                var sky = '#38bdf8';
                if (isDark()) return { dark:darkBlue, blue:'#60a5fa', sky:sky, grid:'rgba(255,255,255,0.10)', tick:'#cbd5e1', bg:'#0b1f3a' };
                return { dark:darkBlue, blue:blue, sky:sky, grid:'#E5E7EB', tick:'#6B7280', bg:'#F3F4F6' };
              }
              function formatPeso(v){
                var num = Number(v || 0);
                return '₱' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
              }
              function formatPercent(v){
                var num = Number(v || 0);
                return (num * 100).toFixed(1) + '%';
              }
              function toggleKpiLoading(isLoading){
                if (!kpiEls.cards) return;
                if (isLoading){
                  kpiEls.cards.classList.add('opacity-60','pointer-events-none');
                  if (kpiEls.status) kpiEls.status.textContent = 'Syncing';
                } else {
                  kpiEls.cards.classList.remove('opacity-60','pointer-events-none');
                }
              }
              function updateKpiRangeLabel(){
                if (!kpiEls.label) return;
                kpiEls.label.textContent = formatDisplayDate(kpiState.start) + ' – ' + formatDisplayDate(kpiState.end);
              }
              function updateKpiRangeStatus(){
                if (!kpiEls.status) return;
                var today = formatDateInput(new Date());
                if (kpiState.loading){
                  kpiEls.status.textContent = 'Syncing';
                } else if (kpiState.end === today){
                  kpiEls.status.textContent = 'Live';
                } else {
                  kpiEls.status.textContent = 'Backtrack';
                }
              }
              function updateKpiCards(data){
                if (!data) data = {};
                if (kpiEls.billed) kpiEls.billed.textContent = formatPeso(data.billed_total ?? data.month_billed ?? 0);
                if (kpiEls.collected) kpiEls.collected.textContent = formatPeso(data.collected_total ?? data.month_collected ?? 0);
                if (kpiEls.customers) kpiEls.customers.textContent = Number(data.new_customers ?? data.customers ?? 0).toLocaleString();
                if (kpiEls.rate) kpiEls.rate.textContent = formatPercent(data.collection_rate ?? 0);
              }
              function yFormatter(){
                if (metric==='revenue') return function(v){ return peso(v); };
                if (metric==='customers') return function(v){ return num(v); };
                return function(v){ return m3(v); };
              }
              function buildChartOptions(){
                var pal = palette();
                return {
                  chart: { type: 'area', height: '100%', toolbar: { show: false }, animations: { enabled: true }, dropShadow: { enabled: true, top: 6, left: 0, blur: 6, color: pal.blue, opacity: 0.35 } },
                  theme: { mode: isDark() ? 'dark' : 'light' },
                  series: [{ name: metricName(), data: chartData.data }],
                  xaxis: { categories: chartData.labels, labels: { rotate: -45, style: { colors: palette().tick } }, axisBorder: { color: pal.grid }, axisTicks: { color: pal.grid } },
                  yaxis: { labels: { formatter: yFormatter(), style: { colors: pal.tick } } },
                  grid: { borderColor: pal.grid },
                  stroke: { curve: 'smooth', width: 2, colors: [pal.blue] },
                  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.08, stops: [0, 60, 100], gradientToColors: [pal.sky] } },
                  colors: [pal.dark, pal.blue, pal.sky].slice(1,2),
                  dataLabels: { enabled: false },
                  tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: yFormatter() } }
                };
              }
              function ensureChart(){
                if (chart) return;
                chart = new ApexCharts(el, buildChartOptions());
                chart.render();
              }
              function refreshChart(){
                if (!chart){ ensureChart(); }
                var pal = palette();
                chart.updateOptions({
                  chart: { dropShadow: { enabled: true, top: 6, left: 0, blur: 6, color: pal.blue, opacity: 0.35 } },
                  theme: { mode: isDark() ? 'dark' : 'light' },
                  xaxis: { categories: chartData.labels, labels: { rotate: -45, style: { colors: pal.tick } }, axisBorder: { color: pal.grid }, axisTicks: { color: pal.grid } },
                  yaxis: { labels: { formatter: yFormatter(), style: { colors: pal.tick } } },
                  grid: { borderColor: pal.grid },
                  stroke: { colors: [pal.blue] },
                  colors: [pal.dark, pal.blue, pal.sky].slice(1,2),
                  tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: yFormatter() } }
                });
                chart.updateSeries([{ name: metricName(), data: chartData.data }]);
              }
              function syncKpis(startISO, endISO){
                if (!kpiEndpoint) return;
                kpiState.loading = true;
                toggleKpiLoading(true);
                updateKpiRangeStatus();
                var params = new URLSearchParams();
                if (startISO) params.append('start', startISO);
                if (endISO) params.append('end', endISO);
                var url = kpiEndpoint + (params.toString() ? ('?' + params.toString()) : '');
                fetch(url, {
                  headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(res){
                  if (!res.ok) throw new Error('Failed to fetch KPI stats');
                  return res.json();
                }).then(function(payload){
                  if (!payload || payload.ok !== true) throw new Error('Invalid KPI payload');
                  kpiState.start = payload.range && payload.range.start ? payload.range.start : startISO;
                  kpiState.end = payload.range && payload.range.end ? payload.range.end : endISO;
                  updateKpiCards(payload.stats || {});
                  updateKpiRangeLabel();
                  kpiState.loading = false;
                  updateKpiRangeStatus();
                }).catch(function(err){
                  console.error(err);
                  kpiState.loading = false;
                  if (kpiEls.status) kpiEls.status.textContent = 'Offline';
                }).finally(function(){
                  toggleKpiLoading(false);
                });
              }
              function fetchInsights(metricKey, startISO, endISO){
                if (!insightsEndpoint) return;
                insightsState.loading = true;
                var params = new URLSearchParams();
                params.append('metric', metricKey || 'revenue');
                if (startISO) params.append('start', startISO);
                if (endISO) params.append('end', endISO);
                fetch(insightsEndpoint + '?' + params.toString(), {
                  headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(res){
                  if (!res.ok) throw new Error('Failed to load insights');
                  return res.json();
                }).then(function(payload){
                  chartData.labels = payload.labels || [];
                  chartData.data = (payload.data || []).map(function(val){
                    var numVal = parseFloat(val);
                    return isFinite(numVal) ? numVal : 0;
                  });
                  refreshChart();
                }).catch(function(err){
                  console.error(err);
                }).finally(function(){
                  insightsState.loading = false;
                });
              }

              ensureChart();
              var mR = document.getElementById('metricRevenue');
              var mC = document.getElementById('metricCustomers');
              var mU = document.getElementById('metricUsage');
              if (mR && mC && mU){
                mR.onclick = function(){ metric='revenue'; setMetricActive(mR,mC,mU); fetchInsights(metric, kpiState.start, kpiState.end); };
                mC.onclick = function(){ metric='customers'; setMetricActive(mC,mR,mU); fetchInsights(metric, kpiState.start, kpiState.end); };
                mU.onclick = function(){ metric='usage'; setMetricActive(mU,mR,mC); fetchInsights(metric, kpiState.start, kpiState.end); };
              }

              var applyBtn = document.getElementById('applyRange');
              if (applyBtn){
                applyBtn.addEventListener('click', function(){
                  var startInput = document.getElementById('rangeStart');
                  var endInput = document.getElementById('rangeEnd');
                  var startVal = startInput && startInput.value ? new Date(startInput.value) : null;
                  var endVal = endInput && endInput.value ? new Date(endInput.value) : null;
                  if (startVal && endVal && startVal > endVal){
                    var tmp = startVal; startVal = endVal; endVal = tmp;
                    if (startInput) startInput.value = formatDateInput(startVal);
                    if (endInput) endInput.value = formatDateInput(endVal);
                  }
                  kpiState.start = formatDateInput(startVal);
                  kpiState.end = formatDateInput(endVal);
                  updateKpiRangeLabel();
                  updateKpiRangeStatus();
                  syncKpis(kpiState.start, kpiState.end);
                  fetchInsights(metric, kpiState.start, kpiState.end);
                });
              }

              // Initial sync on load
              (function(){
                var startInput = document.getElementById('rangeStart');
                var endInput = document.getElementById('rangeEnd');
                if (startInput && !startInput.value) startInput.value = defaultRangeStart;
                if (endInput && !endInput.value) endInput.value = defaultRangeEnd;
                updateKpiRangeLabel();
                updateKpiRangeStatus();
                syncKpis(kpiState.start, kpiState.end);
                fetchInsights(metric, kpiState.start, kpiState.end);
              })();

              // Observe theme changes to update colors
              var mo = new MutationObserver(function(){ refreshChart(); });
              mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            })();
            </script>
        </div>

    </div>

    <!-- Second row: Admin Tasks (8) + Analytics (4) -->
    <div class="grid grid-cols-12 gap-5 lg:gap-6 xl:gap-7 px-3 sm:px-4 lg:px-6 mb-5 lg:mb-7">
        <!-- Admin Tasks -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Admin Tasks</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Stay on top of new applications that still need approvals or installations.</p>
                </div>
                <a href="{{ route('admin.applicants.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                    View applications
                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('admin.applicants.index', ['status' => 'pending']) }}" class="group relative overflow-hidden rounded-2xl border border-amber-200 dark:border-amber-500/40 bg-amber-50 dark:bg-amber-900/10 p-4">
                    <div class="absolute inset-y-0 left-0 w-1 bg-amber-500/80 group-hover:bg-amber-500 transition-all"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-200 p-2.5">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-amber-700 dark:text-amber-200 tracking-wide uppercase">Need approval</p>
                            <p class="text-2xl font-semibold text-amber-900 dark:text-amber-50">{{ number_format($applicationsPendingApprovalCount ?? 0) }}</p>
                            <p class="text-[11px] text-amber-700/80 dark:text-amber-100/70">Applications awaiting review</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.applicants.index', ['status' => 'scheduled']) }}" class="group relative overflow-hidden rounded-2xl border border-sky-200 dark:border-sky-500/40 bg-sky-50 dark:bg-sky-900/10 p-4">
                    <div class="absolute inset-y-0 left-0 w-1 bg-sky-500/80 group-hover:bg-sky-500 transition-all"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="rounded-full bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-200 p-2.5">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-5">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Awaiting approval</h3>
                        <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-200 px-2 py-0.5 text-[11px] font-medium">{{ number_format($applicationsPendingApprovalCount ?? 0) }}</span>
                    </div>
                    <ul class="space-y-2">
                        @forelse($applicationsPendingApprovalList as $application)
                            <li class="rounded-xl border border-gray-100 dark:border-gray-700/70 bg-white dark:bg-gray-900/40 px-3.5 py-1.5 shadow-sm">
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

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Awaiting installation</h3>
                        <span class="inline-flex items-center rounded-full bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-200 px-2 py-0.5 text-[11px] font-medium">{{ number_format($applicationsPendingInstallationCount ?? 0) }}</span>
                    </div>
                    <ul class="space-y-2">
                        @forelse($applicationsPendingInstallationList as $application)
                            <li class="rounded-xl border border-gray-100 dark:border-gray-700/70 bg-white dark:bg-gray-900/40 px-3.5 py-1.5 shadow-sm">
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
        <div class="col-span-12 lg:col-span-4 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base lg:text-lg font-semibold text-gray-800 dark:text-gray-100">Connection Analytics</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Live mix of connection classifications.</p>
                </div>
                <div class="inline-flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 text-[11px] font-medium overflow-hidden">
                    <button type="button" data-connection-mode="count" class="px-3 py-1.5 bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-300">Count</button>
                    <button type="button" data-connection-mode="percentage" class="px-3 py-1.5 text-gray-500 dark:text-gray-400 hover:text-blue-500">Share</button>
                </div>
            </div>
            <div class="h-[11rem] md:h-[13rem] lg:h-[18rem] flex items-center justify-center px-2">
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