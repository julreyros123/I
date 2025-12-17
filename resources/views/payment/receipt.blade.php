@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Payment Receipt</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">MAWASA - Brgy. Manambulan Tugbok District, Davao City</p>
        </div>

        @php
            $customer = $paymentRecord->customer;
            $billingRecord = $paymentRecord->billingRecord;
            $billingDate = $billingRecord?->issued_at ?? $billingRecord?->created_at;
            $billingDateFormatted = $billingDate ? $billingDate->format('M d, Y') : '—';
            $paymentMethod = $paymentRecord->payment_method ? ucfirst($paymentRecord->payment_method) : '—';
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Customer Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Customer Information</h3>
                <div class="space-y-2">
                    <p><span class="font-medium">Account No:</span> {{ $customer->account_no ?? '—' }}</p>
                    <p><span class="font-medium">Name:</span> {{ $customer->name ?? '—' }}</p>
                    <p><span class="font-medium">Address:</span> {{ $customer->address ?? '—' }}</p>
                    <p><span class="font-medium">Meter No:</span> {{ $customer->meter_no ?? '—' }}</p>
                </div>
            </div>

            <!-- Payment Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Payment Information</h3>
                <div class="space-y-2">
                    <p><span class="font-medium">Payment Date:</span> {{ $paymentRecord->created_at->format('M d, Y h:i A') }}</p>
                    <p><span class="font-medium">Payment ID:</span> #{{ $paymentRecord->id }}</p>
                    <p><span class="font-medium">Amount Paid:</span> <span class="text-green-600 font-bold">₱{{ number_format($paymentRecord->amount_paid, 2) }}</span></p>
                    <p><span class="font-medium">Payment Method:</span> {{ $paymentMethod }}</p>
                    @if($paymentRecord->reference_number)
                        <p><span class="font-medium">Reference:</span> {{ $paymentRecord->reference_number }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bill Details -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Bill Details</h3>
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Bill #</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Billing Date</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Consumption</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @if($billingRecord)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $billingRecord->invoice_number ? '#' . $billingRecord->invoice_number : '#'. $paymentRecord->billing_record_id }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $billingDateFormatted }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ number_format($billingRecord->consumption_cu_m ?? 0, 2) }} m³</td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">₱{{ number_format($billingRecord->total_amount ?? 0, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    Billing record details are unavailable (this invoice may have been permanently archived).
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="mt-8 bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-800 dark:text-gray-200">Total Amount Paid:</span>
                <span class="text-2xl font-bold text-green-600 dark:text-green-400">₱{{ number_format($paymentRecord->amount_paid, 2) }}</span>
            </div>
        </div>

        @if($billingRecord?->trashed())
            <div class="mt-6 p-4 border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    Note: This billing record has been archived for safekeeping.
                </p>
            </div>
        @endif

        <!-- Policy Notice -->
        <div class="mt-6 p-4 border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                Settle your outstanding balance to avoid disconnection.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-center space-x-4">
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition">
                Print Receipt
            </button>
            <a href="{{ route('payment.index') }}" class="px-6 py-2 bg-gray-400 hover:bg-gray-500 text-white font-medium rounded-lg shadow-md transition">
                Back to Payment
            </a>
        </div>
    </div>
</div>
@endsection

