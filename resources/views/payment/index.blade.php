<?php
// Routes must be defined in routes/*.php. Removed inline route declarations from this view.
?>
@extends('layouts.app')

@section('title', 'Payment Portal')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-6 font-[Poppins] space-y-6">
    <!-- Tabs + Guide -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex gap-2 text-sm">
            <button id="tabBtnCustomer" class="px-3 py-1.5 rounded bg-blue-600 text-white">Customer Payments</button>
            <button id="tabBtnApplicant" class="px-3 py-1.5 rounded bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">Applicant Fees</button>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">Guide: Search an account, review unpaid bills, then process payment.</p>
    </div>

<!-- Applicant Fees Tab -->
<div id="tabApplicant" class="hidden space-y-6">
    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <!-- Applicant Directory -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div class="w-full sm:max-w-sm">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Applicant Directory</label>
                        <x-ui.input id="applicantSearchInput" placeholder="Search by name, application, or account no." class="w-full h-11 text-sm" />
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Results update instantly. Displaying up to 10 matches.</p>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-100 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-semibold">Application</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Applicant</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Address</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody id="applicantDirectoryBody" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Fee & Payment Summary -->
        <div class="space-y-4">
            <div id="feesCard" class="hidden bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Applicant Fee Details</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Review the assessed amounts before collecting payment.</p>
                </div>
                <ul id="feesDetailsList" class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                    <li class="text-gray-400 dark:text-gray-500">Select an applicant to preview fees.</li>
                </ul>
            </div>

            <div id="feesPayment" class="hidden bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-5 space-y-5">
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Payment Summary</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Enter the amount tendered to compute change automatically.</p>
                </div>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Application No.</label>
                        <x-ui.input id="app_no" :value="'—'" readonly class="w-full h-10 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Applicant Name</label>
                        <x-ui.input id="applicant_name" :value="'—'" readonly class="w-full h-10 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Overall Status</label>
                        <x-ui.input id="app_status" :value="'—'" readonly class="w-full h-10 text-sm" />
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Subtotal</label>
                        <x-ui.input id="feesSubtotal" :value="'₱0.00'" readonly class="h-11 text-base font-semibold" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Amount Tendered</label>
                        <x-ui.input type="number" id="feesAmountPaid" placeholder="Enter amount received" class="h-11 text-base" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Change</label>
                        <x-ui.input id="feesChange" :value="'₱0.00'" readonly class="h-11 text-base font-semibold" />
                    </div>
                </div>
                <div id="feesLiveSummary" class="border border-blue-100 dark:border-blue-900/40 rounded-xl bg-blue-50/60 dark:bg-blue-900/20 p-4 space-y-4">
                    <div class="flex flex-col gap-2">
                        <p class="text-[11px] uppercase tracking-wide text-blue-600 dark:text-blue-300">Live Summary</p>
                        <p id="feesSummaryStatus" class="text-sm font-semibold text-blue-900 dark:text-blue-200">Select a fee to begin</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
                        <div class="sm:flex-[1_1_320px] sm:max-w-full">
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">Invoice No.</p>
                            <p id="feesInvoiceDisplay" class="font-mono text-sm text-gray-900 dark:text-gray-100 break-words sm:break-normal">—</p>
                        </div>
                        <div class="w-full sm:flex-[1_1_220px] sm:min-w-[220px] space-y-2">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-[11px] uppercase text-gray-500 dark:text-gray-400">Due</p>
                                <p id="feesSummaryDue" class="text-base font-semibold text-gray-900 dark:text-gray-100">₱0.00</p>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-[11px] uppercase text-gray-500 dark:text-gray-400">Tendered</p>
                                <p id="feesSummaryTender" class="text-base font-semibold text-gray-900 dark:text-gray-100">₱0.00</p>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-[11px] uppercase text-gray-500 dark:text-gray-400">Change</p>
                                <p id="feesSummaryChange" class="text-base font-semibold text-gray-900 dark:text-gray-100">₱0.00</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase text-gray-500 dark:text-gray-400 mb-1">Selected Fees</p>
                        <ul id="feesSummaryList" class="text-xs text-gray-700 dark:text-gray-200 space-y-1">
                            <li class="text-gray-400 dark:text-gray-500">No fees selected yet.</li>
                        </ul>
                    </div>
                </div>
                <x-primary-button type="button" id="feesProcessBtn" class="w-full justify-center h-11 text-sm font-semibold">
                    <x-heroicon-o-credit-card class="w-5 h-5" />
                    <span>Record Applicant Payment</span>
                </x-primary-button>
            </div>
        </div>
    </div>
</div>

    <!-- Customer Payments Tab -->
    <div id="tabCustomer" class="space-y-5">

        <!-- Alert Box -->
        <div id="alertBox" class="hidden p-4 rounded-lg"></div>

        <!-- Search Customer (Account No.) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-end sm:gap-3 lg:items-center lg:justify-between">
                <div class="w-full sm:max-w-lg">
                    <label class="block text-sm text-gray-600 dark:text-gray-400">Search Customer</label>
                    <div class="relative">
                        <x-ui.input id="unifiedSearch" placeholder="Account no. or customer name" class="w-full h-11 text-sm" />
                        <div id="quickSuggest" class="hidden absolute z-30 mt-2 w-full max-h-72 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl"></div>
                    </div>
                </div>
                <div class="mt-3 sm:mt-0 flex gap-2 w-full sm:w-auto">
                    <x-primary-button type="button" id="searchBtn" class="w-full sm:w-auto h-11 px-6">Search</x-primary-button>
                </div>
            </div>
            <p class="text-xs text-gray-500">Tip: Type at least 2 characters — we’ll match either account numbers or customer names.</p>
            <div id="searchResults" class="hidden mt-2 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-900">
                <div class="flex items-center justify-between px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800">
                    <span>Search Results</span>
                    <button type="button" id="clearResults" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Clear</button>
                </div>
                <ul id="searchResultsList" class="divide-y divide-gray-200 dark:divide-gray-800"></ul>
            </div>
        </div>

    <!-- Payment Summary Layout: Current Bill + Quick Links + Recent Activity -->
    <div id="paymentSummary" class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center">
                    <x-heroicon-o-credit-card class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Current bill for</p>
                    <p id="summaryCustomerName" class="text-sm font-semibold text-gray-900 dark:text-gray-100">—</p>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-2 gap-4 sm:gap-6 w-full sm:w-auto sm:min-w-[220px]">
                <div class="flex flex-col items-end text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Unpaid bills</p>
                    <p id="unpaidCount" class="text-lg font-semibold text-gray-900 dark:text-gray-100">0</p>
                </div>
                <div class="flex flex-col items-end text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Latest bill</p>
                    <p id="latestBillAmount" class="text-lg font-semibold text-gray-900 dark:text-gray-100">₱0.00</p>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-[2fr_1fr] gap-4 mt-2">
            <!-- Current Bill Panel -->
            <div class="border border-dashed border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col justify-between bg-gray-50/80 dark:bg-gray-900/30">
                <div>
                    <p class="text-xs tracking-wide uppercase text-gray-500 dark:text-gray-400 mb-1">Current Balance</p>
                    <p id="totalOutstanding" class="text-3xl font-semibold text-gray-900 dark:text-gray-50">₱0.00</p>
                    <p class="mt-3 text-xs text-gray-600 dark:text-gray-300">
                        Due Date: <span id="currentDueDate">—</span>
                    </p>
                </div>
                <div class="mt-4">
                    <button type="button" onclick="document.getElementById('paymentSection')?.scrollIntoView({ behavior: 'smooth' });" class="inline-flex items-center px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm shadow-sm">
                        <x-heroicon-o-banknotes class="w-4 h-4 mr-1.5" />
                        <span>Pay Now</span>
                    </button>
                </div>
            </div>

            <!-- At-a-glance Metrics -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Overview</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">Summary based on the latest billing record and unpaid balance.</p>
                </div>
                <p class="mt-3 text-[11px] text-gray-500 dark:text-gray-400">Details below let you inspect consumption, select specific months, and process payments with change computation.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 pt-2">
            <!-- Quick Links -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">Quick Links</p>
                <ul class="space-y-1.5 text-sm text-gray-700 dark:text-gray-200">
                    <li class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <button type="button" class="hover:underline text-blue-600 dark:text-blue-400" id="qlViewHistory">View Payment History (coming soon)</button>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <button type="button" class="hover:underline text-blue-600 dark:text-blue-400" id="qlManageMethods">Manage Payment Methods (coming soon)</button>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <button type="button" class="hover:underline text-blue-600 dark:text-blue-400" id="qlUpdateContact">Update Contact Info (coming soon)</button>
                    </li>
                </ul>
            </div>

            <!-- Recent Activity (placeholder wired later if needed) -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">Recent Activity</p>
                <ul id="recentPaymentActivity" class="space-y-1.5 text-sm text-gray-700 dark:text-gray-200">
                    <li class="text-xs text-gray-500 dark:text-gray-400">Recent invoices and payments for this account can be surfaced here in a future update.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Customer Info & Detailed Billing Panel (kept for logic, hidden from UI) -->
    <div id="customerInfo" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-4 hidden">
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

<!-- Customer lookup modal -->
<div id="customerSearchModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-8">
    <div class="relative w-full max-w-3xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Find a Customer</h2>
                <p class="text-sm text-gray-500 dark:text-gray-300">Search by account number or name, then click a match to load billing details.</p>
            </div>
            <button type="button" id="closeSearchModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="relative">
                <x-ui.input id="searchModalInput" placeholder="Start typing a customer or account number" class="h-11 text-sm pr-12" />
                <div class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                </div>
            </div>
            <div id="searchModalResultsWrap" class="border border-gray-200 dark:border-gray-700 rounded-xl max-h-80 overflow-y-auto">
                <ul id="searchModalResults" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900 text-sm"></ul>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800">
            <button type="button" id="resetSearchModal" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100">Clear</button>
            <x-primary-button type="button" id="modalCloseDone" class="px-5">Done</x-primary-button>
        </div>
    </div>
</div>

<script>
const prefillAccount = @json($prefillAccount ?? null);
const alertBox = document.getElementById('alertBox');
let currentAccountNo = null;
const searchModal = document.getElementById('customerSearchModal');
const searchModalInput = document.getElementById('searchModalInput');
const searchModalResults = document.getElementById('searchModalResults');
const openSearchModalBtn = document.getElementById('openSearchModal');
const closeSearchModalBtn = document.getElementById('closeSearchModal');
const modalDoneBtn = document.getElementById('modalCloseDone');
const modalResetBtn = document.getElementById('resetSearchModal');
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

function resetCustomerWorkspace(){
    hideSuggest();
    hideResults();

    if (unifiedInput) unifiedInput.value = '';
    if (acctInput) acctInput.value = '';
    if (nameInputHidden) nameInputHidden.value = '';

    const paymentSummary = document.getElementById('paymentSummary');
    if (paymentSummary) paymentSummary.classList.add('hidden');

    const unpaidListCard = document.getElementById('unpaidListCard');
    if (unpaidListCard) unpaidListCard.classList.add('hidden');
    const unpaidTbody = document.getElementById('unpaidTbody');
    if (unpaidTbody) unpaidTbody.innerHTML = '';

    const selectAll = document.getElementById('selectAllBills');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }

    const latestOnlyToggle = document.getElementById('latestOnly');
    if (latestOnlyToggle) latestOnlyToggle.checked = false;

    [
        ['account_no', ''],
        ['customer_name', ''],
        ['customer_address', ''],
        ['classification', ''],
        ['meter_no', ''],
        ['meter_size', ''],
        ['delivery_date', ''],
        ['previous_reading', ''],
        ['current_reading', ''],
        ['calc_subtotal', '₱0.00'],
        ['calc_advance', '₱0.00'],
        ['calc_latefee', '₱0.00'],
        ['total', '₱0.00'],
        ['amount_paid', ''],
        ['change', '₱0.00'],
    ].forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (!el) return;
        if ('value' in el) {
            el.value = value;
        } else {
            el.textContent = value;
        }
    });

    const summaryFields = [
        ['summaryCustomerName', '—'],
        ['unpaidCount', '0'],
        ['latestBillAmount', '₱0.00'],
        ['totalOutstanding', '₱0.00'],
        ['currentDueDate', '—'],
    ];
    summaryFields.forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    });

    window.__unpaidBills = [];
    window.__totalOutstanding = 0;
    window.__latestAmount = 0;
    window.__manualSelectionTotal = 0;
}

