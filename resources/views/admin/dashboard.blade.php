@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-6 font-[Inter]">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
            <p class="text-xs text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['users'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
            <p class="text-xs text-gray-500">Total Customers</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['customers'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
            <p class="text-xs text-gray-500">Total Bills</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['billings'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
            <p class="text-xs text-gray-500">Bills Today</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['today_billings'] }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Recent Billing Activity</h2>
            <a href="{{ route('records.billing') }}" class="text-sm text-blue-600 hover:text-blue-700">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Account No.</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Consumption (m³)</th>
                        <th class="px-4 py-2">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recent as $r)
                    <tr>
                        <td class="px-4 py-2">{{ optional($r->created_at)->format('Y-m-d') }}</td>
                        <td class="px-4 py-2">{{ $r->account_no }}</td>
                        <td class="px-4 py-2">{{ $r->customer->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ number_format($r->consumption_cu_m, 2) }}</td>
                        <td class="px-4 py-2">₱{{ number_format($r->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No recent records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Notifications and Send Notice panels removed; handled by navbar bell and Notice page. -->
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){ /* notifications handled globally in navbar */ });
</script>
@endpush


