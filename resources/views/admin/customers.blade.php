@extends('layouts.admin')

@section('title', 'Admin • Customer Data Management')

@section('content')
<div x-data="{ selected:new Set(), all:false, drawer:false }" class="w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 font-[Poppins] space-y-6">
    @php
        $advancedFiltersOpen = filled($filters['classification'] ?? null) || filled($filters['created'] ?? null);
    @endphp

    <!-- Replace Meter Modal -->
    <div id="replaceMeterModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Replace Meter</h3>
                <button id="rmClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="hidden" id="rmAccountId" />
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Customer</label>
                    <input id="rmAccountName" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-sm" readonly />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Current Meter</label>
                    <input id="rmCurrent" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-sm" readonly />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Unassign Date</label>
                        <input id="rmUnassignDate" type="date" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Reason</label>
                        <input id="rmReason" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" placeholder="Replacement" />
                    </div>
                </div>
                <div class="border-t pt-3 mt-1 border-gray-200 dark:border-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Select New Meter (Inventory)</div>
                    <div class="flex items-center gap-2 mb-2">
                        <input id="rmSearch" type="text" placeholder="Search serial or address" class="flex-1 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                        <button id="rmSearchBtn" class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Search</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">New Meter</label>
                            <select id="rmMeter" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm"></select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Assign Date</label>
                            <input id="rmAssignDate" type="date" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="rmSave" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Replace</button>
            </div>
        </div>
    </div>

    <!-- Transfer Meter Modal -->
    <div id="transferMeterModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg" data-transfer-modal-content>
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Transfer Meter Ownership</h3>
                <button id="tmClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="hidden" id="tmAccountId" />
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Current Customer</label>
                    <input id="tmAccountName" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-sm" readonly />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Meter Serial</label>
                    <input id="tmMeterSerial" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-sm" readonly />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">New Account Owner</label>
                    <input id="tmNewName" type="text" placeholder="Full name" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">New Contact No.</label>
                        <input id="tmNewContact" type="text" placeholder="09xx…" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Transfer Date</label>
                        <input id="tmDate" type="date" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Reason / Notes</label>
                    <textarea id="tmNotes" rows="2" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" placeholder="Describe why the ownership is being transferred"></textarea>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="tmSave" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Transfer</button>
            </div>
        </div>
    </div>

    <!-- Assess Modal -->
    <div id="assessModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Assess Fees</h3>
                <button id="assessClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="hidden" id="assessId" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Application Fee</label>
                        <input id="fee_application" type="number" step="0.01" min="0" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Inspection Fee</label>
                        <input id="fee_inspection" type="number" step="0.01" min="0" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Materials</label>
                        <input id="fee_materials" type="number" step="0.01" min="0" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Labor</label>
                        <input id="fee_labor" type="number" step="0.01" min="0" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Meter Deposit</label>
                        <input id="meter_deposit" type="number" step="0.01" min="0" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between border-t pt-3 mt-1 border-gray-200 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total</span>
                        <span id="fee_total_display" class="text-base font-semibold text-gray-900 dark:text-gray-100">₱0.00</span>
                    </div>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="assessSave" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Save</button>
            </div>
        </div>
    </div>

    <!-- Pay Modal -->
    <div id="payModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Mark Payment</h3>
                <button id="payClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="hidden" id="payId" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Applicant</div>
                        <div id="payApplicant" class="text-sm font-medium text-gray-800 dark:text-gray-100">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
                        <div id="payStatus" class="text-xs inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">—</div>
                    </div>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-md p-3">
                    <div class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">Fees</div>
                    <div id="payFeesList" class="text-sm text-gray-700 dark:text-gray-200 space-y-1">
                        <div class="flex items-center justify-between"><span>Application</span><span id="pFeeApp">—</span></div>
                        <div class="flex items-center justify-between"><span>Inspection</span><span id="pFeeInsp">—</span></div>
                        <div class="flex items-center justify-between border-t pt-2 mt-1"><span class="font-medium">Total</span><span id="pFeeTotal" class="font-semibold">—</span></div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Receipt No.</label>
                    <input id="receipt_no" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" placeholder="e.g. OR-000123" />
                </div>
                <div class="text-xs text-gray-500">
                    <a id="payModuleLink" href="/payment" target="_blank" class="text-blue-600">Open Payment module</a>
                </div>
                <div id="payHint" class="text-xs text-amber-600 hidden">Payment is available only when status is Assessed and role is Cashier/Admin.</div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-2">
                <button id="paySave" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Save</button>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div id="scheduleModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Schedule Installation</h3>
                <button id="scheduleClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="hidden" id="schedId" />
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Schedule Date</label>
                    <input id="schedule_date" type="date" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="scheduleSave" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Save</button>
            </div>
        </div>
    </div>

    <!-- Install Modal -->
    <div id="installModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Confirm Installation</h3>
                <button id="installClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="hidden" id="installId" />
                <p class="text-sm text-gray-600 dark:text-gray-300">Mark this application as Installed?</p>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="installSave" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Full Address Modal for New Connection Applications -->
    <div id="ncAddressModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Full Address</h3>
                <button id="ncAddressClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4">
                <p id="ncAddressText" class="text-sm text-gray-800 dark:text-gray-100 break-words"></p>
            </div>
            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="ncAddressCloseBtn" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Close</button>
            </div>
        </div>
    </div>

    <!-- Application Info Modal -->
    <div id="appInfoModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-xl">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Application Details</h3>
                <button id="appInfoClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-4 text-sm">
                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Application No.</div>
                        <div id="ai_id" class="font-mono text-gray-900 dark:text-gray-100">—</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Status</div>
                        <div id="ai_status" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-gray-100 text-gray-700">—</div>
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Applicant</div>
                        <div id="ai_name" class="font-medium text-gray-900 dark:text-gray-100">—</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Contact No.</div>
                        <div id="ai_contact" class="text-gray-800 dark:text-gray-200">—</div>
                    </div>
                </div>
                <div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Address</div>
                    <div id="ai_address" class="text-gray-800 dark:text-gray-200">—</div>
                </div>
                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Fee Total</div>
                        <div id="ai_fee_total" class="font-semibold text-gray-900 dark:text-gray-100">—</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Paid At</div>
                        <div id="ai_paid_at" class="text-gray-800 dark:text-gray-200">—</div>
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Schedule Date</div>
                        <div id="ai_schedule" class="text-gray-800 dark:text-gray-200">—</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Installed At</div>
                        <div id="ai_installed_at" class="text-gray-800 dark:text-gray-200">—</div>
                    </div>
                </div>
                <div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Notes</div>
                    <div id="ai_notes" class="text-gray-800 dark:text-gray-200 text-xs">—</div>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="appInfoCloseBtn" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="adminEditCustomerModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Edit Customer</h3>
                <button id="aecClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="hidden" id="aecId" />
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Name</label>
                    <input id="aecName" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Address</label>
                    <input id="aecAddress" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Contact No.</label>
                    <input id="aecContact" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="aecSave" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Save</button>
            </div>
        </div>
    </div>

    <script>
    (function(){
        function toast(msg, type){
            try { if (typeof window.showToast === 'function') { window.showToast(msg, type||'info'); return; } } catch(_){ }
            alert(msg);
        }
        const modal = document.getElementById('adminEditCustomerModal');
        const btnClose = document.getElementById('aecClose');
        const btnSave = document.getElementById('aecSave');
        const fId = document.getElementById('aecId');
        const fName = document.getElementById('aecName');
        const fAddress = document.getElementById('aecAddress');
        const fContact = document.getElementById('aecContact');

        const drawer = document.getElementById('customerDrawer');
        const drawerClose = document.getElementById('drawerClose');
        const drawerCloseFooter = document.getElementById('drawerCloseFooter');
        const drawerName = document.getElementById('drawerName');
        const drawerAccount = document.getElementById('drawerAccount');
        const drawerStatus = document.getElementById('drawerStatus');
        const drawerConnection = document.getElementById('drawerConnection');
        const drawerCreated = document.getElementById('drawerCreated');
        const drawerAddress = document.getElementById('drawerAddress');
        const drawerContact = document.getElementById('drawerContact');
        const drawerExtra = document.getElementById('drawerExtra');
        const drawerAudit = document.getElementById('drawerAudit');
        const drawerAuditLink = document.getElementById('drawerAuditLink');
        const drawerTransferBtn = document.getElementById('drawerTransferBtn');
        const drawerReplaceBtn = document.getElementById('drawerReplaceBtn');

        function openModal(){ if (modal){ modal.classList.remove('hidden'); modal.classList.add('flex'); } }
        function closeModal(){ if (modal){ modal.classList.add('hidden'); modal.classList.remove('flex'); } }

        function openDrawer(){ if (drawer){ drawer.classList.remove('translate-x-full'); } }
        function closeDrawer(){ if (drawer){ drawer.classList.add('translate-x-full'); } }

        function statusBadgeClass(status){
            const s = String(status||'').toLowerCase();
            if (s === 'active') return 'bg-green-100 text-green-700';
            if (s === 'inactive' || s === 'disconnected') return 'bg-red-100 text-red-700';
            return 'bg-yellow-100 text-yellow-700';
        }

        async function loadCustomerAndShow(id){
            if (!id || !drawer) return;
            try{
                if (drawerAccount) drawerAccount.textContent = 'Loading…';
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/api/customer/${id}`, { headers:{ 'Accept':'application/json','X-CSRF-TOKEN': token || '' } });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json().catch(()=>({}));
                const cust = data.customer || data || {};
                if (drawerAccount) drawerAccount.textContent = cust.account_no || '—';
                if (drawerName) drawerName.textContent = cust.name || '—';
                if (drawerContact) drawerContact.textContent = cust.contact_no || '—';
                if (drawerAddress) drawerAddress.textContent = cust.address || '—';
                if (drawerConnection) drawerConnection.textContent = cust.classification || '—';
                if (drawerCreated) drawerCreated.textContent = (cust.created_at || '').toString().slice(0,10) || '—';
                if (drawerStatus){
                    drawerStatus.textContent = cust.status || '—';
                    drawerStatus.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] ' + statusBadgeClass(cust.status);
                }
                if (drawerExtra){
                    const entries = Object.entries(cust || {});
                    const skip = new Set(['id','account_no','name','address','contact_no','classification','status','created_at','updated_at']);
                    const rows = entries
                        .filter(([k,v]) => !skip.has(k) && v !== null && v !== undefined && String(v) !== '')
                        .map(([k,v]) => {
                            const label = k.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
                            let val = v;
                            if (typeof v === 'object'){
                                try { val = JSON.stringify(v); } catch(_) { val = '[object]'; }
                            }
                            return `<div class="flex justify-between gap-2 py-0.5"><span class="text-gray-500">${label}</span><span class="text-gray-800 dark:text-gray-200 text-right">${String(val)}</span></div>`;
                        });
                    drawerExtra.innerHTML = rows.length ? rows.join('') : '<div class="text-gray-400">No additional data.</div>';
                }
                if (drawerAudit){
                    const history = Array.isArray(data.audit || []) ? data.audit : [];
                    if (history.length){
                        drawerAudit.innerHTML = history.map(item => {
                            const when = item.performed_at || item.created_at || '—';
                            const action = item.action || '—';
                            const by = (item.performed_by_user && item.performed_by_user.name) || item.performed_by || 'System';
                            return `<div class="border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                                <div class="flex items-center justify-between text-[11px] text-gray-400">
                                    <span>${when}</span>
                                    <span class="uppercase tracking-wide">${action}</span>
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-200">${item.notes || 'No description provided.'}</div>
                                <div class="text-[11px] text-gray-400 mt-1">Processed by ${by}</div>
                            </div>`;
                        }).join('');
                    } else {
                        drawerAudit.innerHTML = '<p class="text-gray-400">No recent activity for this customer.</p>';
                    }
                }
                if (drawerTransferBtn){
                    const disabled = (cust.status || '').toLowerCase() !== 'active';
                    drawerTransferBtn.dataset.accountId = cust.id || '';
                    drawerTransferBtn.dataset.accountName = cust.name || '';
                    drawerTransferBtn.dataset.custStatus = cust.status || '';
                    drawerTransferBtn.dataset.contactNo = cust.contact_no || '';
                    drawerTransferBtn.dataset.meterSerial = cust.meter_no || '';
                    drawerTransferBtn.classList.toggle('opacity-50', disabled);
                    drawerTransferBtn.classList.toggle('cursor-not-allowed', disabled);
                    drawerTransferBtn.title = disabled ? 'Customer not active. Complete registration/verification first.' : '';
                }
                if (drawerReplaceBtn){
                    const disabled = (cust.status || '').toLowerCase() !== 'active';
                    drawerReplaceBtn.dataset.accountId = cust.id || '';
                    drawerReplaceBtn.dataset.accountName = cust.name || '';
                    drawerReplaceBtn.dataset.custStatus = cust.status || '';
                    drawerReplaceBtn.classList.toggle('opacity-50', disabled);
                    drawerReplaceBtn.classList.toggle('cursor-not-allowed', disabled);
                    drawerReplaceBtn.title = disabled ? 'Customer not active. Complete registration/verification first.' : '';
                }

                if (drawerAuditLink){
                    drawerAuditLink.setAttribute('href', `${drawerAuditLink.dataset.baseHref}?search=${encodeURIComponent(cust.account_no || '')}`);
                }

                openDrawer();
            } catch(e){ toast('Failed to load customer','error'); }
        }

        document.addEventListener('click', function(e){
            const t = e.target;
            if (t && t.classList && t.classList.contains('edit-customer-btn')){
                fId.value = t.getAttribute('data-cust-id') || '';
                fName.value = t.getAttribute('data-cust-name') || '';
                fAddress.value = t.getAttribute('data-cust-address') || '';
                fContact.value = t.getAttribute('data-cust-contact') || '';
                openModal();
            }
            if (t && t.classList && t.classList.contains('view-customer-btn')){
                const id = t.getAttribute('data-cust-id') || '';
                if (id) loadCustomerAndShow(id);
            }
        });
        if (btnClose){ btnClose.addEventListener('click', closeModal); }
        if (modal){ modal.addEventListener('click', function(ev){ if (ev.target === modal) closeModal(); }); }
        if (drawerClose){ drawerClose.addEventListener('click', closeDrawer); }
        if (drawerCloseFooter){ drawerCloseFooter.addEventListener('click', closeDrawer); }
        if (drawer){ drawer.addEventListener('click', function(ev){ if (ev.target === drawer){ closeDrawer(); } }); }
        if (btnSave){
            btnSave.addEventListener('click', async function(){
                const id = fId.value;
                if (!id){ toast('Missing customer id','error'); return; }
                const payload = {
                    name: fName.value || null,
                    address: fAddress.value || null,
                    contact_no: fContact.value || null
                };
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                btnSave.disabled = true; const old = btnSave.textContent; btnSave.textContent = 'Saving...';
                try{
                    const res = await fetch(`/api/customer/${id}`, { method:'PATCH', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token || '' }, body: JSON.stringify(payload) });
                    if (!res.ok){ throw new Error('Failed'); }
                    toast('Customer updated','success');
                    closeModal();
                    // Optional: reload to reflect changes in table
                    setTimeout(function(){ try { window.location.reload(); } catch(_){} }, 300);
                }catch(err){
                    toast('Update failed','error');
                } finally {
                    btnSave.disabled = false; btnSave.textContent = old;
                }
            });
        }
    })();
    </script>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-dashed border-blue-300 dark:border-blue-500/50 p-6 mt-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">New Applicants Live in Their Own Workspace</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">We moved the new connection queue into a dedicated module so you can triage approvals, inspections, and installations with richer analytics.</p>
            </div>
            <a href="{{ route('admin.applicants.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                <x-heroicon-o-arrow-right-circle class="w-5 h-5" /> Go to Applicant Workspace
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 space-y-5">
        <div class="space-y-1">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Customer Management</h1>
            <p class="text-[12px] text-gray-500 dark:text-gray-400">Search, filter, and manage customer records with full audit history.</p>
        </div>

        <form id="customerFilters" method="GET" class="space-y-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <div class="flex items-center rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 overflow-hidden">
                        <span class="px-3 text-gray-400">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </span>
                        <input id="adminCustomerSearch" name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Search name, account, or address" class="flex-1 bg-transparent px-2 py-2 text-sm text-gray-800 dark:text-gray-100 focus:outline-none" autocomplete="off" />
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500 transition">Search</button>
                    </div>
                    <div id="adminCustomerSuggestions" class="absolute z-30 top-full left-0 right-0 mt-2 hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl overflow-hidden"></div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.customers') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-semibold text-gray-600 dark:text-gray-200 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">Reset</a>
                    <button type="button" id="toggleCustomerFilters" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-semibold text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <x-heroicon-o-funnel class="w-4 h-4" />
                        More filters
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                <span class="font-semibold uppercase tracking-wide text-[11px]">Status</span>
                <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 cursor-pointer">
                    <input type="radio" name="status" value="" class="w-3 h-3 text-blue-600 border-gray-300 focus:ring-blue-500" {{ empty($filters['status']) ? 'checked' : '' }}>
                    <span>All</span>
                </label>
                @foreach($statusOptions as $option)
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 cursor-pointer">
                        <input type="radio" name="status" value="{{ $option }}" class="w-3 h-3 text-blue-600 border-gray-300 focus:ring-blue-500" {{ ($filters['status'] ?? '') === $option ? 'checked' : '' }}>
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            </div>
            <div id="customerAdvancedFilters" class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 {{ $advancedFiltersOpen ? '' : 'hidden' }}">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1" for="customer-classification">Connection Type</label>
                    <select id="customer-classification" name="classification" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-200">
                        <option value="">Any</option>
                        @foreach($classificationOptions as $option)
                            <option value="{{ $option }}" @selected(($filters['classification'] ?? null) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1" for="customer-created">Created Date</label>
                    <input id="customer-created" name="created" value="{{ $filters['created'] ?? '' }}" type="date" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-200" />
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-6 lg:px-8 py-5 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Customer Directory</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Currently showing {{ $customers->count() }} of {{ $customers->total() }} records.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                <a href="{{ route('admin.customers', array_filter([...request()->except('export'), 'export' => 'csv'])) }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 font-semibold shadow hover:bg-blue-500 transition">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" /> Export CSV
                </a>
                <a href="#customerFilters" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-600 dark:text-gray-200 hover:border-blue-400 hover:text-blue-600 transition">
                    <x-heroicon-o-funnel class="w-4 h-4" /> Adjust filters
                </a>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-visible">
            <table class="min-w-full text-sm border-collapse">
                <thead class="bg-gradient-to-r from-blue-700 via-blue-600 to-blue-500 text-white uppercase text-[11px] tracking-wide">
                    <tr>
                        <th class="px-6 lg:px-8 py-3 text-left">Account No.</th>
                        <th class="px-6 lg:px-8 py-3 text-left">Customer</th>
                        <th class="px-6 lg:px-8 py-3 text-left">Contact</th>
                        <th class="px-6 lg:px-8 py-3 text-left">Connection</th>
                        <th class="px-6 lg:px-8 py-3 text-left">Status</th>
                        <th class="px-6 lg:px-8 py-3 text-left">Created</th>
                        <th class="px-6 lg:px-8 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-gray-100">
                    @forelse($customers as $c)
                    <tr class="odd:bg-white even:bg-blue-50/40 dark:odd:bg-gray-900 dark:even:bg-gray-800/70 hover:bg-blue-100/60 dark:hover:bg-gray-800 transition-colors" data-customer-id="{{ $c->id }}" data-account-no="{{ $c->account_no }}" data-reconnect-requested="{{ $c->reconnect_requested_at ? '1' : '0' }}">
                        <td class="px-6 lg:px-8 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $c->account_no }}</td>
                        <td class="px-6 lg:px-8 py-3">
                            <div class="font-medium text-gray-900 dark:text-gray-100 flex items-start gap-2">
                                <span>{{ $c->name }}</span>
                                @if($c->status === 'Disconnected')
                                    <span class="px-1.5 py-0.5 rounded-md bg-red-100 text-red-600 text-[11px]">Disconnected</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $c->address }}</div>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $c->contact_no ?? '—' }}</td>
                        <td class="px-6 lg:px-8 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $c->classification ?? '—' }}</td>
                        <td class="px-6 lg:px-8 py-3">
                            @php $color = ($c->status === 'Active') ? 'bg-green-100 text-green-700' : (($c->status === 'Inactive' || $c->status === 'Disconnected') ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'); @endphp
                            <div class="flex flex-col items-start gap-1" data-admin-reconnect-pill>
                                <span class="px-2 py-1 rounded-full text-xs {{ $color }}">{{ $c->status ?? '—' }}</span>
                                @if($c->reconnect_requested_at)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Staff requested reconnect</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 lg:px-8 py-3 text-sm text-gray-600 dark:text-gray-300">{{ optional($c->created_at)->format('Y-m-d') }}</td>
                        <td class="px-6 lg:px-8 py-3 text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-medium">
                                <details class="relative inline-block" data-admin-actions>
                                    <summary class="list-none px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-gray-800 text-gray-600 dark:text-gray-200 cursor-pointer hover:bg-slate-200 dark:hover:bg-gray-700 transition">More ▾</summary>
                                    <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-10 py-2">
                                        <div class="px-3 py-1 text-[10px] uppercase text-gray-400">Customer</div>
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 edit-customer-btn"
                                            data-cust-id="{{ $c->id }}"
                                            data-cust-name="{{ $c->name }}"
                                            data-cust-address="{{ $c->address }}"
                                            data-cust-contact="{{ $c->contact_no }}">
                                            Edit info
                                        </button>
                                        @if (strtolower($c->status) !== 'active')
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 verify-customer-btn"
                                            data-cust-id="{{ $c->id }}">
                                            Verify customer
                                        </button>
                                        @endif
                                        @if (strtolower($c->status) === 'disconnected' && $c->reconnect_requested_at)
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 reconnect-customer-btn"
                                            data-cust-id="{{ $c->id }}"
                                            data-account-no="{{ $c->account_no }}"
                                            data-cust-name="{{ $c->name }}">
                                            Reconnect service
                                        </button>
                                        @elseif (strtolower($c->status) === 'disconnected')
                                        <span class="block px-3 py-2 text-[11px] text-gray-400">Awaiting staff reconnect request</span>
                                        @endif
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 transfer-meter-btn {{ strtolower($c->status) !== 'active' ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            data-account-id="{{ $c->id }}"
                                            data-account-name="{{ $c->name }}"
                                            data-contact-no="{{ $c->contact_no }}"
                                            data-cust-status="{{ $c->status }}"
                                            data-meter-serial="{{ $c->meter_no }}"
                                            title="{{ strtolower($c->status) !== 'active' ? 'Customer not active. Complete registration/verification first.' : '' }}">
                                            Transfer meter
                                        </button>
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 replace-meter-btn {{ strtolower($c->status) !== 'active' ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            data-account-id="{{ $c->id }}"
                                            data-account-name="{{ $c->name }}"
                                            data-cust-status="{{ $c->status }}"
                                            title="{{ strtolower($c->status) !== 'active' ? 'Customer not active. Complete registration/verification first.' : '' }}">
                                            Replace meter
                                        </button>
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 lg:px-8 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            <div class="max-w-sm mx-auto space-y-2">
                                <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-gray-300" />
                                <p class="font-medium">No customer records match the current filters.</p>
                                <p class="text-xs">Adjust the filters or clear the search to see all customers.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 lg:px-8 py-5 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3 mt-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($customers->total())
                    Showing
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $customers->firstItem() }}</span>
                    –
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $customers->lastItem() }}</span>
                    of
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $customers->total() }}</span>
                    customers
                @else
                    No customers to display.
                @endif
            </p>
            <div class="sm:ml-auto">
                {{ $customers->onEachSide(1)->links() }}
                </a>
            </div>
        </div>
    </div>

    @include('admin.partials.reconnect-modal')

    <!-- Audit Log Modal -->
</div>
@endsection

@push('scripts')
<script>
(function(){
    const searchInput = document.getElementById('adminCustomerSearch');
    const suggestionPanel = document.getElementById('adminCustomerSuggestions');
    if (!searchInput || !suggestionPanel) return;

    let controller = null;
    let activeIndex = -1;
    let suggestions = [];

    function resetSuggestions(){
        activeIndex = -1;
        suggestions = [];
        suggestionPanel.classList.add('hidden');
        suggestionPanel.innerHTML = '';
    }

    async function fetchSuggestions(term){
        const query = term.trim();
        if (query.length < 2){
            resetSuggestions();
            return;
        }

        if (controller){ controller.abort(); }
        controller = new AbortController();
        suggestionPanel.innerHTML = '<div class="px-4 py-2 text-xs text-gray-400">Searching…</div>';
        suggestionPanel.classList.remove('hidden');

        try {
            const res = await fetch(`{{ route('customer.searchAccounts') }}?q=${encodeURIComponent(query)}`, {
                headers: { 'Accept':'application/json' },
                signal: controller.signal,
            });
            if (!res.ok) throw new Error('Request failed');
            const data = await res.json();
            suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];
            if (!suggestions.length){
                suggestionPanel.innerHTML = '<div class="px-4 py-2 text-xs text-gray-400">No matches.</div>';
                return;
            }
            suggestionPanel.innerHTML = suggestions.map((item, idx) => {
                const status = (item.status || '').toLowerCase();
                const tone = status === 'active'
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : status === 'disconnected'
                        ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                        : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300';
                return `
                    <button type="button" data-index="${idx}" class="admin-suggestion w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-800 focus:bg-blue-50 dark:focus:bg-gray-800">
                        <div class="flex items-center justify-between gap-3 mb-1">
                            <span class="font-mono text-xs text-gray-500 dark:text-gray-400">${item.account_no ?? ''}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ${tone}">${item.status ?? ''}</span>
                        </div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">${item.name ?? 'Unnamed customer'}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">${item.address ?? 'No address on file'}</div>
                    </button>
                `;
            }).join('');
            activeIndex = -1;
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error(error);
            suggestionPanel.innerHTML = '<div class="px-4 py-2 text-xs text-rose-500">Unable to load suggestions.</div>';
        }
    }

    searchInput.addEventListener('input', (event) => {
        fetchSuggestions(event.target.value);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (suggestionPanel.classList.contains('hidden')) return;
        const buttons = suggestionPanel.querySelectorAll('.admin-suggestion');
        if (!buttons.length) return;

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp'){
            event.preventDefault();
            if (event.key === 'ArrowDown'){
                activeIndex = (activeIndex + 1) % buttons.length;
            } else {
                activeIndex = (activeIndex - 1 + buttons.length) % buttons.length;
            }
            buttons.forEach((btn, idx) => {
                btn.classList.toggle('bg-blue-50', idx === activeIndex);
                btn.classList.toggle('dark:bg-gray-800', idx === activeIndex);
            });
        } else if (event.key === 'Enter' && activeIndex >= 0){
            event.preventDefault();
            buttons[activeIndex]?.click();
        } else if (event.key === 'Escape'){
            resetSuggestions();
        }
    });

    suggestionPanel.addEventListener('mousedown', (event) => {
        const item = event.target.closest('.admin-suggestion');
        if (!item) return;
        const idx = Number(item.dataset.index);
        const data = suggestions[idx];
        if (!data) return;
        searchInput.value = data.account_no || data.name || '';
        resetSuggestions();
        searchInput.form?.submit();
    });

    document.addEventListener('click', (event) => {
        if (event.target === searchInput || suggestionPanel.contains(event.target)) return;
        resetSuggestions();
    });

    searchInput.addEventListener('search', () => {
        resetSuggestions();
        searchInput.form?.submit();
    });
})();
</script>
@endpush

@push('scripts')
<script>
(function(){
    const reconnectModal = document.getElementById('reconnectCustomerModal');
    const reconnectDialog = reconnectModal?.querySelector('[data-reconnect-dialog]');
    const reconnectAccountInput = reconnectModal?.querySelector('#reconnectAccountInput');
    const reconnectAccountDisplay = reconnectModal?.querySelector('#reconnectAccountDisplay');
    const reconnectNameDisplay = reconnectModal?.querySelector('#reconnectCustomerNameDisplay');
    const reconnectNotes = reconnectModal?.querySelector('#reconnectNotes');
    const confirmReconnectBtn = reconnectModal?.querySelector('#confirmReconnectBtn');
    const cancelReconnectBtn = reconnectModal?.querySelector('#cancelReconnectBtn');
    const closeReconnectModalBtn = reconnectModal?.querySelector('#closeReconnectModal');
    let reconnectOriginButton = null;

    function showToast(msg, type){
        try {
            if (typeof window.showToast === 'function') {
                window.showToast(msg, type || 'info');
                return;
            }
        } catch (err) {}
        alert(msg);
    }

    function openReconnectModal(accountNo, name, origin){
        if (!reconnectModal) return;
        reconnectOriginButton = origin || null;
        reconnectAccountInput.value = accountNo || '';
        reconnectAccountDisplay.textContent = accountNo || '—';
        reconnectNameDisplay.textContent = name || '—';
        reconnectNotes.value = '';
        resetProgress();
        reconnectModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            reconnectModal.classList.add('flex');
            reconnectDialog?.classList.remove('scale-95','opacity-0');
            reconnectDialog?.classList.add('scale-100','opacity-100');
        });
    }

    function closeReconnectModal(){
        if (!reconnectModal) return;
        reconnectDialog?.classList.add('scale-95','opacity-0');
        reconnectDialog?.classList.remove('scale-100','opacity-100');
        setTimeout(() => {
            reconnectModal.classList.add('hidden');
            reconnectModal.classList.remove('flex');
            reconnectOriginButton = null;
        }, 180);
    }

    function computeProgress(){
        const checkboxes = Array.from(reconnectModal.querySelectorAll('.reconnect-step-checkbox'));
        const completed = checkboxes.filter(cb => cb.checked).length;
        const total = checkboxes.length || 1;
        const pct = Math.round((completed / total) * 100);
        reconnectModal.querySelector('#reconnectProgressLabel').textContent = `${completed} of ${total} steps completed`;
        reconnectModal.querySelector('#reconnectProgressPercent').textContent = `${pct}%`;
        reconnectModal.querySelector('#reconnectProgressFill').style.width = `${pct}%`;
        confirmReconnectBtn.disabled = completed !== total;
    }

    function resetProgress(){
        reconnectModal.querySelectorAll('.reconnect-step-checkbox').forEach(cb => {
            cb.checked = false;
        });
        computeProgress();
    }

    async function submitReconnect(){
        const account = reconnectAccountInput?.value?.trim();
        if (!account){
            showToast('Missing account number', 'error');
            return;
        }

        confirmReconnectBtn.disabled = true;
        const originalLabel = confirmReconnectBtn.innerHTML;
        confirmReconnectBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg> Processing…</span>';

        try {
            const res = await fetch("{{ route('customer.reconnect') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    account_no: account,
                    notes: reconnectNotes?.value?.trim() || null,
                })
            });

            if (!res.ok){
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || 'Failed to reconnect service');
            }

            const data = await res.json();
            showToast(data.message || 'Customer reconnected', 'success');
            updateRowAfterReconnect(account);
            closeReconnectModal();
        } catch (error) {
            console.error(error);
            showToast(error.message || 'Failed to reconnect service', 'error');
        } finally {
            confirmReconnectBtn.disabled = false;
            confirmReconnectBtn.innerHTML = originalLabel;
        }
    }

    function updateRowAfterReconnect(accountNo){
        const row = document.querySelector(`tr[data-account-no="${CSS.escape(accountNo)}"]`);
        if (!row) {
            window.location.reload();
            return;
        }

        const statusBadge = row.querySelector('span.rounded-full');
        if (statusBadge){
            statusBadge.textContent = 'Active';
            statusBadge.className = 'px-2 py-1 rounded-full text-xs bg-green-100 text-green-700';
        }

        const disconnectedChip = row.querySelector('span.bg-red-100');
        if (disconnectedChip){
            disconnectedChip.remove();
        }

        row.querySelectorAll('.reconnect-customer-btn').forEach(btn => btn.remove());
        row.querySelectorAll('.transfer-meter-btn, .replace-meter-btn').forEach(btn => {
            btn.classList.remove('opacity-50','cursor-not-allowed');
            btn.removeAttribute('title');
        });
    }

    function handleReconnectClick(event){
        event.preventDefault();
        const trigger = event.currentTarget;
        openReconnectModal(
            trigger.getAttribute('data-account-no'),
            trigger.getAttribute('data-cust-name'),
            trigger
        );
        trigger.closest('details')?.removeAttribute('open');
    }

    const stepCheckboxes = reconnectModal ? Array.from(reconnectModal.querySelectorAll('.reconnect-step-checkbox')) : [];
    stepCheckboxes.forEach(cb => cb.addEventListener('change', computeProgress));

    document.querySelectorAll('.reconnect-customer-btn').forEach(btn => {
        btn.addEventListener('click', handleReconnectClick);
    });

    document.addEventListener('click', (event) => {
        if (event.target === reconnectModal){
            closeReconnectModal();
        }
    });

    if (closeReconnectModalBtn){
        closeReconnectModalBtn.addEventListener('click', closeReconnectModal);
    }
    if (cancelReconnectBtn){
        cancelReconnectBtn.addEventListener('click', closeReconnectModal);
    }
    if (confirmReconnectBtn){
        confirmReconnectBtn.addEventListener('click', submitReconnect);
    }

})();

(function(){
    const menus = Array.from(document.querySelectorAll('details[data-admin-actions]'));
    if (menus.length){
        menus.forEach(menu => {
            menu.addEventListener('toggle', function(){
                if (menu.open){
                    menus.forEach(other => {
                        if (other !== menu){ other.open = false; }
                    });
                }
            });
        });
    }

    const auditBtn = document.getElementById('openAuditLog');
    const auditModal = document.getElementById('customerAuditLogModal');
    const auditClose = document.getElementById('closeAuditLog');
    const transferModal = document.getElementById('transferMeterModal');
    const tmClose = document.getElementById('tmClose');
    const tmSave = document.getElementById('tmSave');
    const tmAccountId = document.getElementById('tmAccountId');
    const tmAccountName = document.getElementById('tmAccountName');
    const tmMeterSerial = document.getElementById('tmMeterSerial');
    const tmNewName = document.getElementById('tmNewName');
    const tmNewContact = document.getElementById('tmNewContact');
    const tmDate = document.getElementById('tmDate');
    const tmNotes = document.getElementById('tmNotes');

    function openTransferModal(payload){
        if (!transferModal) return;
        tmAccountId.value = payload.id || '';
        tmAccountName.value = payload.name || '';
        tmMeterSerial.value = payload.meter || '';
        tmNewName.value = payload.name || '';
        tmNewContact.value = payload.contact || '';
        tmDate.value = new Date().toISOString().slice(0,10);
        tmNotes.value = '';
        transferModal.classList.remove('hidden');
        transferModal.classList.add('flex');
    }

    function closeTransferModal(){
        if (!transferModal) return;
        transferModal.classList.add('hidden');
        transferModal.classList.remove('flex');
    }

    async function submitTransfer(){
        const id = tmAccountId.value;
        const newName = tmNewName.value.trim();
        const newContact = tmNewContact.value.trim();
        const transferDate = tmDate.value;

        if (!id){ showToast('Missing customer id','error'); return; }
        if (!newName){ showToast('Please enter the new account owner name.','error'); return; }
        if (!transferDate){ showToast('Select a transfer date.','error'); return; }

        try {
            tmSave.disabled = true;
            const payload = {
                new_owner_name: newName,
                new_contact_no: newContact || null,
                transfer_date: transferDate,
                notes: tmNotes.value.trim() || null,
            };

            const res = await fetch(`/admin/customers/${id}/transfer-meter`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok){
                throw new Error(data.message || data.error || 'Transfer failed');
            }

            showToast(data.message || 'Meter transferred successfully.','success');
            closeTransferModal();
            setTimeout(() => window.location.reload(), 400);
        } catch (error) {
            console.error(error);
            showToast(error.message || 'Meter transfer failed.','error');
        } finally {
            tmSave.disabled = false;
        }
    }

    document.addEventListener('click', function(event){
        const target = event.target.closest('.transfer-meter-btn');
        if (!target) return;
        event.preventDefault();
        const disabled = target.classList.contains('opacity-50');
        if (disabled) return;
        openTransferModal({
            id: target.dataset.accountId,
            name: target.dataset.accountName,
            status: target.dataset.custStatus,
            contact: target.dataset.contactNo || '',
            meter: target.dataset.meterSerial || '',
        });
        target.closest('details')?.removeAttribute('open');
    });

    if (transferModal){
        const transferModalContent = transferModal.querySelector('[data-transfer-modal-content]');
        transferModal.addEventListener('click', function(event){
            if (!transferModalContent) {
                return;
            }
            if (!transferModalContent.contains(event.target)){
                closeTransferModal();
            }
        });
    }
    if (tmClose){
        tmClose.addEventListener('click', closeTransferModal);
    }
    if (tmSave){
        tmSave.addEventListener('click', submitTransfer);
    }

    const form = document.querySelector('form[method="GET"]');
    const toggleFilters = document.getElementById('toggleCustomerFilters');
    const advancedFilters = document.getElementById('customerAdvancedFilters');
    const inlineForm = document.getElementById('inlineFilterForm');

    function toggleModal(show){
        if (!auditModal) return;
        if (show){
            auditModal.classList.remove('hidden');
            auditModal.classList.add('flex');
        } else {
            auditModal.classList.add('hidden');
            auditModal.classList.remove('flex');
        }
    }

    if (auditBtn){ auditBtn.addEventListener('click', function(){ toggleModal(true); }); }
    if (auditClose){ auditClose.addEventListener('click', function(){ toggleModal(false); }); }
    if (auditModal){
        auditModal.addEventListener('click', function(ev){ if (ev.target === auditModal) toggleModal(false); });
    }

    if (toggleFilters && advancedFilters){
        const preferredState = sessionStorage.getItem('customerAdvancedFilters') === 'open';
        if (preferredState){
            advancedFilters.classList.remove('hidden');
        }
        toggleFilters.addEventListener('click', function(){
            const isHidden = advancedFilters.classList.contains('hidden');
            advancedFilters.classList.toggle('hidden', !isHidden);
            sessionStorage.setItem('customerAdvancedFilters', advancedFilters.classList.contains('hidden') ? 'closed' : 'open');
        });
    }

    if (form){
        const autoSubmitControls = form.querySelectorAll('input[type="radio"], select[name="classification"], input[name="created"]');
        autoSubmitControls.forEach(function(control){
            control.addEventListener('change', function(){ form.submit(); });
        });
    }

    if (inlineForm){
        inlineForm.addEventListener('submit', function(ev){
            ev.preventDefault();
            const params = new URLSearchParams(new FormData(inlineForm));
            window.location.href = `${inlineForm.getAttribute('action') || window.location.pathname}?${params.toString()}`;
        });
    }
})();
</script>
@endpush