// --- Customer Search Functionality ---
// --- Account No. formatter and validator ---
const unifiedInput = document.getElementById('unifiedSearch');
const acctInput = document.getElementById('account_no');
const nameInputHidden = document.getElementById('customer_name');
const suggestBox = document.getElementById('quickSuggest');
const resultsPanel = document.getElementById('searchResults');
const resultsList = document.getElementById('searchResultsList');
const clearResultsBtn = document.getElementById('clearResults');
function formatAcct(v){
    const raw = (v || '').trim();
    const digits = raw.replace(/\D+/g, '');
    if (!digits) return raw;
    if (digits.length <= 2) return digits;
    if (digits.length <= 8) {
        return `${digits.slice(0,2)}-${digits.slice(2)}`;
    }
    const first = digits.slice(0,2);
    const middle = digits.slice(2,8);
    const tail = digits.slice(8);
    return tail ? `${first}-${middle}-${tail}` : `${first}-${middle}`;
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
        let data = null;
        try {
            data = await res.json();
        } catch (_) {
            data = null;
        }
        if (!res.ok) {
            const message = (data && typeof data.error === 'string') ? data.error : 'Customer not found or not eligible for payment.';
            throw new Error(message);
        }
        if (!data || !data.customer) {
            throw new Error('Unexpected response from payment search.');
        }

        // Populate customer info fields
        document.getElementById('account_no').value = data.customer.account_no || '';
        currentAccountNo = data.customer.account_no || null;
        document.getElementById('customer_name').value = data.customer.name || '';
        document.getElementById('customer_address').value = data.customer.address || '';
        document.getElementById('classification').value = (data.customer.classification || '').toString();
        document.getElementById('meter_no').value = data.customer.meter_no || '';
        document.getElementById('meter_size').value = (data.customer.meter_size || '').toString().replace(/\\+/g, '');

        // Set transaction metrics
        const unpaidCount = (data.unpaid_bills || []).length;
        const totalOutstanding = Number(data.total_outstanding || data.payment_details?.total_outstanding || 0);
        const latestBill = data.latest_bill || null;
        const latestAmount = latestBill ? Number(latestBill.total_amount || 0) : 0;
        var unpaidCountEl = document.getElementById('unpaidCount');
        if (unpaidCountEl) unpaidCountEl.innerText = unpaidCount;
        var totalOutstandingEl = document.getElementById('totalOutstanding');
        if (totalOutstandingEl) totalOutstandingEl.innerText = `₱${totalOutstanding.toFixed(2)}`;
        var latestBillAmountEl = document.getElementById('latestBillAmount');
        if (latestBillAmountEl) latestBillAmountEl.innerText = `₱${latestAmount.toFixed(2)}`;

        // Update summary header (customer name)
        var summaryNameEl = document.getElementById('summaryCustomerName');
        if (summaryNameEl) summaryNameEl.textContent = data.customer.name || data.customer.account_no || '—';

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

        // Show due date label in summary card
        var dueLabelEl = document.getElementById('currentDueDate');
        if (dueLabelEl) {
            if (due && !isNaN(due.getTime())) {
                dueLabelEl.textContent = due.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: '2-digit' });
            } else {
                dueLabelEl.textContent = '—';
            }
        }

        const lblMaint = document.getElementById('lblMaintenanceAmount');
        if (lblMaint) lblMaint.textContent = `₱${maint.toFixed(2)}`;
        const chkMaint = document.getElementById('chkMaintenance');
        if (chkMaint) chkMaint.checked = maint > 0; // default include if present

        function peso(v){ return `₱${Number(v||0).toFixed(2)}`; }
        function computeSelectedSum(){
            return Array.from(document.querySelectorAll('.bill-check:checked'))
                .reduce((acc, c) => acc + Number(c.dataset.amount || 0), 0);
        }

        function recalcTotal(){
            const totalInput = document.getElementById('total');
            const latestOnlyChecked = document.getElementById('latestOnly')?.checked;
            const incMaint = (document.getElementById('chkMaintenance') && document.getElementById('chkMaintenance').checked) ? maint : 0;
            const latestTotal = latestBill ? Number(latestBill.total_amount || 0) : Math.max(0, subtotal + incMaint + lateFee - advance);
            const manualSum = computeSelectedSum();
            let totalDue = latestTotal;

            if (!latestOnlyChecked) {
                if (manualSum > 0) {
                    totalDue = manualSum;
                } else if (Number(window.__totalOutstanding || 0) > 0) {
                    totalDue = Number(window.__totalOutstanding || 0);
                }
            }

            window.__manualSelectionTotal = manualSum;

            document.getElementById('calc_subtotal').value = peso(subtotal);
            document.getElementById('calc_advance').value = peso(advance);
            document.getElementById('calc_latefee').value = peso(lateFee);
            totalInput.value = peso(totalDue);
            totalInput.dataset.baseTotal = latestTotal;

            const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
            document.getElementById('change').value = peso(Math.max(0, amountPaid - totalDue));
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
        window.__manualSelectionTotal = 0;

        // Notices and button states
        const paidNotice = document.getElementById('paidNotice');
        const partialWarning = document.getElementById('partialWarning');
        const processBtn = document.getElementById('processPayment');
        paidNotice.classList.toggle('hidden', totalOutstanding > 0);
        processBtn.disabled = totalOutstanding <= 0;
        partialWarning.classList.add('hidden');

        // Reveal payment summary card once data is loaded
        var paymentSummary = document.getElementById('paymentSummary');
        if (paymentSummary) paymentSummary.classList.remove('hidden');

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
        return data;
    } catch (e) {
        const message = (e && typeof e.message === 'string' && e.message.trim()) ? e.message : 'Customer not found.';
        showAlert(message, 'error');
        var paymentSummary = document.getElementById('paymentSummary');
        if (paymentSummary) paymentSummary.classList.add('hidden');
        throw e;
    }
}

