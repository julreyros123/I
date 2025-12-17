@extends('layouts.app')

@section('title', 'Billing Records')

@section('content')
<div class="max-w-7xl xl:max-w-full xl:px-12 mx-auto px-4 sm:px-6 py-6 font-[Poppins] space-y-3">
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
                        <div class="flex flex-1 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60">
                            <div class="flex items-center px-3 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </div>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search invoice, account, or customer"
                                   class="flex-1 px-3 py-2 bg-transparent text-sm text-gray-900 dark:text-gray-100 focus:outline-none" />
                            <button type="submit" class="px-4 text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white transition">Search</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <x-heroicon-o-funnel class="w-4 h-4" />
                            <span>Filters</span>
                        </button>
                        <a href="{{ route('records.billing') }}" class="inline-flex items-center gap-2 rounded-xl border border-transparent px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-sky-600 bg-gray-100 dark:bg-gray-800/60 hover:bg-gray-200 dark:hover:bg-gray-700 transition">Clear</a>
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
                            $canBulk = !($r->is_generated ?? false) && in_array($r->bill_status, ['Outstanding Payment','Overdue','Notice of Disconnection']);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/60 transition">
                            <td class="px-4 py-4 align-top">
                                @if($canBulk)
                                    <input type="checkbox" name="ids[]" value="{{ $r->id }}" class="bulkSelectItem rounded border-gray-300 dark:border-gray-600 text-sky-600 focus:ring-sky-500">
                                @else
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ ($r->is_generated ?? false) ? 'Printed' : 'Not eligible' }}</span>
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
                                        <form action="{{ route('records.billing.archive', $r->id) }}" method="POST" onsubmit="return confirm('Archive this record?');" class="inline">
                                            @csrf
                                            <button type="submit" title="Archive" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-sky-600 hover:border-sky-300 dark:hover:text-sky-300">
                                                <x-heroicon-o-archive-box class="w-4 h-4" />
                                            </button>
                                        </form>
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
                    $canBulk = !($r->is_generated ?? false) && in_array($r->bill_status, ['Outstanding Payment','Overdue','Notice of Disconnection']);
                @endphp
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/70 shadow-sm p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Invoice</p>
                            <p class="text-sm font-semibold text-sky-600 dark:text-sky-400">{{ $r->invoice_number ?? 'INV-' . str_pad($r->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Issued {{ optional($r->issued_at ?? $r->created_at)->format('M d, Y') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $r->getStatusBadgeClass() }}">
                            {{ $r->bill_status }}
                        </span>
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

        <div class="px-6 py-5 border-t border-gray-100 dark:border-gray-800">
            {{ $records->links() }}
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

    function syncButton(){
        const anyChecked = items.some(cb => cb.checked);
        if (submitBtn) {
            submitBtn.disabled = !anyChecked;
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
});
</script>
@endpush
@endsection