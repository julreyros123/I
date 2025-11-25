@extends('layouts.app')

@section('title', 'Billing Records')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
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
                        <th class="px-6 py-3 border-b dark:border-gray-600">Due Date</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Status</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Actions</th>
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
                        <td class="px-6 py-3">{{ $r->due_date ? $r->due_date->format('Y-m-d') : '—' }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $r->getStatusBadgeClass() }}">
                                {{ $r->bill_status }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @if($r->bill_status === 'Paid')
                                <form action="{{ route('records.billing.archive', $r->id) }}" method="POST" onsubmit="return confirm('Archive this record?');">
                                    @csrf
                                    <button type="submit" title="Archive" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        <x-heroicon-o-archive-box class="w-4 h-4" />
                                        <span class="sr-only">Archive</span>
                                    </button>
                                </form>
                            @else
                                <button type="button" title="Only paid bills can be archived" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-300 dark:text-gray-600 cursor-not-allowed">
                                    <x-heroicon-o-archive-box class="w-4 h-4" />
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No billing records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection