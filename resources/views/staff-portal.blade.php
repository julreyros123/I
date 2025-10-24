@extends('layouts.app')

@section('content')
<div class="flex">
    <!-- Sidebar & Navbar remain unchanged -->

    <!-- Main Portal Content -->
    <div class="flex-1 p-8 font-[Inter] transition-colors duration-300">
        <div id="portalContent" class="p-2 max-w-6xl mx-auto space-y-6">

            <!-- Title removed to save space -->

            <!-- Customer Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
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
                            <option value="">Select Size</option>
                            <option value="1/2\"">1/2"</option>
                            <option value="3/4\"">3/4"</option>
                            <option value="1\"">1"</option>
                            <option value="2\"">2"</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Billing Computation -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
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

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Consumption (m³) <span class="text-xs text-gray-500">(Auto-calculated)</span></label>
                        <input type="text" id="consumption"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            readonly placeholder="Enter both readings to auto-calculate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Subtotal</label>
                        <input type="text" id="subtotal"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Service Fee (₱)</label>
                        <input type="number" id="service_fee"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="25" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Total Amount Due</label>
                        <input type="text" id="total_amount"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-green-700 dark:text-green-400 font-semibold text-sm"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Amount Paid</label>
                        <input type="number" id="amount_paid"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            placeholder="Enter amount paid" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Account Credit</label>
                        <input type="text" id="credit_balance"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-300 font-semibold text-sm"
                            readonly placeholder="No credit available">
                    </div>
                </div>

                <!-- Charges removed -->
            </div>

            <!-- Payment Button -->
            <div class="flex justify-between items-center">
                <button id="viewPaymentHistory"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition">
                    View Payment History
                </button>
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

