@extends('layouts.app')

@section('title', 'Billing Management')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Inter]">
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">Billing Management</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Manage customer bills and track payment status</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 bg-transparent rounded-lg z-0 ring-1 ring-white/5">
                    <x-heroicon-o-document-text class="w-6 h-6 text-blue-400 dark:text-blue-400" />
                </div>
                <div class="ml-4 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Total Bills</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white leading-tight mt-1">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 bg-transparent rounded-lg z-0 ring-1 ring-white/5">
                    <x-heroicon-o-clock class="w-6 h-6 text-yellow-400 dark:text-yellow-400" />
                </div>
                <div class="ml-4 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Pending</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white leading-tight mt-1">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 bg-transparent rounded-lg z-0 ring-1 ring-white/5">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-400 dark:text-green-400" />
                </div>
                <div class="ml-4 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Paid</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white leading-tight mt-1">{{ $stats['paid'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 bg-transparent rounded-lg z-0 ring-1 ring-white/5">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-400 dark:text-red-400" />
                </div>
                <div class="ml-4 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Overdue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white leading-tight mt-1">{{ $stats['overdue'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">
        <!-- Search and Filter -->
        <form method="GET" class="mb-6 flex flex-col md:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="q" value="{{ $q ?? '' }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm 
                           focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white dark:bg-gray-900 
                           text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500"
                    placeholder="Search by account no., customer name, or address">
            </div>
            <div class="md:w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm 
                                            focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white dark:bg-gray-900 
                                            text-gray-800 dark:text-gray-200">
                    <option value="">All Status</option>
                    <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Paid" {{ $status === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Notice of Disconnection" {{ $status === 'Notice of Disconnection' ? 'selected' : '' }}>Notice of Disconnection</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                Search
            </button>
        </form>

        <!-- Bills Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
                    <tr>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Customer Name</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Account No.</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Address</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Billing Period</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Total Bill</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Status</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($records as $record)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-3 font-medium">{{ $record->customer->name ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $record->account_no }}</td>
                        <td class="px-6 py-3">{{ $record->customer->address ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $record->getBillingPeriod() }}</td>
                        <td class="px-6 py-3 font-semibold text-green-600 dark:text-green-400">
                            ₱{{ number_format($record->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $record->getStatusBadgeClass() }}">
                                {{ $record->bill_status }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center space-x-2">
                                <button onclick="generateBill({{ $record->id }})" 
                                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition">
                                    Generate Bill
                                </button>
                                <button onclick="updateStatus({{ $record->id }}, '{{ $record->bill_status }}')" 
                                        class="px-3 py-1 bg-gray-800 hover:bg-gray-900 text-white text-xs rounded-md transition border border-gray-700 shadow-sm">
                                    Update Status
                                </button>
                                <button onclick="printBill({{ $record->id }})" 
                                        class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-md transition">
                                    Print
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                            No billing records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $records->links() }}</div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Update Bill Status</h3>
        <form id="statusForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="billStatus" name="bill_status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                                               focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white dark:bg-gray-900 
                                                               text-gray-800 dark:text-gray-200">
                    <option value="Pending">Pending</option>
                    <option value="Paid">Paid</option>
                    <option value="Notice of Disconnection">Notice of Disconnection</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                <textarea id="billNotes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                                                      focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white dark:bg-gray-900 
                                                                      text-gray-800 dark:text-gray-200" placeholder="Optional notes..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeStatusModal()" 
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentBillId = null;

function generateBill(id) {
    window.open(`/records/billing/${id}/generate`, '_blank');
}

function updateStatus(id, currentStatus) {
    currentBillId = id;
    document.getElementById('billStatus').value = currentStatus;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    currentBillId = null;
}

function printBill(id) {
    window.open(`/records/billing/${id}/print`, '_blank');
}

document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`/records/billing/${currentBillId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                bill_status: formData.get('bill_status'),
                notes: formData.get('notes')
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error updating status: ' + result.message);
        }
    } catch (error) {
        alert('Error updating status: ' + error.message);
    }
});
</script>
@endsection
