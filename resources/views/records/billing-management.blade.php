@extends('layouts.app')

@section('title', 'Billing Management')

@section('content')
@php
    $generatedFilter = $generated ?? '';
    $filtersQuery = collect(request()->except(['generated', 'page']))
        ->reject(fn($value) => $value === null || $value === '')
        ->all();
@endphp
<div class="w-full px-4 sm:px-6 py-6 space-y-6">
    <div>
        <p class="text-gray-600 text-xs">Generate a bill with invoice details, instant calculations, and save it directly to the billing records.</p>
    </div>

    <div class="bg-white dark:bg-gray-900/70 rounded-2xl shadow-xl ring-1 ring-gray-200 dark:ring-gray-800 p-5 sm:p-6">
        <div id="alertBox" class="hidden rounded-xl border px-4 py-3 text-sm font-medium mb-4"></div>

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-5 xl:gap-6">
            <div>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-50">Generate Bill</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Provide account, readings, and charges. Totals adjust automatically.</p>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Fields marked with <span class="text-sky-500">●</span> are required</div>
                </div>

                <div class="space-y-5">
                    <div class="grid md:grid-cols-12 gap-4">
                        <div class="md:col-span-6 space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice Number <span class="text-sky-500">●</span></label>
                            <div class="flex items-center gap-2">
                                <x-ui.input id="invoice_number" class="uppercase" placeholder="INV-20251204-1023" />
                                <button type="button" id="refreshInvoice" class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-600 hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-900/40 dark:text-sky-200">New</button>
                            </div>
                            <p class="text-[11px] text-gray-400">Automatically generated, but you may override before saving.</p>
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice Date</label>
                            <x-ui.input id="issued_at" type="date" value="{{ now()->format('Y-m-d') }}" />
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Person In Charge</label>
                            <x-ui.input id="prepared_by" value="{{ (trim(auth()->user()->name ?? '') === 'Sample Staff') ? 'Staff' : (auth()->user()->name ?? 'Staff') }}" placeholder="Staff" />
                        </div>
                    </div>

                    <section class="space-y-4">
                        <header class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Customer Account</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enter the account number used in customer records.</p>
                            </div>
                        </header>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer Name or Account Number <span class="text-sky-500">●</span></label>
                            <div class="relative">
                                <x-ui.input id="account_no" placeholder="22-000187" class="uppercase tracking-wide pr-12" />
                                <div class="absolute inset-y-0 right-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div id="accountSuggestions" class="absolute z-20 top-full left-0 right-0 mt-2 hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl overflow-hidden"></div>
                            </div>
                            <p class="text-[11px] text-gray-400">Format: 22-XXXXXX (optional trailing -X check digit)</p>
                            <p id="accountSelectionHint" class="text-[11px] text-gray-500 dark:text-gray-400 hidden"></p>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <header>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Meter Readings</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Consumption is computed from the difference.</p>
                        </header>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Previous Reading <span class="text-sky-500">●</span></label>
                                <x-ui.input id="previous_reading" type="number" min="0" step="0.01" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Current Reading <span class="text-sky-500">●</span></label>
                                <x-ui.input id="current_reading" type="number" min="0" step="0.01" />
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Base Rate (₱/m³)</label>
                                <x-ui.input id="base_rate" type="number" min="0" step="0.01" value="25" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Maintenance Charge (₱)</label>
                                <x-ui.input id="maintenance_charge" type="number" min="0" step="0.01" value="0" />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <header>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Billing Period</h3>
                        </header>
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">From</label>
                                <x-ui.input id="date_from" type="date" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">To</label>
                                <x-ui.input id="date_to" type="date" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Auto Due Date</label>
                                <div class="h-11 flex items-center px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/60" id="dueDatePreview">—</div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Disconnection Date</label>
                                <div class="h-11 flex items-center px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-rose-600 dark:text-rose-300 bg-rose-50/60 dark:bg-rose-900/40" id="disconnectDatePreview">—</div>
                            </div>
                        </div>
                    </section>

                    <div class="hidden">
                        <input type="hidden" id="consumption" />
                        <input type="hidden" id="subtotal_value" />
                        <input type="hidden" id="vat_value" />
                        <input type="hidden" id="total_value" />
                        <input type="hidden" id="due_date_value" />
                        <input type="hidden" id="disconnect_date_value" />
                    </div>

                    <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Review the summary before saving. Saved bills appear in the Billing Ledger as <span class="font-semibold text-sky-500">Pending</span>.</p>
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-slate-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-gray-900 dark:to-gray-900/80 flex flex-col h-full sticky top-20 self-start">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Live Summary</h3>
                        <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold text-sky-600 dark:bg-sky-900/40 dark:text-sky-200">Instant</span>
                    </div>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Account Preview</dt>
                            <dd id="accountPreview" class="font-medium text-gray-800 dark:text-gray-100">—</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Consumption</dt>
                            <dd id="consumptionDisplay" class="font-semibold text-slate-900 dark:text-slate-100">0.00 m³</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Subtotal</dt>
                            <dd id="subtotalDisplay" class="font-semibold text-slate-900 dark:text-slate-100">₱0.00</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">VAT (12%)</dt>
                            <dd id="vatDisplay" class="font-semibold text-slate-900 dark:text-slate-100">₱0.00</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Maintenance</dt>
                            <dd id="maintenanceDisplay" class="text-slate-900 dark:text-slate-100">₱0.00</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Due Date</dt>
                            <dd id="dueDateSummary" class="text-slate-900 dark:text-slate-100">—</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Disconnection</dt>
                            <dd id="disconnectDateSummary" class="text-rose-600 dark:text-rose-300">—</dd>
                        </div>
                    </dl>
                    <div class="mt-6 lg:mt-auto rounded-xl bg-[var(--kpi-primary,#0000ff)] text-white px-3.5 py-3 shadow-md shadow-[var(--kpi-primary,#0000ff)]/45">
                        <p class="text-[10px] uppercase tracking-[0.25em] text-white/70">Total amount due</p>
                        <div class="mt-2 rounded-xl border border-white/50 bg-white text-center px-3 py-2 shadow">
                            <p id="totalDisplay" class="text-3xl font-semibold tracking-tight text-[var(--kpi-primary,#0000ff)]">₱0.00</p>
                        </div>
                        <div class="mt-2">
                            <button id="saveBillBtn" type="button" class="w-full h-10 rounded-lg bg-[var(--kpi-dark,#0000b0)] text-white text-[13px] font-semibold tracking-[0.18em] uppercase shadow-[0_6px_12px_rgba(0,0,0,0.25)] hover:brightness-110 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-white/80">
                                Save Bill
                            </button>
                        </div>
                        <p class="mt-2 text-[10px] text-white/70">Status defaults to <span class="font-semibold text-sky-200">Pending</span> until the customer settles their balance.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- Billing overview -->
    <section class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6">
            <a href="{{ route('billing.management', array_merge($filtersQuery, ['generated' => '0'])) }}"
               class="group relative overflow-hidden rounded-3xl border {{ $generatedFilter === '0' ? 'border-blue-400 ring-2 ring-blue-200' : 'border-gray-200 dark:border-gray-800' }} bg-white dark:bg-gray-900 p-5 shadow transition hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-100/60 via-blue-50/30 to-transparent dark:from-blue-500/10 pointer-events-none"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-300 dark:bg-blue-600/20">
                        <x-heroicon-o-clock class="w-6 h-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-300">Pending Print</p>
                        <p id="pendingCount" class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['pending_generate'] ?? 0) }}</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Bills saved but not yet generated.</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('billing.management', array_merge($filtersQuery, ['generated' => '1'])) }}"
               class="group relative overflow-hidden rounded-3xl border {{ $generatedFilter === '1' ? 'border-emerald-400 ring-2 ring-emerald-200' : 'border-gray-200 dark:border-gray-800' }} bg-white dark:bg-gray-900 p-5 shadow transition hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-100/60 via-emerald-50/30 to-transparent dark:from-emerald-500/10 pointer-events-none"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 dark:bg-emerald-600/20">
                        <x-heroicon-o-printer class="w-6 h-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Printed / Locked</p>
                        <p id="generatedCount" class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['generated'] ?? 0) }}</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Bills exported or queued for distribution.</p>
                    </div>
                </div>
            </a>

            <div class="relative overflow-hidden rounded-3xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300">
                        <x-heroicon-o-bolt class="w-6 h-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-300">Collections Today</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($quick['collections_today'] ?? 0, 2) }}</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Payments captured since midnight.</p>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-3xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-600 dark:text-rose-300">Outstanding Balance</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($quick['outstanding_amount'] ?? 0, 2) }}</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Total unresolved bill amount.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Billing Ledger</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Review accounts requiring printouts and those already dispatched.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 rounded-full border border-gray-300 dark:border-gray-700 px-3 py-1 text-gray-500 dark:text-gray-300"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Pending</span>
                    <span class="inline-flex items-center gap-1 rounded-full border border-gray-300 dark:border-gray-700 px-3 py-1 text-gray-500 dark:text-gray-300"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Printed</span>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 py-4">
                <div class="flex flex-wrap items-center gap-2">
                    @php
                        $allLink = route('billing.management', $filtersQuery);
                        $pendingLink = route('billing.management', array_merge($filtersQuery, ['generated' => '0']));
                        $printedLink = route('billing.management', array_merge($filtersQuery, ['generated' => '1']));
                    @endphp
                    <a href="{{ $allLink }}" class="inline-flex items-center gap-2 h-[36px] rounded-full px-4 text-sm font-medium transition {{ $generatedFilter === '' ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> All
                    </a>
                    <a href="{{ $pendingLink }}" class="inline-flex items-center gap-2 h-[36px] rounded-full px-4 text-sm font-medium transition {{ $generatedFilter === '0' ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Pending Print
                    </a>
                    <a href="{{ $printedLink }}" class="inline-flex items-center gap-2 h-[36px] rounded-full px-4 text-sm font-medium transition {{ $generatedFilter === '1' ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Printed
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto shadow-sm ring-1 ring-gray-200/70 dark:ring-gray-800/60 rounded-2xl">
                <table class="min-w-full text-sm bg-white dark:bg-gray-900 rounded-2xl">
                    <thead class="bg-gradient-to-r from-slate-100 via-slate-50 to-white dark:from-gray-800 dark:via-gray-900 dark:to-gray-900/70 text-gray-600 dark:text-gray-300 uppercase text-[11px] tracking-[0.18em]">
                        <tr>
                            <th class="px-6 py-3 text-left align-middle">
                                <div class="flex items-center gap-2">
                                    <input id="selectAll" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="font-semibold tracking-wide">Customer</span>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left">Account No.</th>
                            <th class="px-6 py-3 text-left hidden xl:table-cell">Address</th>
                            <th class="px-6 py-3 text-left">Billing Period</th>
                            <th class="px-6 py-3 text-left">Due Date</th>
                            <th class="px-6 py-3 text-left">Bill Status</th>
                            <th class="px-6 py-3 text-left">Print Status</th>
                            <th class="px-6 py-3 text-right">Total</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-200">
                        @forelse($records as $record)
                        @php
                            $status = $record->bill_status;
                            $statusClass = match($status){
                                'Paid' => 'bg-green-100 text-green-700',
                                'Outstanding Payment' => 'bg-yellow-100 text-yellow-700',
                                'Overdue' => 'bg-orange-100 text-orange-700',
                                'Notice of Disconnection' => 'bg-red-100 text-red-700',
                                'Disconnected' => 'bg-gray-200 text-gray-800',
                                default => 'bg-gray-100 text-gray-700'
                            };
                            $daysOverdue = ($record->due_date && now()->greaterThan($record->due_date)) ? $record->due_date->diffInDays(now()) : 0;
                            $printed = (bool) $record->is_generated;
                            $collectBlockedMessage = match($status) {
                                'Disconnected' => 'Payment collection is blocked because this account is disconnected. Reconnect the service first.',
                                'Notice of Disconnection' => 'Resolve the notice of disconnection before collecting payment.',
                                'Outstanding Payment' => 'Collect this payment from the Payments module once the bill is marked Pending again.',
                                'Overdue' => 'Clear overdue status before collecting payment from this bill.',
                                default => "This bill can't be collected because the status is " . ($status ?? 'not pending') . '.',
                            };
                        @endphp
                        <tr class="transition hover:bg-blue-50/40 dark:hover:bg-gray-800" data-id="{{ $record->id }}">
                            <td class="px-6 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" class="row-check w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" value="{{ $record->id }}" {{ $printed ? 'disabled' : '' }}>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100 {{ $printed ? 'opacity-70' : '' }}">{{ $record->customer->name ?? '—' }}</p>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400">#{{ $record->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 align-middle font-mono text-xs text-gray-500 dark:text-gray-400">{{ $record->account_no }}</td>
                            <td class="px-6 py-3 align-middle text-xs text-gray-500 dark:text-gray-400 hidden xl:table-cell">
                                <div class="max-w-[18rem] truncate">{{ $record->customer->address ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-3 align-middle text-sm">{{ $record->getBillingPeriod() }}</td>
                            <td class="px-6 py-3 align-middle text-sm">{{ $record->due_date ? $record->due_date->format('Y-m-d') : '—' }}</td>
                            <td class="px-6 py-3 align-middle">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td class="px-6 py-3 align-middle print-status-cell">
                                @if($printed)
                                    <div class="print-status-badge inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-medium dark:bg-emerald-900/30 dark:text-emerald-200" data-status="printed" data-date="{{ optional($record->generated_at)->format('M d, Y') ?? '' }}">
                                        <x-heroicon-o-check-badge class="w-4 h-4" />
                                        Printed {{ optional($record->generated_at)->format('M d, Y') ?? '' }}
                                    </div>
                                @else
                                    <div class="print-status-badge inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-medium dark:bg-blue-900/40 dark:text-blue-200" data-status="pending">
                                        <x-heroicon-o-information-circle class="w-4 h-4" />
                                        Awaiting print
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-3 align-middle text-right font-semibold text-emerald-600">₱{{ number_format($record->total_amount, 2) }}</td>
                            <td class="px-6 py-3 align-middle text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button onclick="generateBill({{ $record->id }})" title="Generate & Print Bill"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-200 hover:bg-blue-600 hover:text-white transition {{ $printed ? 'opacity-50 cursor-not-allowed hover:bg-white hover:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-500' : '' }}" {{ $printed ? 'disabled' : '' }}>
                                        <x-heroicon-o-printer class="w-4 h-4" />
                                    </button>
                                    @if($status === 'Pending')
                                        <a href="{{ route('payment.index', ['account' => $record->account_no]) }}" title="Collect payment"
                                           class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-200 hover:bg-emerald-600 hover:text-white transition">
                                            <x-heroicon-o-credit-card class="w-4 h-4" />
                                        </a>
                                    @else
                                        <button type="button" disabled title="Collection disabled for this status"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-300 dark:text-gray-500 opacity-60 cursor-not-allowed">
                                            <x-heroicon-o-credit-card class="w-4 h-4" />
                                        </button>
                                    @endif
                                    @if($printed)
                                        <a href="{{ route('records.billing.print', $record->id) }}" target="_blank" title="Open printed bill"
                                           class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-200 hover:bg-emerald-600 hover:text-white transition">
                                            <x-heroicon-o-document-text class="w-4 h-4" />
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-gray-300" />
                                    <p class="font-medium">No billing records found for the current filters.</p>
                                    <p class="text-xs">Adjust the status or print filter to broaden the results.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <x-primary-button id="bulkGenerateBtn" type="button" class="inline-flex items-center gap-2 px-5 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <x-heroicon-o-printer class="w-4 h-4" />
                    Generate & Print Selected
                </x-primary-button>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Selecting rows automatically removes printed accounts to avoid duplicates.
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black/40 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 ring-1 ring-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Bill Status</h3>
        <form id="statusForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="billStatus" name="bill_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                                               focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white 
                                                               text-gray-800">
                    <option value="Outstanding Payment">Outstanding Payment</option>
                    <option value="Overdue">Overdue</option>
                    <option value="Notice of Disconnection">Notice of Disconnection</option>
                    <option value="Disconnected">Disconnected</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="billNotes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                                                      focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white 
                                                                      text-gray-800" placeholder="Optional notes..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <x-secondary-button type="button" onclick="closeStatusModal()">Cancel</x-secondary-button>
                <x-primary-button type="submit">Update Status</x-primary-button>
            </div>
        </form>
    </div>
</div>

<style>
  .locked-row { filter: blur(0.3px) grayscale(20%); opacity: 0.6; pointer-events: none; }
  /* Inherit global background from layout; keep cards white individually */
  .locked-row { filter: blur(0.3px) grayscale(20%); opacity: 0.6; pointer-events: none; }
  /* Inherit global background from layout; keep cards white individually */
</style>
<script>
const billingSearchInput = document.getElementById('billingSearchInput');
const billingSearchPanel = document.getElementById('billingSearchSuggestions');

if (billingSearchInput && billingSearchPanel) {
  let billingSuggestionController = null;
  let billingSuggestionIndex = -1;
  let billingSuggestions = [];

  function resetBillingSuggestions() {
    billingSuggestionIndex = -1;
    billingSuggestions = [];
    billingSearchPanel.classList.add('hidden');
    billingSearchPanel.innerHTML = '';
  }

  async function fetchBillingSuggestions(term) {
    if (!term || term.length < 2) {
      resetBillingSuggestions();
      return;
    }

    if (billingSuggestionController) {
      billingSuggestionController.abort();
    }

    billingSuggestionController = new AbortController();
    billingSearchPanel.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400">Searching…</div>';
    billingSearchPanel.classList.remove('hidden');

    try {
      const response = await fetch(`{{ route('customer.searchAccounts') }}?q=${encodeURIComponent(term)}`, {
        headers: { 'Accept': 'application/json' },
        signal: billingSuggestionController.signal,
      });
      if (!response.ok) throw new Error('Request failed');

      const data = await response.json();
      billingSuggestions = Array.isArray(data.suggestions) ? data.suggestions : [];

      if (!billingSuggestions.length) {
        billingSearchPanel.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400">No matches.</div>';
        return;
      }

      billingSearchPanel.innerHTML = billingSuggestions.map((item, index) => {
        const status = (item.status || '').toLowerCase();
        const pillTone = status === 'active'
          ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
          : status === 'disconnected'
            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300';

        return `
          <button type="button" data-index="${index}" class="billing-suggestion w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-800 focus:bg-blue-50 dark:focus:bg-gray-800">
            <div class="flex items-center justify-between gap-3 mb-1">
              <span class="font-mono text-xs text-gray-500 dark:text-gray-400">${item.account_no ?? ''}</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ${pillTone}">${item.status ?? ''}</span>
            </div>
            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">${item.name ?? 'Unnamed customer'}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">${item.address ?? 'No address on file'}</div>
          </button>
        `;
      }).join('');

      billingSuggestionIndex = -1;
    } catch (error) {
      if (error.name === 'AbortError') return;
      console.error(error);
      billingSearchPanel.innerHTML = '<div class="px-4 py-3 text-xs text-rose-500">Unable to load suggestions.</div>';
    }
  }

  billingSearchInput.addEventListener('input', (event) => {
    fetchBillingSuggestions(event.target.value.trim());
  });

  billingSearchInput.addEventListener('keydown', (event) => {
    if (billingSearchPanel.classList.contains('hidden')) return;
    const items = billingSearchPanel.querySelectorAll('.billing-suggestion');
    if (!items.length) return;

    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();
      if (event.key === 'ArrowDown') {
        billingSuggestionIndex = (billingSuggestionIndex + 1) % items.length;
      } else {
        billingSuggestionIndex = (billingSuggestionIndex - 1 + items.length) % items.length;
      }

      items.forEach((el, idx) => {
        el.classList.toggle('bg-blue-50', idx === billingSuggestionIndex);
        el.classList.toggle('dark:bg-gray-800', idx === billingSuggestionIndex);
      });
    } else if (event.key === 'Enter' && billingSuggestionIndex >= 0) {
      event.preventDefault();
      items[billingSuggestionIndex]?.click();
    } else if (event.key === 'Escape') {
      resetBillingSuggestions();
    }
  });

  billingSearchPanel.addEventListener('mousedown', (event) => {
    const target = event.target.closest('.billing-suggestion');
    if (!target) return;
    const index = Number(target.dataset.index);
    const data = billingSuggestions[index];
    if (!data) return;

    billingSearchInput.value = data.name || data.account_no || '';
    resetBillingSuggestions();
    billingSearchInput.form?.submit();
  });

  document.addEventListener('click', (event) => {
    if (event.target === billingSearchInput || billingSearchPanel.contains(event.target)) return;
    resetBillingSuggestions();
  });

  billingSearchInput.addEventListener('search', () => {
    resetBillingSuggestions();
    billingSearchInput.form?.submit();
  });
}

const accountInput = document.getElementById('account_no');
const accountSuggestionsPanel = document.getElementById('accountSuggestions');
const accountSelectionHint = document.getElementById('accountSelectionHint');

if (accountInput && accountSuggestionsPanel) {
  let accountController = null;
  let accountSuggestionIndex = -1;
  let accountSuggestions = [];

  const accountPreviewEl = document.getElementById('accountPreview');

  function resetAccountSuggestions() {
    accountSuggestionIndex = -1;
    accountSuggestions = [];
    accountSuggestionsPanel.classList.add('hidden');
    accountSuggestionsPanel.innerHTML = '';
  }

  function showSelectionHint(customer) {
    if (!accountSelectionHint) return;
    if (!customer) {
      accountSelectionHint.classList.add('hidden');
      accountSelectionHint.textContent = '';
      return;
    }
    accountSelectionHint.classList.remove('hidden');
    accountSelectionHint.innerHTML = `Selected: <span class="font-semibold text-gray-700 dark:text-gray-200">${customer.name ?? 'Unnamed customer'}</span> — ${customer.address ?? 'No address provided'}`;
  }

  async function fetchAccountSuggestions(term) {
    const query = term.trim();
    if (query.length < 2) {
      resetAccountSuggestions();
      return;
    }

    if (accountController) {
      accountController.abort();
    }

    accountController = new AbortController();
    accountSuggestionsPanel.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400">Searching…</div>';
    accountSuggestionsPanel.classList.remove('hidden');

    try {
      const res = await fetch(`{{ route('customer.searchAccounts') }}?q=${encodeURIComponent(query)}`, {
        headers: { 'Accept': 'application/json' },
        signal: accountController.signal,
      });
      if (!res.ok) throw new Error('Search failed');

      const data = await res.json();
      accountSuggestions = Array.isArray(data.suggestions) ? data.suggestions : [];

      if (!accountSuggestions.length) {
        accountSuggestionsPanel.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400">No matches.</div>';
        return;
      }

      accountSuggestionsPanel.innerHTML = accountSuggestions.map((item, index) => {
        const status = (item.status || '').toLowerCase();
        const pillTone = status === 'active'
          ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
          : status === 'disconnected'
            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300';

        return `
          <button type="button" data-index="${index}" class="account-suggestion w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-800 focus:bg-blue-50 dark:focus:bg-gray-800">
            <div class="flex items-center justify-between gap-3 mb-1">
              <span class="font-mono text-xs text-gray-500 dark:text-gray-400">${item.account_no ?? ''}</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ${pillTone}">${item.status ?? ''}</span>
            </div>
            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">${item.name ?? 'Unnamed customer'}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">${item.address ?? 'No address on file'}</div>
          </button>
        `;
      }).join('');

      accountSuggestionIndex = -1;
    } catch (error) {
      if (error.name === 'AbortError') return;
      console.error(error);
      accountSuggestionsPanel.innerHTML = '<div class="px-4 py-3 text-xs text-rose-500">Unable to load suggestions.</div>';
    }
  }

  accountInput.addEventListener('input', (event) => {
    const value = event.target.value;
    showSelectionHint(null);
    fetchAccountSuggestions(value);
  });

  accountInput.addEventListener('keydown', (event) => {
    if (accountSuggestionsPanel.classList.contains('hidden')) return;
    const items = accountSuggestionsPanel.querySelectorAll('.account-suggestion');
    if (!items.length) return;

    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();
      if (event.key === 'ArrowDown') {
        accountSuggestionIndex = (accountSuggestionIndex + 1) % items.length;
      } else {
        accountSuggestionIndex = (accountSuggestionIndex - 1 + items.length) % items.length;
      }
      items.forEach((el, idx) => {
        el.classList.toggle('bg-blue-50', idx === accountSuggestionIndex);
        el.classList.toggle('dark:bg-gray-800', idx === accountSuggestionIndex);
      });
    } else if (event.key === 'Enter' && accountSuggestionIndex >= 0) {
      event.preventDefault();
      items[accountSuggestionIndex]?.click();
    } else if (event.key === 'Escape') {
      resetAccountSuggestions();
    }
  });

  accountSuggestionsPanel.addEventListener('mousedown', (event) => {
    const target = event.target.closest('.account-suggestion');
    if (!target) return;
    const index = Number(target.dataset.index);
    const customer = accountSuggestions[index];
    if (!customer) return;

    accountInput.value = customer.account_no || '';
    showSelectionHint(customer);
    if (accountPreviewEl) accountPreviewEl.textContent = customer.account_no || '—';
    resetAccountSuggestions();
  });

  document.addEventListener('click', (event) => {
    if (event.target === accountInput || accountSuggestionsPanel.contains(event.target)) return;
    resetAccountSuggestions();
  });

  accountInput.addEventListener('blur', () => {
    const value = accountInput.value.trim();
    if (!value) {
      showSelectionHint(null);
      if (accountPreviewEl) accountPreviewEl.textContent = '—';
    }
  });
}

