@extends('layouts.app')

@section('title', 'Payment Records')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Inter]">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">MAWASA WATER AND SANITATION</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Payment records and billing summary</p>
    </div>

    <!-- Payment Records Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Payment Records</h2>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <input 
                type="text" 
                id="searchPayment"
                class="w-full md:w-1/2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm 
                       focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white dark:bg-gray-900 
                       text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500"
                placeholder="Search by account no. or name">
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
                    <tr>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Account No.</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Customer Name</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Amount Due</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Maintenance Fee</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Service Fee</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">VAT (12%)</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Total Payment</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Status</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600">Payment Date</th>
                        <th class="px-6 py-3 border-b dark:border-gray-600 text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="paymentTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-3">000123</td>
                        <td class="px-6 py-3">John Doe</td>
                        <td class="px-6 py-3">₱375.00</td>
                        <td class="px-6 py-3">₱25.00</td>
                        <td class="px-6 py-3">₱15.00</td>
                        <td class="px-6 py-3">₱49.80</td>
                        <td class="px-6 py-3 font-semibold text-green-600 dark:text-green-400">₱464.80</td>
                        <td class="px-6 py-3 font-medium text-green-600 dark:text-green-400">Paid</td>
                        <td class="px-6 py-3">2025-09-30</td>
                        <td class="px-6 py-3 text-center">
                            <button 
                                class="viewHistoryBtn px-3 py-1 text-xs rounded-md bg-blue-600 hover:bg-blue-700 
                                       text-white font-medium transition">
                                View History
                            </button>
                        </td>
                    </tr>
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
            <p><span class="font-semibold">Account No.:</span> <span id="accNoInfo">000123</span></p>
            <p><span class="font-semibold">Name:</span> <span id="nameInfo">John Doe</span></p>
            <p><span class="font-semibold">Address:</span> <span id="addressInfo">Brgy. Manambulan, Davao City</span></p>
        </div>

        <!-- Search & Buttons -->
        <div class="flex justify-between items-center mb-3 print:hidden">
            <input type="text" 
                   id="searchDate" 
                   placeholder="Search Date..." 
                   class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm 
                          focus:ring-2 focus:ring-blue-500 focus:outline-none 
                          bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 placeholder-gray-400">
            
            <button id="printHistory"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg font-medium transition">
                Print History
            </button>
        </div>

        <!-- History Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Previous Billing</th>
                        <th class="px-4 py-2 text-left">Current Billing</th>
                        <th class="px-4 py-2 text-left">Maintenance</th>
                        <th class="px-4 py-2 text-left">Service Fee</th>
                        <th class="px-4 py-2 text-left">Amount Paid</th>
                        <th class="px-4 py-2 text-left">Advance Payment</th>
                        <th class="px-4 py-2 text-left">Consumption cu. m.</th>
                    </tr>
                </thead>
                <tbody id="historyRows" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
            </table>
        </div>

        <!-- Buttons -->
        <div class="text-right mt-6 print:hidden">
            <button id="closeHistory"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 
                       text-gray-700 dark:text-gray-200 rounded-lg text-sm">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    const historyModal = document.getElementById('historyModal');
    const closeHistory = document.getElementById('closeHistory');
    const historyRows = document.getElementById('historyRows');
    const printBtn = document.getElementById('printHistory');

    document.querySelectorAll('.viewHistoryBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            const accNo = row.children[0].innerText;
            const name = row.children[1].innerText;

            // Example data
            const paymentHistory = [
                { date: 'May 12, 2025', prev: '350.45', curr: '403.56', maint: '0.00', service: '50', paid: '405', advance: '1.44', consumption: '19' },
                { date: 'Apr 12, 2025', prev: '340.00', curr: '350.45', maint: '0.00', service: '50', paid: '390', advance: '0.00', consumption: '18' }
            ];

            document.getElementById('accNoInfo').innerText = accNo;
            document.getElementById('nameInfo').innerText = name;
            document.getElementById('addressInfo').innerText = 'Brgy. Manambulan Tugbok District, Davao City';

            historyRows.innerHTML = paymentHistory.map(h => `
                <tr>
                    <td class="px-4 py-2">${h.date}</td>
                    <td class="px-4 py-2">${h.prev}</td>
                    <td class="px-4 py-2">${h.curr}</td>
                    <td class="px-4 py-2">${h.maint}</td>
                    <td class="px-4 py-2">${h.service}</td>
                    <td class="px-4 py-2">${h.paid}</td>
                    <td class="px-4 py-2">${h.advance}</td>
                    <td class="px-4 py-2">${h.consumption}</td>
                </tr>
            `).join('');

            historyModal.classList.remove('hidden');
        });
    });

    closeHistory.addEventListener('click', () => {
        historyModal.classList.add('hidden');
    });

    // 🖨 Print function
    printBtn.addEventListener('click', () => {
        const printContents = document.getElementById('printSection').innerHTML;
        const printWindow = window.open('', '', 'height=700,width=900');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Payment History</title>
                    <style>
                        body { font-family: Inter, sans-serif; color: #333; padding: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                        th { background-color: #f3f4f6; }
                        h3 { text-align: center; }
                    </style>
                </head>
                <body>${printContents}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    });
</script>
@endsection

