@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Payment Receipt</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">MAWASA - Brgy. Manambulan Tugbok District, Davao City</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Customer Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Customer Information</h3>
                <div class="space-y-2">
                    <p><span class="font-medium">Account No:</span> {{ $paymentRecord->customer->account_no }}</p>
                    <p><span class="font-medium">Name:</span> {{ $paymentRecord->customer->name }}</p>
                    <p><span class="font-medium">Address:</span> {{ $paymentRecord->customer->address }}</p>
                    <p><span class="font-medium">Meter No:</span> {{ $paymentRecord->customer->meter_no }}</p>
                </div>
            </div>

            <!-- Payment Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Payment Information</h3>
                <div class="space-y-2">
                    <p><span class="font-medium">Payment Date:</span> {{ $paymentRecord->created_at->format('M d, Y h:i A') }}</p>
                    <p><span class="font-medium">Payment ID:</span> #{{ $paymentRecord->id }}</p>
                    <p><span class="font-medium">Amount Paid:</span> <span class="text-green-600 font-bold">₱{{ number_format($paymentRecord->amount_paid, 2) }}</span></p>
                    <p><span class="font-medium">Payment Method:</span> {{ ucfirst($paymentRecord->payment_method) }}</p>
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
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">#{{ $paymentRecord->billing_record_id }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $paymentRecord->billingRecord->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $paymentRecord->billingRecord->consumption_cu_m }} m³</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">₱{{ number_format($paymentRecord->billingRecord->total_amount, 2) }}</td>
                        </tr>
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