let currentBillId = null;

// Lightweight, no-API counter helpers
function getCount(elId){ const el = document.getElementById(elId); return el ? (parseInt(el.textContent.replace(/[^0-9]/g,'')||'0',10)||0) : 0; }
function setCount(elId, val){ const el = document.getElementById(elId); if (el) el.textContent = String(val); }
function bumpCounts(deltaPending, deltaGenerated){
  setCount('pendingCount', Math.max(0, getCount('pendingCount') + deltaPending));
  setCount('generatedCount', Math.max(0, getCount('generatedCount') + deltaGenerated));
}

function initBulkControls(){
  const bulkBtn = document.getElementById('bulkGenerateBtn');
  const filtersForm = document.getElementById('billingFiltersForm');
  if (!bulkBtn) return;

  const modalCloseButtons = Array.from(document.querySelectorAll('[data-modal-close]'));
  const modalTriggers = Array.from(document.querySelectorAll('[data-modal-target="bulkArchiveModal"]'));

  const selectAll = document.getElementById('selectAll');
  const rowChecks = Array.from(document.querySelectorAll('.row-check'));
  const eligible = () => rowChecks.filter(ch => !ch.disabled);
  const formatPrintedDate = () => {
    const now = new Date();
    const month = now.toLocaleString('en-US', { month: 'short' });
    const day = String(now.getDate()).padStart(2, '0');
    const year = now.getFullYear();
    return `${month} ${day}, ${year}`;
  };

  function refreshBulkState() {
    const selectable = eligible();
    const anyChecked = selectable.some(ch => ch.checked);
    bulkBtn.disabled = !anyChecked;

    if (selectAll) {
      if (selectable.length) {
        selectAll.checked = selectable.every(ch => ch.checked);
        selectAll.indeterminate = !selectAll.checked && anyChecked;
      } else {
        selectAll.checked = false;
        selectAll.indeterminate = false;
      }
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      eligible().forEach(ch => { ch.checked = selectAll.checked; });
      refreshBulkState();
    });
  }

  rowChecks.forEach(ch => ch.addEventListener('change', refreshBulkState));
  refreshBulkState();

  bulkBtn.addEventListener('click', () => {
    const ids = eligible().filter(ch => ch.checked).map(ch => ch.value);
    if (!ids.length) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('records.billing.bulk-generate') }}';
    form.target = '_blank';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrf);

    ids.forEach(id => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = id;
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    form.remove();

    ids.forEach(id => {
      const tr = document.querySelector(`tr[data-id="${id}"]`);
      if (!tr) return;
      tr.classList.add('locked-row');

      const checkbox = tr.querySelector('.row-check');
      if (checkbox) {
        checkbox.checked = false;
        checkbox.disabled = true;
      }

      const badge = tr.querySelector('.print-status-badge');
      if (badge) {
        badge.dataset.status = 'printed';
        const date = formatPrintedDate();
        badge.dataset.date = date;
        badge.classList.remove('bg-blue-50','text-blue-600','dark:bg-blue-900/40','dark:text-blue-200');
        badge.classList.add('bg-emerald-50','text-emerald-600','dark:bg-emerald-900/30','dark:text-emerald-200');
        badge.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M9 2.25A.75.75 0 0 1 9.75 3v1.5h4.5V3a.75.75 0 0 1 1.5 0v1.5H18a2.25 2.25 0 0 1 2.25 2.25v11.25A2.25 2.25 0 0 1 18 20.25H6a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 6 4.5h2.25V3A.75.75 0 0 1 9 2.25Zm-3 7.5A.75.75 0 0 0 5.25 10.5v7.5c0 .414.336.75.75.75h12a.75.75 0 0 0 .75-.75v-7.5a.75.75 0 0 0-.75-.75H6Z" clip-rule="evenodd"/><path d="M7.5 12.75a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Z"/></svg> Printed ${date}`;
      }
    });
    bumpCounts(-ids.length, +ids.length);
    refreshBulkState();
  });
}

