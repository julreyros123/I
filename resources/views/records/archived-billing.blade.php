@extends('layouts.app')

@section('title', 'Archived Billing Records')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Poppins]">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 space-y-3">
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Archived Billing Records</h2>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Search deleted bills and optionally restore or purge them.</p>
            </div>

            <!-- Search + filter bar -->
            <form method="GET" class="flex flex-col lg:flex-row lg:items-center gap-2">
                <div class="w-full md:w-2/3 lg:w-5/12 flex items-stretch gap-2">
                    <div class="flex flex-1 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/60">
                        <div class="flex items-center px-3 text-gray-400">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input type="text" name="q" value="{{ $q ?? '' }}"
                               class="flex-1 px-3 py-2 bg-transparent text-sm text-gray-900 dark:text-gray-100 focus:outline-none"
                               placeholder="Search by account no. or customer name">
                        <button type="submit" class="px-4 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white">Search</button>
                    </div>
                    <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <x-heroicon-o-funnel class="w-4 h-4" />
                        <span>Filter</span>
                    </button>
                </div>
            </form>

            <!-- Show only pills -->
            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600 dark:text-gray-300">
                <span class="font-medium">Show only:</span>
                <label class="inline-flex items-center gap-1 cursor-pointer">
                    <input type="radio" name="archivedFilter" value="all" class="w-3 h-3 text-blue-600 border-gray-300 focus:ring-blue-500" checked>
                    <span>All</span>
                </label>
                <label class="inline-flex items-center gap-1 cursor-pointer">
                    <input type="radio" name="archivedFilter" value="paid" class="w-3 h-3 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <span>Paid bills</span>
                </label>
                <label class="inline-flex items-center gap-1 cursor-pointer">
                    <input type="radio" name="archivedFilter" value="overdue" class="w-3 h-3 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <span>Overdue / disconnected</span>
                </label>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
                    <tr>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Deleted At</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Account No.</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Customer Name</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Total</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Status</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($records as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-3">{{ optional($r->deleted_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-3">{{ $r->account_no }}</td>
                        <td class="px-6 py-3">{{ $r->customer->name ?? '—' }}</td>
                        <td class="px-6 py-3 font-semibold text-green-600 dark:text-green-400">₱{{ number_format($r->total_amount, 2) }}</td>
                        <td class="px-6 py-3">{{ $r->bill_status }}</td>
                        <td class="px-6 py-3 space-x-2">
                            <form action="{{ route('records.billing.restore', $r->id) }}" method="POST" class="inline">
                                @csrf
                                <button class="px-3 py-1 rounded-md text-xs bg-emerald-600 hover:bg-emerald-700 text-white" onclick="return confirm('Restore this record?');">Restore</button>
                            </form>
                            <form action="{{ route('records.billing.force', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete this record?');">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 rounded-md text-xs bg-red-600 hover:bg-red-700 text-white">Delete</button>
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
        <div class="mt-4">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
