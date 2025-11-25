@extends('layouts.app')

@section('title', 'Bill Details - ' . $billingRecord->customer->name)

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">Bill Details</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Complete bill information for {{ $billingRecord->customer->name }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-8">
        <!-- Bill Header -->
        <div class="text-center mb-8 border-b border-gray-200 dark:border-gray-700 pb-6">
            <div class="mb-4">
                <img src="{{ asset('images/mawasa-logo.png') }}" alt="MAWASA Logo" class="h-20 w-auto mx-auto">
            </div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">MANAMBULAN WATERWORKS AND SANITATION INC.</h2>
            <p class="text-gray-600 dark:text-gray-400">Brgy. Manambulan Tugbok District, Davao City</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-4">WATER BILL</p>
        </div>

        <!-- Customer Information -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Customer Information</h3>
                <div class="space-y-2">
                    <p><span class="font-medium">Name:</span> {{ $billingRecord->customer->name }}</p>
                    <p><span class="font-medium">Account No:</span> {{ $billingRecord->account_no }}</p>
                    <p><span class="font-medium">Address:</span> {{ $billingRecord->customer->address }}</p>
                    <p><span class="font-medium">Meter No:</span> {{ $billingRecord->customer->meter_no }}</p>
                    <p><span class="font-medium">Meter Size:</span> {{ $billingRecord->customer->meter_size }}</p>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Billing Information</h3>
                <div class="space-y-2">
                    <p><span class="font-medium">Billing Period:</span> {{ $billingRecord->getBillingPeriod() }}</p>
                    <p><span class="font-medium">Bill Date:</span> {{ $billingRecord->created_at->format('M d, Y') }}</p>
                    <p><span class="font-medium">Due Date:</span> {{ $billingRecord->date_to ? $billingRecord->date_to->format('M d, Y') : 'N/A' }}</p>
                    <p><span class="font-medium">Status:</span> 
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $billingRecord->getStatusBadgeClass() }}">
                            {{ $billingRecord->bill_status }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Reading Information -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Reading Information</h3>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Previous Reading</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($billingRecord->previous_reading, 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Current Reading</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($billingRecord->current_reading, 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Consumption (m³)</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($billingRecord->consumption_cu_m, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Charges Breakdown -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Charges Breakdown</h3>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span>Water Consumption ({{ number_format($billingRecord->consumption_cu_m, 2) }} m³ × ₱{{ number_format($billingRecord->base_rate, 2) }})</span>
                        <span class="font-medium">₱{{ number_format($billingRecord->consumption_cu_m * $billingRecord->base_rate, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Maintenance Charge</span>
                        <span class="font-medium">₱{{ number_format($billingRecord->maintenance_charge, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Service Fee</span>
                        <span class="font-medium">₱{{ number_format($billingRecord->service_fee, 2) }}</span>
                    </div>
                    @if($billingRecord->advance_payment > 0)
                    <div class="flex justify-between text-green-600 dark:text-green-400">
                        <span>Advance Payment (Credit)</span>
                        <span class="font-medium">-₱{{ number_format($billingRecord->advance_payment, 2) }}</span>
                    </div>
                    @endif
                    @if($billingRecord->overdue_penalty > 0)
                    <div class="flex justify-between text-red-600 dark:text-red-400">
                        <span>Overdue Penalty</span>
                        <span class="font-medium">₱{{ number_format($billingRecord->overdue_penalty, 2) }}</span>
                    </div>
                    @endif
                    <hr class="border-gray-300 dark:border-gray-600">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total Amount</span>
                        <span class="text-green-600 dark:text-green-400">₱{{ number_format($billingRecord->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($billingRecord->notes)
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Notes</h3>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <p class="text-gray-800 dark:text-gray-200">{{ $billingRecord->notes }}</p>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex justify-center space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button onclick="printBill()" title="Generate & Print Bill"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-md bg-blue-600 hover:bg-blue-700 text-white transition">
                <x-heroicon-o-printer class="w-5 h-5" />
            </button>
            <button onclick="updateStatus()" title="Update Status"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-md bg-gray-700 hover:bg-gray-800 text-white transition">
                <x-heroicon-o-pencil-square class="w-5 h-5" />
            </button>
            <button onclick="window.close()" title="Close"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-md bg-red-600 hover:bg-red-700 text-white transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
    </div>
</div>

<script>
function printBill() {
    window.open(`/records/billing/{{ $billingRecord->id }}/print`, '_blank');
}

function updateStatus() {
    const newStatus = prompt('Update bill status:\n1. Pending\n2. Paid\n3. Notice of Disconnection\n\nEnter the new status:');
    
    if (newStatus && ['Pending', 'Paid', 'Notice of Disconnection'].includes(newStatus)) {
        fetch(`/records/billing/{{ $billingRecord->id }}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                bill_status: newStatus,
                notes: document.querySelector('textarea')?.value || ''
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Bill status updated successfully!');
                location.reload();
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error updating status: ' + error.message);
        });
    }
}
</script>
@endsection