initBulkControls();

function generateBill(id) {
    // Reuse bulk-generate flow for a single item to ensure locking
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('records.billing.bulk-generate') }}';
    form.target = '_blank';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrf);

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'ids[]';
    input.value = id;
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    form.remove();
}

function updateStatus(id, currentStatus) {
    currentBillId = id;
    document.getElementById('billStatus').value = currentStatus;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    currentBillId = null;
}

function printBill(id) {
    window.open(`/records/billing/${id}/print`, '_blank');
}

document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`/records/billing/${currentBillId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                bill_status: formData.get('bill_status'),
                notes: formData.get('notes')
            })
        });

        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error updating status: ' + result.message);
        }
    } catch (error) {
        alert('Error updating status: ' + error.message);
    }
});

 (function(){
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const $ = id => document.getElementById(id);
  const alertBox = $('alertBox');

  const fields = ['previous_reading','current_reading','base_rate','maintenance_charge','account_no','date_from','date_to','prepared_by','issued_at'];

  const state = {
    accountPreview: $('accountPreview'),
    issuedAtPreview: null,
    preparedByPreview: null,
    dueDatePreview: $('dueDatePreview'),
    disconnectDatePreview: $('disconnectDatePreview'),
    consumptionInput: $('consumption'),
    subtotalInput: $('subtotal_value'),
    totalInput: $('total_value'),
    dueDateValue: $('due_date_value'),
    disconnectDateValue: $('disconnect_date_value'),
    consumptionDisplay: $('consumptionDisplay'),
    subtotalDisplay: $('subtotalDisplay'),
    maintenanceDisplay: $('maintenanceDisplay'),
    totalDisplay: $('totalDisplay'),
    dueDateSummary: $('dueDateSummary'),
    disconnectDateSummary: $('disconnectDateSummary'),
  };

  function showAlert(message, type = 'success') {
    if (!alertBox) return;
    alertBox.classList.remove('hidden');
    alertBox.textContent = message;
    alertBox.className = '';
    alertBox.classList.add('rounded-xl','px-4','py-3','text-sm','font-medium','transition','duration-200','mb-4');
    if (type === 'error') {
      alertBox.classList.add('bg-red-50','border-red-200','text-red-700','dark:bg-red-900/30','dark:border-red-800','dark:text-red-100');
    } else {
      alertBox.classList.add('bg-emerald-50','border-emerald-200','text-emerald-700','dark:bg-emerald-900/30','dark:border-emerald-800','dark:text-emerald-100');
    }
    setTimeout(() => alertBox.classList.add('hidden'), 3500);
  }

  function formatCurrency(value) {
    return '₱' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function sanitizeNumber(value, fallback = 0) {
    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  }

  function isValidAccount(value) {
    return /^22-[0-9]{6}(-[0-9])?$/i.test((value || '').trim());
  }

  function pad(num) {
    return num.toString().padStart(2, '0');
  }

  function generateInvoiceNumber() {
    const now = new Date();
    const base = `INV-${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}`;
    const random = Math.floor(1000 + Math.random() * 9000);
    return `${base}-${random}`;
  }

  function formatInputDate(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
  }

  function resolveIssuedDate() {
    const issuedEl = $('issued_at');
    if (issuedEl && issuedEl.value) {
      const parsed = new Date(`${issuedEl.value}T00:00:00`);
      if (!Number.isNaN(parsed.getTime())) {
        return parsed;
      }
    }
    return new Date();
  }

  function syncPeriodEndWithStart(options = {}) {
    const { force = false } = options;
    const fromEl = $('date_from');
    const toEl = $('date_to');
    if (!fromEl || !toEl) return;

    const rawFrom = (fromEl.value || '').trim();
    if (!rawFrom) return;

    const start = new Date(`${rawFrom}T00:00:00`);
    if (Number.isNaN(start.getTime())) return;

    const rawTo = (toEl.value || '').trim();
    let shouldUpdate = force || !rawTo || toEl.dataset.autoFilled === 'true';

    if (!shouldUpdate && rawTo) {
      const currentTo = new Date(`${rawTo}T00:00:00`);
      if (Number.isNaN(currentTo.getTime()) || currentTo < start) {
        shouldUpdate = true;
      }
    }

    if (!shouldUpdate) return;

    const endOfMonth = new Date(start.getFullYear(), start.getMonth() + 1, 0);
    toEl.value = formatInputDate(endOfMonth);
    toEl.dataset.autoFilled = 'true';

    updateDueDate();
  }

  function applyBillingPeriodDefaults(force = false) {
    const base = resolveIssuedDate();
    const periodStart = new Date(base.getFullYear(), base.getMonth(), 1);
    const periodEnd = new Date(base.getFullYear(), base.getMonth() + 1, 0);

    const fromEl = $('date_from');
    if (fromEl && (force || !fromEl.value || fromEl.dataset.autoFilled === 'true')) {
      fromEl.value = formatInputDate(periodStart);
      fromEl.dataset.autoFilled = 'true';
    }

    const toEl = $('date_to');
    if (toEl && (force || !toEl.value || toEl.dataset.autoFilled === 'true')) {
      toEl.value = formatInputDate(periodEnd);
      toEl.dataset.autoFilled = 'true';
    }

    updateDueDate();
  }

  function formatDisplayDate(date) {
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function updateDueDate() {
    if (!state.dueDatePreview) return;
    const dateToEl = $('date_to');
    const raw = dateToEl ? (dateToEl.value || '').trim() : '';

    let due = null;
    if (raw) {
      const parsed = new Date(`${raw}T00:00:00`);
      if (!Number.isNaN(parsed.getTime())) {
        due = parsed;
      }
    }

    const dueText = due ? formatDisplayDate(due) : '—';
    state.dueDatePreview.textContent = dueText;
    if (state.dueDateSummary) state.dueDateSummary.textContent = dueText;
    if (state.dueDateValue) state.dueDateValue.value = due ? raw : '';

    let disconnect = null;
    if (due) {
      disconnect = new Date(due.getTime());
      disconnect.setDate(disconnect.getDate() + 1);
    }
    const disconnectText = disconnect ? formatDisplayDate(disconnect) : '—';
    if (state.disconnectDatePreview) state.disconnectDatePreview.textContent = disconnectText;
    if (state.disconnectDateSummary) state.disconnectDateSummary.textContent = disconnectText;
    if (state.disconnectDateValue) state.disconnectDateValue.value = disconnect ? disconnect.toISOString().slice(0, 10) : '';
  }

  function calculate() {
    const previousEl = $('previous_reading');
    const currentEl = $('current_reading');
    const baseRateEl = $('base_rate');
    const maintenanceEl = $('maintenance_charge');
    const previous = sanitizeNumber(previousEl ? previousEl.value : 0);
    const current = sanitizeNumber(currentEl ? currentEl.value : 0);
    const baseRate = sanitizeNumber(baseRateEl ? baseRateEl.value : 25, 25);
    const maintenance = sanitizeNumber(maintenanceEl ? maintenanceEl.value : 0);

    const consumption = Math.max(0, current - previous);
    const subtotal = consumption * baseRate;
    const vat = subtotal * 0.12;
    const total = subtotal + vat + maintenance;

    if (state.consumptionInput) state.consumptionInput.value = consumption.toFixed(2);
    if (state.subtotalInput) state.subtotalInput.value = subtotal.toFixed(2);
    const vatInput = $('vat_value');
    if (vatInput) vatInput.value = vat.toFixed(2);
    if (state.totalInput) state.totalInput.value = total.toFixed(2);

    if (state.consumptionDisplay) state.consumptionDisplay.textContent = `${consumption.toFixed(2)} m³`;
    if (state.subtotalDisplay) state.subtotalDisplay.textContent = formatCurrency(subtotal);
    const vatDisplay = $('vatDisplay');
    if (vatDisplay) vatDisplay.textContent = formatCurrency(vat);
    if (state.maintenanceDisplay) state.maintenanceDisplay.textContent = formatCurrency(maintenance);
    if (state.totalDisplay) state.totalDisplay.textContent = formatCurrency(total);

    const accountEl = $('account_no');
    const account = (accountEl && accountEl.value ? accountEl.value : '').trim().toUpperCase();
    if (state.accountPreview) state.accountPreview.textContent = account || '—';

    updateDueDate();
  }

  function hydrateDefaults() {
    const invoiceField = $('invoice_number');
    if (invoiceField && !invoiceField.value) {
      invoiceField.value = generateInvoiceNumber();
    }
    applyBillingPeriodDefaults();
    calculate();
  }

  const refreshBtn = $('refreshInvoice');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
      const invoiceField = $('invoice_number');
      if (!invoiceField) return;
      invoiceField.value = generateInvoiceNumber();
      showAlert('Generated a fresh invoice number.');
    });
  }

  fields.forEach(id => {
    const el = $(id);
    if (!el) return;
    el.addEventListener('input', calculate);
    el.addEventListener('change', calculate);
    if (id === 'date_from' || id === 'date_to') {
      const markManual = () => { el.dataset.autoFilled = 'false'; };
      el.addEventListener('input', markManual);
      el.addEventListener('change', markManual);
    }
  });

  const issuedField = $('issued_at');
  if (issuedField) {
    issuedField.addEventListener('change', () => {
      applyBillingPeriodDefaults();
      calculate();
    });
  }

  const dateFromField = $('date_from');
  if (dateFromField) {
    dateFromField.addEventListener('input', () => {
      dateFromField.dataset.autoFilled = 'false';
    });
    dateFromField.addEventListener('change', () => {
      dateFromField.dataset.autoFilled = 'false';
      syncPeriodEndWithStart({ force: true });
      calculate();
    });
  }

  const dateToField = $('date_to');
  if (dateToField) {
    const markManualTo = () => { dateToField.dataset.autoFilled = 'false'; };
    dateToField.addEventListener('input', markManualTo);
    dateToField.addEventListener('change', markManualTo);
  }

  const saveBtn = $('saveBillBtn');
  if (saveBtn) {
    saveBtn.addEventListener('click', async () => {
      const accountEl = $('account_no');
      const accountNo = (accountEl && accountEl.value ? accountEl.value : '').trim().toUpperCase();
      if (!isValidAccount(accountNo)) {
        return showAlert('Invalid account number. Use 22-XXXXXX with an optional -X suffix (e.g., 22-000187 or 22-000187-1).', 'error');
      }

      const previousEl = $('previous_reading');
      const currentEl = $('current_reading');
      const previous = sanitizeNumber(previousEl ? previousEl.value : 0);
      const current = sanitizeNumber(currentEl ? currentEl.value : 0);
      if (!(current > previous)) {
        return showAlert('Current reading must be higher than the previous reading.', 'error');
      }

      const invoiceField = $('invoice_number');
      const preparedEl = $('prepared_by');
      const issuedEl = $('issued_at');

      const invoiceNumber = (invoiceField && invoiceField.value ? invoiceField.value : '').trim().toUpperCase();
      const preparedBy = (preparedEl && preparedEl.value ? preparedEl.value : '').trim();
      const issuedAt = issuedEl && issuedEl.value ? new Date(issuedEl.value) : null;

      const payload = {
        invoice_number: invoiceNumber,
        prepared_by: preparedBy,
        issued_at: issuedAt ? issuedAt.toISOString() : null,
        account_no: accountNo,
        previous_reading: previous,
        current_reading: current,
        consumption_cu_m: sanitizeNumber(state.consumptionInput ? state.consumptionInput.value : 0),
        base_rate: sanitizeNumber(($('base_rate') || {}).value, 25),
        maintenance_charge: sanitizeNumber(($('maintenance_charge') || {}).value),
        vat: 0,
        total_amount: sanitizeNumber(state.totalInput ? state.totalInput.value : 0),
        date_from: ($('date_from') || {}).value || null,
        date_to: ($('date_to') || {}).value || null,
        due_date: state.dueDateValue ? state.dueDateValue.value || null : null,
        disconnection_date: state.disconnectDateValue ? state.disconnectDateValue.value || null : null,
      };

      const btn = saveBtn;
      const originalText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Saving…';

      try {
        const response = await fetch('{{ route('api.billing.store') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload),
        });

        const data = await response.json();
        if (!response.ok || !data.ok) {
          throw new Error(data.error || 'Unable to save bill');
        }

        showAlert(`Bill saved successfully. Invoice ${data.invoice_number || invoiceNumber} is now pending.`, 'success');
      } catch (error) {
        showAlert(error.message || 'Failed to save the bill.', 'error');
        btn.disabled = false;
        btn.textContent = originalText;
        return;
      }

      btn.textContent = originalText;
      btn.disabled = false;
    });
  }

  hydrateDefaults();
})();
</script>
@endsection