function resetUI() {
    const paymentSummary = document.getElementById('paymentSummary');
    if (paymentSummary) paymentSummary.classList.add('hidden');
    const listCard = document.getElementById('unpaidListCard');
    if (listCard) listCard.classList.add('hidden');
    const tbody = document.getElementById('unpaidTbody');
    if (tbody) tbody.innerHTML = '';
    const paidNotice = document.getElementById('paidNotice');
    if (paidNotice) paidNotice.classList.add('hidden');
    const partialWarning = document.getElementById('partialWarning');
    if (partialWarning) partialWarning.classList.add('hidden');
    const processBtn = document.getElementById('processPayment');
    if (processBtn) processBtn.disabled = true;
    const clearResultsBtn = document.getElementById('clearResults');
    if (clearResultsBtn) clearResultsBtn.click();
    const unifiedInput = document.getElementById('unifiedSearch');
    if (unifiedInput) unifiedInput.value = '';
    const acctInput = document.getElementById('account_no');
    if (acctInput) acctInput.value = '';
    const nameInputHidden = document.getElementById('customer_name');
    if (nameInputHidden) nameInputHidden.value = '';
    const suggestBox = document.getElementById('quickSuggest');
    if (suggestBox) suggestBox.classList.add('hidden');
    const resultsPanel = document.getElementById('searchResults');
    if (resultsPanel) resultsPanel.classList.add('hidden');
    const resultsList = document.getElementById('searchResultsList');
    if (resultsList) resultsList.innerHTML = '';
    window.__unpaidBills = [];
    window.__totalOutstanding = 0;
    window.__latestAmount = 0;
    window.__manualSelectionTotal = 0;
    showAlert('Payment successful! Outstanding balance is now ₱0.00.');
}

var searchBtnEl = document.getElementById('searchBtn');
if (searchBtnEl){
    searchBtnEl.addEventListener('click', async () => {
        const unifiedValue = (unifiedInput?.value || '').trim();
        if (unifiedValue) {
            await searchUnified(unifiedValue);
            return;
        }

        const account = (acctInput?.value || '').trim();
        if (account) {
            await searchByAccount(account);
        } else {
            showAlert('Please enter an account number or customer name.', 'error');
        }
    });
}

function hideSuggest(){
    if (!suggestBox) return;
    suggestBox.classList.add('hidden');
    suggestBox.innerHTML = '';
}

function hideResults(){
    if (!resultsPanel || !resultsList) return;
    resultsPanel.classList.add('hidden');
    resultsList.innerHTML = '';
}

function renderResults(customers){
    if (!resultsPanel || !resultsList) return;
    resultsList.innerHTML = '';
    if (!customers.length) {
        resultsList.innerHTML = `
            <li class="px-4 py-6 text-xs text-gray-500 dark:text-gray-400 text-center">
                No active customers with unpaid balances were found for that search. Try a different name or account number.
            </li>
        `;
        resultsPanel.classList.remove('hidden');
        return;
    }
    customers.forEach(customer => {
        const { account_no, name, address, classification, unpaid_count, formatted_unpaid_total, status } = customer;
        const li = document.createElement('li');
        li.className = 'px-4 py-3 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer transition';
        li.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <span class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">${escapeHtml(account_no || '—')}</span>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">${escapeHtml(name || '—')}</p>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 max-w-[360px] truncate">${escapeHtml(address || '—')}</p>
                </div>
                <div class="flex flex-col items-end gap-1 text-xs text-gray-600 dark:text-gray-300">
                    ${buildStatusBadge(status)}
                    <span class="font-semibold text-gray-900 dark:text-gray-100">${formatted_unpaid_total || '₱0.00'}</span>
                    <span>${unpaid_count ? `${unpaid_count} unpaid bill${unpaid_count === 1 ? '' : 's'}` : 'Up to date'}</span>
                </div>
            </div>
        `;
        li.addEventListener('click', async () => {
            if (unifiedInput) unifiedInput.value = account_no || name || '';
            if (acctInput) acctInput.value = account_no || '';
            if (nameInputHidden) nameInputHidden.value = name || '';
            hideSuggest();
            hideResults();
            await searchByAccount(account_no || '');
        });
        resultsList.appendChild(li);
    });
    resultsPanel.classList.remove('hidden');
}

function buildSuggestionItem(customer){
    const { account_no, name, address, classification, unpaid_count, formatted_unpaid_total, status } = customer;
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'w-full text-left px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition focus:outline-none focus:ring-2 focus:ring-blue-400 rounded-lg';
    item.innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <span class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">${escapeHtml(account_no || '—')}</span>
                <p class="text-base font-semibold text-gray-900 dark:text-gray-100">${escapeHtml(name || '—')}</p>
                <p class="text-[12px] text-gray-500 dark:text-gray-400 max-w-[360px] truncate">${escapeHtml(address || '—')}</p>
            </div>
            <div class="flex flex-col items-end gap-1 text-xs text-gray-600 dark:text-gray-300">
                ${buildStatusBadge(status)}
                <span class="font-semibold text-gray-900 dark:text-gray-50">${formatted_unpaid_total || '₱0.00'}</span>
                <span>${unpaid_count ? `${unpaid_count} unpaid bill${unpaid_count === 1 ? '' : 's'}` : 'Up to date'}</span>
            </div>
        </div>
    `;

    item.addEventListener('click', async () => {
        if (unifiedInput) unifiedInput.value = account_no || name || '';
        if (acctInput) acctInput.value = account_no || '';
        if (nameInputHidden) nameInputHidden.value = name || '';
        hideSuggest();
        hideResults();
        await searchByAccount(account_no || '');
    });

    return item;
}

function renderSuggestions(customers){
    if (!suggestBox) return;
    suggestBox.innerHTML = '';
    if (!customers.length) {
        suggestBox.innerHTML = `
            <div class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                No active customers with unpaid balances match that search yet.
            </div>
        `;
        suggestBox.classList.remove('hidden');
        return;
    }
    customers.forEach(customer => suggestBox.appendChild(buildSuggestionItem(customer)));
    suggestBox.classList.remove('hidden');
}

function escapeHtml(s){
    return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));
}

function buildStatusBadge(status){
    const label = (status || '').trim() || 'Unspecified';
    const tone = label.toLowerCase();
    let classes = 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-200';
    if (tone === 'active') classes = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    else if (tone.includes('disconnect')) classes = 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300';
    else if (tone === 'pending') classes = 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold ${classes}">${escapeHtml(label)}</span>`;
}

async function fetchCustomers(query){
    if (!query || query.length < 2) return [];
    const url = new URL(`{{ route('api.payment.quick-search') }}`, window.location.origin);
    url.searchParams.set('q', query);
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) return [];
    const data = await res.json();
    return data.results || [];
}

async function quickSuggest(query){
    if (!suggestBox) return;
    const customers = await fetchCustomers(query);
    if (!customers.length) {
        hideSuggest();
        return;
    }
    renderSuggestions(customers);
}

