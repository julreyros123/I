@extends('layouts.app')

@section('title', 'Payment Records')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Poppins]">
    <div class="mb-3">
        <p class="text-gray-600 dark:text-gray-400 text-xs">Guide: Search by account no. or name to find a receipt. Use "View History" to review billed months and amounts.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6 space-y-4">
        <!-- Search + filter bar -->
        <form method="GET" class="flex flex-col lg:flex-row lg:items-center gap-2 mb-1">
            <div class="w-full md:w-2/3 lg:w-5/12 flex items-stretch gap-2">
                <div class="flex flex-1 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/60">
                    <div class="flex items-center px-3 text-gray-400">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input 
                        type="text" 
                        name="q"
                        value="{{ $q ?? '' }}"
                        class="flex-1 px-3 py-2 bg-transparent text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none"
                        placeholder="Search by account no. or name">
                    <button type="submit" class="px-4 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                        Search
                    </button>
                </div>
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <x-heroicon-o-funnel class="w-4 h-4" />
                    <span>Filter</span>
                </button>
            </div>
        </form>

        <!-- Show only pills (UI only for now) -->
        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600 dark:text-gray-300">
            <span class="font-medium">Show only:</span>
            <label class="inline-flex items-center gap-1 cursor-pointer">
                <input type="radio" name="paymentFilter" value="all" class="w-3 h-3 text-blue-600 border-gray-300 focus:ring-blue-500" checked>
                <span>All</span>
            </label>
            <label class="inline-flex items-center gap-1 cursor-pointer">
                <input type="radio" name="paymentFilter" value="recent" class="w-3 h-3 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span>Last 30 days</span>
            </label>
            <label class="inline-flex items-center gap-1 cursor-pointer">
                <input type="radio" name="paymentFilter" value="high" class="w-3 h-3 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span>High amounts</span>
            </label>
        </div>

        <div class="overflow-x-auto table-responsive-wrapper">
            <table class="w-full min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
                    <tr>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Date</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Account No.</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Customer Name</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Total Payment</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600 text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="paymentTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse(($payments ?? []) as $p)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-3">{{ optional($p->created_at)->format('Y-m-d') }}</td>
                        <td class="px-6 py-3">{{ $p->account_no }}</td>
                        <td class="px-6 py-3">{{ $p->customer->name ?? '—' }}</td>
                        <td class="px-6 py-3 font-semibold text-green-600 dark:text-green-400">₱{{ number_format($p->total_amount, 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            <button 
                                data-account="{{ $p->account_no }}"
                                data-name="{{ $p->customer->name ?? '' }}"
                                data-address="{{ $p->customer->address ?? '' }}"
                                class="viewHistoryBtn px-3 py-1 text-xs rounded-md bg-blue-600 hover:bg-blue-700 
                                       text-white font-medium transition">
                                View History
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No payment records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<!-- Payment History Modal -->
<div id="historyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div id="printSection" class="bg-white dark:bg-gray-800 w-full max-w-3xl rounded-xl shadow-lg p-6 relative">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Payment History</h3>

        <!-- Account Info -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4 text-sm">
            <p><span class="font-semibold">Account No.:</span> <span id="accNoInfo">—</span></p>
            <p><span class="font-semibold">Name:</span> <span id="nameInfo">—</span></p>
            <p><span class="font-semibold">Address:</span> <span id="addressInfo">—</span></p>
        </div>

        <!-- History Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Previous</th>
                        <th class="px-4 py-2 text-left">Current</th>
                        <th class="px-4 py-2 text-left">Maintenance</th>
                        <th class="px-4 py-2 text-left">Service Fee</th>
                        <th class="px-4 py-2 text-left">Amount Paid</th>
                        <th class="px-4 py-2 text-left">Consumption (m³)</th>
                    </tr>
                </thead>
                <tbody id="historyRows" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
            </table>
        </div>

        <div class="text-right mt-6">
            <x-secondary-button type="button" id="closeHistory">Close</x-secondary-button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('historyModal');
    const rows = document.getElementById('historyRows');
    const accNoInfo = document.getElementById('accNoInfo');
    const nameInfo = document.getElementById('nameInfo');
    const addressInfo = document.getElementById('addressInfo');
    document.querySelectorAll('.viewHistoryBtn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const account = btn.getAttribute('data-account');
            const name = btn.getAttribute('data-name') || '—';
            const address = btn.getAttribute('data-address') || '—';
            accNoInfo.textContent = account;
            nameInfo.textContent = name;
            addressInfo.textContent = address;
            rows.innerHTML = '<tr><td class="px-4 py-3" colspan="7">Loading...</td></tr>';
            const res = await fetch(`{{ route('api.records.history') }}?account_no=${encodeURIComponent(account)}`);
            if (!res.ok) { rows.innerHTML = '<tr><td class="px-4 py-3" colspan="7">Failed to load history.</td></tr>'; modal.classList.remove('hidden'); return; }
            const data = await res.json();
            rows.innerHTML = (data.history || []).map(h => `
                <tr>
                    <td class="px-4 py-2">${h.date}</td>
                    <td class="px-4 py-2">${h.previous.toFixed(2)}</td>
                    <td class="px-4 py-2">${h.current.toFixed(2)}</td>
                    <td class="px-4 py-2">₱${h.maintenance.toFixed(2)}</td>
                    <td class="px-4 py-2">₱${h.service_fee.toFixed(2)}</td>
                    <td class="px-4 py-2">₱${h.amount_paid.toFixed(2)}</td>
                    <td class="px-4 py-2">${h.consumption.toFixed(2)}</td>
                </tr>
            `).join('');
            modal.classList.remove('hidden');
        });
    });
    document.getElementById('closeHistory').addEventListener('click', () => modal.classList.add('hidden'));
});
</script>
@endsection
