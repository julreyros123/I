@extends('layouts.app')

@section('title', 'Archived Billing Records')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-6 font-[Poppins] space-y-5">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 sm:p-6 lg:p-8 space-y-4">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">Archived Billing Records</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">View and restore archived billing records.</p>
                </div>
                @isset($archivedCount)
                    <p class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-500 mt-1">Total archived: <span class="font-semibold">{{ number_format($archivedCount) }}</span></p>
                @endisset
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('records.billing.export-archived', array_filter(['q' => $q ?? null])) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs sm:text-sm rounded-lg border border-amber-500 text-amber-600 dark:text-amber-400 hover:bg-amber-500 hover:text-white dark:hover:text-white font-semibold transition">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" /> Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto rounded-3xl border border-gray-200 dark:border-gray-700">
            <table class="w-full min-w-[820px] text-sm text-left text-gray-700 dark:text-gray-200">
                <thead class="bg-gradient-to-r from-sky-600 to-blue-500 text-white font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase">Deleted At</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase">Account No.</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase">Customer Name</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase text-right">Total</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase">Status</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($records as $r)
                    <tr class="bg-white even:bg-slate-50 dark:bg-gray-900 dark:even:bg-gray-900/60 hover:bg-blue-50/70 dark:hover:bg-gray-800 transition">
                        <td class="px-6 py-4 align-top text-xs text-gray-500 dark:text-gray-400">{{ optional($r->deleted_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 align-top text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $r->account_no }}</td>
                        <td class="px-6 py-4 align-top">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $r->customer->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[220px]">{{ $r->customer->address ?? 'Address unavailable' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top text-right text-sm font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($r->total_amount, 2) }}</td>
                        <td class="px-6 py-4 align-top">
                            @php
                                $statusClass = match($r->bill_status) {
                                    'Paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
                                    'Outstanding Payment' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-200',
                                    'Overdue' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-200',
                                    'Notice of Disconnection' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
                                    'Disconnected' => 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
                                    default => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $r->bill_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top text-right space-x-2">
                            <form action="{{ route('records.billing.restore', $r->id) }}" method="POST" class="inline">
                                @csrf
                                <button class="px-3 py-1 rounded-full text-xs bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm" onclick="return confirm('Restore this record?');">Restore</button>
                            </form>
                            <form action="{{ route('records.billing.force', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete this record?');">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 rounded-full text-xs bg-red-500 hover:bg-red-600 text-white shadow-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No archived records.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">Showing {{ $records->firstItem() ?? 0 }}-{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records</p>
            <div class="self-start sm:self-auto">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
