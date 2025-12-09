@extends('layouts.app')

@section('title', 'Billing Records')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10 font-[Poppins] space-y-6">
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Billing ledger</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl">Monitor invoices issued by staff, track collection progress, and drill into any bill using the filters below.</p>
    </div>

    <div class="bg-white dark:bg-gray-900/70 rounded-3xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-800 overflow-hidden">
        <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-6 space-y-5">
            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span class="font-semibold text-gray-600 dark:text-gray-300">Status quick view:</span>
                @foreach(['Pending','Outstanding Payment','Overdue','Notice of Disconnection','Disconnected','Paid'] as $label)
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-900/70">{{ $label }}</span>
                @endforeach
                <span class="w-full md:w-auto md:ml-auto text-[11px] uppercase tracking-wide text-gray-400">Showing {{ $records->total() }} result{{ $records->total() === 1 ? '' : 's' }}</span>
            </div>

            <form method="GET" class="flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="w-full md:w-1/2 lg:w-5/12 flex items-stretch gap-2">
                    <div class="flex flex-1 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60">
                        <div class="flex items-center px-3 text-gray-400">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search invoice, account, or customer"
                               class="flex-1 px-3 py-2 bg-transparent text-sm text-gray-900 dark:text-gray-100 focus:outline-none" />
                        <button type="submit" class="px-4 text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white transition">Search</button>
                    </div>
                    <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <x-heroicon-o-funnel class="w-4 h-4" />
                        <span>Filters</span>
                    </button>
                </div>
                <a href="{{ route('records.billing') }}" class="inline-flex items-center gap-2 rounded-xl border border-transparent px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-sky-600 bg-gray-100 dark:bg-gray-800/60 hover:bg-gray-200 dark:hover:bg-gray-700 transition">Clear</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
                <thead class="bg-gray-50 dark:bg-gray-900/70 text-xs uppercase tracking-wide text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Invoice / Date</th>
                        <th class="px-6 py-3">Account & Customer</th>
                        <th class="px-6 py-3">Readings</th>
                        <th class="px-6 py-3">Charges</th>
                        <th class="px-6 py-3">Prepared By</th>
                        <th class="px-6 py-3">Due</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($records as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/60 transition">
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
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            No billing records found. Generate a new bill to populate this table.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-5 border-t border-gray-100 dark:border-gray-800">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection