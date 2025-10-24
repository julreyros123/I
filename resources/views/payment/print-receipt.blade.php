<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - MAWASA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
                <h1 class="text-3xl font-bold text-gray-800">MAWASA</h1>
                <p class="text-gray-600 mt-2">Brgy. Manambulan Tugbok District, Davao City</p>
                <h2 class="text-2xl font-semibold text-gray-800 mt-4">PAYMENT RECEIPT</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Customer Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">Customer Information</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium">Account No:</span> {{ $paymentRecord->customer->account_no }}</p>
                        <p><span class="font-medium">Name:</span> {{ $paymentRecord->customer->name }}</p>
                        <p><span class="font-medium">Address:</span> {{ $paymentRecord->customer->address }}</p>
                        <p><span class="font-medium">Meter No:</span> {{ $paymentRecord->customer->meter_no }}</p>
                    </div>
                </div>

                <!-- Payment Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">Payment Information</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium">Payment Date:</span> {{ $paymentRecord->created_at->format('M d, Y h:i A') }}</p>
                        <p><span class="font-medium">Receipt No:</span> #{{ $paymentRecord->id }}</p>
                        <p><span class="font-medium">Amount Paid:</span> <span class="text-green-600 font-bold">₱{{ number_format($paymentRecord->amount_paid, 2) }}</span></p>
                        <p><span class="font-medium">Payment Method:</span> {{ ucfirst($paymentRecord->payment_method) }}</p>
                        @if($paymentRecord->reference_number)
                            <p><span class="font-medium">Reference:</span> {{ $paymentRecord->reference_number }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bill Details -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">Bill Details</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border border-gray-300">Bill #</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border border-gray-300">Billing Date</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border border-gray-300">Previous Reading</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border border-gray-300">Current Reading</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border border-gray-300">Consumption</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border border-gray-300">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 border border-gray-300">#{{ $paymentRecord->billing_record_id }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 border border-gray-300">{{ $paymentRecord->billingRecord->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 border border-gray-300">{{ $paymentRecord->billingRecord->previous_reading }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 border border-gray-300">{{ $paymentRecord->billingRecord->current_reading }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 border border-gray-300">{{ $paymentRecord->billingRecord->consumption_cu_m }} m³</td>
                                <td class="px-4 py-2 text-sm text-gray-900 border border-gray-300">₱{{ number_format($paymentRecord->billingRecord->total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="bg-gray-50 p-6 rounded-lg border-2 border-gray-300">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-800">Total Amount Paid:</span>
                    <span class="text-3xl font-bold text-green-600">₱{{ number_format($paymentRecord->amount_paid, 2) }}</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-600">
                <p>Thank you for your payment!</p>
                <p class="mt-2">This receipt serves as proof of payment.</p>
                <p class="mt-4 text-xs">Printed on: {{ now()->format('M d, Y h:i A') }}</p>
            </div>
        </div>

        <!-- Print Button (Hidden when printing) -->
        <div class="no-print mt-6 text-center">
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition">
                Print Receipt
            </button>
            <button onclick="window.close()" class="px-6 py-2 bg-gray-400 hover:bg-gray-500 text-white font-medium rounded-lg shadow-md transition ml-4">
                Close
            </button>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

