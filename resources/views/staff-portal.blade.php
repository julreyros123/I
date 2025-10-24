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
                    <div class="relative">
                        <input type="text" id="search"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100
                                focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Type account number to see suggestions..."
                            autocomplete="off">
                        
                        <!-- Suggestions Dropdown -->
                        <div id="suggestions" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            <!-- Suggestions will be populated here -->
                        </div>
                    </div>
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
                        <label class="block text-sm font-semibold mb-2">Maintenance Charge (₱)</label>
                        <input type="number" id="maintenance_charge"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="0" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Advance Payment (₱)</label>
                        <input type="text" id="advance_payment"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-200"
                            readonly placeholder="Auto-calculated from previous excess">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Overdue Penalty (₱)</label>
                        <input type="number" id="overdue_penalty"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100"
                            value="0" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Total Amount Due</label>
                        <input type="text" id="total_amount"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg
                                bg-gray-50 dark:bg-gray-700 text-green-700 dark:text-green-400 font-semibold text-sm"
                            readonly>
                    </div>
                </div>

                <!-- Charges removed -->
            </div>

            <!-- Payment Status Section -->
            <div id="paymentStatus" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 hidden">
                <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-6">Payment Status</h5>
                
                <div id="paymentStatusContent" class="space-y-4">
                    <!-- Payment status will be populated here -->
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center">
                <button id="viewPaymentHistory"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition">
                    View Payment History
                </button>
                <button id="checkPaymentStatus"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg shadow-md transition">
                    Check Payment Status
                </button>
                <button id="saveBill"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-md transition">
                    Save Bill
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bill Summary Modal -->
<div id="billModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Bill Summary</h3>
        <div id="billSummary" class="space-y-3 text-gray-700 dark:text-gray-200"></div>
        <div class="mt-6 text-right space-x-3">
            <button id="closeModal" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg">Cancel</button>
            <button id="confirmBill" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Save Bill</button>
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
    const maintenanceChargeInput = document.getElementById('maintenance_charge');
    const advancePaymentInput = document.getElementById('advance_payment');
    const overduePenaltyInput = document.getElementById('overdue_penalty');
    // service_charge may not exist in all templates; guard its usage to avoid JS errors
    const serviceChargeInput = document.getElementById('service_charge');

    async function compute() {
        const prevVal = parseFloat(prev.value) || 0;
        const currVal = parseFloat(curr.value) || 0;
        const maintenanceCharge = parseFloat(maintenanceChargeInput.value) || 0;
        const advancePayment = parseFloat(advancePaymentInput.value) || 0;
        const overduePenalty = parseFloat(overduePenaltyInput.value) || 0;

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
                base_rate: 25
            })
        });
        const data = await res.json();

        consumption.value = (data.consumption_cu_m ?? 0).toFixed(2);
        subtotal.value = data.formatted?.subtotal ?? '₱0.00';
        
        // Calculate total with all charges and adjustments
        const baseTotal = parseFloat(data.total ?? 0);
        const finalTotal = baseTotal + maintenanceCharge + overduePenalty - advancePayment;
        total.value = '₱' + Math.max(0, finalTotal).toFixed(2);
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
    maintenanceChargeInput.addEventListener('input', compute);
    if (serviceChargeInput) serviceChargeInput.addEventListener('input', compute);
    overduePenaltyInput.addEventListener('input', compute);
    autoCalculateConsumption();

    const modal = document.getElementById('billModal');
    const openModal = document.getElementById('saveBill');
    const closeModal = document.getElementById('closeModal');
    const confirmBill = document.getElementById('confirmBill');
    const summary = document.getElementById('billSummary');

    openModal.addEventListener('click', () => {
        // Validate required fields before opening modal
        const acctVal = document.getElementById('account_no')?.value || '';
        const prevVal = parseFloat(document.getElementById('prev_reading')?.value || 0);
        const currVal = parseFloat(document.getElementById('current_reading')?.value || 0);
        
        if (!acctVal) {
            alert('Please load or enter an account number before saving the bill.');
            return;
        }
        if (isNaN(prevVal) || isNaN(currVal) || currVal <= 0) {
            alert('Please enter valid previous and current readings. Current reading must be greater than 0.');
            return;
        }
        if (currVal <= prevVal) {
            alert('Current reading must be greater than previous reading.');
            return;
        }

        const maintenanceCharge = parseFloat(maintenanceChargeInput.value) || 0;
        const advancePayment = parseFloat(advancePaymentInput.value) || 0;
        const overduePenalty = parseFloat(overduePenaltyInput.value) || 0;
        const totalAmountVal = parseFloat(total.value.replace(/[^0-9.]/g,'')) || 0;
        
        summary.innerHTML = `
            <div class="flex justify-between"><span>Consumption:</span><span>${consumption.value} m³</span></div>
            <div class="flex justify-between"><span>Subtotal:</span><span>${subtotal.value}</span></div>
            <div class="flex justify-between"><span>Maintenance Charge:</span><span>₱${maintenanceCharge.toFixed(2)}</span></div>
            <div class="flex justify-between"><span>Overdue Penalty:</span><span>₱${overduePenalty.toFixed(2)}</span></div>
            <div class="flex justify-between"><span>Advance Payment:</span><span>₱${advancePayment.toFixed(2)}</span></div>
            <div class="flex justify-between font-bold"><span>Total Bill:</span><span>${total.value}</span></div>
            <hr class="my-2">
            <div class="flex justify-between font-bold text-green-600 dark:text-green-400">
                <span>Bill Status:</span><span>Ready to Save</span>
            </div>`;
        modal.classList.remove('hidden');
    });

    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    confirmBill.addEventListener('click', async () => {
        try {
            const acct = document.getElementById('account_no')?.value || '';
            if (!acct) { alert('Account number missing.'); return; }
            
            const maintenanceCharge = parseFloat(maintenanceChargeInput.value) || 0;
            const advancePayment = parseFloat(advancePaymentInput.value) || 0;
            const overduePenalty = parseFloat(overduePenaltyInput.value) || 0;
            const totalParsed = (function(){ const t = total.value.replace(/[^0-9.]/g,''); return parseFloat(t)||0; })();
            
            const payload = {
                account_no: acct,
                previous_reading: parseFloat(prev.value) || 0,
                current_reading: parseFloat(curr.value) || 0,
                consumption_cu_m: parseFloat(consumption.value) || 0,
                base_rate: 25,
                maintenance_charge: maintenanceCharge,
                service_fee: 0,
                advance_payment: advancePayment,
                overdue_penalty: overduePenalty,
                vat: 0,
                total_amount: totalParsed,
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
                let message = '✅ Bill Saved Successfully!';
                
                if (result.message) {
                    message = result.message;
                }
                
                alert(message);
                modal.classList.add('hidden');
                
                // Clear form for next bill
                document.getElementById('prev_reading').value = parseFloat(curr.value) || 0;
                document.getElementById('current_reading').value = '';
                document.getElementById('consumption').value = '';
                document.getElementById('subtotal').value = '';
                document.getElementById('total_amount').value = '';
                document.getElementById('maintenance_charge').value = '0';
                if (serviceChargeInput) serviceChargeInput.value = '0';
                document.getElementById('overdue_penalty').value = '0';
                document.getElementById('advance_payment').value = '';
                
            } else {
                const result = await res.json().catch(() => null);
                const errorMessage = result?.error || 'Failed to save bill';
                alert(`❌ ${errorMessage}`);
            }
        } catch (err) {
            console.error('Bill save error', err);
            alert('❌ An error occurred while saving the bill.');
        }
    });
    // Account search functionality with autocomplete
    let searchTimeout;
    const searchInput = document.getElementById('search');
    const suggestionsDiv = document.getElementById('suggestions');

    // Load customer data
    async function loadCustomer(accountNo) {
        const res = await fetch(`{{ route('customer.findByAccount') }}?account_no=${encodeURIComponent(accountNo)}`);
        if (!res.ok) { 
            alert('Account not found'); 
            return; 
        }
        const data = await res.json();
        const c = data.customer;
        document.getElementById('account_no').value = c.account_no || '';
        document.getElementById('customer_name').value = c.name || '';
        document.getElementById('customer_address').value = c.address || '';
        document.getElementById('meter_no').value = c.meter_no || '';
        document.getElementById('meter_size').value = c.meter_size || '';
        document.getElementById('prev_reading').value = (c.previous_reading ?? 0);
        document.getElementById('current_reading').value = (c.previous_reading ?? 0);
        
        // Reset advance payment to 0
        advancePaymentInput.value = '0.00';
        
        // Trigger auto-calculation after loading customer data
        autoCalculateConsumption();
    }

    // Search for account suggestions
    async function searchSuggestions(query) {
        if (query.length < 2) {
            suggestionsDiv.classList.add('hidden');
            return;
        }

        try {
            const res = await fetch(`{{ route('customer.searchAccounts') }}?q=${encodeURIComponent(query)}`);
            const data = await res.json();
            
            if (data.suggestions && data.suggestions.length > 0) {
                showSuggestions(data.suggestions);
            } else {
                suggestionsDiv.classList.add('hidden');
            }
        } catch (error) {
            console.error('Search error:', error);
            suggestionsDiv.classList.add('hidden');
        }
    }

    // Display suggestions dropdown
    function showSuggestions(suggestions) {
        const html = suggestions.map(suggestion => `
            <div class="suggestion-item px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-600 last:border-b-0" 
                 data-account="${suggestion.account_no}">
                <div class="font-semibold text-gray-900 dark:text-gray-100">${suggestion.account_no}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">${suggestion.name}</div>
                <div class="text-xs text-gray-500 dark:text-gray-500">${suggestion.address}</div>
            </div>
        `).join('');
        
        suggestionsDiv.innerHTML = html;
        suggestionsDiv.classList.remove('hidden');
    }

    // Handle input events
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Set new timeout to avoid too many requests
        searchTimeout = setTimeout(() => {
            searchSuggestions(query);
        }, 300);
    });

    // Handle suggestion clicks
    suggestionsDiv.addEventListener('click', (e) => {
        const suggestionItem = e.target.closest('.suggestion-item');
        if (suggestionItem) {
            const accountNo = suggestionItem.dataset.account;
            searchInput.value = accountNo;
            suggestionsDiv.classList.add('hidden');
            loadCustomer(accountNo);
        }
    });

    // Handle Enter key
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query) {
                suggestionsDiv.classList.add('hidden');
                loadCustomer(query);
            }
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.add('hidden');
        }
    });

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

    // Check Payment Status functionality
    const checkPaymentStatusBtn = document.getElementById('checkPaymentStatus');
    const paymentStatus = document.getElementById('paymentStatus');
    const paymentStatusContent = document.getElementById('paymentStatusContent');

    checkPaymentStatusBtn.addEventListener('click', async () => {
        const accountNo = document.getElementById('account_no')?.value;
        if (!accountNo) {
            alert('Please search for a customer first.');
            return;
        }

        try {
            paymentStatusContent.innerHTML = '<div class="text-center py-4">Loading payment status...</div>';
            paymentStatus.classList.remove('hidden');

            const response = await fetch(`{{ route('api.billing.payment-history') }}?account_no=${encodeURIComponent(accountNo)}`);
            const data = await response.json();

            if (data.error) {
                paymentStatusContent.innerHTML = `<div class="text-center py-4 text-red-600">${data.error}</div>`;
                return;
            }

            const { customer, payments } = data;
            
            // Get unpaid bills
            const unpaidBills = await fetch(`{{ route('api.payment.search-customer') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ account_no: accountNo })
            }).then(res => res.json()).catch(() => ({ unpaid_bills: [] }));

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

            if (unpaidBills.unpaid_bills && unpaidBills.unpaid_bills.length > 0) {
                html += `
                    <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">Outstanding Bills (${unpaidBills.unpaid_bills.length})</h4>
                        <p class="text-red-600 dark:text-red-400 font-bold">Total Outstanding: ${unpaidBills.formatted_total_outstanding}</p>
                    </div>
                `;
            } else {
                html += `
                    <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-green-800 dark:text-green-200">✅ All Bills Paid</h4>
                        <p class="text-green-600 dark:text-green-400">No outstanding bills for this customer.</p>
                    </div>
                `;
            }

            if (payments.length > 0) {
                html += `
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Recent Payments (${payments.length})</h4>
                        <div class="space-y-2">
                `;

                payments.slice(0, 3).forEach(payment => {
                    const statusColor = payment.payment_status === 'overpaid' ? 'text-orange-600 dark:text-orange-400' : 
                                      payment.payment_status === 'partial' ? 'text-yellow-600 dark:text-yellow-400' : 
                                      'text-green-600 dark:text-green-400';
                    
                    html += `
                        <div class="flex justify-between items-center text-sm">
                            <span>${payment.date} - ₱${payment.amount_paid.toFixed(2)}</span>
                            <span class="${statusColor} font-semibold">${payment.payment_status.toUpperCase()}</span>
                        </div>
                    `;
                });

                html += '</div></div>';
            }

            paymentStatusContent.innerHTML = html;
        } catch (error) {
            console.error('Error loading payment status:', error);
            paymentStatusContent.innerHTML = '<div class="text-center py-4 text-red-600">Failed to load payment status.</div>';
        }
    });
});
</script>
@endsection
