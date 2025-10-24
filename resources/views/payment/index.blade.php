<?php
// Routes must be defined in routes/*.php. Removed inline route declarations from this view.
?>
@extends('layouts.app')

@section('title', 'Payment Portal')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-6 font-[Inter] space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Water Bill Payment Portal</h1>
        <button id="printReceipt" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-md transition">
            Print Receipt
        </button>
    </div>

    <!-- Alert Box -->
    <div id="alertBox" class="hidden p-4 rounded-lg"></div>

    <!-- Search Customer -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Search Customer</h2>
        <div class="flex space-x-4">
            <input type="text" id="searchAccount" placeholder="Enter Account No. or Name"
                class="flex-1 border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white">
            <button id="searchBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-md">
                Search
            </button>
        </div>
    </div>

    <!-- Customer Info -->
    <div id="customerInfo" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Customer Information</h2>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Account No.</label>
                <input type="text" id="account_no" value="00123"
                    class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white" readonly>
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Customer Name</label>
                <input type="text" id="customer_name" value="Juan Dela Cruz"
                    class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white" readonly>
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Address</label>
                <input type="text" id="customer_address" value="Purok 3, Manambulan"
                    class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white" readonly>
            </div>
        </div>
    </div>

    <!-- Billing details removed from this view.
         Billing management and billing UI should live under RecordController::billingManagement
         and resources/views/records/*. The payment page now focuses on tender/change and receipt printing. -->

    <!-- Cashier Payment Section -->
    <div id="paymentSection" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Cashier Payment</h2>

        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Total Amount</label>
                <input type="text" id="total" value="₱0.00"
                    class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white font-bold" readonly>
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Amount Tendered</label>
                <input type="number" id="amount_paid" placeholder="Enter amount tendered"
                    class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Change</label>
                <input type="text" id="change" value="₱0.00"
                    class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white font-semibold" readonly>
            </div>
            <div class="flex items-end">
                <button id="processPayment"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg shadow-md">
                    💵 Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const alertBox = document.getElementById('alertBox');
function showAlert(msg, type = 'success') {
    alertBox.classList.remove('hidden', 'bg-green-100', 'bg-red-100', 'text-green-700', 'text-red-700');
    alertBox.classList.add(type === 'error' ? 'bg-red-100' : 'bg-green-100');
    alertBox.classList.add(type === 'error' ? 'text-red-700' : 'text-green-700');
    alertBox.innerText = msg;
    setTimeout(() => alertBox.classList.add('hidden'), 3000);
}

// --- Customer Search Functionality ---
document.getElementById('searchBtn').addEventListener('click', async () => {
    const query = document.getElementById('searchAccount').value.trim();
    if (!query) return showAlert('Please enter an account number or name.', 'error');

    try {
        const res = await fetch(`/api/customers/search?q=${encodeURIComponent(query)}`);
        if (!res.ok) throw new Error('Customer not found');
        const data = await res.json();

        // Populate customer info fields
        // Support two possible response shapes: { account_no, name, address, total } or { customer: {...}, latest_bill: {...} }
        if (data.customer) {
            document.getElementById('account_no').value = data.customer.account_no || '';
            document.getElementById('customer_name').value = data.customer.name || '';
            document.getElementById('customer_address').value = data.customer.address || '';
        } else {
            document.getElementById('account_no').value = data.account_no || '';
            document.getElementById('customer_name').value = data.name || '';
            document.getElementById('customer_address').value = data.address || '';
        }

        // If a bill total is returned, populate the total field. Keep format with currency symbol.
        let totalValue = null;
        if (data.latest_bill && typeof data.latest_bill.total_amount !== 'undefined') {
            totalValue = data.latest_bill.formatted_total || (`₱${Number(data.latest_bill.total_amount).toFixed(2)}`);
        } else if (data.total) {
            totalValue = data.total;
        } else if (data.billing && typeof data.billing.total !== 'undefined') {
            totalValue = data.billing.total;
        }
        if (totalValue !== null) {
            document.getElementById('total').value = totalValue;
        }
        showAlert('Customer found!');
    } catch (e) {
        showAlert('Customer not found.', 'error');
    }
});

// Process Payment
document.getElementById('processPayment').addEventListener('click', () => {
    const totalRaw = document.getElementById('total').value || '0';
    const totalAmount = parseFloat(String(totalRaw).replace(/[^0-9.-]+/g, '')) || 0;
    const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
    const changeField = document.getElementById('change');

    if (!amountPaid) return showAlert('Please enter payment amount.', 'error');
    if (amountPaid < totalAmount) return showAlert('Insufficient payment.', 'error');

    const change = amountPaid - totalAmount;
    changeField.value = `₱${change.toFixed(2)}`;
    showAlert('Payment processed successfully!');
});

// Print Receipt
document.getElementById('printReceipt').addEventListener('click', () => {
    const acct = document.getElementById('account_no').value;
    const name = document.getElementById('customer_name').value;
    const addr = document.getElementById('customer_address').value;
    const total = document.getElementById('total').value;
    const paid = document.getElementById('amount_paid').value;
    const change = document.getElementById('change').value;

    const receipt = window.open('', '_blank');
    receipt.document.write(`
        <html><head><title>Payment Receipt</title>
        <style>
            body { font-family: Arial; padding: 20px; }
            h2 { text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            td, th { border: 1px solid #ccc; padding: 8px; }
            th { background: #f0f0f0; }
        </style></head>
        <body>
            <h2>WATER BILL RECEIPT</h2>
            <p><strong>Account No:</strong> ${acct}</p>
            <p><strong>Customer:</strong> ${name}</p>
            <p><strong>Address:</strong> ${addr}</p>
            <table>
                <tr><th>Total Amount</th><td>${total}</td></tr>
                <tr><th>Amount Paid</th><td>₱${paid}</td></tr>
                <tr><th>Change</th><td>${change}</td></tr>
            </table>
            <p style="margin-top:20px;">Date: ${new Date().toLocaleString()}</p>
            <p style="text-align:center; margin-top:20px;">Thank you for your payment!</p>
        </body></html>
    `);
    receipt.document.close();
    receipt.print();
});
</script>
@endsection

