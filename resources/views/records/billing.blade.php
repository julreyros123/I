@extends('layouts.app')

@section('title', 'Billing Records')

@section('content')
<div class="w-full px-2 sm:px-4 lg:px-6 xl:px-8 2xl:px-10 py-6 font-[Poppins] space-y-3">
    <div class="space-y-0.5">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">Billing Records</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl">Monitor invoices issued by staff, track collection progress, and drill into any bill using the filters below.</p>
    </div>

    <div class="bg-white dark:bg-gray-900/70 rounded-3xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-800 overflow-hidden">
        <div class="border-b border-gray-100 dark:border-gray-800 px-4 sm:px-6 py-5 space-y-5">
            <form method="GET" id="billingFiltersForm" class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="font-semibold text-gray-600 dark:text-gray-300">Status quick view:</span>
                    @foreach(($statusOptions ?? ['Pending','Outstanding Payment','Overdue','Notice of Disconnection','Disconnected','Paid']) as $label)
                        @php $active = in_array($label, $statuses ?? []); @endphp
                        <button type="button"
                                class="js-status-chip inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-semibold transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0090ff] {{ $active ? 'bg-[#0090ff] text-white border-transparent shadow-lg dark:bg-[#0090ff] dark:text-white' : 'bg-gray-50 dark:bg-gray-900/70 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700' }}"
                                data-status="{{ $label }}"
                                data-active-classes="bg-[#0090ff] text-white border-transparent shadow-lg dark:bg-[#0090ff] dark:text-white"
                                data-inactive-classes="bg-gray-50 dark:bg-gray-900/70 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 shadow-sm"
                                aria-pressed="{{ $active ? 'true' : 'false' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                    <span class="w-full md:w-auto md:ml-auto text-[11px] uppercase tracking-wide text-gray-400">Showing {{ $records->total() }} result{{ $records->total() === 1 ? '' : 's' }}</span>
                </div>

                <div id="statusFiltersHidden" class="hidden">
                    @foreach(($statuses ?? []) as $selectedStatus)
                        <input type="hidden" name="statuses[]" value="{{ $selectedStatus }}">
                    @endforeach
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                    <div class="w-full md:w-1/2 lg:w-5/12 flex items-stretch gap-2">
                        <div class="relative flex flex-1 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 focus-within:ring-2 focus-within:ring-sky-400/60 focus-within:border-sky-300" data-autocomplete="container">
                            <div class="flex items-center px-3 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </div>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search invoice, account, or customer"
                                   class="flex-1 px-3 py-2 bg-transparent text-sm text-gray-900 dark:text-gray-100 focus:outline-none" data-autocomplete="input" autocomplete="off" />
                            <button type="submit" class="h-full px-4 text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white transition-colors rounded-none rounded-r-xl">Search</button>
                            <div class="absolute z-30 inset-x-0 top-full mt-1 hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 shadow-2xl text-sm overflow-hidden" data-autocomplete="panel">
                                <div class="py-3 text-center text-xs text-gray-400 dark:text-gray-500" data-autocomplete="empty">Start typing a customer name or account number</div>
                                <ul class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800" data-autocomplete="list"></ul>
                                <div class="hidden py-3 text-center text-xs text-gray-400 dark:text-gray-500" data-autocomplete="loading">Searching…</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <x-heroicon-o-funnel class="w-4 h-4" />
                            <span>Filters</span>
                        </button>
                        <a href="{{ route('records.billing') }}" class="inline-flex items-center gap-2 rounded-xl border border-transparent px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-sky-600 bg-gray-100 dark:bg-gray-800/60 hover:bg-gray-200 dark:hover:bg-gray-700 transition">Clear</a>
                        <button type="button" id="bulkArchiveBtn" data-modal-target="bulkArchiveModal"
                                class="hidden md:inline-flex items-center gap-1.5 px-3 py-2 text-xs rounded-xl border border-transparent bg-amber-500 hover:bg-amber-400 text-white font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed">
                            <x-heroicon-o-archive-box class="w-4 h-4" /> Archive selected
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="hidden lg:block overflow-x-auto">
            <form method="POST" action="{{ route('records.billing.bulk-generate') }}" id="bulkGenerateForm" class="space-y-3">
                @csrf
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
                    <thead class="bg-gray-50 dark:bg-gray-900/70 text-xs uppercase tracking-wide text-gray-600 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3 w-12">
                                <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    <input type="checkbox" id="bulkSelectAll" class="rounded border-gray-300 dark:border-gray-600 text-sky-600 focus:ring-sky-500">
                                    <span>Select</span>
                                </label>
                            </th>
                            <th class="px-6 py-3">Invoice / Date</th>
                            <th class="px-6 py-3">Account & Customer</th>
                            <th class="px-6 py-3">Readings</th>
                            <th class="px-6 py-3">Charges</th>
                            <th class="px-6 py-3">Prepared By</th>
                            <th class="px-6 py-3">Due</th>
                            <th class="px-6 py-3">Print Status</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($records as $r)
                        @php
                            $isPaid = $r->bill_status === 'Paid';
                            $canGenerate = !($r->is_generated ?? false) && in_array($r->bill_status, ['Outstanding Payment','Overdue','Notice of Disconnection']);
                            $canSelect = $isPaid || $canGenerate;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/60 transition">
                            <td class="px-4 py-4 align-top">
                                @if($canSelect)
                                    <input type="checkbox"
                                           name="ids[]"
                                           value="{{ $r->id }}"
                                           class="bulkSelectItem rounded border-gray-300 dark:border-gray-600 text-sky-600 focus:ring-sky-500"
                                           data-can-generate="{{ $canGenerate ? '1' : '0' }}"
                                           data-can-archive="{{ $isPaid ? '1' : '0' }}">
                                @else
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">
                                        {{ ($r->is_generated ?? false) ? 'Printed' : 'Not eligible' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-semibold text-sky-600 dark:text-sky-400">{{ $r->invoice_number ?? 'INV-' . str_pad($r->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Issued {{ optional($r->issued_at ?? $r->created_at)->format('M d, Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $r->customer->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $r->account_no }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                    <div>Prev: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($r->previous_reading, 2) }}</span></div>
                                    <div>Curr: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($r->current_reading, 2) }}</span></div>
                                    <div>Used: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($r->consumption_cu_m, 2) }} m³</span></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                    <div>Rate: <span class="font-semibold text-gray-800 dark:text-gray-200">₱{{ number_format($r->base_rate, 2) }}</span></div>
                                    <div>Maint: <span class="font-semibold text-gray-800 dark:text-gray-200">₱{{ number_format($r->maintenance_charge, 2) }}</span></div>
                                    <div>Total: <span class="text-base font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($r->total_amount, 2) }}</span></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $r->prepared_by ?? '—' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Staff</div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="text-sm text-gray-800 dark:text-gray-200">{{ $r->due_date ? $r->due_date->format('M d, Y') : '—' }}</div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                @if($r->is_generated)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                        <x-heroicon-o-check-badge class="w-4 h-4" /> Printed {{ optional($r->generated_at)->format('M d, Y') ?? '' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                                        <x-heroicon-o-information-circle class="w-4 h-4" /> Awaiting print
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $r->getStatusBadgeClass() }}">
                                    {{ $r->bill_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-top text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('records.billing.generate', $r->id) }}" title="View details" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-sky-600 hover:border-sky-300 dark:hover:text-sky-300">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('records.billing.print', $r->id) }}" title="Print" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-sky-600 hover:border-sky-300 dark:hover:text-sky-300">
                                        <x-heroicon-o-printer class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('payment.index', ['account' => $r->account_no]) }}" title="Collect payment" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-emerald-600 hover:border-emerald-300 dark:hover:text-emerald-300">
                                        <x-heroicon-o-credit-card class="w-4 h-4" />
                                    </a>
                                    @if($r->bill_status === 'Paid')
                                        <button type="button" title="Archive" data-archive-target="archiveForm-{{ $r->id }}"
                                                class="js-archive-trigger inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-sky-600 hover:border-sky-300 dark:hover:text-sky-300">
                                            <x-heroicon-o-archive-box class="w-4 h-4" />
                                        </button>
                                        @push('archive-forms')
                                            <form id="archiveForm-{{ $r->id }}" action="{{ route('records.billing.archive', $r->id) }}" method="POST" class="hidden">
                                                @csrf
                                            </form>
                                        @endpush
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No billing records found. Generate a new bill to populate this table.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 pb-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Only outstanding, not-yet-printed invoices can be generated in bulk.</p>
                    <button type="submit" id="bulkGenerateBtn" disabled
                            class="inline-flex items-center gap-2 rounded-full bg-sky-600 hover:bg-sky-500 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-white px-4 py-2 text-sm font-semibold transition">
                        <x-heroicon-o-printer class="w-4 h-4" /> Generate &amp; print selected
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:hidden px-4 pb-6 space-y-4">
            @forelse($records as $r)
                @php
                    $isPaid = $r->bill_status === 'Paid';
                    $canGenerate = !($r->is_generated ?? false) && in_array($r->bill_status, ['Outstanding Payment','Overdue','Notice of Disconnection']);
                    $canSelect = $isPaid || $canGenerate;
                @endphp
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/70 shadow-sm p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Invoice</p>
                            <p class="text-sm font-semibold text-sky-600 dark:text-sky-400">{{ $r->invoice_number ?? 'INV-' . str_pad($r->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Issued {{ optional($r->issued_at ?? $r->created_at)->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $r->getStatusBadgeClass() }}">
                                {{ $r->bill_status }}
                            </span>
                            @if($canSelect)
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-500 dark:text-gray-300">
                                    <input type="checkbox"
                                           name="ids[]"
                                           value="{{ $r->id }}"
                                           class="bulkSelectItem rounded border-gray-300 dark:border-gray-600 text-sky-600 focus:ring-sky-500"
                                           data-can-generate="{{ $canGenerate ? '1' : '0' }}"
                                           data-can-archive="{{ $isPaid ? '1' : '0' }}">
                                    Select
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 text-sm text-gray-700 dark:text-gray-200">
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-3">
                            <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Account</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $r->customer->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $r->account_no }}</p>
                        </div>
                        <div class="grid grid-cols-3 gap-3 text-xs text-gray-600 dark:text-gray-400">
                            <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-2"><span class="text-[11px] uppercase tracking-wide text-gray-400">Prev</span><p class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($r->previous_reading, 2) }}</p></div>
                            <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-2"><span class="text-[11px] uppercase tracking-wide text-gray-400">Curr</span><p class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($r->current_reading, 2) }}</p></div>
                            <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-2"><span class="text-[11px] uppercase tracking-wide text-gray-400">Used</span><p class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($r->consumption_cu_m, 2) }} m³</p></div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-3 flex items-center justify-between text-sm">
                            <div>
                                <p class="text-[11px] uppercase tracking-wide text-gray-400">Amount Due</p>
                                <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($r->total_amount, 2) }}</p>
                            </div>
                            <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                                <p>Rate: ₱{{ number_format($r->base_rate, 2) }}</p>
                                <p>Maint: ₱{{ number_format($r->maintenance_charge, 2) }}</p>
                                <p>Due: {{ $r->due_date ? $r->due_date->format('M d, Y') : '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-200">Prepared by</p>
                            <p>{{ $r->prepared_by ?? '—' }}</p>
                        </div>
                        <div>
                            @if($r->is_generated)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                    <x-heroicon-o-check-badge class="w-4 h-4" /> Printed {{ optional($r->generated_at)->format('M d, Y') ?? '' }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                                    <x-heroicon-o-information-circle class="w-4 h-4" /> Awaiting print
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <a href="{{ route('records.billing.generate', $r->id) }}" class="inline-flex items-center gap-1 rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-sky-600 hover:border-sky-300 dark:hover:text-sky-300">
                            <x-heroicon-o-eye class="w-4 h-4" /> View
                        </a>
                        <a href="{{ route('records.billing.print', $r->id) }}" class="inline-flex items-center gap-1 rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-sky-600 hover:border-sky-300 dark:hover:text-sky-300">
                            <x-heroicon-o-printer class="w-4 h-4" /> Print
                        </a>
                        <a href="{{ route('payment.index', ['account' => $r->account_no]) }}" class="inline-flex items-center gap-1 rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-emerald-600 hover:border-emerald-300 dark:hover:text-emerald-300">
                            <x-heroicon-o-credit-card class="w-4 h-4" /> Collect
                        </a>
                        @if($r->bill_status === 'Paid')
                            <button type="button" data-archive-target="archiveForm-{{ $r->id }}"
                                    class="js-archive-trigger inline-flex items-center gap-1 rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-sky-600 hover:border-sky-300 dark:hover:text-sky-300">
                                <x-heroicon-o-archive-box class="w-4 h-4" /> Archive
                            </button>
                            @push('archive-forms')
                                <form id="archiveForm-{{ $r->id }}" action="{{ route('records.billing.archive', $r->id) }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            @endpush
                        @endif
                        @if($r->bill_status === 'Paid' && $canBulk)
                            <span class="text-[11px] text-gray-400">Eligible for bulk actions on desktop</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    No billing records found. Generate a new bill to populate this list.
                </div>
            @endforelse

            <div class="pt-2">
                {{ $records->links() }}
            </div>
        </div>

        <div class="px-6 py-5 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            {{ $records->links() }}
            <div class="md:hidden">
                <button type="button" id="bulkArchiveBtnMobile" data-modal-target="bulkArchiveModal"
                        class="inline-flex items-center gap-2 rounded-xl border border-transparent px-4 py-2 text-xs font-semibold text-white bg-amber-500 hover:bg-amber-400 transition disabled:opacity-60 disabled:cursor-not-allowed">
                    <x-heroicon-o-archive-box class="w-4 h-4" /> Archive selected
                </button>
            </div>
        </div>
    </div>
</div>

@stack('archive-forms')

<div id="bulkArchiveModal" class="hidden fixed inset-0 z-40 items-center justify-center bg-black/50 p-4">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Archive selected bills</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Only paid invoices can be archived. The rest will be skipped.</p>
            </div>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">This moves the selected bills to the archive list. You can restore them later from Archived Records.</p>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-sm text-gray-700 dark:text-gray-200">
            <p><span class="font-semibold" id="bulkArchiveCount">0</span> bill(s) selected.</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bills that are not marked as <span class="font-semibold">Paid</span> will stay in the list.</p>
        </div>
        <div class="flex items-center justify-end gap-3">
            <button type="button" data-modal-close class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100">Cancel</button>
            <form method="POST" action="{{ route('records.billing.bulk-archive') }}" id="bulkArchiveForm">
                @csrf
                <div id="bulkArchiveInputs"></div>
                <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-white text-sm font-semibold rounded-xl transition">Archive now</button>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const selectAll = document.getElementById('bulkSelectAll');
    const items = Array.from(document.querySelectorAll('.bulkSelectItem'));
    const submitBtn = document.getElementById('bulkGenerateBtn');
    const filtersForm = document.getElementById('billingFiltersForm');
    const statusHiddenWrap = document.getElementById('statusFiltersHidden');
    const statusChips = Array.from(document.querySelectorAll('.js-status-chip'));
    const autoContainer = document.querySelector('[data-autocomplete="container"]');
    const autoInput = autoContainer ? autoContainer.querySelector('[data-autocomplete="input"]') : null;
    const autoPanel = autoContainer ? autoContainer.querySelector('[data-autocomplete="panel"]') : null;
    const autoList = autoPanel ? autoPanel.querySelector('[data-autocomplete="list"]') : null;
    const autoEmpty = autoPanel ? autoPanel.querySelector('[data-autocomplete="empty"]') : null;
    const autoLoading = autoPanel ? autoPanel.querySelector('[data-autocomplete="loading"]') : null;
    let autoDebounce;
    let autoFetchToken = 0;
    let autoItems = [];
    let autoButtons = [];
    let autoActiveIndex = -1;
    const archiveTriggers = Array.from(document.querySelectorAll('.js-archive-trigger'));
    const modal = document.getElementById('bulkArchiveModal');
    const modalInputsWrap = document.getElementById('bulkArchiveInputs');
    const modalCount = document.getElementById('bulkArchiveCount');
    const bulkArchiveBtn = document.getElementById('bulkArchiveBtn');
    const bulkArchiveBtnMobile = document.getElementById('bulkArchiveBtnMobile');
    const modalCloseButtons = Array.from(document.querySelectorAll('[data-modal-close]'));
    const modalTriggers = Array.from(document.querySelectorAll('[data-modal-target="bulkArchiveModal"]'));

    function syncButton(){
        const anyChecked = items.some(cb => cb.checked);
        if (submitBtn) {
            submitBtn.disabled = !anyChecked;
        }
        const paidSelected = items.some(cb => cb.checked && cb.dataset.canArchive === '1');
        const generateSelected = items.some(cb => cb.checked && cb.dataset.canGenerate === '1');
        [bulkArchiveBtn, bulkArchiveBtnMobile].forEach(btn => {
            if (!btn) return;
            btn.disabled = !paidSelected;
            if (btn.id === 'bulkArchiveBtn') {
                btn.classList.toggle('hidden', !paidSelected);
            }
        });
        if (submitBtn) {
            submitBtn.disabled = !generateSelected;
        }
    }

    function setChipState(chip, active) {
        const activeClasses = (chip.dataset.activeClasses || '').split(' ').filter(Boolean);
        const inactiveClasses = (chip.dataset.inactiveClasses || '').split(' ').filter(Boolean);
        chip.classList.remove(...(active ? inactiveClasses : activeClasses));
        chip.classList.add(...(active ? activeClasses : inactiveClasses));
        chip.setAttribute('aria-pressed', active ? 'true' : 'false');
    }

    function syncStatusInputs() {
        if (!statusHiddenWrap) return [];
        statusHiddenWrap.innerHTML = '';
        const activeStatuses = statusChips
            .filter(chip => chip.getAttribute('aria-pressed') === 'true')
            .map(chip => chip.dataset.status)
            .filter(Boolean);

        activeStatuses.forEach(status => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'statuses[]';
            input.value = status;
            statusHiddenWrap.appendChild(input);
        });

        return activeStatuses;
    }

    if (statusChips.length) {
        statusChips.forEach(chip => {
            // Ensure classes reflect current aria-pressed state on load
            setChipState(chip, chip.getAttribute('aria-pressed') === 'true');

            chip.addEventListener('click', () => {
                const isActive = chip.getAttribute('aria-pressed') === 'true';
                setChipState(chip, !isActive);
                syncStatusInputs();
                if (filtersForm) {
                    if (typeof filtersForm.requestSubmit === 'function') {
                        filtersForm.requestSubmit();
                    } else {
                        filtersForm.submit();
                    }
                }
            });
        });

        syncStatusInputs();
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            items.forEach(cb => { cb.checked = selectAll.checked; });
            syncButton();
        });
    }

    items.forEach(cb => cb.addEventListener('change', () => {
        if (!cb.checked && selectAll) {
            selectAll.checked = false;
        } else if (selectAll) {
            const allChecked = items.length > 0 && items.every(item => item.checked);
            selectAll.checked = allChecked;
        }
        syncButton();
    }));

    syncButton();

    function hideAutocompletePanel(resetEmpty = true) {
        if (!autoPanel) return;
        autoPanel.classList.add('hidden');
        autoList && (autoList.innerHTML = '');
        autoButtons = [];
        autoItems = [];
        autoActiveIndex = -1;
        if (autoLoading) autoLoading.classList.add('hidden');
        if (autoEmpty && resetEmpty) {
            autoEmpty.textContent = 'Start typing a customer name or account number';
            autoEmpty.classList.remove('hidden');
        }
    }

    function showAutocompletePanel() {
        if (!autoPanel) return;
        autoPanel.classList.remove('hidden');
    }

    function setActiveAutocomplete(index) {
        autoActiveIndex = index;
        autoButtons.forEach((btn, idx) => {
            if (idx === index) {
                btn.classList.add('bg-sky-100', 'dark:bg-sky-900/40', 'text-sky-900', 'dark:text-sky-100');
            } else {
                btn.classList.remove('bg-sky-100', 'dark:bg-sky-900/40', 'text-sky-900', 'dark:text-sky-100');
            }
        });
    }

    function chooseAutocomplete(index) {
        const item = autoItems[index];
        if (!item || !autoInput) return;
        autoInput.value = item.account_no || item.name || '';
        hideAutocompletePanel();
        if (filtersForm) {
            if (typeof filtersForm.requestSubmit === 'function') {
                filtersForm.requestSubmit();
            } else {
                filtersForm.submit();
            }
        }
    }

    function renderAutocomplete(items, term) {
        if (!autoList || !autoEmpty) return;
        autoList.innerHTML = '';
        autoItems = items;
        autoButtons = [];
        autoActiveIndex = -1;

        if (!items.length) {
            autoEmpty.textContent = `No customers match “${term}”.`;
            autoEmpty.classList.remove('hidden');
            showAutocompletePanel();
            return;
        }

        autoEmpty.classList.add('hidden');
        items.forEach((item, index) => {
            const li = document.createElement('li');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.index = String(index);
            btn.className = 'w-full text-left px-4 py-3 flex flex-col gap-2 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition focus:outline-none';
            const statusLabel = (item.status || '').toLowerCase();
            let statusClasses = 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300';
            if (statusLabel === 'active') {
                statusClasses = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200';
            } else if (statusLabel === 'inactive' || statusLabel === 'disconnected') {
                statusClasses = 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200';
            }
            btn.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">${item.account_no || '—'}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">${item.name || 'No name on file'}</p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold ${statusClasses}">
                        ${item.status || '—'}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">${item.address || 'No address on file'}</p>
            `;
            btn.addEventListener('click', () => chooseAutocomplete(index));
            li.appendChild(btn);
            autoList.appendChild(li);
            autoButtons.push(btn);
        });

        showAutocompletePanel();
    }

    async function fetchAutocomplete(term) {
        if (!autoPanel || !autoInput) return;
        const token = ++autoFetchToken;
        if (autoLoading) autoLoading.classList.remove('hidden');
        if (autoEmpty) autoEmpty.classList.add('hidden');
        showAutocompletePanel();

        try {
            const url = new URL("{{ route('customer.searchAccounts') }}", window.location.origin);
            url.searchParams.set('q', term);
            url.searchParams.set('include_all', '1');
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) throw new Error('Request failed');
            const payload = await response.json();
            if (token !== autoFetchToken) return;
            const suggestions = Array.isArray(payload.suggestions) ? payload.suggestions : [];
            renderAutocomplete(suggestions, term);
        } catch (error) {
            if (token !== autoFetchToken) return;
            if (autoEmpty) {
                autoEmpty.textContent = 'Unable to load suggestions right now.';
                autoEmpty.classList.remove('hidden');
            }
        } finally {
            if (token === autoFetchToken && autoLoading) {
                autoLoading.classList.add('hidden');
            }
        }
    }

    if (autoInput) {
        autoInput.addEventListener('input', () => {
            clearTimeout(autoDebounce);
            const term = autoInput.value.trim();
            if (term.length < 2) {
                hideAutocompletePanel(false);
                return;
            }
            autoDebounce = setTimeout(() => fetchAutocomplete(term), 250);
        });

        autoInput.addEventListener('focus', () => {
            const term = autoInput.value.trim();
            if (term.length >= 2) {
                fetchAutocomplete(term);
            } else {
                hideAutocompletePanel();
            }
        });

        autoInput.addEventListener('keydown', (event) => {
            if (!autoPanel || autoPanel.classList.contains('hidden')) return;
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (!autoButtons.length) return;
                const nextIndex = (autoActiveIndex + 1) % autoButtons.length;
                setActiveAutocomplete(nextIndex);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (!autoButtons.length) return;
                const nextIndex = autoActiveIndex <= 0 ? autoButtons.length - 1 : autoActiveIndex - 1;
                setActiveAutocomplete(nextIndex);
            } else if (event.key === 'Enter') {
                if (autoActiveIndex >= 0) {
                    event.preventDefault();
                    chooseAutocomplete(autoActiveIndex);
                }
            } else if (event.key === 'Escape') {
                hideAutocompletePanel(false);
            }
        });

        document.addEventListener('click', (event) => {
            if (!autoContainer) return;
            if (!autoContainer.contains(event.target)) {
                hideAutocompletePanel(false);
            }
        });

        autoPanel?.addEventListener('mouseenter', () => setActiveAutocomplete(-1));
    }

    if (archiveTriggers.length) {
        archiveTriggers.forEach(trigger => {
            trigger.addEventListener('click', (event) => {
                const targetId = trigger.getAttribute('data-archive-target');
                if (!targetId) return;
                if (!confirm('Archive this record?')) return;
                const form = document.getElementById(targetId);
                if (form) {
                    form.submit();
                }
            });
        });
    }

    function openModal() {
        if (!modal) return;
        const paidIds = items.filter(cb => cb.checked && cb.dataset.canArchive === '1').map(cb => cb.value);
        if (!paidIds.length) {
            alert('Select at least one paid bill to archive.');
            return;
        }
        if (modalInputsWrap) {
            modalInputsWrap.innerHTML = '';
            paidIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'archive_ids[]';
                input.value = id;
                modalInputsWrap.appendChild(input);
            });
        }
        if (modalCount) modalCount.textContent = String(paidIds.length);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    modalTriggers.forEach(btn => btn.addEventListener('click', openModal));
    modalCloseButtons.forEach(btn => btn.addEventListener('click', closeModal));
    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    }
});
</script>
@endpush
@endsection