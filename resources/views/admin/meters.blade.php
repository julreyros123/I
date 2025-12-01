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
            <div class="col-span-2 flex items-center p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                <div>
                    <div class="text-[11px] text-gray-500">Total</div>
                    <div class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ ($kpis['inventory'] ?? 0)+($kpis['installed'] ?? 0)+($kpis['active'] ?? 0)+($kpis['maintenance'] ?? 0)+($kpis['inactive'] ?? 0)+($kpis['retired'] ?? 0) }}</div>
                </div>
            </div>
            <div class="p-2 rounded-lg bg-blue-50/60 dark:bg-blue-900/10">
                <div class="text-[11px] text-gray-500">Inventory</div>
                <div class="text-base font-semibold text-blue-800 dark:text-blue-200">{{ $kpis['inventory'] ?? 0 }}</div>
            </div>
            <div class="p-2 rounded-lg bg-blue-50/60 dark:bg-blue-900/10">
                <div class="text-[11px] text-gray-500">Active</div>
                <div class="text-base font-semibold text-blue-800 dark:text-blue-200">{{ $kpis['active'] ?? 0 }}</div>
            </div>
            <div class="p-2 rounded-lg bg-blue-50/60 dark:bg-blue-900/10">
                <div class="text-[11px] text-gray-500">Maintenance</div>
                <div class="text-base font-semibold text-blue-800 dark:text-blue-200">{{ $kpis['maintenance'] ?? 0 }}</div>
            </div>
            <div class="p-2 rounded-lg bg-blue-50/60 dark:bg-blue-900/10">
                <div class="text-[11px] text-gray-500">Retired</div>
                <div class="text-base font-semibold text-blue-800 dark:text-blue-200">{{ $kpis['retired'] ?? 0 }}</div>
            </div>
        </div>
        <div class="px-3 md:px-4 pb-3">
            <form method="get" class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 md:flex-1">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search serial, address, or barangay" class="w-full h-10 px-3 text-sm rounded-md border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-900" />
                    <select name="status" class="w-full h-10 px-3 text-sm rounded-md border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-900">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="type" value="{{ request('type') }}" placeholder="Type" class="w-full h-10 px-3 text-sm rounded-md border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-900" />
                    <input type="text" name="barangay" value="{{ request('barangay') }}" placeholder="Barangay" class="w-full h-10 px-3 text-sm rounded-md border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-900" />
                </div>
                <div class="flex flex-wrap md:flex-nowrap gap-2 items-center md:justify-end md:self-end md:mt-1">
                    <button title="Apply filters" class="h-10 inline-flex items-center justify-center gap-1.5 px-3 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('admin.meters') }}" title="Reset filters" class="h-10 inline-flex items-center justify-center gap-1.5 px-3 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        <span>Reset</span>
                    </a>
                    <a href="{{ route('admin.meters.export', request()->all()) }}" title="Export" class="h-10 inline-flex items-center justify-center gap-1.5 px-3 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                        <span>Export</span>
                    </a>
                    <a href="{{ route('admin.meters.template') }}" title="Download template" class="h-10 inline-flex items-center justify-center gap-1.5 px-3 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                        <span>Template</span>
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
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-100 text-sm">
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
                                        $badge = 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200';
                                        if ($status==='inactive') $badge = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200';
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
                    				<label class="text-xs">Customer (installed  waiting for meter assignment)</label>
                                                        <select name="account_id" id="installedCustomerForMeter-{{ $m->id }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                                                            <option value="">Select installed customer...</option>
                                                        </select>
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

@push('scripts')
<script>
(function(){
    let __installedAppsCache = null;

    async function fetchInstalledApps(){
        if (__installedAppsCache) return __installedAppsCache;
        try {
            const url = new URL('/api/connections', window.location.origin);
            url.searchParams.set('status', 'installed');
            const res = await fetch(url.toString(), { headers:{ 'Accept':'application/json' } });
            if (!res.ok) throw new Error('Failed to load installed applications');
            const data = await res.json();
            const list = (data && data.items && (data.items.data || data.items)) || [];
            __installedAppsCache = Array.isArray(list) ? list : [];
        } catch(_){
            __installedAppsCache = [];
        }
        return __installedAppsCache;
    }

    async function populateInstalledCustomerSelects(){
        const selects = document.querySelectorAll('select[id^="installedCustomerForMeter-"]');
        if (!selects.length) return;
        const apps = await fetchInstalledApps();
        if (!apps.length) return;

        // Optionally fetch basic customer meter info so we skip already-metered customers
        const customersById = {};
        try {
            // Build a unique set of customer IDs
            const ids = Array.from(new Set(apps.map(a => a.customer_id).filter(Boolean)));
            // We don't have a bulk customer API, so we skip this step to keep it simple.
            // Meter assignment guard in MeterController will prevent invalid assignment anyway.
        } catch(_){ }

        selects.forEach(sel => {
            // Reset to placeholder
            const first = sel.querySelector('option[value=""]');
            sel.innerHTML = '';
            if (first){ sel.appendChild(first); } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'Select installed customer...';
                sel.appendChild(opt);
            }

            apps.forEach(app => {
                if (!app.customer_id) return;
                const label = `${app.applicant_name || 'Customer'} (APP-${app.id})`;
                const opt = document.createElement('option');
                opt.value = app.customer_id;
                opt.textContent = label;
                sel.appendChild(opt);
            });
        });
    }

    if (document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', populateInstalledCustomerSelects);
    } else {
        populateInstalledCustomerSelects();
    }
})();
</script>
@endpush
