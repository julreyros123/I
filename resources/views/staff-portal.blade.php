@extends('layouts.app')

@section('content')
<div class="flex">
    <!-- Sidebar & Navbar remain unchanged -->

    <!-- Main Portal Content -->
    <div class="flex-1 p-8 font-[Inter] transition-colors duration-300">
        <div id="portalContent" class="p-4 max-w-6xl mx-auto space-y-10">

            <!-- Header -->
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-8 tracking-tight">
                Staff Portal
            </h2>

            <!-- Customer Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
                <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-6">Customer Information</h5>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Search by Account No.
                    </label>
                    <input type="text" id="search"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                            bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100
                            focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Enter account number">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Account No.</label>
                    <input type="text" id="account_no"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg bg-gray-100 dark:bg-gray-700 
                                text-sm text-gray-900 dark:text-gray-200"
                            value="" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Customer Name</label>
                        <input type="text" id="customer_name"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Address</label>
                        <input type="text" id="customer_address"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Meter No.</label>
                        <input type="text" id="meter_no"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Meter Size</label>
                        <select id="meter_size"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100">
                            <option value="">Select</option>
                            <option>15mm</option>
                            <option>20mm</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Billing Computation -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
                <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-6">Billing Computation</h5>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Previous Reading</label>
                        <input type="number" id="prev_reading"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="0">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Current Reading</label>
                        <input type="number" id="current_reading"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="0">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Billing Date From</label>
                        <input type="date" id="date_from"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Billing Date To</label>
                        <input type="date" id="date_to"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold mb-2">Consumption (m³)</label>
                    <input type="text" id="consumption"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                            bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                        readonly>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Maintenance Charge</label>
                        <input type="text" id="maintenance_charge"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            value="0" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Service Fee</label>
                        <input type="number" id="service_fee"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            value="25" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">VAT (12%)</label>
                        <input type="text" id="vat_fee"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Total Amount Due</label>
                        <input type="text" id="total_amount"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-green-700 dark:text-green-400 font-semibold text-sm"
                            readonly>
                    </div>
                </div>
            </div>

            <!-- Payment Button -->
            <div class="text-right">
                <button id="openPaymentModal"
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-md transition">
                    Proceed to Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Summary Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Payment Summary</h3>
        <div id="paymentSummary" class="space-y-3 text-gray-700 dark:text-gray-200"></div>
        <div class="mt-6 text-right space-x-3">
            <button id="closeModal" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg">Cancel</button>
            <button id="confirmPayment" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Confirm Payment</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prev = document.getElementById('prev_reading');
    const curr = document.getElementById('current_reading');
    const consumption = document.getElementById('consumption');
    const total = document.getElementById('total_amount');
    const vatFee = document.getElementById('vat_fee');
    const maintenanceCharge = 0; // default to zero as requested

    async function compute() {
        const prevVal = parseFloat(prev.value) || 0;
        const currVal = parseFloat(curr.value) || 0;
        const svc = parseFloat(document.getElementById('service_fee').value) || 25;

        const res = await fetch("{{ route('api.billing.compute') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                previous_reading: prevVal,
                current_reading: currVal,
                maintenance_charge: maintenanceCharge,
                service_fee: svc,
                base_rate: 25
            })
        });
        const data = await res.json();

        consumption.value = (data.consumption_cu_m ?? 0).toFixed(2);
        vatFee.value = data.formatted?.vat ?? '₱0.00';
        total.value = data.formatted?.total ?? '₱0.00';
    }

    prev.addEventListener('input', compute);
    curr.addEventListener('input', compute);
    document.getElementById('service_fee').addEventListener('input', compute);
    compute();

    const modal = document.getElementById('paymentModal');
    const openModal = document.getElementById('openPaymentModal');
    const closeModal = document.getElementById('closeModal');
    const confirmPayment = document.getElementById('confirmPayment');
    const summary = document.getElementById('paymentSummary');

    openModal.addEventListener('click', () => {
        summary.innerHTML = `
            <div class="flex justify-between"><span>Consumption:</span><span>${consumption.value} m³</span></div>
            <div class="flex justify-between"><span>Maintenance Charge:</span><span>₱${maintenanceCharge.toFixed(2)}</span></div>
            <div class="flex justify-between"><span>Service Fee:</span><span>₱${(parseFloat(document.getElementById('service_fee').value)||0).toFixed(2)}</span></div>
            <div class="flex justify-between"><span>VAT (12%):</span><span>${vatFee.value}</span></div>
            <hr class="my-2">
            <div class="flex justify-between font-bold text-green-600 dark:text-green-400">
                <span>Total Amount:</span><span>${total.value}</span>
            </div>`;
        modal.classList.remove('hidden');
    });

    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    confirmPayment.addEventListener('click', async () => {
        const payload = {
            account_no: document.getElementById('account_no').value || 'UNKNOWN',
            previous_reading: parseFloat(prev.value) || 0,
            current_reading: parseFloat(curr.value) || 0,
            consumption_cu_m: parseFloat(consumption.value) || 0,
            base_rate: 25,
            maintenance_charge: maintenanceCharge,
            service_fee: parseFloat(document.getElementById('service_fee').value) || 25,
            vat: (function(){ const v = vatFee.value.replace(/[^0-9.]/g,''); return parseFloat(v)||0; })(),
            total_amount: (function(){ const t = total.value.replace(/[^0-9.]/g,''); return parseFloat(t)||0; })(),
            date_from: document.getElementById('date_from')?.value || null,
            date_to: document.getElementById('date_to')?.value || null,
        };

        const res = await fetch("{{ route('api.billing.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            alert('✅ Payment Saved!');
            modal.classList.add('hidden');
        } else {
            alert('❌ Failed to save payment.');
        }
    });
    // Account search -> load customer
    async function searchAndLoad() {
        const q = document.getElementById('search').value.trim();
        if (!q) return;
        const res = await fetch(`{{ route('customer.findByAccount') }}?account_no=${encodeURIComponent(q)}`);
        if (!res.ok) { alert('Account not found'); return; }
        const data = await res.json();
        const c = data.customer;
        document.getElementById('account_no').value = c.account_no || '';
        document.getElementById('customer_name').value = c.name || '';
        document.getElementById('customer_address').value = c.address || '';
        document.getElementById('meter_no').value = c.meter_no || '';
        document.getElementById('meter_size').value = c.meter_size || '';
        document.getElementById('prev_reading').value = (c.previous_reading ?? 0);
        document.getElementById('current_reading').value = (c.previous_reading ?? 0);
        compute();
    }

    document.getElementById('search').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchAndLoad();
        }
    });
    document.getElementById('search').addEventListener('blur', searchAndLoad);
});
</script>
@endsection