async function searchUnified(value){
    const trimmed = (value || '').trim();
    if (!trimmed) {
        showAlert('Please enter an account number or customer name.', 'error');
        return;
    }

    // If it looks like an account number, go straight to search
    if (/^[0-9]{2}-[0-9]{6}-[0-9]$/i.test(trimmed) || /^[0-9]{9}$/.test(trimmed.replace(/\D+/g,''))) {
        if (acctInput) acctInput.value = formatAcct(trimmed);
        hideSuggest();
        await searchByAccount(formatAcct(trimmed));
        return;
    }

    // Otherwise, treat as name search
    const matches = await fetchCustomers(trimmed);
    if (!matches.length) {
        hideSuggest();
        showAlert('No customer found for that search.', 'error');
        return;
    }
    if (matches.length === 1) {
        const customer = matches[0];
        if (acctInput) acctInput.value = customer.account_no || '';
        if (nameInputHidden) nameInputHidden.value = customer.name || '';
        if (unifiedInput) unifiedInput.value = customer.account_no || customer.name || '';
        hideSuggest();
        await searchByAccount(customer.account_no || '');
        return;
    }
    renderSuggestions(matches);
}

function escapeHtml(s){
    return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));
}

function buildStatusBadge(status){
    const label = (status || '').trim() || 'Unspecified';
    const tone = label.toLowerCase();
    let classes = 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-200';
    if (tone === 'active') classes = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    else if (tone.includes('disconnect')) classes = 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300';
    else if (tone === 'pending') classes = 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold ${classes}">${escapeHtml(label)}</span>`;
}

function openSearchModal(){
    if (!searchModal) return;
    searchModal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    const seed = (unifiedInput?.value || '').trim();
    if (searchModalInput) {
        searchModalInput.value = seed;
        setTimeout(() => searchModalInput.focus(), 50);
        if (seed.length >= 2) {
            triggerModalSearch(seed);
        } else {
            renderModalResults([]);
        }
    }
}

function closeSearchModal(){
    if (!searchModal) return;
    searchModal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

let modalDebounce;
function triggerModalSearch(query){
    clearTimeout(modalDebounce);
    modalDebounce = setTimeout(async () => {
        const customers = await fetchCustomers(query);
        renderModalResults(customers);
    }, 200);
}

if (openSearchModalBtn){
    openSearchModalBtn.addEventListener('click', openSearchModal);
}
if (closeSearchModalBtn){
    closeSearchModalBtn.addEventListener('click', closeSearchModal);
}
if (modalDoneBtn){
    modalDoneBtn.addEventListener('click', closeSearchModal);
}
if (modalResetBtn){
    modalResetBtn.addEventListener('click', () => {
        if (searchModalInput) searchModalInput.value = '';
        renderModalResults([]);
    });
}
if (searchModalInput){
    searchModalInput.addEventListener('input', (e) => {
        const value = (e.target.value || '').trim();
        if (value.length >= 2) {
            triggerModalSearch(value);
        } else {
            renderModalResults([]);
        }
    });
    searchModalInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = (searchModalInput.value || '').trim();
            if (value.length >= 2) {
                triggerModalSearch(value);
            }
        }
    });
}

if (searchModal){
    searchModal.addEventListener('click', (event) => {
        if (event.target === searchModal) {
            closeSearchModal();
        }
    });
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && searchModal && !searchModal.classList.contains('hidden')) {
        closeSearchModal();
    }
});

async function loadPrefillAccount(account){
    const unifiedField = document.getElementById('unifiedSearch');
    const normalizedInput = String(account || '').trim();
    const candidates = Array.from(new Set([
        normalizedInput,
        formatAcct(normalizedInput),
        normalizedInput.replace(/\s+/g, ''),
    ])).filter(Boolean);

    if (unifiedField) unifiedField.value = candidates[0];
    if (acctInput) acctInput.value = candidates[0];

    for (const candidate of candidates) {
        if (!candidate) continue;
        try {
            await searchByAccount(candidate);
            return true;
        } catch (primaryError) {
            console.warn('Direct lookup failed for prefill candidate', candidate, primaryError);
            try {
                const matches = await fetchCustomers(candidate);
                if (matches.length) {
                    const first = matches[0];
                    const fallbackAccount = first.account_no || candidate;
                    if (unifiedField) unifiedField.value = first.account_no || first.name || fallbackAccount;
                    if (acctInput) acctInput.value = fallbackAccount;
                    await searchByAccount(fallbackAccount);
                    return true;
                }
            } catch (secondaryError) {
                console.error('Prefill fallback search failed', secondaryError);
            }
        }
    }
    console.error('Prefill account lookup failed for all candidates', candidates);
    return false;
}

