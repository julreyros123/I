@extends('layouts.admin')

@section('title', 'Admin • Meter Management')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Poppins] space-y-6">
    <div id="meterToast" class="hidden"></div>
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Meter Management</h1>
        <div class="inline-flex items-center gap-2">
            <button type="button" id="openCreateMeter" class="inline-flex items-center gap-2 h-10 px-4 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold shadow">
                <x-heroicon-o-plus class="w-4 h-4" />
                Add meter to inventory
            </button>
            <a href="#installationPipeline" class="inline-flex items-center gap-2 h-10 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                <x-heroicon-o-wrench-screwdriver class="w-4 h-4" />
                Scheduling pipeline
            </a>
        </div>

<div id="createMeterOverlay" class="fixed inset-0 z-40 hidden opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black/40"></div>
    <div id="createMeterDrawer" class="absolute inset-y-0 right-0 w-full max-w-md bg-white dark:bg-gray-900 shadow-xl border-l border-gray-200 dark:border-gray-800 transform translate-x-full transition-transform duration-200">
        <div class="h-full flex flex-col">
            <div class="px-5 py-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Add meter to inventory</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">All meters created here start in <span class="font-semibold text-sky-600">inventory</span> status.</p>
                </div>
                <button type="button" id="closeCreateMeter" class="w-8 h-8 inline-flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            <form method="post" action="{{ route('admin.meters.store') }}" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Serial Number <span class="text-rose-500">*</span></label>
                    <input name="serial" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm" placeholder="e.g. WM-2025-0001" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</label>
                        <input name="type" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm" placeholder="Residential" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Size</label>
                        <input name="size" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm" placeholder="1/2 in" />
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Manufacturer</label>
                        <input name="manufacturer" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm" placeholder="e.g. Kent" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Seal Number</label>
                        <input name="seal_no" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm" placeholder="Optional" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Location / Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm" placeholder="Optional remarks about storage location or condition."></textarea>
                </div>
                <input type="hidden" name="status" value="inventory" />
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-2">
                    <button type="button" class="inline-flex items-center gap-2 h-9 px-4 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" onclick="document.getElementById('closeCreateMeter')?.click()">Cancel</button>
                    <x-primary-button type="submit" class="px-5">Save to inventory</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
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
        <div class="px-4 py-4 md:px-6 md:py-5 space-y-4">
            @php
                $eligibleInventory = $eligibleCounts['inventory'] ?? 0;
                $eligibleInstalled = $eligibleCounts['installed'] ?? 0;
                $eligibleActive = $eligibleCounts['active'] ?? 0;
                $eligibleTotal = $eligibleInventory + $eligibleInstalled + $eligibleActive;
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="rounded-xl border border-sky-100 dark:border-sky-900/40 bg-sky-50 dark:bg-sky-900/20 p-3">
                    <div class="text-[11px] uppercase tracking-wide text-sky-700 dark:text-sky-200">Eligible meters</div>
                    <div class="text-2xl font-semibold text-sky-900 dark:text-sky-100">{{ $eligibleTotal }}</div>
                    <p class="text-[11px] text-sky-700/80 dark:text-sky-200/80">Inventory · Installed · Active</p>
                </div>
                <div class="rounded-xl border border-amber-100 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-900/10 p-3">
                    <div class="text-[11px] uppercase tracking-wide text-amber-700 dark:text-amber-200">Inventory ready</div>
                    <div class="text-xl font-semibold text-amber-800 dark:text-amber-100">{{ $eligibleInventory }}</div>
                    <p class="text-[11px] text-amber-700/75 dark:text-amber-200/75">Meters staged for deployment</p>
                </div>
                <div class="rounded-xl border border-indigo-100 dark:border-indigo-900/40 bg-indigo-50 dark:bg-indigo-900/20 p-3">
                    <div class="text-[11px] uppercase tracking-wide text-indigo-700 dark:text-indigo-200">Awaiting activation</div>
                    <div class="text-xl font-semibold text-indigo-800 dark:text-indigo-100">{{ $eligibleInstalled }}</div>
                    <p class="text-[11px] text-indigo-700/75 dark:text-indigo-200/75">Installed, pending confirmation</p>
                </div>
                <div class="rounded-xl border border-emerald-100 dark:border-emerald-900/40 bg-emerald-50 dark:bg-emerald-900/20 p-3">
                    <div class="text-[11px] uppercase tracking-wide text-emerald-700 dark:text-emerald-200">Active connections</div>
                    <div class="text-xl font-semibold text-emerald-800 dark:text-emerald-100">{{ $eligibleActive }}</div>
                    <p class="text-[11px] text-emerald-700/75 dark:text-emerald-200/75">Currently linked to customers</p>
                </div>
            </div>

            <form method="get" class="space-y-2">
                <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                    <div class="w-full md:w-2/3 lg:w-5/12 flex items-stretch gap-2">
                        <div class="flex flex-1 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/60">
                            <div class="flex items-center px-3 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </div>
                            <input type="search" name="q" value="{{ request('q') }}" class="flex-1 px-3 py-2 bg-transparent text-sm text-gray-900 dark:text-gray-100 focus:outline-none" placeholder="Search serial, address, or barangay" />
                            <button type="submit" class="px-4 text-xs font-semibold bg-sky-600 hover:bg-sky-700 text-white">Search</button>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800">
                            <x-heroicon-o-funnel class="w-4 h-4" />
                            <span>Filters</span>
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600 dark:text-gray-300">
                    <span class="font-medium uppercase tracking-wide text-[11px]">Filter by:</span>
                    <label class="inline-flex items-center gap-2 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900/60 px-3 py-1.5">
                        <span class="text-[11px] uppercase text-gray-500 dark:text-gray-400">Status</span>
                        <select name="status" class="bg-transparent border-none text-sm text-gray-700 dark:text-gray-100 focus:ring-0 focus:outline-none">
                            <option value="">Any</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="inline-flex items-center gap-2 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900/60 px-3 py-1.5">
                        <span class="text-[11px] uppercase text-gray-500 dark:text-gray-400">Scope</span>
                        <select name="scope" class="bg-transparent border-none text-sm text-gray-700 dark:text-gray-100 focus:ring-0 focus:outline-none">
                            <option value="eligible" @selected(request('scope', 'eligible')==='eligible')>Eligible for installation</option>
                            <option value="all" @selected(request('scope')==='all')>Show all statuses</option>
                        </select>
                    </label>
                    <a href="{{ route('admin.meters') }}" class="text-xs font-medium text-sky-600 hover:text-sky-700 dark:text-sky-300 dark:hover:text-sky-200">Clear</a>
                </div>
            </form>
        </div>

        <div class="p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Meters eligible for installation</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Showing {{ request('scope', 'eligible') === 'all' ? 'all tracked meters' : 'inventory, installed, and active lines' }}</p>
                </div>
                <a href="{{ route('admin.meters.export', request()->all()) }}" class="h-10 inline-flex items-center gap-2 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    Export
                </a>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-800">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 sticky top-0 backdrop-blur text-xs">
                        <tr>
                            <th class="px-2.5 py-1.5 text-left">Serial</th>
                            <th class="px-2.5 py-1.5 text-left">Status</th>
                            <th class="px-2.5 py-1.5 text-left">Type</th>
                            <th class="px-2.5 py-1.5 text-left">Barangay</th>
                            <th class="px-2.5 py-1.5 text-left">Readiness</th>
                            <th class="px-2.5 py-1.5 text-left">Last Reading</th>
                            <th class="px-2.5 py-1.5 text-left">Customer</th>
                            <th class="px-2.5 py-1.5 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-100 text-sm">
                        @forelse($meters as $m)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/60">
                                <td class="px-2.5 py-1.5">
                                    <span class="font-medium truncate max-w-[180px] inline-block">{{ $m->serial }}</span>
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
                                <td class="px-2.5 py-1.5">
                                    @php
                                        $application = optional($m->currentCustomer)->latestApplication;
                                        $appStatus = $application?->status;
                                        $installBadge = match ($appStatus) {
                                            'scheduled' => ['bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200', 'Schedule confirmed'],
                                            'installing' => ['bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-200', 'Installation in progress'],
                                            'installed' => ['bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200', 'Installed'],
                                            default => ($m->status === 'inventory'
                                                ? ['bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-200', 'Ready for assignment']
                                                : ['bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200', ucfirst($m->status)])
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $installBadge[0] }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ $installBadge[1] }}
                                    </span>
                                </td>
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
                                                        <label class="text-xs">Customer (installed – waiting for meter assignment)</label>
                                                        <select name="account_id" id="installedCustomerForMeter-{{ $m->id }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                                                            <option value="">Select installed customer...</option>
                                                        </select>
                                                        <input type="hidden" name="application_id" class="selected-application-id" value="" />
                                                    </div>
                                                    <div>
                                                        <label class="text-xs">Assigned At</label>
                                                        <input name="assigned_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
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

                                        <button type="button" class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-amber-500 hover:bg-amber-600 text-white" title="Log service issue" data-report-meter data-meter-id="{{ $m->id }}" data-meter-serial="{{ $m->serial }}" data-customer-id="{{ $m->current_account_id }}" data-customer-name="{{ $m->currentCustomer?->name }}" data-application-id="{{ $app->id ?? '' }}">
                                            <x-heroicon-o-lifebuoy class="w-4 h-4" />
                                        </button>

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
        </div>
        @if(($inventoryMeters ?? collect())->isNotEmpty())
            <div class="border-t border-gray-100 dark:border-gray-800 px-4 py-5 md:px-6 md:py-6 bg-gray-50/40 dark:bg-gray-900/40">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">Inventory quick-pick</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach(($inventoryMeters ?? collect())->take(8) as $inventory)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 text-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-gray-800 dark:text-gray-50">{{ $inventory->serial }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 text-[11px] rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">Inventory</span>
                            </div>
                            <dl class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                <div class="flex items-center justify-between">
                                    <dt>Type</dt>
                                    <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $inventory->type ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt>Size</dt>
                                    <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $inventory->size ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt>Manufacturer</dt>
                                    <dd class="font-medium text-gray-700 dark:text-gray-200 truncate max-w-[8rem]">{{ $inventory->manufacturer ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt>Seal No.</dt>
                                    <dd class="font-mono text-gray-600 dark:text-gray-300">{{ $inventory->seal_no ?? '—' }}</dd>
                                </div>
                            </dl>
                            <button type="button" data-copy-serial="{{ $inventory->serial }}" class="inline-flex items-center gap-1 text-xs font-semibold text-sky-600 hover:text-sky-700 dark:text-sky-300 dark:hover:text-sky-200">Copy serial</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="border-t border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/40 px-4 py-5 md:px-6 md:py-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Installation pipeline</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Track newly paid applicants and scheduled installations awaiting action.</p>
                </div>
                <button type="button" id="refreshPipeline" class="inline-flex items-center gap-2 h-9 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                    Refresh
                </button>
            </div>
            <div class="grid gap-4 lg:grid-cols-2" id="installationPipeline">
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        Paid applicants awaiting scheduling
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Once scheduled, these move to the next column.</p>
                    <div id="paidApplicationsList" class="space-y-2 text-sm text-gray-700 dark:text-gray-100">
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">No paid applications found.</div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <span class="inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                        Scheduled installations
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Keep an eye on upcoming meter installations.</p>
                    <div id="scheduledApplicationsList" class="space-y-2 text-sm text-gray-700 dark:text-gray-100">
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">No scheduled installations yet.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="reportTicketOverlay" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" data-report-dismiss></div>
    <div class="relative mx-auto mt-24 w-full max-w-lg px-4">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Log remediation ticket</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Report installation issues so the maintenance team can respond promptly.</p>
                </div>
                <button type="button" class="h-8 w-8 rounded-full text-gray-500 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800" data-report-dismiss>
                    <x-heroicon-o-x-mark class="h-5 w-5 mx-auto" />
                </button>
            </div>
            <form id="reportTicketForm" class="px-5 py-4 space-y-4">
                <input type="hidden" id="reportTicketMeter" />
                <input type="hidden" id="reportTicketCustomer" />
                <input type="hidden" id="reportTicketApplication" />
                <div class="rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600 dark:bg-gray-800/80 dark:text-gray-300">
                    <dl class="grid grid-cols-1 gap-1">
                        <div>
                            <dt class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Meter</dt>
                            <dd id="reportTicketMeterLabel" class="font-semibold text-gray-800 dark:text-gray-100">—</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer</dt>
                            <dd id="reportTicketCustomerLabel" class="text-gray-700 dark:text-gray-200">Unassigned</dd>
                        </div>
                    </dl>
                </div>
                @include('components.ticket.form')
                <div class="flex items-center justify-between gap-3">
                    <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" data-report-dismiss>Cancel</button>
                    <button id="reportTicketSubmit" type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <x-heroicon-o-paper-airplane class="h-4 w-4" />
                        Submit ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const drawer = document.getElementById('createMeterDrawer');
    const openBtn = document.getElementById('openCreateMeter');
    const closeBtn = document.getElementById('closeCreateMeter');
    const overlay = document.getElementById('createMeterOverlay');
    const reportOverlay = document.getElementById('reportTicketOverlay');
    const reportForm = document.getElementById('reportTicketForm');
    const reportSubmit = document.getElementById('reportTicketSubmit');
    const reportMeterInput = document.getElementById('reportTicketMeter');
    const reportCustomerInput = document.getElementById('reportTicketCustomer');
    const reportApplicationInput = document.getElementById('reportTicketApplication');
    const reportMeterLabel = document.getElementById('reportTicketMeterLabel');
    const reportCustomerLabel = document.getElementById('reportTicketCustomerLabel');
    const issueTypeField = document.getElementById('ticketIssueType');
    const descriptionField = document.getElementById('ticketDescription');
    const scheduleField = document.getElementById('ticketSchedule');

    function toggleDrawer(show){
        if (!drawer || !overlay) return;
        drawer.classList.toggle('translate-x-full', !show);
        overlay.classList.toggle('hidden', !show);
        overlay.classList.toggle('opacity-0', !show);
    }

    if (openBtn){
        openBtn.addEventListener('click', () => toggleDrawer(true));
    }
    if (closeBtn){
        closeBtn.addEventListener('click', () => toggleDrawer(false));
    }
    if (overlay){
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay){
                toggleDrawer(false);
            }
        });
    }

    function toggleReport(show) {
        if (!reportOverlay) return;
        reportOverlay.classList.toggle('hidden', !show);
        if (show) {
            reportOverlay.classList.remove('opacity-0');
        }
    }

    function resetReportForm(){
        if (reportForm) reportForm.reset();
        if (descriptionField) descriptionField.value = '';
        if (scheduleField) scheduleField.value = '';
    }

    function openReportModal({ meterId, meterSerial, customerName, customerId, applicationId }){
        if (!reportOverlay || !issueTypeField) return;
        reportMeterInput.value = meterId || '';
        reportCustomerInput.value = customerId || '';
        reportApplicationInput.value = applicationId || '';
        reportMeterLabel.textContent = meterSerial || '—';
        reportCustomerLabel.textContent = customerName || 'Unassigned';
        if (!issueTypeField.value) issueTypeField.value = 'bad_installation';
        toggleReport(true);
    }

    if (reportOverlay){
        reportOverlay.querySelectorAll('[data-report-dismiss]').forEach(btn => {
            btn.addEventListener('click', () => {
                toggleReport(false);
                resetReportForm();
            });
        });
    }

    document.querySelectorAll('[data-report-meter]').forEach(btn => {
        btn.addEventListener('click', () => {
            const meterId = btn.dataset.meterId;
            const meterSerial = btn.dataset.meterSerial;
            const customerId = btn.dataset.customerId;
            const customerName = btn.closest('tr')?.querySelector('[data-customer-name]')?.textContent?.trim();
            const applicationId = btn.dataset.applicationId || '';
            openReportModal({ meterId, meterSerial, customerName, customerId, applicationId });
        });
    });

    if (reportForm){
        reportForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!reportSubmit) return;
            const payload = {
                meter_id: reportMeterInput.value || null,
                customer_id: reportCustomerInput.value || null,
                customer_application_id: reportApplicationInput.value || null,
                issue_type: issueTypeField?.value || 'other',
                description: descriptionField?.value || null,
                scheduled_visit_at: scheduleField?.value || null,
            };
            reportSubmit.disabled = true;
            reportSubmit.classList.add('opacity-70');
            try {
                const res = await fetch('{{ route('admin.meter-service-tickets.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    throw new Error(data.message || 'Unable to submit ticket.');
                }
                toast('Remediation ticket logged.');
                toggleReport(false);
                resetReportForm();
            } catch (err){
                toast(err.message || 'Unable to log ticket.', 'error');
            } finally {
                reportSubmit.disabled = false;
                reportSubmit.classList.remove('opacity-70');
            }
        });
    }

    const __applicationsByStatus = {};

    const toast = (message, variant = 'success') => {
        const alert = document.getElementById('meterToast');
        if (!alert) return;
        alert.textContent = message;
        alert.className = 'fixed top-6 right-6 z-50 px-4 py-2 rounded-lg shadow-lg text-sm font-semibold transition';
        if (variant === 'error') {
            alert.classList.add('bg-red-600', 'text-white');
        } else {
            alert.classList.add('bg-emerald-600', 'text-white');
        }
        alert.classList.remove('hidden');
        setTimeout(() => alert.classList.add('hidden'), 3200);
    };

    function registerCopySerialButtons(){
        const buttons = document.querySelectorAll('[data-copy-serial]');
        if (!buttons.length) return;
        buttons.forEach(btn => {
            btn.addEventListener('click', async () => {
                const serial = btn.dataset.copySerial;
                if (!serial) return;
                try {
                    if (navigator.clipboard?.writeText){
                        await navigator.clipboard.writeText(serial);
                    } else {
                        const temp = document.createElement('textarea');
                        temp.value = serial;
                        temp.style.position = 'fixed';
                        temp.style.opacity = '0';
                        document.body.appendChild(temp);
                        temp.select();
                        document.execCommand('copy');
                        document.body.removeChild(temp);
                    }
                    toast(`Copied ${serial} to clipboard.`);
                } catch (err){
                    console.error(err);
                    toast('Unable to copy serial.', 'error');
                }
            });
        });
    }

    function escapeHtml(value){
        return String(value ?? '').replace(/[&<>"']/g, (ch) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[ch]);
    }

    function friendlyDate(value){
        if (!value) return '—';
        try {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return '—';
            return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: '2-digit' });
        } catch(_){
            return '—';
        }
    }

    function friendlyDateTime(value){
        if (!value) return '—';
        try {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return '—';
            return date.toLocaleString();
        } catch(_){
            return '—';
        }
    }

    async function fetchApplicationsByStatus(status){
        if (__applicationsByStatus[status]) return __applicationsByStatus[status];
        try {
            const url = new URL('/api/connections', window.location.origin);
            url.searchParams.set('status', status);
            const res = await fetch(url.toString(), { headers:{ 'Accept':'application/json' } });
            if (!res.ok) throw new Error('Failed to load applications');
            const data = await res.json();
            const list = (data && data.items && (data.items.data || data.items)) || [];
            __applicationsByStatus[status] = Array.isArray(list) ? list : [];
        } catch(_){
            __applicationsByStatus[status] = [];
        }
        return __applicationsByStatus[status];
    }

    async function getPipelineApplications(){
        const statuses = ['paid','scheduled','installing','installed'];
        const results = {};
        for (const status of statuses){
            results[status] = await fetchApplicationsByStatus(status);
        }
        return results;
    }

    function renderPipelineList(containerId, items, emptyMessage, type){
        const container = document.getElementById(containerId);
        if (!container) return;
        if (!items.length){
            container.innerHTML = `<div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">${escapeHtml(emptyMessage)}</div>`;
            return;
        }
        container.innerHTML = items.map(app => {
            const code = escapeHtml(app.application_code || `APP-${app.id}`);
            const name = escapeHtml(app.applicant_name || 'Unnamed applicant');
            const address = escapeHtml(app.address || 'Address unavailable');
            const paidAt = friendlyDateTime(app.paid_at);
            const scheduleDate = friendlyDate(app.schedule_date);
            const tag = (app.status || '').toString();
            const badge = tag === 'scheduled'
                ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200'
                : (tag === 'installed'
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200'
                    : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200');

            const scheduleAction = type === 'paid' ? `
                <button type="button" data-action="schedule" data-id="${app.id}" class="inline-flex items-center gap-1 rounded-lg border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-900/40 dark:text-sky-200">
                    <x-heroicon-o-calendar class="w-4 h-4" />
                    Schedule install
                </button>` : '';

            const detailAction = ['scheduled','installing'].includes(tag) ? `
                <button type="button" data-action="meter-details" data-id="${app.id}" class="inline-flex items-center gap-1 rounded-lg border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-900/40 dark:text-sky-200">
                    <x-heroicon-o-wrench class="w-4 h-4" />
                    Log meter details
                </button>` : '';

            const installAction = tag === 'installing' ? `
                <button type="button" data-action="install" data-id="${app.id}" class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                    <x-heroicon-o-bolt class="w-4 h-4" />
                    Mark installed
                </button>` : '';

            const assignAction = tag === 'installed' ? `
                <button type="button" data-action="assign" data-id="${app.id}" data-customer="${app.customer_id || ''}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200">
                    <x-heroicon-o-arrow-right-circle class="w-4 h-4" />
                    Assign meter
                </button>` : '';

            const actions = [scheduleAction, detailAction, installAction, assignAction].filter(Boolean).join('');

            return `
                <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60 px-3 py-2 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">${code}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">${name}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">${address}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium ${badge}">
                            ${escapeHtml(tag)}
                        </span>
                    </div>
                    <dl class="grid grid-cols-2 gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                        <div>
                            <dt class="uppercase">Paid</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-200">${paidAt}</dd>
                        </div>
                        <div>
                            <dt class="uppercase">Schedule</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-200">${scheduleDate}</dd>
                        </div>
                    </dl>
                    ${actions ? `<div class="flex flex-wrap items-center gap-2" data-actions>${actions}</div>` : ''}
                </div>
            `;
        }).join('');
        container.querySelectorAll('[data-actions] button').forEach(btn => {
            btn.addEventListener('click', async () => {
                const action = btn.dataset.action;
                const id = btn.dataset.id;
                if (!action || !id) return;
                if (action === 'schedule') {
                    await promptSchedule(id);
                } else if (action === 'meter-details') {
                    await promptMeterDetails(id);
                } else if (action === 'install') {
                    await markInstalled(id);
                } else if (action === 'assign') {
                    await openAssignFlow(id, btn.dataset.customer || '');
                }
            });
        });
    }

    async function refreshPipeline(){
        const data = await getPipelineApplications();
        renderPipelineList('paidApplicationsList', (data.paid || []).filter(item => (item.status || '') === 'paid'), 'No paid applications found.', 'paid');
        const scheduledItems = [...(data.scheduled || []), ...(data.installing || [])]
            .filter(item => (item.status || '').toLowerCase() !== 'installed')
            .filter((item, index, arr) => arr.findIndex(candidate => candidate.id === item.id) === index);
        renderPipelineList('scheduledApplicationsList', scheduledItems, 'No scheduled installations yet.', 'scheduled');
    }

    async function populateInstalledCustomerSelects(){
        const selects = document.querySelectorAll('select[id^="installedCustomerForMeter-"]');
        if (!selects.length) return;
        const pipeline = await getPipelineApplications();
        const apps = [
            ...(pipeline.paid || []),
            ...(pipeline.scheduled || []),
            ...(pipeline.installing || []),
            ...(pipeline.installed || []),
        ].filter(item => item.customer_id).filter((item, index, arr) => arr.findIndex(candidate => candidate.id === item.id) === index);
        if (!apps.length) return;

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
                const labelParts = [app.applicant_name || 'Customer'];
                if (app.application_code) labelParts.push(app.application_code);
                if (app.status) labelParts.push(app.status.toUpperCase());
                const opt = document.createElement('option');
                opt.value = app.customer_id;
                opt.dataset.applicationId = app.id;
                opt.textContent = labelParts.join(' • ');
                sel.appendChild(opt);
            });

            sel.addEventListener('change', () => {
                const hidden = sel.closest('form')?.querySelector('input.selected-application-id');
                if (!hidden) return;
                const selected = sel.options[sel.selectedIndex];
                hidden.value = selected?.dataset.applicationId || '';
            });
        });
    }

    async function init(){
        await Promise.all([
            populateInstalledCustomerSelects(),
            refreshPipeline(),
        ]);
    }

    const refreshButton = document.getElementById('refreshPipeline');
    if (refreshButton){
        refreshButton.addEventListener('click', async () => {
            Object.keys(__applicationsByStatus).forEach(key => delete __applicationsByStatus[key]);
            await init();
        });
    }

    function boot(){
        registerCopySerialButtons();
        init();
    }

    if (document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    async function promptSchedule(id){
        const scheduleDate = window.prompt('Set installation schedule (YYYY-MM-DD):');
        if (!scheduleDate) return;
        try {
            const res = await fetch(`/api/connections/${id}/schedule`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ schedule_date: scheduleDate }),
            });
            if (!res.ok){
                const data = await res.json().catch(() => ({}));
                throw new Error(data.message || 'Unable to schedule');
            }
            await refreshAfterMutation();
            toast('Installation scheduled.');
        } catch (err){
            toast(err.message || 'Unable to schedule installation.', 'error');
        }
    }

    async function promptMeterDetails(id){
        const meterNo = window.prompt('Enter assigned meter number:');
        if (!meterNo) return;
        const meterSize = window.prompt('Optional: meter size (e.g. 1/2")');
        try {
            const res = await fetch(`/api/connections/${id}/meter-details`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ meter_no: meterNo, meter_size: meterSize || null }),
            });
            if (!res.ok){
                const data = await res.json().catch(() => ({}));
                throw new Error(data.message || 'Unable to save meter details');
            }
            await refreshAfterMutation();
            toast('Meter details logged.');
        } catch (err){
            toast(err.message || 'Unable to save meter details.', 'error');
        }
    }

    async function markInstalled(id){
        if (!confirm('Mark this installation as completed now?')) return;
        try {
            const res = await fetch(`/api/connections/${id}/install`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({}),
            });
            if (!res.ok){
                const data = await res.json().catch(() => ({}));
                throw new Error(data.message || 'Unable to mark installed');
            }
            await refreshAfterMutation();
            toast('Application marked installed.');
        } catch (err){
            toast(err.message || 'Unable to mark as installed.', 'error');
        }
    }

    async function openAssignFlow(applicationId, customerId){
        try {
            const selects = Array.from(document.querySelectorAll('select[id^="installedCustomerForMeter-"]'));
            const select = selects.find(sel => sel.options.length > 1) || selects[0];
            if (!select){
                toast('No meter assignment form found on page.', 'error');
                return;
            }
            const pipeline = await getPipelineApplications();
            const target = [...(pipeline.installed || [])].find(app => String(app.id) === String(applicationId));
            if (!target){
                toast('Installed application not found in pipeline.', 'error');
                return;
            }
            const meterAccordion = select.closest('details');
            await refreshAfterMutation();
            select.value = customerId || target.customer_id || '';
            select.dispatchEvent(new Event('change'));
            select.scrollIntoView({ behavior: 'smooth', block: 'center' });
            toast('Select an inventory meter to assign.', 'success');
        } catch (err){
            toast(err.message || 'Unable to open assignment flow.', 'error');
        }
    }

    async function refreshAfterMutation(){
        Object.keys(__applicationsByStatus).forEach(key => delete __applicationsByStatus[key]);
        await init();
    }
})();
</script>
@endpush
