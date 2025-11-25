@extends('layouts.admin')

@section('title', 'Billing Management')

@section('content')
<div class="w-full mx-auto px-4 sm:px-6 py-4 sm:py-6 lg:py-8 font-[Poppins] space-y-6 lg:space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Billing Management</h1>
        <div></div>
    </div>

    <!-- (Cards removed) -->

    <!-- Billing Table -->
    <div class="grid grid-cols-12 gap-4 lg:gap-6">
        <div class="col-span-12 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-3 lg:p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col gap-3">
                    <form method="GET" class="w-full">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 w-full">
                            <!-- Right group: Filters then Search (all right-aligned on desktop) -->
                            <div class="flex w-full sm:w-auto justify-start sm:justify-end gap-3 sm:ml-auto">
                                <div class="w-full sm:w-32">
                                    <label for="status" class="sr-only">Status</label>
                                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                        <option value="">All Status</option>
                                        <option value="Outstanding Payment">Outstanding Payment</option>
                                        <option value="Overdue">Overdue</option>
                                        <option value="Notice of Disconnection">Notice of Disconnection</option>
                                        <option value="Disconnected">Disconnected</option>
                                        <option value="Paid">Paid</option>
                                    </select>
                                </div>
                                <div class="w-full sm:w-32">
                                    <label for="period" class="sr-only">Period</label>
                                    <select id="period" name="period" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                        <option value="">This Month</option>
                                        <option value="last-month">Last Month</option>
                                        <option value="last-3-months">Last 3 Months</option>
                                        <option value="this-year">This Year</option>
                                    </select>
                                </div>
                                <!-- Search: reduced width, beside filters on right -->
                                <div class="relative w-full sm:w-40">
                                    <label for="q" class="sr-only">Search bills</label>
                                    <input id="q" name="q" type="text" placeholder="Search"
                                           class="w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                    <x-heroicon-o-magnifying-glass aria-hidden="true" class="pointer-events-none w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm js-datatable">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Account No.</th>
                            <th class="px-4 py-2 text-left">Customer</th>
                            <th class="px-4 py-2 text-left">Total</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Due Date</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-100">
                        @forelse($records as $r)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm">{{ optional($r->created_at)->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $r->account_no }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm">{{ $r->customer->name ?? '—' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-semibold">₱{{ number_format($r->total_amount, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $r->getStatusBadgeClass() }}">{{ $r->bill_status ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm">{{ $r->due_date ? $r->due_date->format('Y-m-d') : '—' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    <button type="button" title="View" data-bill-view
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                            data-modal-target="viewBillModal" data-modal-toggle="viewBillModal">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                        <span class="sr-only">View</span>
                                    </button>
                                    <a href="{{ route('records.billing.print', $r->id) }}" title="Print"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        <x-heroicon-o-printer class="w-4 h-4" />
                                        <span class="sr-only">Print</span>
                                    </a>
                                    @if($r->bill_status === 'Paid')
                                        <form action="{{ route('admin.billing.archive', $r->id) }}" method="POST" onsubmit="return confirm('Archive this record?');" class="inline">
                                            @csrf
                                            <button type="submit" title="Archive" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                                <x-heroicon-o-archive-box class="w-4 h-4" />
                                                <span class="sr-only">Archive</span>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" title="Only paid bills can be archived" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-300 dark:text-gray-600 cursor-not-allowed">
                                            <x-heroicon-o-archive-box class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Flowbite Modal: View Bill / Customer Details -->
            <div id="viewBillModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-start md:items-center w-full h-full p-2 sm:p-4">
              <div class="relative w-full max-w-full sm:max-w-3xl lg:max-w-5xl max-h-[90vh]">
                <div id="viewBillPanel" class="relative bg-white rounded-lg shadow dark:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-2xl hover:-translate-y-0.5">
                  <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Customer Billing Overview</h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-6 h-6 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-700 dark:hover:text-white" data-modal-hide="viewBillModal">
                      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                      <span class="sr-only">Close modal</span>
                    </button>
                  </div>
                  <div class="p-3 sm:p-4 space-y-3 sm:space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
                      <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Customer Details</h4>
                        <div class="space-y-3 text-sm">
                          <label class="block">
                            <span class="text-gray-500 dark:text-gray-400">Name</span>
                            <input id="mdName" type="text" class="mt-1 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </label>
                          <label class="block">
                            <span class="text-gray-500 dark:text-gray-400">Email</span>
                            <input id="mdEmail" type="email" class="mt-1 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </label>
                          <label class="block">
                            <span class="text-gray-500 dark:text-gray-400">Meter #</span>
                            <input id="mdMeter" type="text" class="mt-1 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </label>
                          <label class="block">
                            <span class="text-gray-500 dark:text-gray-400">Current Period</span>
                            <input id="mdPeriod" type="text" class="mt-1 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </label>
                          <label class="block">
                            <span class="text-gray-500 dark:text-gray-400">Amount</span>
                            <input id="mdAmount" type="text" class="mt-1 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </label>
                          <label class="block">
                            <span class="text-gray-500 dark:text-gray-400">Due Date</span>
                            <input id="mdDue" type="text" class="mt-1 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </label>
                        </div>
                      </div>
                      <div class="lg:col-span-2 space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
                          <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">5-Month Usage</h4>
                          <div id="mdUsageChart" class="w-full h-56"></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                          <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Outstanding Bills</h4>
                            <ul id="mdOutstanding" class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                              <li>No outstanding bills.</li>
                            </ul>
                          </div>
                          <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Billing History</h4>
                            <div class="overflow-x-auto">
                              <table class="min-w-full text-xs">
                                <thead class="text-gray-600 dark:text-gray-400">
                                  <tr>
                                    <th class="px-2 py-1 text-left">Period</th>
                                    <th class="px-2 py-1 text-left">Amount</th>
                                    <th class="px-2 py-1 text-left">Paid</th>
                                  </tr>
                                </thead>
                                <tbody id="mdHistory" class="text-gray-800 dark:text-gray-200">
                                  <tr><td class="px-2 py-1">-</td><td class="px-2 py-1">-</td><td class="px-2 py-1">-</td></tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center justify-end gap-2 p-4 border-t border-gray-200 dark:border-gray-700">
                    <button id="mdSave" type="button" data-modal-hide="viewBillModal" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm">Save changes</button>
                    <button type="button" data-modal-hide="viewBillModal" class="px-4 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm">Close</button>
                  </div>
                </div>
              </div>
            </div>
            
            <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js" defer></script>
            <script src="https://cdn.jsdelivr.net/npm/apexcharts" defer></script>
            <style>
              @keyframes slideDown { from { transform: translateY(-12px); opacity: 0 } to { transform: translateY(0); opacity: 1 } }
              .animate-slide-down { animation: slideDown .25s ease-out }
            </style>
            <script>
              document.addEventListener('DOMContentLoaded', function(){
                function parseRow(tr){
                  var tds = tr.querySelectorAll('td');
                  return {
                    billId: tds[0]?.textContent.trim(),
                    name: tds[1]?.querySelector('div:nth-child(1)')?.textContent.trim() || '',
                    email: tds[1]?.querySelector('div:nth-child(2)')?.textContent.trim() || '',
                    meter: tds[2]?.textContent.trim(),
                    period: tds[3]?.textContent.trim(),
                    amount: tds[4]?.textContent.trim(),
                    indicator: tds[5]?.textContent.trim() || '',
                    due: tds[6]?.textContent.trim()
                  };
                }

                function renderChart(){
                  var el = document.querySelector('#mdUsageChart');
                  if (!el) return;
                  el.innerHTML = '';
                  var isDark = document.documentElement.classList.contains('dark');
                  var pal = { line: '#3b82f6', grid: isDark ? '#374151' : '#e5e7eb', tick: isDark ? '#cbd5e1' : '#4b5563' };
                  var opts = {
                    chart: { type: 'bar', height: 220, toolbar: { show: false }, animations: { enabled: true } },
                    series: [{ name: 'Usage (m³)', data: [18, 22, 19, 24, 21] }],
                    plotOptions: { bar: { columnWidth: '45%', borderRadius: 6, dataLabels: { position: 'top' } } },
                    xaxis: { categories: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct'], labels: { style: { colors: pal.tick } }, axisBorder: { color: pal.grid }, axisTicks: { color: pal.grid } },
                    yaxis: { labels: { style: { colors: pal.tick } } },
                    grid: { borderColor: pal.grid },
                    colors: [pal.line],
                    dataLabels: { enabled: false }
                  };
                  try { new ApexCharts(el, opts).render(); } catch(_){ }
                }

                document.querySelectorAll('table.js-datatable tbody tr').forEach(function(tr){
                  var btn = tr.querySelector('button[data-bill-view]');
                  if (!btn) return;
                  btn.addEventListener('click', function(){
                    var d = parseRow(tr);
                    document.getElementById('mdName').value = d.name || '';
                    document.getElementById('mdEmail').value = d.email || '';
                    document.getElementById('mdMeter').value = d.meter || '';
                    document.getElementById('mdPeriod').value = d.period || '';
                    document.getElementById('mdAmount').value = d.amount || '';
                    document.getElementById('mdDue').value = d.due || '';
                    var ind = document.getElementById('mdOutstanding');
                    if (ind){ ind.innerHTML = d.indicator ? '<li>'+d.indicator+'</li>' : '<li>No outstanding bills.</li>'; }
                    var hist = document.getElementById('mdHistory');
                    if (hist){
                      hist.innerHTML = ''+
                        '<tr><td class="px-2 py-1">May 2024</td><td class="px-2 py-1">₱1,050.00</td><td class="px-2 py-1">Yes</td></tr>'+
                        '<tr><td class="px-2 py-1">Jun 2024</td><td class="px-2 py-1">₱1,180.00</td><td class="px-2 py-1">Yes</td></tr>'+
                        '<tr><td class="px-2 py-1">Jul 2024</td><td class="px-2 py-1">₱1,220.00</td><td class="px-2 py-1">Yes</td></tr>'+
                        '<tr><td class="px-2 py-1">Aug 2024</td><td class="px-2 py-1">₱1,260.00</td><td class="px-2 py-1">Yes</td></tr>'+
                        '<tr><td class="px-2 py-1">Sep 2024</td><td class="px-2 py-1">₱1,245.50</td><td class="px-2 py-1">No</td></tr>';
                    }
                    renderChart();
                    // Animate panel and blur/dim backdrop
                    var panel = document.getElementById('viewBillPanel');
                    if (panel){ panel.classList.remove('animate-slide-down'); void panel.offsetWidth; panel.classList.add('animate-slide-down'); }
                    setTimeout(function(){ var bd = document.getElementById('viewBillModal-backdrop'); if (bd) { bd.classList.add('backdrop-blur-sm','bg-black/30'); } }, 0);

                    // Wire save button to update the row (UI only)
                    var save = document.getElementById('mdSave');
                    if (save && !save._wired){
                      save._wired = true;
                      save.addEventListener('click', function(){
                        var name = document.getElementById('mdName').value.trim();
                        var email = document.getElementById('mdEmail').value.trim();
                        var meter = document.getElementById('mdMeter').value.trim();
                        var period = document.getElementById('mdPeriod').value.trim();
                        var amount = document.getElementById('mdAmount').value.trim();
                        var due = document.getElementById('mdDue').value.trim();
                        try{
                          tr.querySelector('td:nth-child(2) div:nth-child(1)').textContent = name || tr.querySelector('td:nth-child(2) div:nth-child(1)').textContent;
                          tr.querySelector('td:nth-child(2) div:nth-child(2)').textContent = email || tr.querySelector('td:nth-child(2) div:nth-child(2)').textContent;
                          tr.querySelector('td:nth-child(3)').textContent = meter || tr.querySelector('td:nth-child(3)').textContent;
                          tr.querySelector('td:nth-child(4)').textContent = period || tr.querySelector('td:nth-child(4)').textContent;
                          tr.querySelector('td:nth-child(5)').textContent = amount || tr.querySelector('td:nth-child(5)').textContent;
                          tr.querySelector('td:nth-child(7)').textContent = due || tr.querySelector('td:nth-child(7)').textContent;
                        } catch(_){}
                      });
                    }
                  });
                });

                // Re-render chart on theme toggle if needed
                var mo = new MutationObserver(function(){ var m = document.getElementById('viewBillModal'); if (m && !m.classList.contains('hidden')) renderChart(); });
                mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
              });
            </script>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">247</span> results
                </div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Previous
                    </button>
                    <button class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm">1</button>
                    <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">2</button>
                    <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">3</button>
                    <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
