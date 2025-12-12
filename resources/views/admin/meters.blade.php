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
                                        <button type="button" data-open-modal="meter-edit-modal-{{ $m->id }}" class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" aria-label="Edit meter {{ $m->serial }}">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </button>
                                        <button type="button" data-open-modal="meter-assign-modal-{{ $m->id }}" class="h-9 w-9 inline-flex items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" aria-label="Assign meter {{ $m->serial }}">
                                            <x-heroicon-o-user-plus class="w-4 h-4" />
                                        </button>
                                        <button type="button" data-open-modal="meter-unassign-modal-{{ $m->id }}" class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" aria-label="Unassign meter {{ $m->serial }}">
                                            <x-heroicon-o-user-minus class="w-4 h-4" />
                                        </button>
                                        <button type="button" data-open-modal="meter-delete-modal-{{ $m->id }}" class="h-9 w-9 inline-flex items-center justify-center rounded-lg bg-rose-600 text-white hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500" aria-label="Delete meter {{ $m->serial }}">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                                @push('modals')
                                    <div class="hidden fixed inset-0 z-50 overflow-y-auto" id="meter-edit-modal-{{ $m->id }}" data-modal aria-hidden="true">
                                        <div class="absolute inset-0 bg-black/50" data-modal-backdrop data-modal-dismiss></div>
                                        <div class="relative mx-auto my-16 w-full max-w-3xl px-4">
                                            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                                    <div>
                                                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Edit meter {{ $m->serial }}</h2>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Update metadata and status before reassignment.</p>
                                                    </div>
                                                    <button type="button" class="h-9 w-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500" data-modal-dismiss aria-label="Close">
                                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                                    </button>
                                                </div>
                                                <div class="px-5 py-6">
                                                    <form method="post" action="{{ route('admin.meters.update', $m) }}" class="grid gap-4 md:grid-cols-2" data-modal-autofocus>
                                                        @csrf
                                                        @method('PATCH')
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                            Status
                                                            <select name="status" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100">
                                                                @foreach($statuses as $s)
                                                                    <option value="{{ $s }}" @selected($m->status===$s)>{{ ucfirst($s) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </label>
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                            Type
                                                            <input name="type" value="{{ $m->type }}" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                        </label>
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                            Size
                                                            <input name="size" value="{{ $m->size }}" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                        </label>
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                            Manufacturer
                                                            <input name="manufacturer" value="{{ $m->manufacturer }}" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                        </label>
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 md:col-span-2">
                                                            Address
                                                            <input name="location_address" value="{{ $m->location_address }}" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                        </label>
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                            Barangay
                                                            <input name="barangay" value="{{ $m->barangay }}" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                        </label>
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 md:col-span-2">
                                                            Notes
                                                            <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100">{{ $m->notes }}</textarea>
                                                        </label>
                                                        <div class="md:col-span-2 flex justify-end gap-2">
                                                            <button type="button" class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800" data-modal-dismiss>Cancel</button>
                                                            <button class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500">Save changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hidden fixed inset-0 z-50 overflow-y-auto" id="meter-assign-modal-{{ $m->id }}" data-modal aria-hidden="true">
                                        <div class="absolute inset-0 bg-black/50" data-modal-backdrop data-modal-dismiss></div>
                                        <div class="relative mx-auto my-16 w-full max-w-2xl px-4">
                                            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                                    <div>
                                                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Assign meter {{ $m->serial }}</h2>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Link this meter to a scheduled installation.</p>
                                                    </div>
                                                    <button type="button" class="h-9 w-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500" data-modal-dismiss aria-label="Close">
                                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                                    </button>
                                                </div>
                                                <div class="px-5 py-6 space-y-4">
                                                    <form method="post" action="{{ route('admin.meters.assign', $m) }}" class="space-y-4" data-modal-autofocus>
                                                            @csrf
                                                            <input type="hidden" name="account_id" id="accountInput-{{ $m->id }}">
                                                            <input type="hidden" name="application_id" id="applicationInput-{{ $m->id }}">
                                                            <div class="space-y-2">
                                                                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Select scheduled customer</label>
                                                                <select id="scheduledSelect-{{ $m->id }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" data-app-select data-account-target="#accountInput-{{ $m->id }}" data-application-target="#applicationInput-{{ $m->id }}" data-fallback-reset="#recentCustomerSelect-{{ $m->id }}">
                                                                    <option value="">Choose scheduled installation...</option>
                                                                    @foreach($assignmentOptions as $option)
                                                                        <option value="{{ $option['customer_id'] }}" data-application-id="{{ $option['application_id'] }}">
                                                                            {{ $option['customer_name'] }}
                                                                            @if(!empty($option['account_no']))
                                                                                · Acct {{ $option['account_no'] }}
                                                                            @endif
                                                                            @if(!empty($option['address']))
                                                                                · {{ $option['address'] }}
                                                                            @endif
                                                                            @if(!empty($option['scheduled_for']))
                                                                                · Visit {{ $option['scheduled_for'] }}
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Pick a scheduled installation to auto-fill the application link.</p>
                                                            </div>
                                                            <hr class="border-gray-200 dark:border-gray-700" />
                                                            <div class="space-y-2">
                                                                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Or choose a recent customer</label>
                                                                <select id="recentCustomerSelect-{{ $m->id }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" data-fallback-select data-account-target="#accountInput-{{ $m->id }}" data-application-target="#applicationInput-{{ $m->id }}" data-scheduled-reset="#scheduledSelect-{{ $m->id }}">
                                                                    <option value="">Recent registrations...</option>
                                                                    @foreach(($recentCustomers ?? collect()) as $recent)
                                                                        <option value="{{ $recent->id }}">
                                                                            {{ $recent->name }}
                                                                            @if(!empty($recent->account_no))
                                                                                · Acct {{ $recent->account_no }}
                                                                            @endif
                                                                            @if(!empty($recent->address))
                                                                                · {{ $recent->address }}
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Use this when the customer was recently registered but the application isn’t linked yet.</p>
                                                            </div>
                                                            <div class="grid gap-3 md:grid-cols-2">
                                                                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                                    Assigned at
                                                                    <input name="assigned_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                                </label>
                                                                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                                    Reason
                                                                    <input name="reason" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                                </label>
                                                                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 md:col-span-2">
                                                                    Notes
                                                                    <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100"></textarea>
                                                                </label>
                                                            </div>
                                                            <div class="flex justify-end gap-2">
                                                                <button type="button" class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800" data-modal-dismiss>Cancel</button>
                                                                <button class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500">Assign meter</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hidden fixed inset-0 z-50 overflow-y-auto" id="meter-unassign-modal-{{ $m->id }}" data-modal aria-hidden="true">
                                        <div class="absolute inset-0 bg-black/50" data-modal-backdrop data-modal-dismiss></div>
                                        <div class="relative mx-auto my-16 w-full max-w-xl px-4">
                                            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                                    <div>
                                                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Unassign meter {{ $m->serial }}</h2>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Release the meter back to inventory.</p>
                                                    </div>
                                                    <button type="button" class="h-9 w-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500" data-modal-dismiss aria-label="Close">
                                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                                    </button>
                                                </div>
                                                <div class="px-5 py-6">
                                                    <form method="post" action="{{ route('admin.meters.unassign', $m) }}" class="space-y-4" data-modal-autofocus>
                                                        @csrf
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                            Unassigned at
                                                            <input name="unassigned_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                        </label>
                                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                            Reason
                                                            <input name="reason" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
                                                        </label>
                                                        <div class="flex justify-end gap-2">
                                                            <button type="button" class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800" data-modal-dismiss>Cancel</button>
                                                            <button class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-gray-700 text-white text-sm font-semibold hover:bg-gray-600">Unassign</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hidden fixed inset-0 z-50 overflow-y-auto" id="meter-delete-modal-{{ $m->id }}" data-modal aria-hidden="true">
                                        <div class="absolute inset-0 bg-black/60" data-modal-backdrop data-modal-dismiss></div>
                                        <div class="relative mx-auto my-16 w-full max-w-md px-4">
                                            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                                    <div>
                                                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Delete meter {{ $m->serial }}</h2>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
                                                    </div>
                                                    <button type="button" class="h-9 w-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500" data-modal-dismiss aria-label="Close">
                                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                                    </button>
                                                </div>
                                                <div class="px-5 py-6 space-y-4">
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">Are you sure you want to delete this meter? Any historical audit entries will remain, but the meter will be removed from active records.</p>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800" data-modal-dismiss>Cancel</button>
                                                        <form method="post" action="{{ route('admin.meters.destroy', $m) }}" onsubmit="return confirm('Delete meter {{ $m->serial }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-rose-600 text-white text-sm font-semibold hover:bg-rose-500">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endpush
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
        @php
            $pendingScheduling = ($installationQueue ?? collect())->where('status', 'paid');
            $scheduledInstalls = ($installationQueue ?? collect())->where('status', 'scheduled');
        @endphp
        <div id="installationPipeline" class="border-t border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/40 px-4 py-5 md:px-6 md:py-6 space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Installation pipeline</h2>
                <span class="text-xs text-gray-500 dark:text-gray-400">Manage scheduling for paid applicants</span>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <section class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/70 p-4 space-y-3">
                    <header class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Awaiting schedule</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Paid applications that still need a visit date.</p>
                        </div>
                        <span class="inline-flex items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200 px-2 py-0.5 text-[11px] font-medium">{{ $pendingScheduling->count() }}</span>
                    </header>

                    <div class="space-y-3">
                        @forelse($pendingScheduling as $app)
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $app->application_code ?? ('APP-'.str_pad($app->id, 6, '0', STR_PAD_LEFT)) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $app->applicant_name }} · {{ $app->address ?? 'No address on file' }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200 text-[11px] font-semibold">Paid</span>
                                </div>
                                <form method="POST" action="{{ route('connections.schedule', $app->id) }}" class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                                    @csrf
                                    @method('PUT')
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        Schedule date
                                        <input type="date" name="schedule_date" value="{{ old('schedule_date', optional($app->schedule_date)->toDateString()) }}" min="{{ now()->toDateString() }}" required class="mt-1 w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100" />
                                    </label>
                                    <button class="inline-flex justify-center items-center h-10 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500">Schedule</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 dark:text-gray-400">No paid applications are waiting for scheduling.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/70 p-4 space-y-3">
                    <header class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Scheduled installs</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Update visit dates or confirm progress.</p>
                        </div>
                        <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200 px-2 py-0.5 text-[11px] font-medium">{{ $scheduledInstalls->count() }}</span>
                    </header>

                    <div class="space-y-3">
                        @forelse($scheduledInstalls as $app)
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $app->application_code ?? ('APP-'.str_pad($app->id, 6, '0', STR_PAD_LEFT)) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $app->applicant_name }} · {{ $app->address ?? 'No address on file' }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200 text-[11px] font-semibold">Scheduled</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-300">
                                    <span class="font-medium text-gray-700 dark:text-gray-100">Visit:</span>
                                    <span>{{ optional($app->schedule_date)->format('M d, Y') ?? 'Not set' }}</span>
                                </div>
                                <form method="POST" action="{{ route('connections.schedule', $app->id) }}" class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                                    @csrf
                                    @method('PUT')
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        Reschedule
                                        <input type="date" name="schedule_date" value="{{ optional($app->schedule_date)->toDateString() ?? now()->toDateString() }}" min="{{ now()->toDateString() }}" required class="mt-1 w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-100" />
                                    </label>
                                    <button class="inline-flex justify-center items-center h-10 px-4 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">Update</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nothing scheduled yet. Once dates are set, they will appear here.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('createMeterOverlay');
    const drawer = document.getElementById('createMeterDrawer');
    const openBtn = document.getElementById('openCreateMeter');
    const closeBtn = document.getElementById('closeCreateMeter');

    if (!overlay || !drawer || !openBtn || !closeBtn) {
        return;
    }

    const form = overlay.querySelector('form');

    const showDrawer = () => {
        overlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
            drawer.classList.remove('translate-x-full');
        });
    };

    const hideDrawer = () => {
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
        drawer.classList.add('translate-x-full');
        setTimeout(() => overlay.classList.add('hidden'), 200);
    };

    openBtn.addEventListener('click', showDrawer);
    closeBtn.addEventListener('click', hideDrawer);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            hideDrawer();
        }
    });

    if (form) {
        form.addEventListener('submit', () => {
            openBtn.disabled = true;
        });
    }
});
</script>
@endpush
