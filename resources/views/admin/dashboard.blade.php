@extends('layouts.app')

@section('title', 'Staff Portal')d')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Inter] space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <!-- Header + Role -->te dark:bg-gray-800 rounded-xl shadow p-5">
    <div class="flex justify-between items-center">Users</p>
        <div>p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['users'] }}</p>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">💧 Staff Water Billing Portal</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Role: <span class="font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->role ?? 'staff' }}</span></p>
        </div> class="text-xs text-gray-500">Total Customers</p>
        <div class="flex items-center gap-3">-gray-800 dark:text-gray-100">{{ $stats['customers'] }}</p>
            <button id="printBill" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-md transition">
                🖨️ Print Billark:bg-gray-800 rounded-xl shadow p-5">
            </button>"text-xs text-gray-500">Total Bills</p>
            <button id="saveBill" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-md transition">
                💾 Save Bill
            </button>g-white dark:bg-gray-800 rounded-xl shadow p-5">
        </div> class="text-xs text-gray-500">Bills Today</p>
    </div>  <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['today_billings'] }}</p>
        </div>
    <!-- Dashboard cards (summary) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Customers</p>
            <h2 id="cardCustomers" class="text-2xl font-bold mt-1">—</h2>-100">Recent Billing Activity</h2>
        </div> href="{{ route('records.billing') }}" class="text-sm text-blue-600 hover:text-blue-700">View all</a>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Unpaid Bills</p>
            <h2 id="cardUnpaid" class="text-2xl font-bold text-red-500 mt-1">—</h2>-200">
        </div>  <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Collections (Today)</p>
            <h2 id="cardCollections" class="text-2xl font-bold text-green-600 mt-1">—</h2>
        </div>          <th class="px-4 py-2">Customer</th>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Overdues</p>
            <h2 id="cardOverdues" class="text-2xl font-bold text-orange-500 mt-1">—</h2>
        </div>  </thead>
    </div>      <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recent as $r)
    <!-- Alert Messages -->
    <div id="alertBox" class="hidden p-4 rounded-lg"></div>r->created_at)->format('Y-m-d') }}</td>
                        <td class="px-4 py-2">{{ $r->account_no }}</td>
    <!-- Stepper (Reading -> Billing -> Review) -->->customer->name ?? '—' }}</td>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">->consumption_cu_m, 2) }}</td>
        <div class="flex items-center gap-4">>₱{{ number_format($r->total_amount, 2) }}</td>
            <div id="stepReading" class="p-3 rounded-full bg-indigo-600 text-white">1</div>
            <div class="flex-1 border-t border-gray-200"></div>
            <div id="stepBilling" class="p-3 rounded-full bg-gray-200 text-gray-600">2</div>-gray-400">No recent records.</td></tr>
            <div class="flex-1 border-t border-gray-200"></div>
            <div id="stepReview" class="p-3 rounded-full bg-gray-200 text-gray-600">3</div>
        </div>table>
        <div class="mt-3 text-sm text-gray-600 dark:text-gray-300">Workflow: Reading → Generate bill → Review & Save</div>
    </div>

    <!-- Customer Info & Search -->ice panels removed; handled by navbar bell and Notice page. -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Customer Information</h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">Tip: Type account no or name to search</div>
        </div>
document.addEventListener('DOMContentLoaded', function(){ /* notifications handled globally in navbar */ });
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Search (Account / Name)</label>
                <div class="relative">
                    <input id="search" type="text" autocomplete="off"                           class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white"                           placeholder="Type account number or name...">                    <div id="suggestions" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>                </div>            </div>            <div>                <label class="block text-sm text-gray-600 dark:text-gray-400">Account No.</label>                <input type="text" id="account_no" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white" placeholder="Account No">            </div>            <div>