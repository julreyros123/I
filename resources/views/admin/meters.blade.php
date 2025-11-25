@extends('layouts.admin')

@section('title', 'Admin • Meter Management')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Poppins] space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Meter Management</h1>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-2">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow border border-gray-200 dark:border-gray-800">
        <div class="p-3 md:p-4 grid grid-cols-2 md:grid-cols-6 gap-2 md:gap-3">
            <div class="col-span-2 flex items-center p-2 rounded-lg bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-900/10">
                <div>
                    <div class="text-[11px] text-gray-500">Total</div>
                    <div class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ ($kpis['inventory'] ?? 0)+($kpis['installed'] ?? 0)+($kpis['active'] ?? 0)+($kpis['maintenance'] ?? 0)+($kpis['inactive'] ?? 0)+($kpis['retired'] ?? 0) }}</div>
                </div>
            </div>
            <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800/60">
                <div class="text-[11px] text-gray-500">Inventory</div>
                <div class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ $kpis['inventory'] ?? 0 }}</div>
            </div>
            <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                <div class="text-[11px] text-gray-500">Active</div>
                <div class="text-base font-semibold text-emerald-700 dark:text-emerald-300">{{ $kpis['active'] ?? 0 }}</div>
            </div>
            <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                <div class="text-[11px] text-gray-500">Maintenance</div>
                <div class="text-base font-semibold text-amber-700 dark:text-amber-300">{{ $kpis['maintenance'] ?? 0 }}</div>
            </div>
            <div class="p-2 rounded-lg bg-rose-50 dark:bg-rose-900/20">
                <div class="text-[11px] text-gray-500">Retired</div>
                <div class="text-base font-semibold text-rose-700 dark:text-rose-300">{{ $kpis['retired'] ?? 0 }}</div>
            </div>
        </div>
        <div class="px-3 md:px-4 pb-3">
            <form method="get" class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 md:flex-1">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search serial, address, or barangay" class="w-full h-9 text-sm rounded-md border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-900" />
                    <select name="status" class="w-full h-9 text-sm rounded-md border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-900">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="type" value="{{ request('type') }}" placeholder="Type" class="w-full h-9 text-sm rounded-md border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-900" />
                    <input type="text" name="barangay" value="{{ request('barangay') }}" placeholder="Barangay" class="w-full h-9 text-sm rounded-md border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-900" />
                </div>
                <div class="flex flex-wrap md:flex-nowrap gap-2 items-center md:justify-end md:self-end md:mt-1">
                    <button title="Apply filters" class="h-9 w-9 inline-flex items-center justify-center rounded-md bg-blue-600 hover:bg-blue-700 text-white">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                    </button>
                    <a href="{{ route('admin.meters') }}" title="Reset filters" class="h-9 w-9 inline-flex items-center justify-center rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                    </a>
                    <a href="{{ route('admin.meters.export', request()->all()) }}" title="Export" class="h-9 w-9 inline-flex items-center justify-center rounded-md bg-blue-600 hover:bg-blue-700 text-white">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                    </a>
                    <a href="{{ route('admin.meters.template') }}" title="Download template" class="h-9 w-9 inline-flex items-center justify-center rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
                        <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                    </a>
                </div>
            </form>
        </div>

        <div class="p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Meters</h2>
                <details class="relative">
                    <summary class="list-none inline-flex items-center justify-center w-10 h-10 rounded-md bg-blue-600 hover:bg-blue-700 text-white cursor-pointer" title="Add meter">
                        <x-heroicon-o-plus class="w-5 h-5" />
                    </summary>
                    <div class="absolute right-0 mt-2 w-[28rem] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow p-4 z-10">
                        <form method="post" action="{{ route('admin.meters.store') }}" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm">Serial</label>
                                    <input name="serial" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                </div>
                                <div>
                                    <label class="text-sm">Status</label>
                                    <select name="status" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                                        @foreach($statuses as $s)
                                            <option value="{{ $s }}" @selected($s==='inventory')>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm">Type</label>
                                    <input name="type" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                </div>
                                <div>
                                    <label class="text-sm">Size</label>
                                    <input name="size" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                </div>
                                <div class="col-span-2">
                                    <label class="text-sm">Manufacturer</label>
                                    <input name="manufacturer" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                </div>
                                <div>
                                    <label class="text-sm">Seal No.</label>
                                    <input name="seal_no" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                </div>
                                <div>
                                    <label class="text-sm">Install Date</label>
                                    <input type="date" name="install_date" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                </div>
                                <div class="col-span-2">
                                    <label class="text-sm">Address</label>
                                    <input name="location_address" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                </div>
                                <div>
                                    <label class="text-sm">Barangay</label>
                                    <input name="barangay" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                </div>
                                <div class="col-span-2">
                                    <label class="text-sm">Notes</label>
                                    <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="submit" class="h-9 px-4 rounded-md bg-blue-600 hover:bg-blue-700 text-white">Save</button>
                            </div>
                        </form>
                    </div>
                </details>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-800">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 sticky top-0 backdrop-blur text-xs">
                        <tr>
                            <th class="px-2.5 py-1.5 text-left">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" id="checkAllHeader" onclick="headerToggleAll()" />
                                    <span>Serial</span>
                                </label>
                            </th>
                            <th class="px-2.5 py-1.5 text-left">Status</th>
                            <th class="px-2.5 py-1.5 text-left">Type</th>
                            <th class="px-2.5 py-1.5 text-left">Barangay</th>
                            <th class="px-2.5 py-1.5 text-left">Last Reading</th>
                            <th class="px-2.5 py-1.5 text-left">Customer</th>
                            <th class="px-2.5 py-1.5 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-100 text-[13px]">
                        <tr>
                            <td colspan="7" class="px-2.5 py-2">
                                <form id="bulkForm" method="post" action="{{ route('admin.meters.bulk-status') }}" class="flex flex-wrap gap-2 items-center text-sm">
                                    @csrf
                                    <input id="bulkIds" type="hidden" name="ids[]" />
                                    <span class="text-sm">Bulk status for selected:</span>
                                    <select name="status" class="h-9 text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                                        @foreach($statuses as $s)
                                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" title="Apply" class="h-9 w-9 inline-flex items-center justify-center rounded-md bg-blue-600 hover:bg-blue-700 text-white" onclick="collectSelected(event)">
                                        <x-heroicon-o-check class="w-5 h-5" />
                                    </button>
                                    <button type="button" title="Toggle all" class="h-9 w-9 inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600" onclick="toggleAll()">
                                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                                    </button>
                                    <button type="button" title="Clear" class="h-9 w-9 inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600" onclick="clearAll()">
                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                    </button>
                                </form>
                                <script>
                                function collectSelected(e){
                                  var checks = document.querySelectorAll('.meter-check:checked');
                                  var form = document.getElementById('bulkForm');
                                  // remove existing ids inputs
                                  Array.from(form.querySelectorAll('input[name="ids[]"]')).forEach(el=>el.remove());
                                  checks.forEach(ch=>{
                                    var hidden = document.createElement('input');
                                    hidden.type='hidden'; hidden.name='ids[]'; hidden.value=ch.value; form.appendChild(hidden);
                                  });
                                  if (checks.length===0){ e.preventDefault(); alert('Select at least one meter.'); }
                                }
                                function toggleAll(){
                                  var all = document.querySelectorAll('.meter-check');
                                  var allChecked = Array.from(all).every(c=>c.checked);
                                  all.forEach(c=>c.checked = !allChecked);
                                  var header = document.getElementById('checkAllHeader'); if (header) header.checked = Array.from(all).every(c=>c.checked);
                                }
                                function clearAll(){ document.querySelectorAll('.meter-check').forEach(c=>c.checked=false); }
                                function headerToggleAll(){
                                  var header = document.getElementById('checkAllHeader');
                                  document.querySelectorAll('.meter-check').forEach(c=>c.checked = header.checked);
                                }
                                </script>
                            </td>
                        </tr>
                        @forelse($meters as $m)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/60">
                                <td class="px-2.5 py-1.5">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" class="meter-check" value="{{ $m->id }}" />
                                        <span class="font-medium truncate max-w-[180px] inline-block">{{ $m->serial }}</span>
                                    </label>
                                    <div class="text-[11px] text-gray-500 truncate max-w-[220px]">{{ $m->location_address }}</div>
                                </td>
                                <td class="px-2.5 py-1.5">
                                    @php
                                        $status = $m->status;
                                        $badge = 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200';
                                        if ($status==='active') $badge = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                                        elseif ($status==='maintenance') $badge = 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
                                        elseif ($status==='installed') $badge = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300';
                                        elseif ($status==='inactive') $badge = 'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300';
                                        elseif ($status==='retired') $badge = 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst($m->status) }}</span>
                                </td>
                                <td class="px-2.5 py-1.5">{{ $m->type ?? '—' }}</td>
                                <td class="px-2.5 py-1.5">{{ $m->barangay ?? '—' }}</td>
                                <td class="px-2.5 py-1.5">{{ $m->last_reading_value ? number_format($m->last_reading_value,2) : '—' }}</td>
                                <td class="px-2.5 py-1.5">
                                    @if($m->currentCustomer)
                                        @php
                                            $cust = $m->currentCustomer;
                                            $custUrl = \Illuminate\Support\Facades\Route::has('register.index') ? (route('register.index') . '?q=' . urlencode($cust->account_no)) : null;
                                            $app = \App\Models\CustomerApplication::where('customer_id', $cust->id)->orderByDesc('created_at')->first();
                                            $feeTotal = $app?->fee_total ?? 0;
                                            $hasPaid = $app && $feeTotal > 0 && !is_null($app->paid_at);
                                        @endphp
                                        @if($custUrl)
                                            <a href="{{ $custUrl }}" class="font-medium text-blue-600 hover:underline">{{ $cust->account_no }}</a>
                                            <div class="text-xs text-gray-500 flex items-center gap-2">
                                                <span>{{ $cust->name }}</span>
                                                @if($app)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $hasPaid ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                                        {{ $hasPaid ? 'Fee paid' : 'Fee pending' }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="font-medium">{{ $cust->account_no }}</div>
                                            <div class="text-xs text-gray-500 flex items-center gap-2">
                                                <span>{{ $cust->name }}</span>
                                                @if($app)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $hasPaid ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                                        {{ $hasPaid ? 'Fee paid' : 'Fee pending' }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-2.5 py-1.5">
                                    <div class="flex flex-wrap gap-2">
                                        <details>
                                            <summary class="list-none w-8 h-8 inline-flex items-center justify-center rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 cursor-pointer" title="Edit">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            </summary>
                                            <div class="mt-2 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                                <form method="post" action="{{ route('admin.meters.update', $m) }}" class="grid grid-cols-2 gap-3">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div>
                                                        <label class="text-xs">Status</label>
                                                        <select name="status" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                                                            @foreach($statuses as $s)
                                                                <option value="{{ $s }}" @selected($m->status===$s)>{{ ucfirst($s) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs">Type</label>
                                                        <input name="type" value="{{ $m->type }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div>
                                                        <label class="text-xs">Size</label>
                                                        <input name="size" value="{{ $m->size }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div>
                                                        <label class="text-xs">Manufacturer</label>
                                                        <input name="manufacturer" value="{{ $m->manufacturer }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs">Address</label>
                                                        <input name="location_address" value="{{ $m->location_address }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div>
                                                        <label class="text-xs">Barangay</label>
                                                        <input name="barangay" value="{{ $m->barangay }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs">Notes</label>
                                                        <textarea name="notes" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900">{{ $m->notes }}</textarea>
                                                    </div>
                                                    <div class="col-span-2 flex justify-end">
                                                        <button class="h-9 px-4 rounded-md bg-blue-600 hover:bg-blue-700 text-white">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>

                                        <details>
                                            <summary class="list-none w-8 h-8 inline-flex items-center justify-center rounded-md bg-blue-600 hover:bg-blue-700 text-white cursor-pointer" title="Assign">
                                                <x-heroicon-o-user-plus class="w-4 h-4" />
                                            </summary>
                                            <div class="mt-2 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                                <form method="post" action="{{ route('admin.meters.assign', $m) }}" class="grid grid-cols-2 gap-3 relative">
                                                    @csrf
                                                    <div class="col-span-2">
                                                        <label class="text-xs">Customer</label>
                                                        <div class="relative">
                                                            <input type="text" id="search-{{ $m->id }}" placeholder="Search name or account no" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 pr-2" oninput="customerSearch{{ $m->id }}(this.value)" autocomplete="off" />
                                                            <input type="hidden" name="account_id" id="account-{{ $m->id }}" required />
                                                            <div id="results-{{ $m->id }}" class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow max-h-56 overflow-auto hidden"></div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs">Assigned At</label>
                                                        <input name="assigned_at" type="datetime-local" value="{{ now()->format('Y-m-d\\TH:i') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs">Reason</label>
                                                        <input name="reason" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs">Notes</label>
                                                        <textarea name="notes" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900"></textarea>
                                                    </div>
                                                    <div class="col-span-2 flex justify-end">
                                                        <button class="h-9 px-4 rounded-md bg-blue-600 hover:bg-blue-700 text-white">Assign</button>
                                                    </div>
                                                </form>
                                                <script>
                                                window._custTimers = window._custTimers || {};
                                                function customerSearch{{ $m->id }}(q){
                                                  const box = document.getElementById('results-{{ $m->id }}');
                                                  const hidden = document.getElementById('account-{{ $m->id }}');
                                                  hidden.value = '';
                                                  if(!q || q.length < 2){ box.classList.add('hidden'); box.innerHTML=''; return; }
                                                  if(window._custTimers['{{ $m->id }}']) clearTimeout(window._custTimers['{{ $m->id }}']);
                                                  window._custTimers['{{ $m->id }}'] = setTimeout(async ()=>{
                                                    try{
                                                      const res = await fetch("{{ route('customer.searchAccounts') }}?q="+encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                                                      const data = await res.json();
                                                      box.innerHTML = '';
                                                      (data || []).slice(0,10).forEach(item=>{
                                                        const opt = document.createElement('div');
                                                        opt.className = 'px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer';
                                                        opt.textContent = `${item.account_no || item.id} — ${item.name || ''}`;
                                                        opt.onclick = ()=>{ document.getElementById('search-{{ $m->id }}').value = `${item.account_no} — ${item.name}`; hidden.value = item.id; box.classList.add('hidden'); };
                                                        box.appendChild(opt);
                                                      });
                                                      box.classList.toggle('hidden', box.children.length===0);
                                                    }catch(e){ box.classList.add('hidden'); }
                                                  }, 250);
                                                }
                                                </script>
                                            </div>
                                        </details>

                                        <details>
                                            <summary class="list-none w-8 h-8 inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 cursor-pointer" title="Unassign">
                                                <x-heroicon-o-user-minus class="w-4 h-4" />
                                            </summary>
                                            <div class="mt-2 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                                <form method="post" action="{{ route('admin.meters.unassign', $m) }}" class="grid grid-cols-2 gap-3">
                                                    @csrf
                                                    <div>
                                                        <label class="text-xs">Unassigned At</label>
                                                        <input name="unassigned_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs">Reason</label>
                                                        <input name="reason" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                                    </div>
                                                    <div class="col-span-2 flex justify-end">
                                                        <button class="h-9 px-4 rounded-md bg-gray-700 hover:bg-gray-800 text-white">Unassign</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>

                                        <form method="post" action="{{ route('admin.meters.destroy', $m) }}" onsubmit="return confirm('Delete meter {{ $m->serial }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button title="Delete" class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-red-600 hover:bg-red-700 text-white">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-10 text-center">
                                    <div class="inline-flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 text-gray-500">
                                        <span>📭</span>
                                        <span>No meters found. Adjust filters or add a new meter.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            
            <div class="mt-6">
                <details>
                    <summary class="list-none inline-flex items-center px-3 py-2 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer">Import CSV</summary>
                    <div class="mt-2 p-3 border border-gray-200 dark:border-gray-700 rounded-lg inline-block">
                        <form method="post" action="{{ route('admin.meters.import') }}" enctype="multipart/form-data" class="flex items-center gap-3">
                            @csrf
                            <input type="file" name="file" accept=".csv,.txt" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                            <button class="px-3 py-1.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white">Upload</button>
                        </form>
                        <div class="text-xs text-gray-500 mt-2">CSV columns: serial,status,type,size,barangay,location_address</div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>
@endsection
