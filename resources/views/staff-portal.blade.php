@extends('layouts.app')

@section('content')
<div class="flex">
    <!-- Sidebar & Navbar remain unchanged -->

    <!-- Main Portal Content -->
    <div class="flex-1 p-8 font-[Poppins] transition-colors duration-300">
        <div id="portalContent" class="p-2 max-w-6xl mx-auto space-y-6">

            <!-- Title removed to save space -->

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                    <div class="flex items-center">
                        <div class="shrink-0 kpi-icon rounded-full flex items-center justify-center bg-transparent text-blue-600 dark:text-blue-300 mr-3" style="--kpi-icon-size: 1.5rem;">
                            <x-heroicon-o-clock class="w-5 h-5" />
                        </div>
                        <div class="ml-4 min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Pending to Generate</p>
                            <p id="portalPendingCount" class="text-xl font-bold text-gray-900 dark:text-white mt-0.5 text-right">{{ $stats['pending_generate'] ?? 0 }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Bills still waiting to be generated</p>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 dark:bg-green-950 rounded-xl shadow p-5">
                    <div class="flex items-center">
                        <div class="shrink-0 kpi-icon rounded-full flex items-center justify-center bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 mr-3" style="--kpi-icon-size: 1.5rem;">
                            <x-heroicon-o-check-circle class="w-5 h-5" />
                        </div>
                        <div class="ml-4 min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Total Generated Bills</p>
                            <p id="portalGeneratedCount" class="text-xl font-bold text-gray-900 dark:text-white mt-0.5 text-right">{{ $stats['generated_total'] ?? ($stats['generated'] ?? 0) }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Today: {{ $stats['generated_today'] ?? 0 }} · This month: {{ $stats['generated_month'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-950 rounded-xl shadow p-5">
                    <div class="flex items-center">
                        <div class="shrink-0 kpi-icon rounded-full flex items-center justify-center bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 mr-3" style="--kpi-icon-size: 1.5rem;">
                            <x-heroicon-o-user-plus class="w-5 h-5" />
                        </div>
                        <div class="ml-4 min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Newly Registered</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white mt-0.5 text-right">{{ $stats['new_customers_today'] ?? 0 }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Today · This month: {{ $stats['new_customers_month'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 -mt-2 text-sm">
                <a href="{{ route('billing.generation') }}" class="inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100/70 dark:hover:bg-gray-700/60 px-2 py-1 rounded">
                    <x-heroicon-o-document-plus class="w-4 h-4" />
                    <span>New Bill</span>
                </a>
                <a href="{{ route('payment.index') }}" class="inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100/70 dark:hover:bg-gray-700/60 px-2 py-1 rounded">
                    <x-heroicon-o-credit-card class="w-4 h-4" />
                    <span>Payments</span>
                </a>
                <a href="{{ route('register.index') }}" class="inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100/70 dark:hover:bg-gray-700/60 px-2 py-1 rounded">
                    <x-heroicon-o-user-group class="w-4 h-4" />
                    <span>Register</span>
                </a>
            </div>

 

<!-- Staff Customer Modal -->
<div id="staffCustomerModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-start md:items-center w-full h-full p-2 sm:p-4">
  <div class="relative w-full max-w-full sm:max-w-2xl max-h-[90vh]">
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Customer</h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-7 h-7 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-700 dark:hover:text-white" data-modal-hide="staffCustomerModal">
          <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
        </button>
      </div>
      <div class="p-4 space-y-4">
        <input type="hidden" id="sc_account_no">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account No.</label>
            <input id="sc_account_no_display" type="text" class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100" readonly>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer Name</label>
            <input id="sc_name" type="text" class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100" placeholder="Full name">
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
            <input id="sc_address" type="text" class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100" placeholder="Address">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meter No.</label>
            <input id="sc_meter_no" type="text" class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100" placeholder="Meter no.">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meter Size</label>
            <select id="sc_meter_size" class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100">
              <option value="">Select Size</option>
              <option value="1/2\"">1/2"</option>
              <option value="3/4\"">3/4"</option>
              <option value="1\"">1"</option>
              <option value="2\"">2"</option>
            </select>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between p-4 border-t dark:border-gray-700">
        <button id="sc_new" type="button" class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs hover:bg-gray-50 dark:hover:bg-gray-700">New</button>
        <div class="flex items-center gap-2">
          <button id="sc_save" type="button" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm">Save</button>
          <button type="button" data-modal-hide="staffCustomerModal" class="px-4 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm">Close</button>
        </div>
      </div>
    </div>
  </div>
  <div class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>
</div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-gray-200">Recent activity</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Last 7 days · Registrations, generated bills, and payments</p>
                        </div>
                    </div>

                    <div class="h-64 md:h-72">
                        <div id="staffActivityChart" class="w-full h-full"></div>
                    </div>

                    @php
                        $actLabels = $activity['labels'] ?? [];
                        $actBills = $activity['bills'] ?? [];
                        $actPayments = $activity['payments'] ?? [];
                        $actRegs = $activity['registrations'] ?? [];
                    @endphp
                    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                    <script>
                        (function(){
                            var el = document.getElementById('staffActivityChart');
                            if (!el) return;

                            var labels = @json($actLabels);
                            var bills = @json($actBills);
                            var payments = @json($actPayments);
                            var regs = @json($actRegs);

                            function isDark(){ return document.documentElement.classList.contains('dark'); }

                            var options = {
                                chart: {
                                    type: 'bar',
                                    height: '100%',
                                    stacked: true,
                                    toolbar: { show: false }
                                },
                                theme: { mode: isDark() ? 'dark' : 'light' },
                                series: [
                                    { name: 'Generated bills', data: bills },
                                    { name: 'Payments', data: payments },
                                    { name: 'Registrations', data: regs }
                                ],
                                xaxis: {
                                    categories: labels,
                                    labels: { style: { colors: isDark() ? '#cbd5e1' : '#6b7280' } }
                                },
                                yaxis: {
                                    labels: { style: { colors: isDark() ? '#cbd5e1' : '#6b7280' } },
                                    min: 0,
                                    forceNiceScale: true
                                },
                                plotOptions: {
                                    bar: {
                                        columnWidth: '55%',
                                        borderRadius: 3,
                                        dataLabels: {
                                            position: 'top'
                                        }
                                    }
                                },
                                dataLabels: { enabled: false },
                                grid: {
                                    borderColor: isDark() ? 'rgba(148,163,184,0.35)' : '#e5e7eb'
                                },
                                // Blue theme for all series
                                colors: ['#1d4ed8', '#3b82f6', '#60a5fa'],
                                legend: {
                                    fontSize: '11px',
                                    labels: { colors: isDark() ? '#e5e7eb' : '#4b5563' }
                                },
                                tooltip: {
                                    theme: isDark() ? 'dark' : 'light'
                                }
                            };

                            var chart = new ApexCharts(el, options);
                            chart.render();

                            var mo = new MutationObserver(function(){
                                chart.updateOptions({
                                    theme: { mode: isDark() ? 'dark' : 'light' },
                                    xaxis: { labels: { style: { colors: isDark() ? '#cbd5e1' : '#6b7280' } } },
                                    yaxis: { labels: { style: { colors: isDark() ? '#cbd5e1' : '#6b7280' } } },
                                    grid: { borderColor: isDark() ? 'rgba(148,163,184,0.35)' : '#e5e7eb' },
                                    legend: { labels: { colors: isDark() ? '#e5e7eb' : '#4b5563' } }
                                });
                            });
                            mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                        })();
                    </script>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Newly Added Customers</h3>
                    </div>
                    <div class="space-y-3">
                        @php($__nc = (isset($newCustomers) && is_array($newCustomers) && count($newCustomers) > 0) ? $newCustomers : [
                            ['name'=>'Demo Customer A','account_no'=>'22-000001-1','created_at'=>'Today 09:15'],
                            ['name'=>'Demo Customer B','account_no'=>'22-000002-1','created_at'=>'Today 10:02'],
                            ['name'=>'Demo Customer C','account_no'=>'22-000003-1','created_at'=>'Yesterday 16:41'],
                        ])
                        @foreach($__nc as $c)
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $c['name'] ?? '' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $c['account_no'] ?? '' }}</div>
                                </div>
                                <span class="text-xs text-gray-400">{{ $c['created_at'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Upcoming Activities</h3>
                        <button type="button" id="staffAddActivityToggle" class="text-[11px] px-2 py-1 rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Add Activity</button>
                    </div>
                    <div class="space-y-2 text-xs text-gray-700 dark:text-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="font-medium">Meter Reading</span>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">Scheduled every 4th week of the month</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::now()->copy()->startOfMonth()->addWeeks(3)->format('M d') }}
                                –
                                {{ \Carbon\Carbon::now()->copy()->startOfMonth()->addWeeks(3)->addDays(6)->format('M d') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="font-medium">Billing Generation</span>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">Prepare and review bills (monthly)</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::now()->copy()->startOfMonth()->format('M d') }}
                                –
                                {{ \Carbon\Carbon::now()->copy()->startOfMonth()->addDays(6)->format('M d') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="font-medium">Delivery of Billing Statements</span>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">Distribution to customers (aligned with billing generation)</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::now()->copy()->startOfMonth()->format('M d') }}
                                –
                                {{ \Carbon\Carbon::now()->copy()->startOfMonth()->addDays(6)->format('M d') }}
                            </span>
                        </div>
                        <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-[11px] font-semibold text-gray-700 dark:text-gray-200">My Activities</h4>
                            </div>
                            <form id="staffActivityForm" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input type="date" id="staffActivityDate" class="flex-none w-full sm:w-auto border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-[11px] bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100">
                                <input type="text" id="staffActivityDesc" placeholder="Description" class="flex-1 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-[11px] bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100">
                                <button type="submit" class="flex-none px-3 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-[11px]">Save</button>
                            </form>
                            <div id="staffActivityEmpty" class="text-[11px] text-gray-400">No personal activities yet.</div>
                            <div id="staffActivityList" class="space-y-1"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity: Staff login/logout -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Recent Activity</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse(($recentActivity ?? []) as $act)
                        <div class="flex items-center gap-3 py-2">
                            @if(($act['type'] ?? '') === 'login')
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 text-green-600" />
                            @elseif(($act['type'] ?? '') === 'logout')
                                <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4 text-gray-500" />
                            @else
                                <x-heroicon-o-bell class="w-4 h-4 text-blue-600" />
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $act['message'] ?? 'Activity' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $act['time'] ?? '' }}</p>
                            </div>
                            @if(!empty($act['user']))
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $act['user'] }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="py-4 text-sm text-gray-500 dark:text-gray-400">No recent activity.</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 hidden">
                <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-6">Customer Information</h5>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Search by Account No.
                    </label>
                    <div class="relative">
                        <input type="text" id="search"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100
                                focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Type account number to see suggestions..."
                            autocomplete="off">
                        
                        <!-- Suggestions Dropdown -->
                        <div id="suggestions" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            <!-- Suggestions will be populated here -->
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Account No.</label>
                    <input type="text" id="account_no"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg bg-gray-100 dark:bg-gray-700 
                                text-sm text-gray-900 dark:text-gray-200"
                            value="" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Customer Name</label>
                        <input type="text" id="customer_name"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Address</label>
                        <input type="text" id="customer_address"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Meter No.</label>
                        <input type="text" id="meter_no"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Meter Size</label>
                        <select id="meter_size"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100">
                            <option value="">Select Size</option>
                            <option value="1/2\"">1/2"</option>
                            <option value="3/4\"">3/4"</option>
                            <option value="1\"">1"</option>
                            <option value="2\"">2"</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 hidden">
                <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-6">Billing Computation</h5>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Previous Reading</label>
                        <input type="number" id="prev_reading"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="0">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Current Reading</label>
                        <input type="number" id="current_reading"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="0">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Billing Date From</label>
                        <input type="date" id="date_from"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Billing Date To</label>
                        <input type="date" id="date_to"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Consumption (m³) <span class="text-xs text-gray-500">(Auto-calculated)</span></label>
                        <input type="text" id="consumption"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            readonly placeholder="Enter both readings to auto-calculate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Subtotal</label>
                        <input type="text" id="subtotal"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Maintenance Charge (₱)</label>
                        <input type="number" id="maintenance_charge"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="0" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Advance Payment (₱)</label>
                        <input type="text" id="advance_payment"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            readonly placeholder="Auto-calculated from previous excess">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Overdue Penalty (₱)</label>
                        <input type="number" id="overdue_penalty"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="0" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Total Amount Due</label>
                        <input type="text" id="total_amount"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-green-700 dark:text-green-400 font-semibold text-sm"
                            readonly>
                    </div>
                </div>

                <!-- Charges removed -->
            </div>

            <!-- Payment Status Section -->
            <div id="paymentStatus" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 hidden">
                <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-6">Payment Status</h5>
                
                <div id="paymentStatusContent" class="space-y-4">
                    <!-- Payment status will be populated here -->
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center justify-end gap-3">
                <button id="viewPaymentHistory"
                    class="hidden inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition">
                    <x-heroicon-o-clock class="w-5 h-5" />
                    <span>Payment History</span>
                </button>
                <button id="checkPaymentStatus"
                    class="hidden inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition">
                    <x-heroicon-o-information-circle class="w-5 h-5" />
                    <span>Payment Status</span>
                </button>
                <button id="saveBill"
                    class="hidden inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    <span>Save Bill</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bill Summary Modal -->
<div id="billModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Bill Summary</h3>
        <div id="billSummary" class="space-y-3 text-gray-700 dark:text-gray-200"></div>
        <div class="mt-6 text-right space-x-3">
            <button id="closeModal" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg">Cancel</button>
            <button id="confirmBill" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Save Bill</button>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div id="paymentHistoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 w-full max-w-4xl max-h-[80vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Payment History</h3>
        <div id="paymentHistoryContent" class="space-y-4">
            <!-- Content will be loaded here -->
        </div>
        <div class="mt-6 text-right">
            <button id="closeHistoryModal" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg">Close</button>
        </div>
    </div>
</div>

<!-- Progress Settings Modal -->
<div id="progressSettingsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Progress Settings</h3>
        <div id="progressSettingsContent" class="space-y-3 text-gray-700 dark:text-gray-200">
            <label class="block text-sm font-semibold mb-2">Progress Color:</label>
            <input type="color" id="progressColor" value="#3b82f6">
            <label class="block text-sm font-semibold mb-2">Progress Background Color:</label>
            <input type="color" id="progressBackgroundColor" value="#f3f4f6">
        </div>
        <div class="mt-6 text-right space-x-3">
            <button id="closeProgressSettingsModal" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg">Cancel</button>
        </div>
      </div>
      <div class="flex items-center justify-between p-4 border-t dark:border-gray-700">
        <button id="progressReset" type="button" class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs hover:bg-gray-50 dark:hover:bg-gray-700">Reset today</button>
        <div class="flex items-center gap-2">
          <button id="progressSave" type="button" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm">Save</button>
          <button type="button" data-modal-hide="staffProgressModal" class="px-4 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function toast(msg, type){
        try { if (typeof window.showToast === 'function') { window.showToast(msg, type||'info'); return; } } catch(_){ }
        alert(msg);
    }
    // Scope billing inputs to the Billing modal to avoid duplicate IDs elsewhere
    var billingRoot = document.getElementById('staffBillingModal');
    var prev = billingRoot?.querySelector('#prev_reading');
    var curr = billingRoot?.querySelector('#current_reading');
    var consumption = billingRoot?.querySelector('#consumption');
    var total = billingRoot?.querySelector('#total_amount');
    var subtotal = billingRoot?.querySelector('#subtotal');
    var maintenanceChargeInput = billingRoot?.querySelector('#maintenance_charge');
    var advancePaymentInput = billingRoot?.querySelector('#advance_payment');
    var overduePenaltyInput = billingRoot?.querySelector('#overdue_penalty');
    // service_charge may not exist; guard usage
    var serviceChargeInput = billingRoot?.querySelector('#service_charge');

    function loadProgressSettings(){
        if (window._staffProgressData && typeof window._staffProgressData.target !== 'undefined') {
            return { target: window._staffProgressData.target || 0, completed: window._staffProgressData.completed || 0 };
        }
        var t = parseFloat(localStorage.getItem('staff.progress.target'));
        var c = parseFloat(localStorage.getItem('staff.progress.completed'));
        return {
            target: isFinite(t) && t > 0 ? t : ({{ $stats['daily_target'] ?? 20 }}),
            completed: isFinite(c) && c >= 0 ? c : ({{ $stats['completed_today'] ?? 12 }})
        };
    }

    // ==== Staff Customer Modal Handlers ====
    function scSetDisabled(disabled){
        ['sc_name','sc_address','sc_meter_no','sc_meter_size'].forEach(function(id){ var el = document.getElementById(id); if (el) el.disabled = !!disabled; });
        var saveBtn = document.getElementById('sc_save'); if (saveBtn) saveBtn.disabled = !!disabled;
    }
    function scClear(){
        ['sc_account_no','sc_account_no_display','sc_name','sc_address','sc_meter_no'].forEach(function(id){ var el = document.getElementById(id); if (el) el.value=''; });
        var sel = document.getElementById('sc_meter_size'); if (sel) sel.value='';
    }
    async function scNextAccount(){
        try {
            const res = await fetch('/api/customer/next-account');
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            var acct = data.account_no || '';
            var hid = document.getElementById('sc_account_no'); if (hid) hid.value = acct;
            var disp = document.getElementById('sc_account_no_display'); if (disp) disp.value = acct;
        } catch(e){ toast('Failed to get next account number', 'error'); }
    }
    var scNew = document.getElementById('sc_new');
    if (scNew){
        scNew.addEventListener('click', async function(){
            scClear();
            scSetDisabled(false);
            await scNextAccount();
            var name = document.getElementById('sc_name'); if (name) name.focus();
        });

    
    }
    var scSave = document.getElementById('sc_save');
    if (scSave){
        scSave.addEventListener('click', async function(){
            var btn = scSave; var old = btn.textContent; btn.disabled = true; btn.textContent = 'Saving...';
            try{
                var acct = document.getElementById('sc_account_no')?.value?.trim();
                var name = document.getElementById('sc_name')?.value?.trim();
                if (!acct){ toast('Click New first to generate an account number.', 'warning'); return; }
                if (!name){ toast('Customer name is required.', 'warning'); return; }
                var payload = {
                    account_no: acct,
                    name: name,
                    address: document.getElementById('sc_address')?.value || null,
                    meter_no: document.getElementById('sc_meter_no')?.value || null,
                    meter_size: document.getElementById('sc_meter_size')?.value || null,
                    status: 'Active',
                    previous_reading: 0
                };
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/customer', { method:'POST', headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': token || '' }, body: JSON.stringify(payload) });
                if (!res.ok){
                    const msg = await res.text();
                    throw new Error(msg||'Save failed');
                }
                scSetDisabled(true);
                toast('Customer saved. Admin can edit details if needed.', 'success');
            } catch(e){
                toast('Failed to save customer.', 'error');
            } finally {
                btn.disabled = false; btn.textContent = old;
            }
        });
    }

    async function apiGetProgress(){
        try{
            const res = await fetch("/api/staff/progress/today", { headers: { 'Accept':'application/json' } });
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            window._staffProgressData = { target: Number(data.target)||0, completed: Number(data.completed)||0 };
            // cache fallback
            localStorage.setItem('staff.progress.target', String(window._staffProgressData.target));
            localStorage.setItem('staff.progress.completed', String(window._staffProgressData.completed));
            // prefill settings modal if present
            var t = document.getElementById('progressTarget'); if (t) t.value = window._staffProgressData.target;
            var c = document.getElementById('progressCompleted'); if (c) c.value = window._staffProgressData.completed;
            renderProgressRadial();
            renderTeamRadial();
        }catch(e){ /* keep fallback */ }
    }

    async function apiPutProgress(payload){
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch("/api/staff/progress/today",{
            method:'PUT',
            headers:{ 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': token || '' },
            body: JSON.stringify(payload)
        });
        if (!res.ok) throw new Error('Save failed');
        return await res.json();
    }
    function ensureApexCharts(cb){
        if (window.ApexCharts) { cb(true); return; }
        if (window.__apexLoading) { setTimeout(()=>ensureApexCharts(cb), 300); return; }
        window.__apexLoading = true;
        var s = document.createElement('script');
        s.src = 'https://unpkg.com/apexcharts@3.44.0/dist/apexcharts.min.js';
        s.onload = function(){ window.__apexLoading = false; cb(!!window.ApexCharts); };
        s.onerror = function(){ window.__apexLoading = false; cb(false); };
        document.head.appendChild(s);
    }

    // Team progress radial (multi-series) helpers
    async function apiGetBreakdown(){
        try{
            const res = await fetch('/api/staff/progress/breakdown', { headers: { 'Accept':'application/json' } });
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            return { done: Number(data.done)||0, inProg: Number(data.in_progress)||0, todo: Number(data.to_do)||0, target: Number(data.target)||0 };
        } catch(e){
            var s = loadProgressSettings();
            var done = Math.max(0, Math.round(s.completed || 0));
            var target = Math.max(0, Math.round(s.target || 0));
            var todo = Math.max(0, target - done);
            var inProg = Math.min(todo, Math.round(done * 0.3));
            return { done: done, inProg: inProg, todo: Math.max(0, todo - inProg), target: target };
        }
    }
    function teamChartOptions(parts){
        return {
            series: [parts.done, parts.inProg, parts.todo],
            // Dark blue, blue, sky blue
            colors: ["#1e3a8a", "#2563eb", "#38bdf8"],
            chart: { height: 350, width: "100%", type: "radialBar", sparkline: { enabled: true } },
            plotOptions: { radialBar: { track: { background: '#E5E7EB' }, dataLabels: { show: false }, hollow: { margin: 0, size: "32%" } } },
            grid: { show: false, strokeDashArray: 4, padding: { left: 2, right: 2, top: -23, bottom: -20 } },
            labels: ["Done", "In progress", "To do"],
            legend: { show: true, position: "bottom", fontFamily: "Inter, sans-serif" },
            tooltip: { enabled: true, x: { show: false } },
            yaxis: { show: false, labels: { formatter: function (value) { return value + '%'; } } }
        };
    }
    function updateProgressStats(parts){
        var elDone = document.getElementById('doneCount');
        var elIn = document.getElementById('inProgressCount');
        var elTodo = document.getElementById('todoCount');
        var avg = document.getElementById('avgRate');
        if (elDone) elDone.textContent = parts.done;
        if (elIn) elIn.textContent = parts.inProg;
        if (elTodo) elTodo.textContent = parts.todo;
        if (avg){
            var rate = parts.target > 0 ? Math.round((parts.done/parts.target)*100) : 0;
            avg.textContent = rate + '%';
        }
    }
    async function renderTeamRadial(){
        var mount = document.getElementById('radial-chart');
        if (!mount) return;
        var parts = await apiGetBreakdown();
        updateProgressStats(parts);
        var draw = function(){
            try { if (window._staffTeamRadial) { window._staffTeamRadial.destroy(); } } catch(_){ }
            try { window._staffTeamRadial = new ApexCharts(mount, teamChartOptions(parts)); window._staffTeamRadial.render(); } catch(_){ }
        };
        if (window.ApexCharts) { draw(); return; }
        ensureApexCharts(function(ok){ if (ok) draw(); });
    }

    function renderProgressRadial(){
        var el = document.getElementById('staffProgressRadial');
        if (!el) return;
        var s = loadProgressSettings();
        var percent = s.target > 0 ? (s.completed / s.target) * 100 : 0;
        var meta = document.getElementById('staffProgressMeta');
        if (meta) { meta.textContent = 'Completed ' + (s.completed||0) + ' of ' + (s.target||0); }
        if (window.ApexCharts){
            try { if (window._staffProgressChart) { window._staffProgressChart.destroy(); } } catch(_){ }
            var isDark = document.documentElement.classList.contains('dark');
            // Use blue in light, lighter blue in dark
            var col = isDark ? '#60a5fa' : '#2563eb';
            var options = {
                chart: { type: 'radialBar', height: 380, sparkline: { enabled: true }, animations: { enabled: true } },
                series: [Math.max(0, Math.min(100, percent))],
                labels: ['Progress'],
                plotOptions: { radialBar: { hollow: { size: '58%' }, track: { background: isDark ? '#1f2937' : '#f3f4f6' }, dataLabels: { name: { show: true, fontSize: '13px', offsetY: -6, color: isDark ? '#cbd5e1' : '#475569' }, value: { fontSize: '34px', offsetY: 6, fontWeight: 700, formatter: function(v){ return Math.round(v) + '%' } }, total: { show: false } } } },
                colors: [col],
                fill: { type: 'gradient', gradient: { shade: 'light', type: 'vertical', shadeIntensity: 0.4, gradientToColors: [col], stops: [0, 90, 100] } }
            };
            try { window._staffProgressChart = new ApexCharts(el, options); window._staffProgressChart.render(); } catch(_){}
        } else {
            var tried = 0;
            ensureApexCharts(function(ok){
                if (ok) { renderProgressRadial(); return; }
                tried++;
                if (tried < 2) { setTimeout(renderProgressRadial, 400); return; }
                var isDark = document.documentElement.classList.contains('dark');
                el.innerHTML = '<div style="height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;line-height:1.1;">\
                  <div style=\"font-size:36px;font-weight:700;margin-bottom:6px;color:'+ (isDark?'#e5e7eb':'#111827') +'\">'+ Math.round(Math.max(0, Math.min(100, percent))) +'%</div>\
                  <div style=\"font-size:12px;color:'+ (isDark?'#94a3b8':'#6b7280') +'\">Progress (chart unavailable)</div>\
                </div>';
            });
        }
    }
    // Initial renders + load from API
    renderProgressRadial();
    renderTeamRadial();
    apiGetProgress();
    var mo = new MutationObserver(function(){ renderProgressRadial(); renderTeamRadial(); });
    mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    async function compute() {
        const prevVal = parseFloat(prev.value) || 0;
        const currVal = parseFloat(curr.value) || 0;
        const maintenanceCharge = parseFloat(maintenanceChargeInput.value) || 0;
        const advancePayment = parseFloat(advancePaymentInput.value) || 0;
        const overduePenalty = parseFloat(overduePenaltyInput.value) || 0;

        const res = await fetch("{{ route('api.billing.compute') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                previous_reading: prevVal,
                current_reading: currVal,
                maintenance_charge: maintenanceCharge,
                base_rate: 25
            })
        });
        const data = await res.json();

        consumption.value = (data.consumption_cu_m ?? 0).toFixed(2);
        subtotal.value = data.formatted?.subtotal ?? '₱0.00';
        
        // Calculate total with all charges and adjustments
        const baseTotal = parseFloat(data.total ?? 0);
        const finalTotal = baseTotal + overduePenalty - advancePayment;
        total.value = '₱' + Math.max(0, finalTotal).toFixed(2);
    }

    // Auto-calculate consumption when both readings are entered
    function autoCalculateConsumption() {
        const prevVal = parseFloat(prev.value) || 0;
        const currVal = parseFloat(curr.value) || 0;
        
        // If both readings have values, calculate consumption immediately
        if (prevVal > 0 && currVal > 0) {
            const consumption = Math.max(0, currVal - prevVal);
            document.getElementById('consumption').value = consumption.toFixed(2);
        }
        
        // Always call the full compute function for billing calculations
        compute();
    }

    if (prev) prev.addEventListener('input', autoCalculateConsumption);
    if (curr) curr.addEventListener('input', autoCalculateConsumption);
    if (maintenanceChargeInput) maintenanceChargeInput.addEventListener('input', compute);
    if (serviceChargeInput) serviceChargeInput.addEventListener('input', compute);
    if (overduePenaltyInput) overduePenaltyInput.addEventListener('input', compute);
    if (prev && curr) autoCalculateConsumption();

    const modal = document.getElementById('billModal');
    const openModal = document.getElementById('saveBill');
    const closeModal = document.getElementById('closeModal');
    const confirmBill = document.getElementById('confirmBill');
    const summary = document.getElementById('billSummary');

    openModal.addEventListener('click', () => {
        // Validate required fields before opening modal
        const acctVal = document.getElementById('account_no')?.value || '';
        const prevVal = parseFloat(document.getElementById('prev_reading')?.value || 0);
        const currVal = parseFloat(document.getElementById('current_reading')?.value || 0);
        
        if (!acctVal) {
            alert('Please load or enter an account number before saving the bill.');
            return;
        }
        if (isNaN(prevVal) || isNaN(currVal) || currVal <= 0) {
            alert('Please enter valid previous and current readings. Current reading must be greater than 0.');
            return;
        }
        if (currVal <= prevVal) {
            alert('Current reading must be greater than previous reading.');
            return;
        }

        const maintenanceCharge = parseFloat(maintenanceChargeInput.value) || 0;
        const advancePayment = parseFloat(advancePaymentInput.value) || 0;
        const overduePenalty = parseFloat(overduePenaltyInput.value) || 0;
        const totalAmountVal = parseFloat(total.value.replace(/[^0-9.]/g,'')) || 0;
        
        summary.innerHTML = `
            <div class="flex justify-between"><span>Consumption:</span><span>${consumption.value} m³</span></div>
            <div class="flex justify-between"><span>Subtotal:</span><span>${subtotal.value}</span></div>
            <div class="flex justify-between"><span>Maintenance Charge:</span><span>₱${maintenanceCharge.toFixed(2)}</span></div>
            <div class="flex justify-between"><span>Overdue Penalty:</span><span>₱${overduePenalty.toFixed(2)}</span></div>
            <div class="flex justify-between"><span>Advance Payment:</span><span>₱${advancePayment.toFixed(2)}</span></div>
            <div class="flex justify-between font-bold"><span>Total Bill:</span><span>${total.value}</span></div>
            <hr class="my-2">
            <div class="flex justify-between font-bold text-green-600 dark:text-green-400">
                <span>Bill Status:</span><span>Ready to Save</span>
            </div>`;
        modal.classList.remove('hidden');
    });

    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    confirmBill.addEventListener('click', async () => {
        try {
            const acct = document.getElementById('account_no')?.value || '';
            if (!acct) { alert('Account number missing.'); return; }
            
            const maintenanceCharge = parseFloat(maintenanceChargeInput.value) || 0;
            const advancePayment = parseFloat(advancePaymentInput.value) || 0;
            const overduePenalty = parseFloat(overduePenaltyInput.value) || 0;
            const totalParsed = (function(){ const t = total.value.replace(/[^0-9.]/g,''); return parseFloat(t)||0; })();
            
            const payload = {
                account_no: acct,
                previous_reading: parseFloat(prev.value) || 0,
                current_reading: parseFloat(curr.value) || 0,
                consumption_cu_m: parseFloat(consumption.value) || 0,
                base_rate: 25,
                maintenance_charge: maintenanceCharge,
                service_fee: 0,
                advance_payment: advancePayment,
                overdue_penalty: overduePenalty,
                vat: 0,
                total_amount: totalParsed,
                date_from: document.getElementById('date_from')?.value || null,
                date_to: document.getElementById('date_to')?.value || null,
            };

            const res = await fetch("{{ route('api.billing.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const result = await res.json();
                let message = '✅ Bill Saved Successfully!';
                
                if (result.message) {
                    message = result.message;
                }
                
                alert(message);
                // Optimistically increment Pending counter
                const pcEl = document.getElementById('portalPendingCount');
                if (pcEl) { setText(pcEl, getInt(pcEl) + 1); }
                // Refresh from server to be accurate
                refreshPortalStats();
                modal.classList.add('hidden');
                
                // Clear form for next bill
                document.getElementById('prev_reading').value = parseFloat(curr.value) || 0;
                document.getElementById('current_reading').value = '';
                document.getElementById('consumption').value = '';
                document.getElementById('subtotal').value = '';
                document.getElementById('total_amount').value = '';
                document.getElementById('maintenance_charge').value = '0';
                if (serviceChargeInput) serviceChargeInput.value = '0';
                document.getElementById('overdue_penalty').value = '0';
                document.getElementById('advance_payment').value = '';
                
                // Redirect to Billing Management with Not Generated filter for faster generation
                window.location.href = "{{ url('/billing?generated=0') }}";
            } else {
                const result = await res.json().catch(() => null);
                const errorMessage = result?.error || 'Failed to save bill';
                alert(`❌ ${errorMessage}`);
            }
        } catch (err) {
            console.error('Bill save error', err);
            alert('❌ An error occurred while saving the bill.');
        }
    });
    // Account search functionality with autocomplete
    let searchTimeout;
    var customerRoot = document.getElementById('staffCustomerModal');
    const searchInput = customerRoot?.querySelector('#search');
    const suggestionsDiv = customerRoot?.querySelector('#suggestions');

    // Load customer data
    async function loadCustomer(accountNo) {
        const res = await fetch(`{{ route('customer.findByAccount') }}?account_no=${encodeURIComponent(accountNo)}`);
        if (!res.ok) { 
            alert('Account not found'); 
            return; 
        }
        const data = await res.json();
        const c = data.customer;
        customerRoot.querySelector('#account_no').value = c.account_no || '';
        customerRoot.querySelector('#customer_name').value = c.name || '';
        customerRoot.querySelector('#customer_address').value = c.address || '';
        customerRoot.querySelector('#meter_no').value = c.meter_no || '';
        customerRoot.querySelector('#meter_size').value = c.meter_size || '';
        billingRoot.querySelector('#prev_reading').value = (c.previous_reading ?? 0);
        billingRoot.querySelector('#current_reading').value = (c.previous_reading ?? 0);
        
        // Reset advance payment to 0
        advancePaymentInput.value = '0.00';
        
        // Trigger auto-calculation after loading customer data
        autoCalculateConsumption();
    }

    // Search for account suggestions
    async function searchSuggestions(query) {
        if (query.length < 2) {
            suggestionsDiv.classList.add('hidden');
            return;
        }

        try {
            const res = await fetch(`{{ route('customer.searchAccounts') }}?q=${encodeURIComponent(query)}`);
            const data = await res.json();
            
            if (data.suggestions && data.suggestions.length > 0) {
                showSuggestions(data.suggestions);
            } else {
                suggestionsDiv.classList.add('hidden');
            }
        } catch (error) {
            console.error('Search error:', error);
            suggestionsDiv.classList.add('hidden');
        }
    }

    // Display suggestions dropdown
    function showSuggestions(suggestions) {
        const html = suggestions.map(suggestion => `
            <div class="suggestion-item px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-600 last:border-b-0" 
                 data-account="${suggestion.account_no}">
                <div class="font-semibold text-gray-900 dark:text-gray-100">${suggestion.account_no}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">${suggestion.name}</div>
                <div class="text-xs text-gray-500 dark:text-gray-500">${suggestion.address}</div>
            </div>
        `).join('');
        
        suggestionsDiv.innerHTML = html;
        suggestionsDiv.classList.remove('hidden');
    }

    // Handle input events
    if (searchInput) searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Set new timeout to avoid too many requests
        searchTimeout = setTimeout(() => {
            searchSuggestions(query);
        }, 300);
    });

    // Handle suggestion clicks
    if (suggestionsDiv) suggestionsDiv.addEventListener('click', (e) => {
        const suggestionItem = e.target.closest('.suggestion-item');
        if (suggestionItem) {
            const accountNo = suggestionItem.dataset.account;
            searchInput.value = accountNo;
            suggestionsDiv.classList.add('hidden');
            loadCustomer(accountNo);
        }
    });

    // Handle Enter key
    if (searchInput) searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query) {
                suggestionsDiv.classList.add('hidden');
                loadCustomer(query);
            }
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (searchInput && suggestionsDiv && !searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.add('hidden');
        }
    });

    // Customer modal CRUD placeholders
    var btnNew = document.getElementById('staffCustomerNew');
    var btnSave = document.getElementById('staffCustomerSave');
    if (btnNew) btnNew.addEventListener('click', function(){
        ['#account_no','#customer_name','#customer_address','#meter_no','#meter_size'].forEach(function(sel){ var el = customerRoot.querySelector(sel); if (el){ el.value = ''; }});
        ['#prev_reading','#current_reading','#consumption','#subtotal','#total_amount'].forEach(function(sel){ var el = billingRoot.querySelector(sel); if (el){ el.value = ''; }});
    });
    if (btnSave) btnSave.addEventListener('click', async function(){
        try{
            var payload = {
                account_no: customerRoot.querySelector('#account_no')?.value || '',
                name: customerRoot.querySelector('#customer_name')?.value || '',
                address: customerRoot.querySelector('#customer_address')?.value || '',
                meter_no: customerRoot.querySelector('#meter_no')?.value || '',
                meter_size: customerRoot.querySelector('#meter_size')?.value || ''
            };
            console.log('Saving customer (placeholder):', payload);
            alert('Customer saved (placeholder). Implement API call to persist.');
        } catch(err){
            console.error(err); alert('Failed to save (placeholder).');
        }
    });

    // Progress settings: save and re-render
    var psSave = document.getElementById('progressSave');
    if (psSave){
        psSave.addEventListener('click', async function(){
            var btn = psSave; var oldText = btn.textContent; btn.disabled = true; btn.textContent = 'Saving...';
            try{
                var t = parseFloat(document.getElementById('progressTarget')?.value || '');
                var c = parseFloat(document.getElementById('progressCompleted')?.value || '');
                var payload = {};
                if (isFinite(t) && t >= 0) payload.target = Math.round(t);
                if (isFinite(c) && c >= 0) payload.completed = Math.round(c);
                const data = await apiPutProgress(payload);
                window._staffProgressData = { target: Number(data.target)||0, completed: Number(data.completed)||0 };
                localStorage.setItem('staff.progress.target', String(window._staffProgressData.target));
                localStorage.setItem('staff.progress.completed', String(window._staffProgressData.completed));
                renderProgressRadial();
                renderTeamRadial();
            } catch(e){
                toast('Failed to save progress.', 'error');
            } finally {
                btn.disabled = false; btn.textContent = oldText;
            }
        });
    }

    // Reset today's progress
    var psReset = document.getElementById('progressReset');
    if (psReset){
        psReset.addEventListener('click', async function(){
            var btn = psReset; var old = btn.textContent; btn.disabled = true; btn.textContent = 'Resetting...';
            try{
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/staff/progress/reset', { method:'POST', headers: { 'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN': token || '' } });
                if (!res.ok) throw new Error('reset failed');
                window._staffProgressData = { target: 0, completed: 0 };
                localStorage.setItem('staff.progress.target', '0');
                localStorage.setItem('staff.progress.completed', '0');
                var t = document.getElementById('progressTarget'); if (t) t.value = 0;
                var c = document.getElementById('progressCompleted'); if (c) c.value = 0;
                await apiGetProgress(); // refresh from server just in case
                renderProgressRadial();
                renderTeamRadial();
            } catch(e){
                toast('Failed to reset.', 'error');
            } finally {
                btn.disabled = false; btn.textContent = old;
            }
        });
    }

    // Payment History Modal
    const paymentHistoryModal = document.getElementById('paymentHistoryModal');
    const viewPaymentHistoryBtn = document.getElementById('viewPaymentHistory');
    const closeHistoryModal = document.getElementById('closeHistoryModal');
    const paymentHistoryContent = document.getElementById('paymentHistoryContent');

    viewPaymentHistoryBtn.addEventListener('click', async () => {
        const accountNo = document.getElementById('account_no')?.value;
        if (!accountNo) {
            alert('Please search for a customer first.');
            return;
        }

        try {
            paymentHistoryContent.innerHTML = '<div class="text-center py-4">Loading payment history...</div>';
            paymentHistoryModal.classList.remove('hidden');

            const response = await fetch(`{{ route('api.billing.payment-history') }}?account_no=${encodeURIComponent(accountNo)}`);
            const data = await response.json();

            if (data.error) {
                paymentHistoryContent.innerHTML = `<div class="text-center py-4 text-red-600">${data.error}</div>`;
                return;
            }

            const { customer, payments } = data;
            
            let html = `
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg mb-4">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200">Customer Information</h4>
                    <p><strong>Account:</strong> ${customer.account_no}</p>
                    <p><strong>Name:</strong> ${customer.name}</p>
                    <p><strong>Address:</strong> ${customer.address}</p>
                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                        <strong>Current Credit Balance:</strong> ${customer.formatted_credit_balance}
                    </p>
                </div>
            `;

            if (payments.length === 0) {
                html += '<div class="text-center py-4 text-gray-500">No payment history found.</div>';
            } else {
                html += `
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2 text-left">Date</th>
                                    <th class="px-4 py-2 text-left">Bill Amount</th>
                                    <th class="px-4 py-2 text-left">Amount Paid</th>
                                    <th class="px-4 py-2 text-left">Credit Applied</th>
                                    <th class="px-4 py-2 text-left">Overpayment</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    <th class="px-4 py-2 text-left">Consumption</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                `;

                payments.forEach(payment => {
                    const statusColor = payment.payment_status === 'overpaid' ? 'text-orange-600 dark:text-orange-400' : 
                                      payment.payment_status === 'partial' ? 'text-yellow-600 dark:text-yellow-400' : 
                                      'text-green-600 dark:text-green-400';
                    
                    html += `
                        <tr>
                            <td class="px-4 py-2">${payment.date}</td>
                            <td class="px-4 py-2">₱${payment.bill_amount.toFixed(2)}</td>
                            <td class="px-4 py-2">₱${payment.amount_paid.toFixed(2)}</td>
                            <td class="px-4 py-2 text-blue-600 dark:text-blue-400">₱${payment.credit_applied.toFixed(2)}</td>
                            <td class="px-4 py-2 text-orange-600 dark:text-orange-400">₱${payment.overpayment.toFixed(2)}</td>
                            <td class="px-4 py-2 ${statusColor} font-semibold">${payment.payment_status.toUpperCase()}</td>
                            <td class="px-4 py-2">${payment.consumption.toFixed(2)} m³</td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';
            }

            paymentHistoryContent.innerHTML = html;
        } catch (error) {
            console.error('Error loading payment history:', error);
            paymentHistoryContent.innerHTML = '<div class="text-center py-4 text-red-600">Failed to load payment history.</div>';
        }
    });

    closeHistoryModal.addEventListener('click', () => {
        paymentHistoryModal.classList.add('hidden');
    });

    // Check Payment Status functionality
    const checkPaymentStatusBtn = document.getElementById('checkPaymentStatus');
    const paymentStatus = document.getElementById('paymentStatus');
    const paymentStatusContent = document.getElementById('paymentStatusContent');

    checkPaymentStatusBtn.addEventListener('click', async () => {
        const accountNo = document.getElementById('account_no')?.value;
        if (!accountNo) {
            alert('Please search for a customer first.');
            return;
        }

        try {
            paymentStatusContent.innerHTML = '<div class="text-center py-4">Loading payment status...</div>';
            paymentStatus.classList.remove('hidden');

            const response = await fetch(`{{ route('api.billing.payment-history') }}?account_no=${encodeURIComponent(accountNo)}`);
            const data = await response.json();

            if (data.error) {
                paymentStatusContent.innerHTML = `<div class="text-center py-4 text-red-600">${data.error}</div>`;
                return;
            }

            const { customer, payments } = data;
            
            // Get unpaid bills
            const unpaidBills = await fetch(`{{ route('api.payment.search-customer') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ account_no: accountNo })
            }).then(res => res.json()).catch(() => ({ unpaid_bills: [] }));

            let html = `
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg mb-4">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200">Customer Information</h4>
                    <p><strong>Account:</strong> ${customer.account_no}</p>
                    <p><strong>Name:</strong> ${customer.name}</p>
                    <p><strong>Address:</strong> ${customer.address}</p>
                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                        <strong>Current Credit Balance:</strong> ${customer.formatted_credit_balance}
                    </p>
                </div>
            `;

            if (unpaidBills.unpaid_bills && unpaidBills.unpaid_bills.length > 0) {
                html += `
                    <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">Outstanding Bills (${unpaidBills.unpaid_bills.length})</h4>
                        <p class="text-red-600 dark:text-red-400 font-bold">Total Outstanding: ${unpaidBills.formatted_total_outstanding}</p>
                    </div>
                `;
            } else {
                html += `
                    <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-green-800 dark:text-green-200">✅ All Bills Paid</h4>
                        <p class="text-green-600 dark:text-green-400">No outstanding bills for this customer.</p>
                    </div>
                `;
            }

            if (payments.length > 0) {
                html += `
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Recent Payments (${payments.length})</h4>
                        <div class="space-y-2">
                `;

                payments.slice(0, 3).forEach(payment => {
                    const statusColor = payment.payment_status === 'overpaid' ? 'text-orange-600 dark:text-orange-400' : 
                                      payment.payment_status === 'partial' ? 'text-yellow-600 dark:text-yellow-400' : 
                                      'text-green-600 dark:text-green-400';
                    
                    html += `
                        <div class="flex justify-between items-center text-sm">
                            <span>${payment.date} - ₱${payment.amount_paid.toFixed(2)}</span>
                            <span class="${statusColor} font-semibold">${payment.payment_status.toUpperCase()}</span>
                        </div>
                    `;
                });

                html += '</div></div>';
            }

            paymentStatusContent.innerHTML = html;
        } catch (error) {
            console.error('Error loading payment status:', error);
            paymentStatusContent.innerHTML = '<div class="text-center py-4 text-red-600">Failed to load payment status.</div>';
        }
        });

    // Personal "My Activities" handling (client-side only)
    (function(){
        var toggleBtn = document.getElementById('staffAddActivityToggle');
        var form = document.getElementById('staffActivityForm');
        var dateInput = document.getElementById('staffActivityDate');
        var descInput = document.getElementById('staffActivityDesc');
        var list = document.getElementById('staffActivityList');
        var empty = document.getElementById('staffActivityEmpty');
        if (!form || !dateInput || !descInput || !list) return;

        var STORAGE_KEY = 'staff.portal.myActivities';
        function loadActivities(){
            try{
                var raw = localStorage.getItem(STORAGE_KEY);
                var arr = raw ? JSON.parse(raw) : [];
                if (!Array.isArray(arr)) return [];
                return arr;
            }catch(_){ return []; }
        }
        function saveActivities(arr){
            try{ localStorage.setItem(STORAGE_KEY, JSON.stringify(arr||[])); }catch(_){ }
        }
        function renderActivities(){
            var items = loadActivities();
            if (!items.length){
                list.innerHTML = '';
                if (empty) empty.classList.remove('hidden');
                return;
            }
            items.sort(function(a,b){ return String(a.date||'').localeCompare(String(b.date||'')); });
            var html = items.map(function(it){
                var d = it.date ? new Date(it.date) : null;
                var label = it.date;
                if (d && !isNaN(d.getTime())){
                    label = d.toLocaleDateString(undefined,{ month:'short', day:'2-digit' });
                }
                return '<div class="flex items-center justify-between gap-2">'
                    + '<span class="text-[11px] text-gray-500">'+ (label||'—') +'</span>'
                    + '<span class="flex-1 text-[11px] text-gray-700 dark:text-gray-200 truncate">'+ (it.desc||'') +'</span>'
                    + '</div>';
            }).join('');
            list.innerHTML = html;
            if (empty) empty.classList.add('hidden');
        }

        if (toggleBtn){
            toggleBtn.addEventListener('click', function(){
                var hidden = form.classList.contains('hidden');
                if (hidden){
                    form.classList.remove('hidden');
                    dateInput.focus();
                } else {
                    form.classList.add('hidden');
                }
            });
        }

        form.addEventListener('submit', function(e){
            e.preventDefault();
            var d = (dateInput.value||'').trim();
            var desc = (descInput.value||'').trim();
            if (!d || !desc) return;
            var items = loadActivities();
            items.push({ date:d, desc:desc });
            saveActivities(items);
            renderActivities();
            descInput.value = '';
        });

        renderActivities();
    })();

    // Initial refresh
    if (typeof refreshPortalStats === 'function') refreshPortalStats();
});
</script>
@endsection
