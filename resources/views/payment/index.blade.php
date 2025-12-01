<?php
// Routes must be defined in routes/*.php. Removed inline route declarations from this view.
?>
@extends('layouts.app')

@section('title', 'Payment Portal')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-6 font-[Poppins] space-y-6">
    <!-- Tabs -->
    <div class="flex gap-2 text-sm">
        <button id="tabBtnCustomer" class="px-3 py-1.5 rounded bg-blue-600 text-white">Customer Payments</button>
        <button id="tabBtnApplicant" class="px-3 py-1.5 rounded bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">Applicant Fees</button>
    </div>

<!-- Applicant Fees Tab -->
<div id="tabApplicant" class="hidden">
    <div id="applicantHeader" class="hidden bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-3">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Application No.</label>
                <x-ui.input id="app_no" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Applicant Name</label>
                <x-ui.input id="applicant_name" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Status</label>
                <x-ui.input id="app_status" readonly />
            </div>
        </div>
    </div>

    <!-- Fees List -->
    <div id="feesCard" class="hidden bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-3">
        <div class="flex items-center justify-between">
            <p class="text-xs text-gray-500 dark:text-gray-400">Select fees to include. You can adjust partial amounts where allowed.</p>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" id="selectAllFees" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span>Select All</span>
            </label>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2">Fee</th>
                        <th class="px-4 py-2 text-right">Amount</th>
                        <th class="px-4 py-2 text-right">Pay Now</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody id="feesTbody" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
            </table>
        </div>
    </div>

    <!-- Fees Payment Panel -->
    <div id="feesPayment" class="hidden bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-4">
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Subtotal</label>
                <x-ui.input id="feesSubtotal" :value="'₱0.00'" readonly class="font-semibold" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Amount Tendered</label>
                <x-ui.input type="number" id="feesAmountPaid" placeholder="Enter amount" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Change</label>
                <x-ui.input id="feesChange" :value="'₱0.00'" readonly class="font-semibold" />
            </div>
            <div class="flex items-end">
                <x-primary-button type="button" id="feesProcessBtn" class="w-full justify-center">
                    <x-heroicon-o-credit-card class="w-5 h-5" />
                    <span>Process Fees</span>
                </x-primary-button>
            </div>
        </div>
        <p class="text-xs text-gray-500">Note: Applicant fees processing is currently a demo flow. Integrate with connections API to persist payments.</p>
    </div>