// Quick search: auto-trigger when a valid account number is typed (debounced)
let acctDebounce;
let nameDebounce;
if (acctInput){
    acctInput.addEventListener('input', () => {
        clearTimeout(acctDebounce);
        const account = (acctInput.value || '').trim();
        if (isValidAcct(account)) {
            acctDebounce = setTimeout(() => searchByAccount(account), 400);
            hideSuggest();
        } else {
            const nameHint = nameInputHidden ? nameInputHidden.value.trim() : '';
            if ((account || '').replace(/\D+/g,'').length >= 2 || account.length >= 2) {
                acctDebounce = setTimeout(() => quickSuggest(account, nameHint), 250);
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

if (unifiedInput){
    unifiedInput.addEventListener('input', () => {
        clearTimeout(nameDebounce);
        const value = (unifiedInput.value || '').trim();
        if (value.length >= 2) {
            nameDebounce = setTimeout(() => quickSuggest(value), 250);
        } else {
            hideSuggest();
            hideResults();
        }
    });

    unifiedInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = (unifiedInput.value || '').trim();
            if (value) {
                searchUnified(value);
            }
        }
    });

    unifiedInput.addEventListener('focus', () => {
        const value = (unifiedInput.value || '').trim();
        if (value.length >= 2) {
            quickSuggest(value);
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
    const totalField = document.getElementById('total');
    const latestToggle = document.getElementById('latestOnly');
    const useLatest = latestToggle ? latestToggle.checked : false;
    const sum = Array.from(document.querySelectorAll('.bill-check:checked'))
        .reduce((acc, c) => acc + Number(c.dataset.amount || 0), 0);

    window.__manualSelectionTotal = useLatest ? 0 : sum;

    const baseTotal = Number(totalField?.dataset.baseTotal || 0);
    let displayedTotal = baseTotal;

    if (!useLatest) {
        if (sum > 0) {
            displayedTotal = sum;
            document.getElementById('partialWarning').classList.add('hidden');
        } else {
            displayedTotal = Number(window.__totalOutstanding || baseTotal || 0);
        }
    }

    if (totalField) totalField.value = `₱${Number(displayedTotal || 0).toFixed(2)}`;

    const tenderEl = document.getElementById('amount_paid');
    if (tenderEl) {
        tenderEl.dispatchEvent(new Event('input'));
    }
}

// Submit payment to API and open receipt, then refresh unpaid bills
document.getElementById('processPayment').addEventListener('click', async () => {
    const accountNo = (document.getElementById('account_no').value || currentAccountNo || '').trim();
    const totalRaw = document.getElementById('total').value || '0';
    const totalAmount = parseFloat(String(totalRaw).replace(/[^0-9.-]+/g, '')) || 0;
    const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
    if (window.__totalOutstanding <= 0) return showAlert('This account is already fully paid. Refresh or search another account.', 'error');
    if (!amountPaid) return showAlert('Please enter payment amount.', 'error');
    if (amountPaid < totalAmount) return showAlert('Amount tendered must at least cover the selected bills.', 'error');

    // collect selection
    const latestToggle = document.getElementById('latestOnly');
    const latestOnly = !!(latestToggle && latestToggle.checked);
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

        if (accountNo) {
            try {
                const refreshed = await searchByAccount(accountNo);
                const updatedOutstanding = refreshed && typeof refreshed.total_outstanding !== 'undefined'
                    ? Number(refreshed.total_outstanding || 0)
                    : Number(refreshed?.payment_details?.total_outstanding || 0);

                const paidNotice = document.getElementById('paidNotice');
                const processBtn = document.getElementById('processPayment');
                if (!isNaN(updatedOutstanding)) {
                    window.__totalOutstanding = updatedOutstanding;
                    const totalOutstandingEl = document.getElementById('totalOutstanding');
                    if (totalOutstandingEl) totalOutstandingEl.textContent = `₱${updatedOutstanding.toFixed(2)}`;
                    if (paidNotice) paidNotice.classList.toggle('hidden', updatedOutstanding > 0);
                    if (processBtn) processBtn.disabled = updatedOutstanding <= 0;
                }

                if (updatedOutstanding <= 0) {
                    resetCustomerWorkspace();
                    showAlert('Payment processed successfully! Outstanding balance is now ₱0.00.');
                } else {
                    updateManualSelectionTotal();
                    showAlert('Payment processed successfully! Outstanding balance updated.');
                }
            } catch(err) {
                showAlert('Payment completed, but failed to refresh customer data automatically. Please search again.', 'warning');
                resetCustomerWorkspace();
            }
        } else {
            resetCustomerWorkspace();
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
        ensureApplicantDirectoryLoaded().then(() => {
            refreshApplicantDirectory();
            if (!__currentApplication && __applicantDirectoryCache.length){
                selectApplicantFromDirectory(__applicantDirectoryCache[0].id);
            }
        });
    };
}

// Initialize tab from URL (?tab=app) or hash (#applicant-fees)
 function initTabFromUrl(){
     try {
         var params = new URLSearchParams(window.location.search);
        var hash = window.location.hash || '';
        if ((params.get('tab')||'') === 'app' || hash === '#applicant-fees') {
           showTab('app');
           ensureApplicantDirectoryLoaded().then(() => {
               refreshApplicantDirectory();
               if (!__currentApplication && __applicantDirectoryCache.length){
                   selectApplicantFromDirectory(__applicantDirectoryCache[0].id);
               }
           });
        } else {
            showTab('cust');
        }
    } catch(_) { showTab('cust'); }
}
initTabFromUrl();
window.addEventListener('hashchange', initTabFromUrl);
window.addEventListener('popstate', initTabFromUrl);

async function initPrefillIfNeeded(){
    if (!prefillAccount) return;
    try {
        const success = await loadPrefillAccount(prefillAccount);
        if (!success) {
            showAlert('Unable to auto-load the selected customer. Please search manually.', 'warning');
        }
    } catch (error) {
        console.error('Failed to prefill payment search', error);
    }
}

if (prefillAccount){
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPrefillIfNeeded);
    } else {
        initPrefillIfNeeded();
    }
}

// --- Applicant fee directory & payment flow ---
const feesCard = document.getElementById('feesCard');
const feesPanel = document.getElementById('feesPayment');
const applicantSearchInput = document.getElementById('applicantSearchInput');
const applicantDirectoryBody = document.getElementById('applicantDirectoryBody');
const feesDetailsList = document.getElementById('feesDetailsList');
const feesSummaryList = document.getElementById('feesSummaryList');
const feesSubtotalInput = document.getElementById('feesSubtotal');
const feesAmountPaidInput = document.getElementById('feesAmountPaid');
const feesChangeInput = document.getElementById('feesChange');

let __currentApplication = null;
let __currentInvoice = null;
let __lastSavedInvoice = null;
let __selectedFeeItems = [];
let __currentAmountDue = 0;
let __currentTender = 0;
let __currentChange = 0;
let __applicantDirectoryCache = [];
let __activeApplicantId = null;

function peso(n){
    return '₱' + (Number(n || 0)).toFixed(2);
}

function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    })[ch]);
}