<!-- Payment History Modal -->
<div id="paymentHistoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 w-full max-w-4xl max-h-[80vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Payment History</h3>
        <div id="paymentHistoryContent" class="space-y-4">
            <!-- Content will be loaded here -->
        </div>
        <div class="mt-6 text-right">
            <button id="closeHistoryModal" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prev = document.getElementById('prev_reading');
    const curr = document.getElementById('current_reading');
    const consumption = document.getElementById('consumption');
    const total = document.getElementById('total_amount');
    const subtotal = document.getElementById('subtotal');
    const amountPaid = document.getElementById('amount_paid');
    const creditBalance = document.getElementById('credit_balance');
    const maintenanceCharge = 0;

    async function compute() {
        const prevVal = parseFloat(prev.value) || 0;
        const currVal = parseFloat(curr.value) || 0;
        const svc = 0;

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
        subtotal.value = data.formatted?.subtotal ?? '₱0.00';
        total.value = data.formatted?.total ?? '₱0.00';
        
        // Set amount paid to total amount by default
        const totalAmount = parseFloat(data.total ?? 0);
        if (amountPaid.value === '' || amountPaid.value === '0') {
            amountPaid.value = totalAmount.toFixed(2);
        }
    }

    // Auto-calculate consumption when both readings are entered
    function autoCalculateConsumption() {
        const prevVal = parseFloat(prev.value) || 0;
        const currVal = parseFloat(curr.value) || 0;
        
        // If both readings have values, calculate consumption immediately
        if (prevVal > 0 && currVal > 0) {
            const consumption = Math.max(0, currVal - prevVal);
            document.getElementById('consumption').value = consumption.toFixed(2);
        }
        
        // Always call the full compute function for billing calculations
        compute();
    }

    prev.addEventListener('input', autoCalculateConsumption);
    curr.addEventListener('input', autoCalculateConsumption);
    // no service fee field anymore
    autoCalculateConsumption();

    const modal = document.getElementById('paymentModal');
    const openModal = document.getElementById('openPaymentModal');
    const closeModal = document.getElementById('closeModal');
    const confirmPayment = document.getElementById('confirmPayment');
    const summary = document.getElementById('paymentSummary');

    openModal.addEventListener('click', () => {
        // Validate required fields before opening modal
        const acctVal = document.getElementById('account_no')?.value || '';
        const prevVal = parseFloat(document.getElementById('prev_reading')?.value || 0);
        const currVal = parseFloat(document.getElementById('current_reading')?.value || 0);
        if (!acctVal) {
            alert('Please load or enter an account number before proceeding to payment.');
            return;
        }
        if (isNaN(prevVal) || isNaN(currVal)) {
            alert('Please enter valid previous and current readings.');
            return;
        }
        const serviceFeeInput = document.getElementById('service_fee');
        const serviceFeeVal = serviceFeeInput ? (parseFloat(serviceFeeInput.value) || 0) : 0;

        const amountPaidVal = parseFloat(amountPaid.value) || 0;
        const totalAmountVal = parseFloat(total.value.replace(/[^0-9.]/g,'')) || 0;
        const creditVal = parseFloat(creditBalance.value.replace(/[^0-9.]/g,'')) || 0;
        const overpayment = Math.max(0, amountPaidVal - totalAmountVal);
        
        summary.innerHTML = `
            <div class="flex justify-between"><span>Consumption:</span><span>${consumption.value} m³</span></div>
            <div class="flex justify-between"><span>Subtotal:</span><span>${subtotal.value}</span></div>
            <div class="flex justify-between"><span>Maintenance Charge:</span><span>₱${maintenanceCharge.toFixed(2)}</span></div>
            <div class="flex justify-between"><span>Service Fee:</span><span>₱${serviceFeeVal.toFixed(2)}</span></div>
            <div class="flex justify-between"><span>Total Bill:</span><span>${total.value}</span></div>
            <div class="flex justify-between"><span>Amount Paid:</span><span>₱${amountPaidVal.toFixed(2)}</span></div>
            ${creditVal > 0 ? `<div class="flex justify-between text-blue-600 dark:text-blue-400"><span>Available Credit:</span><span>₱${creditVal.toFixed(2)}</span></div>` : ''}
            ${overpayment > 0 ? `<div class="flex justify-between text-orange-600 dark:text-orange-400"><span>Overpayment:</span><span>₱${overpayment.toFixed(2)}</span></div>` : ''}
            <hr class="my-2">
            <div class="flex justify-between font-bold text-green-600 dark:text-green-400">
                <span>Payment Status:</span><span>${overpayment > 0 ? 'Overpaid' : 'Paid'}</span>
            </div>`;
        modal.classList.remove('hidden');
    });

    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    confirmPayment.addEventListener('click', async () => {
        try {
            const acct = document.getElementById('account_no')?.value || '';
            if (!acct) { alert('Account number missing.'); return; }
            const svcFee = parseFloat(document.getElementById('service_fee')?.value || 0) || 0;
            const totalParsed = (function(){ const t = total.value.replace(/[^0-9.]/g,''); return parseFloat(t)||0; })();
            const payload = {
                account_no: acct,
                previous_reading: parseFloat(prev.value) || 0,
                current_reading: parseFloat(curr.value) || 0,
                consumption_cu_m: parseFloat(consumption.value) || 0,
                base_rate: 25,
                maintenance_charge: maintenanceCharge,
                service_fee: svcFee,
                vat: 0,
                total_amount: totalParsed,
                amount_paid: parseFloat(amountPaid.value) || totalParsed,
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
                const result = await res.json();
                let message = '✅ Payment Saved!';
                
                if (result.message) {
                    message = result.message;
                }
                
                if (result.credit_applied > 0) {
                    message += `\n\nCredit Applied: ₱${result.credit_applied.toFixed(2)}`;
                }
                
                if (result.overpayment > 0) {
                    message += `\n\nOverpayment: ₱${result.overpayment.toFixed(2)} added to account credit`;
                }
                
                if (result.remaining_credit > 0) {
                    message += `\n\nRemaining Credit: ₱${result.remaining_credit.toFixed(2)}`;
                }
                
                alert(message);
                modal.classList.add('hidden');
                
                // Update credit balance display
                if (result.remaining_credit !== undefined) {
                    creditBalance.value = result.remaining_credit > 0 ? 
                        `₱${result.remaining_credit.toFixed(2)}` : 'No credit available';
                }
            } else {
                const result = await res.json().catch(() => null);
                const errorMessage = result?.error || 'Failed to save payment';
                alert(`❌ ${errorMessage}`);
            }
        } catch (err) {
            console.error('Payment error', err);
            alert('❌ An error occurred while processing payment.');
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
        
        // Update credit balance display
        const credit = parseFloat(c.credit_balance ?? 0);
        creditBalance.value = credit > 0 ? `₱${credit.toFixed(2)}` : 'No credit available';
        
        // Trigger auto-calculation after loading customer data
        autoCalculateConsumption();
    }

    document.getElementById('search').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchAndLoad();
        }
    });
    document.getElementById('search').addEventListener('blur', searchAndLoad);

    // Payment History Modal
    const paymentHistoryModal = document.getElementById('paymentHistoryModal');
    const viewPaymentHistoryBtn = document.getElementById('viewPaymentHistory');
    const closeHistoryModal = document.getElementById('closeHistoryModal');
    const paymentHistoryContent = document.getElementById('paymentHistoryContent');

    viewPaymentHistoryBtn.addEventListener('click', async () => {
        const accountNo = document.getElementById('account_no')?.value;
        if (!accountNo) {
            alert('Please search for a customer first.');
            return;
        }

        try {
            paymentHistoryContent.innerHTML = '<div class="text-center py-4">Loading payment history...</div>';
            paymentHistoryModal.classList.remove('hidden');

            const response = await fetch(`{{ route('api.billing.payment-history') }}?account_no=${encodeURIComponent(accountNo)}`);
            const data = await response.json();

            if (data.error) {
                paymentHistoryContent.innerHTML = `<div class="text-center py-4 text-red-600">${data.error}</div>`;
                return;
            }

            const { customer, payments } = data;
            
            let html = `
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg mb-4">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200">Customer Information</h4>
                    <p><strong>Account:</strong> ${customer.account_no}</p>
                    <p><strong>Name:</strong> ${customer.name}</p>
                    <p><strong>Address:</strong> ${customer.address}</p>
                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                        <strong>Current Credit Balance:</strong> ${customer.formatted_credit_balance}
                    </p>
                </div>
            `;

            if (payments.length === 0) {
                html += '<div class="text-center py-4 text-gray-500">No payment history found.</div>';
            } else {
                html += `
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2 text-left">Date</th>
                                    <th class="px-4 py-2 text-left">Bill Amount</th>
                                    <th class="px-4 py-2 text-left">Amount Paid</th>
                                    <th class="px-4 py-2 text-left">Credit Applied</th>
                                    <th class="px-4 py-2 text-left">Overpayment</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    <th class="px-4 py-2 text-left">Consumption</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                `;

                payments.forEach(payment => {
                    const statusColor = payment.payment_status === 'overpaid' ? 'text-orange-600 dark:text-orange-400' : 
                                      payment.payment_status === 'partial' ? 'text-yellow-600 dark:text-yellow-400' : 
                                      'text-green-600 dark:text-green-400';
                    
                    html += `
                        <tr>
                            <td class="px-4 py-2">${payment.date}</td>
                            <td class="px-4 py-2">₱${payment.bill_amount.toFixed(2)}</td>
                            <td class="px-4 py-2">₱${payment.amount_paid.toFixed(2)}</td>
                            <td class="px-4 py-2 text-blue-600 dark:text-blue-400">₱${payment.credit_applied.toFixed(2)}</td>
                            <td class="px-4 py-2 text-orange-600 dark:text-orange-400">₱${payment.overpayment.toFixed(2)}</td>
                            <td class="px-4 py-2 ${statusColor} font-semibold">${payment.payment_status.toUpperCase()}</td>
                            <td class="px-4 py-2">${payment.consumption.toFixed(2)} m³</td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';
            }

            paymentHistoryContent.innerHTML = html;
        } catch (error) {
            console.error('Error loading payment history:', error);
            paymentHistoryContent.innerHTML = '<div class="text-center py-4 text-red-600">Failed to load payment history.</div>';
        }
    });

    closeHistoryModal.addEventListener('click', () => {
        paymentHistoryModal.classList.add('hidden');
    });
});
</script>
@endsection
