@extends('layouts.admin')

@section('title', 'Archived Billing Records')

@section('content')
<div class="w-full mx-auto px-4 sm:px-6 py-4 sm:py-6 lg:py-8 font-[Poppins] space-y-6 lg:space-y-8">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Archived Billing Records</h1>
        <form method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search by account or name"
                   class="w-56 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">Search</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
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
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