function debounce(fn, delay = 200){
    let timer;
    return function(...args){
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

function markActiveApplicantRow(){
    if (!applicantDirectoryBody) return;
    applicantDirectoryBody.querySelectorAll('tr[data-applicant-row]').forEach(row => {
        const same = Number(row.dataset.applicantRow) === Number(__activeApplicantId);
        row.classList.toggle('bg-blue-50', same);
        row.classList.toggle('dark:bg-blue-900/40', same);
        row.setAttribute('aria-selected', same ? 'true' : 'false');
    });
}

function renderApplicantDirectory(rows){
    if (!applicantDirectoryBody) return;
    if (!rows.length){
        let blanks = '';
        for (let i = 0; i < 10; i++){
            blanks += '<tr class="h-12"><td colspan="4" class="px-4 py-2"><div class="h-6"></div></td></tr>';
        }
        applicantDirectoryBody.innerHTML = blanks;
        return;
    }

    const html = rows.map(item => {
        const status = (item.status || '—').toString().replace(/_/g, ' ');
        return `
            <tr data-applicant-row="${item.id}" tabindex="0" class="cursor-pointer transition hover:bg-blue-50 dark:hover:bg-blue-900/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-900 dark:text-gray-100">${escapeHtml(item.application_code || `APP-${item.id || 'NEW'}`)}</div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-2 items-center">
                        <span>#${escapeHtml(item.id ?? '—')}</span>
                        ${item.account_no ? `<span class="inline-flex px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-200 text-[10px] uppercase">${escapeHtml(item.account_no)}</span>` : ''}
                    </p>
                </td>
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-900 dark:text-gray-100">${escapeHtml(item.applicant_name || '—')}</p>
                    ${item.contact_no ? `<p class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.contact_no)}</p>` : ''}
                </td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">${escapeHtml(item.address || '—')}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">${escapeHtml(status)}</span>
                </td>
            </tr>
        `;
    }).join('');

    let fillers = '';
    if (rows.length < 10){
        for (let i = rows.length; i < 10; i++){
            fillers += '<tr class="h-12"><td colspan="4" class="px-4 py-2"><div class="h-6"></div></td></tr>';
        }
    }

    applicantDirectoryBody.innerHTML = html + fillers;
    markActiveApplicantRow();
}

function normalizeSearchTerm(value){
    return (value || '').toString().trim().toLowerCase();
}

function normalizeAccountTerm(value){
    return (value || '').toString().replace(/[^0-9a-z]/gi, '').toLowerCase();
}

function filterApplicants(list, term){
    const normalized = normalizeSearchTerm(term);
    const accountTerm = normalizeAccountTerm(term);
    if (!normalized && !accountTerm) return list;
    return list.filter(item => {
        const fields = [
            item.application_code,
            item.applicant_name,
            item.address,
            item.contact_no,
            item.account_no,
            item.id ? String(item.id) : '',
        ];

        const matchesText = normalized ? fields.some(value => value && value.toString().toLowerCase().includes(normalized)) : false;
        const matchesAccount = accountTerm ? fields.some(value => value && normalizeAccountTerm(value).includes(accountTerm)) : false;

        return matchesText || matchesAccount;
    });
}

function normalizeApplication(item){
    if (!item) return null;
    const id = Number(item.id || item.ID || 0);
    return {
        ...item,
        id,
        application_code: item.application_code || (id ? `APP-${String(id).padStart(6, '0')}` : 'APP-NEW'),
        account_no: item.account_no || item.accountNumber || null,
    };
}

async function ensureApplicantDirectoryLoaded(force = false){
    if (__applicantDirectoryCache.length && !force) return __applicantDirectoryCache;
    try {
        const url = new URL('{{ route('connections.index') }}', window.location.origin);
        url.searchParams.set('per_page', '100');
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Failed to fetch applicants');
        const payload = await res.json();
        let list = [];
        if (payload && payload.items){
            if (Array.isArray(payload.items)) {
                list = payload.items;
            } else if (payload.items && Array.isArray(payload.items.data)) {
                list = payload.items.data;
            }
        }
        __applicantDirectoryCache = list.map(normalizeApplication).filter(Boolean);
    } catch (error) {
        console.error(error);
        __applicantDirectoryCache = [];
    }
    return __applicantDirectoryCache;
}

async function refreshApplicantDirectory(force = false){
    await ensureApplicantDirectoryLoaded(force);
    const term = (applicantSearchInput?.value || '').trim();
    const matches = filterApplicants(__applicantDirectoryCache, term).slice(0, 10);
    renderApplicantDirectory(matches);
}

async function selectApplicantFromDirectory(id){
    if (!id) return;
    __activeApplicantId = id;
    markActiveApplicantRow();
    await loadApplicantFeesById(id);
}

function setFeeDetails(feeItems, status){
    const sourceItems = Array.isArray(feeItems) && feeItems.length ? feeItems : [{ code: 'APPF', name: 'Applicant Fee', amount: 300 }];
    __selectedFeeItems = sourceItems.filter(item => Number(item.amount || 0) > 0).map(item => ({
        code: item.code || 'APPF',
        name: item.name || 'Applicant Fee',
        amount: Number(item.amount || 0),
    }));

    if (feesDetailsList){
        if (!__selectedFeeItems.length){
            feesDetailsList.innerHTML = '<li class="text-gray-400 dark:text-gray-500">Applicant fee not assessed yet.</li>';
        } else {
            const statusLine = status ? `<li class="text-xs text-gray-500 dark:text-gray-400 pt-1">Current status: ${escapeHtml(status.toString().replace(/_/g, ' '))}</li>` : '';
            feesDetailsList.innerHTML = __selectedFeeItems.map(item => `
                <li class="flex items-center justify-between">
                    <span>${escapeHtml(item.name)}</span>
                    <span class="font-semibold">${peso(item.amount)}</span>
                </li>
            `).join('') + statusLine;
        }
    }

    if (feesCard) feesCard.classList.remove('hidden');
    if (feesPanel) feesPanel.classList.remove('hidden');

    calcFeesSubtotal();
}

function calcFeesSubtotal(){
    const subtotal = __selectedFeeItems.reduce((total, item) => total + Number(item.amount || 0), 0);
    __currentAmountDue = subtotal;
    if (feesSubtotalInput) feesSubtotalInput.value = peso(subtotal);

    const tender = Number(feesAmountPaidInput?.value || 0);
    __currentTender = tender;
    __currentChange = Math.max(0, tender - subtotal);
    if (feesChangeInput) feesChangeInput.value = peso(__currentChange);

    updateLiveSummary(subtotal, tender);
}