</div>

    <!-- Customer Payments Tab -->
    <div id="tabCustomer">
    <!-- Quick actions -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
        <p class="text-sm text-gray-600 dark:text-gray-400">Guide: Search an account, review unpaid bills, then process payment.</p>
    </div>

    <!-- Alert Box -->
    <div id="alertBox" class="hidden p-4 rounded-lg"></div>

    <!-- Search Customer (Account No.) -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-3">
        <div class="grid md:grid-cols-[1fr_auto] gap-3 items-end">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Account No.</label>
                <div class="relative">
                    <x-ui.input id="searchAccount" placeholder="e.g. 22-123456-7" />
                    <div id="quickSuggest" class="hidden absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow"></div>
                </div>
            </div>
            <div class="flex gap-2">
                <x-primary-button type="button" id="searchBtn" class="h-[42px]">Search</x-primary-button>
            </div>
        </div>
        <p class="text-xs text-gray-500">Tip: Type at least 2 characters to see quick suggestions.</p>
    </div>

    <!-- Customer Info & Detailed Billing Panel -->
    <div id="customerInfo" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-4">
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Delivery Date</label>
                <x-ui.input id="delivery_date" readonly />
            </div>
        </div>
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Account No.</label>
                <x-ui.input id="account_no" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Customer Name</label>
                <x-ui.input id="customer_name" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Address</label>
                <x-ui.input id="customer_address" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Classification</label>
                <x-ui.input id="classification" readonly />
            </div>
        </div>
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Meter No.</label>
                <x-ui.input id="meter_no" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Meter Size</label>
                <x-ui.input id="meter_size" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Previous Reading</label>
                <x-ui.input id="previous_reading" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Current Reading</label>
                <x-ui.input id="current_reading" readonly />
            </div>
        </div>

        <!-- Calculation Panel -->
        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-900/20">
            <div class="grid md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400">Subtotal</label>
                    <x-ui.input id="calc_subtotal" :value="'₱0.00'" readonly class="font-semibold" />
                    <p class="text-[11px] text-gray-500">Consumption x rate</p>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400">Advance Payment</label>
                    <x-ui.input id="calc_advance" :value="'₱0.00'" readonly />
                </div>
                <div class="pt-6">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" id="chkMaintenance" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span>Include Maintenance (<span id="lblMaintenanceAmount">₱0.00</span>)</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400">Late Fee (auto)</label>
                    <x-ui.input id="calc_latefee" :value="'₱0.00'" readonly />
                </div>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400">Total</label>
                    <x-ui.input id="total" :value="'₱0.00'" readonly class="font-semibold" />
                </div>
            </div>
        </div>
        <div id="paidNotice" class="hidden mt-3 p-3 rounded-md bg-green-100 text-green-800 text-sm">Customer is fully paid. Generating a new payment is disabled.</div>
        <div id="partialWarning" class="hidden mt-3 p-3 rounded-md bg-yellow-100 text-yellow-800 text-sm">Settle your outstanding balance to avoid disconnection.</div>
    </div>

    <!-- Unpaid Bills Selection -->
    <div id="unpaidListCard" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-3 hidden">
        <div class="flex items-center justify-between">
            <p class="text-xs text-gray-500 dark:text-gray-400">Select month(s) to include in this payment or use "Pay latest bill only".</p>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" id="selectAllBills" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span>Select All</span>
            </label>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2">Billing Date</th>
                        <th class="px-4 py-2 text-right">Consumption</th>
                        <th class="px-4 py-2 text-right">Amount</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody id="unpaidTbody" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Tip: Use "Pay latest bill only" above or pick specific months here.</p>
    </div>

    <!-- Billing details removed from this view.
         Billing management and billing UI should live under RecordController::billingManagement
         and resources/views/records/*. The payment page now focuses on tender/change and receipt printing. -->

    <!-- Cashier Payment Section -->
    <div id="paymentSection" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-4">
        <p class="text-xs text-gray-500 dark:text-gray-400">Enter tendered amount. Change is computed automatically. Click Process Payment to save and print.</p>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Amount Tendered</label>
                <x-ui.input type="number" id="amount_paid" placeholder="Enter amount tendered" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Change</label>
                <x-ui.input id="change" :value="'₱0.00'" readonly class="font-semibold" />
            </div>
            <div class="flex items-end">
                <x-primary-button type="button" id="processPayment" class="w-full justify-center">
                    <x-heroicon-o-credit-card class="w-5 h-5" />
                    <span>Process Payment</span>
                </x-primary-button>
            </div>
        </div>
    </div>
</div>

<script>
const alertBox = document.getElementById('alertBox');
function showAlert(msg, type = 'success') {
    if (window.showToast) {
        showToast(type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success'), String(msg));
    } else {
        // Fallback to the previous inline box if showToast is unavailable
        alertBox.classList.remove('hidden', 'bg-green-100', 'bg-red-100', 'text-green-700', 'text-red-700');
        alertBox.classList.add(type === 'error' ? 'bg-red-100' : 'bg-green-100');
        alertBox.classList.add(type === 'error' ? 'text-red-700' : 'text-green-700');
        alertBox.innerText = msg;
        setTimeout(() => alertBox.classList.add('hidden'), 3000);
    }
}

// --- Customer Search Functionality ---
// --- Account No. formatter and validator ---
const acctInput = document.getElementById('searchAccount');
const suggestBox = document.getElementById('quickSuggest');
function formatAcct(v){
    const d = (v || '').replace(/\D+/g,'').slice(0,9); // 9 digits total
    const p1 = d.slice(0,2);
    const p2 = d.slice(2,8);
    const p3 = d.slice(8,9);
    let out = p1;
    if (p2) out += '-' + p2; else if (p1.length === 2 && d.length>2) out += '-';
    if (p3) out += '-' + p3; else if (p2.length === 6 && d.length>8) out += '-';
    return out;
}
function isValidAcct(v){
    return /^[A-Za-z0-9-]{3,}$/.test((v || '').trim());
}
acctInput.addEventListener('input', () => {
    const pos = acctInput.selectionStart;
    acctInput.value = formatAcct(acctInput.value);
});

async function searchByAccount(account){
    if (!isValidAcct(account)) { showAlert('Invalid account number. Use 01-123456-7.', 'error'); return; }
    try {
        const res = await fetch(`/api/payment/search-customer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ account_no: account })
        });
        if (!res.ok) throw new Error('Customer not found');
        const data = await res.json();

        // Populate customer info fields
        document.getElementById('account_no').value = data.customer.account_no || '';
        document.getElementById('customer_name').value = data.customer.name || '';
        document.getElementById('customer_address').value = data.customer.address || '';
        document.getElementById('classification').value = (data.customer.classification || '').toString();
        document.getElementById('meter_no').value = data.customer.meter_no || '';
        document.getElementById('meter_size').value = (data.customer.meter_size || '').toString().replace(/\\+/g, '');

        // Set transaction metrics
        const unpaidCount = (data.unpaid_bills || []).length;
        const totalOutstanding = Number(data.total_outstanding || 0);
        const latestBill = data.latest_bill || null;
        const latestAmount = latestBill ? Number(latestBill.total_amount || 0) : 0;
        var unpaidCountEl = document.getElementById('unpaidCount');
        if (unpaidCountEl) unpaidCountEl.innerText = unpaidCount;
        var totalOutstandingEl = document.getElementById('totalOutstanding');
        if (totalOutstandingEl) totalOutstandingEl.innerText = `₱${totalOutstanding.toFixed(2)}`;
        var latestBillAmountEl = document.getElementById('latestBillAmount');
        if (latestBillAmountEl) latestBillAmountEl.innerText = `₱${latestAmount.toFixed(2)}`;

        // Delivery date and readings
        document.getElementById('delivery_date').value = latestBill && latestBill.delivery_date ? latestBill.delivery_date : '';
        document.getElementById('previous_reading').value = (latestBill && latestBill.previous_reading != null)
            ? Number(latestBill.previous_reading).toFixed(2)
            : (data.customer.previous_reading != null ? Number(data.customer.previous_reading).toFixed(2) : '');
        document.getElementById('current_reading').value = latestBill && latestBill.current_reading != null
            ? Number(latestBill.current_reading).toFixed(2)
            : '';

        // Calculation panel values (subtotal, advance, maintenance, late fee)
        const subtotal = latestBill ? Number(latestBill.subtotal || 0) : 0;
        const maint = latestBill ? Number(latestBill.maintenance_charge || 0) : 0;
        const advance = latestBill ? Number(latestBill.advance_payment || 0) : 0;
        const overduePenalty = latestBill ? Number(latestBill.overdue_penalty || 0) : 0;
        const due = latestBill && latestBill.date_to ? new Date(latestBill.date_to) : null;
        const today = new Date();
        let lateFee = 0;
        if (due) {
            // If customer missed at least 1 day after due/delivery date, add the bill's overdue penalty
            const msInDay = 24*60*60*1000;
            const daysLate = Math.floor((today.setHours(0,0,0,0) - new Date(due).setHours(0,0,0,0)) / msInDay);
            if (daysLate >= 1) lateFee = overduePenalty;
        }

        const lblMaint = document.getElementById('lblMaintenanceAmount');
        if (lblMaint) lblMaint.textContent = `₱${maint.toFixed(2)}`;
        const chkMaint = document.getElementById('chkMaintenance');
        if (chkMaint) chkMaint.checked = maint > 0; // default include if present

        function peso(v){ return `₱${Number(v||0).toFixed(2)}`; }
        function recalcTotal(){
            const incMaint = (document.getElementById('chkMaintenance') && document.getElementById('chkMaintenance').checked) ? maint : 0;
            const total = Math.max(0, subtotal + incMaint + lateFee - advance);
            document.getElementById('calc_subtotal').value = peso(subtotal);
            document.getElementById('calc_advance').value = peso(advance);
            document.getElementById('calc_latefee').value = peso(lateFee);
            document.getElementById('total').value = peso(total);
            const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
            document.getElementById('change').value = peso(Math.max(0, amountPaid - total));
        }
        if (chkMaint) chkMaint.addEventListener('change', recalcTotal);
        const tenderEl = document.getElementById('amount_paid');
        if (tenderEl) tenderEl.addEventListener('input', recalcTotal);
        recalcTotal();

        // Default total outstanding retained for other logic
        // But customer payment total now uses component calculation panel
        window.__latestAmount = latestAmount;
        window.__totalOutstanding = totalOutstanding;
        window.__unpaidBills = data.unpaid_bills || [];

        // Notices and button states
        const paidNotice = document.getElementById('paidNotice');
        const partialWarning = document.getElementById('partialWarning');
        const processBtn = document.getElementById('processPayment');
        paidNotice.classList.toggle('hidden', totalOutstanding > 0);
        processBtn.disabled = totalOutstanding <= 0;
        partialWarning.classList.add('hidden');

        // Build unpaid bills list
        const listCard = document.getElementById('unpaidListCard');
        const tbody = document.getElementById('unpaidTbody');
        tbody.innerHTML = '';
        if (unpaidCount > 0) {
            listCard.classList.remove('hidden');
            window.__unpaidBills.forEach((b) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-4 py-2">
                        <input type="checkbox" class="bill-check w-4 h-4" data-id="${b.id}" data-amount="${Number(b.total_amount || 0)}">
                    </td>
                    <td class="px-4 py-2">${b.billing_date || ''}</td>
                    <td class="px-4 py-2 text-right">${Number(b.consumption || 0).toFixed(2)} m³</td>
                    <td class="px-4 py-2 text-right">₱${Number(b.total_amount || 0).toFixed(2)}</td>
                    <td class="px-4 py-2">${b.bill_status || ''}</td>
                `;
                tbody.appendChild(tr);
            });
            hookSelectionHandlers();
        } else {
            listCard.classList.remove('hidden');
            tbody.innerHTML = `<tr><td class="px-4 py-6 text-center text-gray-500 dark:text-gray-400" colspan="5">No unpaid bills found.</td></tr>`;
        }

        showAlert('Customer found!');
        try { loadApplicantFeesForAccount(data.customer.account_no || '', data.customer.name || ''); } catch(_){}
    } catch (e) {
        showAlert('Customer not found.', 'error');
    }
}

var searchBtnEl = document.getElementById('searchBtn');
if (searchBtnEl && acctInput){
    searchBtnEl.addEventListener('click', async () => {
        const account = (acctInput.value || '').trim();
        searchByAccount(account);
    });
}

function hideSuggest(){ if (!suggestBox) return; suggestBox.classList.add('hidden'); suggestBox.innerHTML = ''; }

async function quickSuggest(q){
    if (!suggestBox) return;
    try {
        const url = new URL(`{{ route('api.payment.quick-search') }}`, window.location.origin);
        url.searchParams.set('q', q);
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) { hideSuggest(); return; }
        const data = await res.json();
        const items = (data.results || []);
        if (!items.length) { hideSuggest(); return; }
        suggestBox.innerHTML = items.map(it => `
            <button type="button" data-account="${it.account_no}" class="w-full text-left px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                <span class="block">
                    <span class="font-medium">${escapeHtml(it.name || '')}</span>
                    <span class="block text-xs text-gray-500">${escapeHtml(it.address || '')}</span>
                </span>
                <span class="ml-3 text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700">${escapeHtml(it.account_no || '')}</span>
            </button>
        `).join('');
        suggestBox.classList.remove('hidden');
        suggestBox.querySelectorAll('button[data-account]').forEach(btn => {
            btn.addEventListener('click', () => {
                const acct = btn.getAttribute('data-account') || '';
                if (acctInput) acctInput.value = acct;
                hideSuggest();
                searchByAccount(acct);
            });
        });
    } catch { hideSuggest(); }
}

function escapeHtml(s){
    return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));
}

// Quick search: auto-trigger when a valid account number is typed (debounced)
let acctDebounce;
if (acctInput){
    acctInput.addEventListener('input', () => {
        clearTimeout(acctDebounce);
        const account = (acctInput.value || '').trim();
        if (isValidAcct(account)) {
            acctDebounce = setTimeout(() => searchByAccount(account), 400);
            hideSuggest();
        } else {
            if ((account || '').replace(/\D+/g,'').length >= 2 || account.length >= 2) {
                acctDebounce = setTimeout(() => quickSuggest(account), 250);
            } else {
                hideSuggest();
            }
        }
    });
    acctInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const account = (acctInput.value || '').trim();
            if (!account) return;
            searchByAccount(account);
        }
    });
}

// Process Payment
document.getElementById('processPayment').addEventListener('click', () => {
    const totalRaw = document.getElementById('total').value || '0';
    const totalAmount = parseFloat(String(totalRaw).replace(/[^0-9.-]+/g, '')) || 0;
    const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
    const changeField = document.getElementById('change');

    if (window.__totalOutstanding <= 0) {
        return showAlert('Customer is fully paid. Payment is disabled. Print the confirmation invoice instead.', 'error');
    }
    if (!amountPaid) return showAlert('Please enter payment amount.', 'error');
    if (amountPaid < totalAmount) return showAlert('Insufficient payment.', 'error');

    const change = amountPaid - totalAmount;
    changeField.value = `₱${change.toFixed(2)}`;
    showAlert('Payment processed successfully!');
});

// Toggle latest only (guard if control is not present)
var latestOnlyEl = document.getElementById('latestOnly');
if (latestOnlyEl) latestOnlyEl.addEventListener('change', (e) => {
    const useLatest = e && e.target ? e.target.checked : false;
    const totalField = document.getElementById('total');
    const partialWarning = document.getElementById('partialWarning');
    const totalOutstanding = Number(window.__totalOutstanding || 0);
    const latest = Number(window.__latestAmount || 0);
    if (useLatest && totalOutstanding > latest && latest > 0) {
        totalField.value = `₱${latest.toFixed(2)}`;
        partialWarning.classList.remove('hidden');
    } else {
        totalField.value = `₱${totalOutstanding.toFixed(2)}`;
        partialWarning.classList.add('hidden');
    }
    // Clear manual selections when toggling latestOnly
    document.querySelectorAll('.bill-check').forEach(ch => ch.checked = false);
    updateManualSelectionTotal();
});

// Print Invoice removed

// Manual Selection: select all and recalc total
function hookSelectionHandlers() {
    const selectAll = document.getElementById('selectAllBills');
    const checks = () => Array.from(document.querySelectorAll('.bill-check'));
    selectAll.checked = false;
    selectAll.indeterminate = false;
    selectAll.onchange = () => {
        checks().forEach(ch => ch.checked = selectAll.checked);
        updateManualSelectionTotal();
    };
    checks().forEach(ch => ch.addEventListener('change', () => {
        const all = checks();
        const any = all.some(c => c.checked);
        const every = all.every(c => c.checked);
        selectAll.checked = every;
        selectAll.indeterminate = any && !every;
        updateManualSelectionTotal();
    }));
}

function updateManualSelectionTotal() {
    const useLatest = document.getElementById('latestOnly').checked;
    if (useLatest) return; // latest-only controls total
    const checks = Array.from(document.querySelectorAll('.bill-check'));
    const sum = checks.filter(c => c.checked).reduce((acc, c) => acc + Number(c.dataset.amount || 0), 0);
    const totalField = document.getElementById('total');
    if (sum > 0) {
        totalField.value = `₱${sum.toFixed(2)}`;
        document.getElementById('partialWarning').classList.toggle('hidden', true);
    } else {
        const totalOutstanding = Number(window.__totalOutstanding || 0);
        totalField.value = `₱${totalOutstanding.toFixed(2)}`;
    }
}

// Submit payment to API and open receipt, then refresh unpaid bills
document.getElementById('processPayment').addEventListener('click', async () => {
    const accountNo = (document.getElementById('account_no').value || '').trim();
    const totalRaw = document.getElementById('total').value || '0';
    const totalAmount = parseFloat(String(totalRaw).replace(/[^0-9.-]+/g, '')) || 0;
    const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
    if (window.__totalOutstanding <= 0) return; // guarded earlier
    if (!amountPaid || amountPaid < totalAmount) return;

    // collect selection
    const latestOnly = document.getElementById('latestOnly').checked;
    const selectedIds = Array.from(document.querySelectorAll('.bill-check:checked')).map(c => Number(c.getAttribute('data-id')));

    try {
        const res = await fetch(`{{ route('api.payment.process') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                account_no: accountNo,
                amount_paid: amountPaid,
                latest_only: latestOnly,
                bill_ids: selectedIds
            })
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Payment failed');
        // Open receipt
        const id = data.payment_record_id;
        window.open(`/payment/receipt/${id}`, '_blank');
        showAlert('Payment processed successfully!');

        // Reset payment inputs
        const amountInput = document.getElementById('amount_paid');
        const changeInput = document.getElementById('change');
        if (amountInput) amountInput.value = '';
        if (changeInput) changeInput.value = '₱0.00';

        // Refresh customer data so unpaid bills table updates (paid bills removed)
        if (accountNo) {
            try {
                await searchByAccount(accountNo);
            } catch(_) {}
        }
    } catch (err) {
        showAlert(String(err.message || err), 'error');
    }
});

// --- Tabs logic ---
const tabBtnCustomer = document.getElementById('tabBtnCustomer');
const tabBtnApplicant = document.getElementById('tabBtnApplicant');
const tabCustomer = document.getElementById('tabCustomer');
const tabApplicant = document.getElementById('tabApplicant');
function showTab(which){
    if (which === 'app'){
        tabApplicant.classList.remove('hidden');
        tabCustomer.classList.add('hidden');
        tabBtnApplicant.classList.add('bg-blue-600','text-white');
        tabBtnApplicant.classList.remove('bg-gray-100','text-gray-700','dark:bg-gray-700','dark:text-gray-200');
        tabBtnCustomer.classList.remove('bg-blue-600','text-white');
        tabBtnCustomer.classList.add('bg-gray-100','text-gray-700','dark:bg-gray-700','dark:text-gray-200');
    } else {
        tabCustomer.classList.remove('hidden');
        tabApplicant.classList.add('hidden');
        tabBtnCustomer.classList.add('bg-blue-600','text-white');
        tabBtnCustomer.classList.remove('bg-gray-100','text-gray-700','dark:bg-gray-700','dark:text-gray-200');
        tabBtnApplicant.classList.remove('bg-blue-600','text-white');
        tabBtnApplicant.classList.add('bg-gray-100','text-gray-700','dark:bg-gray-700','dark:text-gray-200');
    }
}
 if (tabBtnCustomer && tabBtnApplicant){
     tabBtnCustomer.onclick = function(){ showTab('cust'); };
     tabBtnApplicant.onclick = function(){
        showTab('app');
        try { history.replaceState(null, '', '#applicant-fees'); } catch(_) { location.hash = '#applicant-fees'; }
        // Show placeholder immediately to avoid blank UI
        try {
            var acct = document.getElementById('account_no')?.value || '';
            var name = document.getElementById('customer_name')?.value || '';
            loadApplicantFeesForAccount(acct, name);
        } catch(_){}
        var _feesCard = document.getElementById('feesCard');
        if (_feesCard && _feesCard.classList.contains('hidden')){ try { loadApplicantFeesFromApi(); } catch(_){} }
     };
 }

// Initialize tab from URL (?tab=app) or hash (#applicant-fees)
 function initTabFromUrl(){
     try {
         var params = new URLSearchParams(window.location.search);
         var hash = window.location.hash || '';
         if ((params.get('tab')||'') === 'app' || hash === '#applicant-fees') {
            showTab('app');
            // Show placeholder immediately
            try {
                var acct2 = document.getElementById('account_no')?.value || '';
                var name2 = document.getElementById('customer_name')?.value || '';
                loadApplicantFeesForAccount(acct2, name2);
            } catch(_){}
            var _feesCard2 = document.getElementById('feesCard');
            if (_feesCard2 && _feesCard2.classList.contains('hidden')){ try { loadApplicantFeesFromApi(); } catch(_){} }
         } else {
             showTab('cust');
         }
     } catch(_) { showTab('cust'); }
 }
initTabFromUrl();
window.addEventListener('hashchange', initTabFromUrl);
window.addEventListener('popstate', initTabFromUrl);

// --- Applicant fees placeholder logic ---
const feesTbody = document.getElementById('feesTbody');
const feesCard = document.getElementById('feesCard');
const feesPanel = document.getElementById('feesPayment');
const applicantHeader = document.getElementById('applicantHeader');
const selectAllFees = document.getElementById('selectAllFees');
let __currentApplication = null;
function peso(n){ return '₱'+(Number(n||0)).toFixed(2); }
function renderFees(rows){
    if (!rows || !rows.length){
        feesTbody.innerHTML = `<tr><td class="px-4 py-6 text-center text-gray-500 dark:text-gray-400" colspan="5">No fees to display.</td></tr>`;
        return;
    }
    feesTbody.innerHTML = rows.map(r => `
        <tr>
            <td class="px-4 py-2"><input type="checkbox" class="fee-check w-4 h-4" data-id="${r.code}" data-due="${r.amount}" ${r.status==='paid'?'disabled':''}></td>
            <td class="px-4 py-2">${r.name}</td>
            <td class="px-4 py-2 text-right">${peso(r.amount)}</td>
            <td class="px-4 py-2 text-right"><input ${r.partial?'' : 'readonly'} type="number" step="0.01" min="0" class="fee-pay-now w-28 border rounded px-2 py-1 text-right" value="${r.status==='paid'?0:r.amount}"></td>
            <td class="px-4 py-2">${r.status}</td>
        </tr>
    `).join('');
}
function calcFeesSubtotal(){
    const checks = Array.from(document.querySelectorAll('.fee-check'));
    let sum = 0;
    checks.forEach((ch, i) => {
        if (!ch.checked) return;
        const row = ch.closest('tr');
        const input = row.querySelector('.fee-pay-now');
        sum += Number(input && input.value ? input.value : 0);
    });
    document.getElementById('feesSubtotal').value = peso(sum);
    const tender = Number(document.getElementById('feesAmountPaid').value||0);
    document.getElementById('feesChange').value = peso(Math.max(0, tender - sum));
}
function hookFeeHandlers(){
    const checks = Array.from(document.querySelectorAll('.fee-check'));
    checks.forEach(ch => ch.addEventListener('change', calcFeesSubtotal));
    document.querySelectorAll('.fee-pay-now').forEach(inp => inp.addEventListener('input', calcFeesSubtotal));
    if (selectAllFees){
        selectAllFees.checked = false; selectAllFees.indeterminate = false;
        selectAllFees.onchange = () => {
            checks.forEach(c => { if (!c.disabled) c.checked = selectAllFees.checked; });
            calcFeesSubtotal();
        };
    }
}
function loadApplicantFeesForAccount(accountNo, applicantName){
    // Backward-compatible placeholder when no backend data is available yet
    applicantHeader.classList.remove('hidden');
    const appNoInput = document.getElementById('app_no');
    if (appNoInput && !appNoInput.value) {
        appNoInput.value = (accountNo ? ('APP-'+ String(accountNo).replace(/\W+/g,'') ) : '');
    }
    document.getElementById('applicant_name').value = applicantName || '—';
    document.getElementById('app_status').value = 'Pending Fees';
    const rows = [
        { code:'APP', name:'Application Fee', amount:300, partial:false, status:'unpaid' },
        { code:'INSP', name:'Inspection Fee', amount:200, partial:true, status:'unpaid' },
    ];
    renderFees(rows);
    feesCard.classList.remove('hidden');
    feesPanel.classList.remove('hidden');
    hookFeeHandlers();
    calcFeesSubtotal();
}

async function loadApplicantFeesById(id){
    if (!id) return;
    try {
        const res = await fetch(`/api/connections/${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (!res.ok || !data.ok || !data.application) throw new Error('Application not found');
        const app = data.application;
        __currentApplication = app;
        applicantHeader.classList.remove('hidden');
        document.getElementById('app_no').value = 'APP-' + String(app.id);
        document.getElementById('applicant_name').value = app.applicant_name || '—';
        document.getElementById('app_status').value = (app.status || '').toString();

        const rows = [];
        const pushFee = (code, name, amount, partial=false) => {
            const amt = Number(amount||0);
            if (isNaN(amt) || amt <= 0) return;
            rows.push({ code, name, amount: amt, partial, status: (app.status === 'paid') ? 'paid' : 'unpaid' });
        };
        pushFee('APP', 'Application Fee', app.fee_application, false);
        pushFee('INSP', 'Inspection Fee', app.fee_inspection, true);

        // If no assessed fees are stored yet, fall back to defaults for required payments
        if (rows.length === 0){
            const fallbackRows = [
                { code:'APP', name:'Application Fee', amount:300, partial:false, status:'unpaid' },
                { code:'INSP', name:'Inspection Fee', amount:200, partial:true, status:'unpaid' },
            ];
            renderFees(fallbackRows);
        } else {
            renderFees(rows);
        }
        hookFeeHandlers();
        calcFeesSubtotal();
        feesCard.classList.remove('hidden');
        feesPanel.classList.remove('hidden');
    } catch (e) {
        alert('Unable to load applicant fees for that application number.');
    }
}

async function loadApplicantFeesFromApi(){
    try {
        const url = new URL('{{ route('connections.index') }}', window.location.origin);
        url.searchParams.set('status', 'assessed');
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('Failed to load applications');
        const data = await res.json();
        const list = (data && data.items && (data.items.data || data.items)) || [];
        if (!Array.isArray(list) || list.length === 0){
            // Fallback to placeholder if nothing to show
            loadApplicantFeesForAccount('', '');
            return;
        }
        const app = list[0];
        __currentApplication = app;
        applicantHeader.classList.remove('hidden');
        document.getElementById('app_no').value = 'APP-' + String(app.id);
        document.getElementById('applicant_name').value = app.applicant_name || '—';
        document.getElementById('app_status').value = (app.status || '').toString();
        const rows = [];
        const pushFee = (code, name, amount, partial=false) => {
            const amt = Number(amount||0);
            if (isNaN(amt) || amt <= 0) return;
            rows.push({ code, name, amount: amt, partial, status: (app.status === 'paid') ? 'paid' : 'unpaid' });
        };
        pushFee('APP', 'Application Fee', app.fee_application, false);
        pushFee('INSP', 'Inspection Fee', app.fee_inspection, true);
        if (rows.length === 0){
            feesTbody.innerHTML = `<tr><td class="px-4 py-6 text-center text-gray-500 dark:text-gray-400" colspan="5">No fees assessed for this application.</td></tr>`;
        } else {
            renderFees(rows);
            hookFeeHandlers();
            calcFeesSubtotal();
        }
        feesCard.classList.remove('hidden');
        feesPanel.classList.remove('hidden');
    } catch (e) {
        // Fallback to placeholder on error
        loadApplicantFeesForAccount('', '');
    }
}
document.getElementById('feesAmountPaid').addEventListener('input', calcFeesSubtotal);
document.getElementById('feesProcessBtn').addEventListener('click', async function(){
    try {
        if (!__currentApplication || !__currentApplication.id){
            alert('No application selected.');
            return;
        }
        const subtotalText = document.getElementById('feesSubtotal').value || '₱0.00';
        const subtotal = parseFloat(subtotalText.replace(/[^0-9.\-]+/g, '')) || 0;
        if (subtotal <= 0){
            alert('Please select at least one fee to pay.');
            return;
        }
        // Auto-generate receipt number: OR-YYYYMMDDHHMMSS
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const receipt = `OR-${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
        const res = await fetch(`/api/connections/${__currentApplication.id}/pay`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ payment_receipt_no: receipt })
        });
        let data;
        try {
            data = await res.json();
        } catch(_) {
            const text = await res.text();
            throw new Error(`HTTP ${res.status} - ${text.slice(0,200)}`);
        }
        if (!res.ok || !data.ok){
            const serverMsg = data.message || data.error || (data.errors ? JSON.stringify(data.errors) : '');
            throw new Error(serverMsg || `Failed to process applicant fees payment (HTTP ${res.status})`);
        }
        alert('Applicant fees paid successfully.');
        // Reload details for the same application so status and fees update
        if (__currentApplication && __currentApplication.id){
            loadApplicantFeesById(__currentApplication.id);
        } else {
            loadApplicantFeesFromApi();
        }
    } catch (err){
        alert(String(err.message || err));
    }
});

// Applicant search by Application No. (press Enter)
const appNoInputEl = document.getElementById('app_no');
if (appNoInputEl){
    appNoInputEl.addEventListener('keydown', function(e){
        if (e.key === 'Enter'){
            e.preventDefault();
            const raw = (appNoInputEl.value || '').trim();
            if (!raw) return;
            const match = raw.match(/(\d+)/);
            const id = match ? Number(match[1]) : NaN;
            if (!id || isNaN(id)){
                alert('Please enter a valid application number (e.g. APP-5).');
                return;
            }
            loadApplicantFeesById(id);
        }
    });
}
</script>
@endsection

