@extends('layouts.app')

@section('title', 'Billing Records')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Inter]">
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">Billing Records</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Search and review generated bills</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">
        <form method="GET" class="mb-6 flex gap-3">
            <input type="text" name="q" value="{{ $q ?? '' }}"
                class="w-full md:w-1/2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm 
                       focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white dark:bg-gray-900 
                       text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500"
                placeholder="Search by account no. or name">
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
                    <tr>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Date</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Account No.</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Customer Name</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Prev</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Curr</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Consump (m³)</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Rate</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Total</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($records as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-3">{{ optional($r->created_at)->format('Y-m-d') }}</td>
                        <td class="px-6 py-3">{{ $r->account_no }}</td>
                        <td class="px-6 py-3">{{ $r->customer->name ?? '—' }}</td>
                        <td class="px-6 py-3">{{ number_format($r->previous_reading, 2) }}</td>
                        <td class="px-6 py-3">{{ number_format($r->current_reading, 2) }}</td>
                        <td class="px-6 py-3">{{ number_format($r->consumption_cu_m, 2) }}</td>
                        <td class="px-6 py-3">₱{{ number_format($r->base_rate, 2) }}</td>
                        <td class="px-6 py-3 font-semibold text-green-600 dark:text-green-400">₱{{ number_format($r->total_amount, 2) }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $r->getStatusBadgeClass() }}">
                                {{ $r->bill_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No billing records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $records->links() }}</div>
    </div>
</div>
@endsection