function updateLiveSummary(amountDue, tendered){
    const statusEl = document.getElementById('feesSummaryStatus');
    const dueEl = document.getElementById('feesSummaryDue');
    const tenderEl = document.getElementById('feesSummaryTender');
    const changeEl = document.getElementById('feesSummaryChange');
    const invoiceEl = document.getElementById('feesInvoiceDisplay');

    if (dueEl) dueEl.textContent = peso(amountDue || 0);
    if (tenderEl) tenderEl.textContent = peso(tendered || 0);
    if (changeEl) changeEl.textContent = peso(Math.max(0, tendered - amountDue));

    if (!__selectedFeeItems.length){
        if (feesSummaryList) feesSummaryList.innerHTML = '<li class="text-gray-400 dark:text-gray-500">No fees selected yet.</li>';
        if (statusEl) statusEl.textContent = __lastSavedInvoice ? `Last invoice: ${__lastSavedInvoice}` : 'Awaiting assessed fees';
        __currentInvoice = null;
        if (invoiceEl) invoiceEl.textContent = __lastSavedInvoice || '—';
        return;
    }

    if (!__currentInvoice){
        __currentInvoice = generateApplicantInvoice();
    }

    if (feesSummaryList){
        feesSummaryList.innerHTML = __selectedFeeItems.map(item => `
            <li class="flex items-center justify-between">
                <span>${escapeHtml(item.name)}</span>
                <span class="font-semibold">${peso(item.amount)}</span>
            </li>
        `).join('');
    }

    if (invoiceEl) invoiceEl.textContent = __currentInvoice;
    if (statusEl){
        if (tendered + 0.01 < amountDue){
            const remaining = peso(amountDue - tendered);
            statusEl.textContent = `Need ${remaining} more to proceed`;
        } else {
            statusEl.textContent = 'Ready to process payment';
        }
    }
}

function generateApplicantInvoice(){
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const stamp = `${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
    const suffix = Math.random().toString(36).substring(2, 6).toUpperCase();
    return `APP-${stamp}-${suffix}`;
}

function hydrateApplicantPayment(app){
    __currentApplication = app;
    __activeApplicantId = Number(app.id || 0);
    const codeInput = document.getElementById('app_no');
    const nameInput = document.getElementById('applicant_name');
    const statusInput = document.getElementById('app_status');
    const code = app.application_code || (app.id ? `APP-${String(app.id)}` : 'APP-NEW');
    if (codeInput) codeInput.value = code;
    if (nameInput) nameInput.value = app.applicant_name || '—';
    if (statusInput) statusInput.value = (app.status || '').toString();

    __lastSavedInvoice = app.payment_receipt_no || __lastSavedInvoice || null;
    __currentInvoice = null;

    const amount = Number(app.fee_application ?? app.fee_inspection ?? 0) || 0;
    const feeItems = amount > 0 ? [{ code: 'APPF', name: 'Applicant Fee', amount }] : [];
    setFeeDetails(feeItems, app.status || '');
    markActiveApplicantRow();
}

async function loadApplicantFeesById(id){
    if (!id) return;
    try {
        const res = await fetch(`/api/connections/${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (!res.ok || !data.ok || !data.application) throw new Error('Application not found');
        const app = normalizeApplication(data.application);
        if (!app) return;

        const existingIndex = __applicantDirectoryCache.findIndex(item => Number(item.id) === Number(app.id));
        if (existingIndex >= 0){
            __applicantDirectoryCache[existingIndex] = app;
        } else {
            __applicantDirectoryCache.unshift(app);
        }
        refreshApplicantDirectory();
        hydrateApplicantPayment(app);
    } catch (error) {
        alert('Unable to load applicant fees for that application number.');
    }
}

if (applicantDirectoryBody){
    applicantDirectoryBody.addEventListener('click', (event) => {
        const row = event.target.closest('[data-applicant-row]');
        if (!row) return;
        const id = Number(row.dataset.applicantRow);
        if (!id || isNaN(id)) return;
        selectApplicantFromDirectory(id);
    });

    applicantDirectoryBody.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' '){
            const row = event.target.closest('[data-applicant-row]');
            if (!row) return;
            event.preventDefault();
            const id = Number(row.dataset.applicantRow);
            if (!id || isNaN(id)) return;
            selectApplicantFromDirectory(id);
        }
    });
}

if (applicantSearchInput){
    applicantSearchInput.addEventListener('focus', () => { refreshApplicantDirectory(); }, { once: true });
    applicantSearchInput.addEventListener('input', debounce(() => { refreshApplicantDirectory(); }, 200));
}

if (feesAmountPaidInput){
    feesAmountPaidInput.addEventListener('input', calcFeesSubtotal);
}

const feesProcessBtn = document.getElementById('feesProcessBtn');
if (feesProcessBtn){
    feesProcessBtn.addEventListener('click', async function(){
        try {
            if (!__currentApplication || !__currentApplication.id){
                alert('No application selected.');
                return;
            }
            if (!__selectedFeeItems.length){
                alert('No assessed fees available for this applicant.');
                return;
            }

            const amountDue = Number(__currentAmountDue || 0);
            if (amountDue <= 0){
                alert('Subtotal must be greater than zero.');
                return;
            }

            const amountTendered = Number(feesAmountPaidInput?.value || 0);
            if (!amountTendered){
                alert('Enter the amount tendered by the applicant.');
                return;
            }
            if (amountTendered + 0.01 < amountDue){
                alert('Amount tendered is insufficient to cover the applicant fee.');
                return;
            }

            const change = Math.max(0, amountTendered - amountDue);
            const payload = {
                amount_due: Number(amountDue.toFixed(2)),
                amount_tendered: Number(amountTendered.toFixed(2)),
                change_given: Number(change.toFixed(2)),
                fee_items: __selectedFeeItems.map(item => ({
                    code: item.code,
                    name: item.name,
                    amount: Number(Number(item.amount || 0).toFixed(2)),
                })),
            };

            feesProcessBtn.disabled = true;
            feesProcessBtn.classList.add('opacity-60', 'pointer-events-none');

            const res = await fetch(`/api/connections/${__currentApplication.id}/pay`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || !data.ok){
                const serverMsg = data.message || data.error || (data.errors ? JSON.stringify(data.errors) : '');
                throw new Error(serverMsg || `Failed to process applicant fees payment (HTTP ${res.status})`);
            }

            __lastSavedInvoice = data.invoice_number || __currentInvoice || __lastSavedInvoice;
            __currentInvoice = null;
            alert(`Applicant fee captured. Invoice ${__lastSavedInvoice}.`);

            if (feesAmountPaidInput) feesAmountPaidInput.value = '';
            if (feesChangeInput) feesChangeInput.value = '₱0.00';

            await ensureApplicantDirectoryLoaded(true);
            await refreshApplicantDirectory();
            if (__currentApplication && __currentApplication.id){
                await loadApplicantFeesById(__currentApplication.id);
            }
        } catch (err){
            alert(String(err.message || err));
        } finally {
            feesProcessBtn.disabled = false;
            feesProcessBtn.classList.remove('opacity-60', 'pointer-events-none');
        }
    });
}

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
            selectApplicantFromDirectory(id);
        }
    });
}
</script>
@endsection

