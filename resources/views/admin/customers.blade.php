@extends('layouts.admin')

@section('title', 'Admin • Customer Data Management')

@section('content')
<div x-data="{ selected:new Set(), all:false, drawer:false }" class="w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 font-[Poppins] space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Customer Management</h1>
        <div class="flex flex-wrap items-center gap-2">
            <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Import CSV</button>
            <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Export CSV</button>
            <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Merge Duplicates</button>
            <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Audit Log</button>
        </div>
    </div>

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

    <!-- Assign Meter Modal -->
    <div id="assignMeterModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Assign Meter</h3>
                <button id="amClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="hidden" id="amAccountId" />
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Customer</label>
                    <input id="amAccountName" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-sm" readonly />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Find Meter (Inventory)</label>
                    <div class="flex items-center gap-2">
                        <input id="amSearch" type="text" placeholder="Search serial or address" class="flex-1 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                        <button id="amSearchBtn" class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Search</button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Select Meter</label>
                    <select id="amMeter" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm"></select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Assigned Date</label>
                        <input id="amDate" type="date" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Reason (optional)</label>
                        <input id="amReason" type="text" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Notes (optional)</label>
                    <textarea id="amNotes" rows="2" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm"></textarea>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="amSave" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Assign</button>
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
                        <div class="flex items-center justify-between"><span>Materials</span><span id="pFeeMat">—</span></div>
                        <div class="flex items-center justify-between"><span>Labor</span><span id="pFeeLab">—</span></div>
                        <div class="flex items-center justify-between"><span>Meter Deposit</span><span id="pFeeDep">—</span></div>
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

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 mt-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">New Connection Applications</h2>
            <div class="flex items-center gap-2 text-xs">
                <input id="ncSearch" type="text" placeholder="Search applicant or address" class="border border-gray-300 dark:border-gray-700 rounded-md px-2 py-1 bg-white dark:bg-gray-900" />
                <select id="ncFilter" class="border border-gray-300 dark:border-gray-700 rounded-md px-2 py-1 bg-white dark:bg-gray-900">
                    <option value="">All</option>
                    <option value="registered">Registered</option>
                    <option value="inspected">Inspected</option>
                    <option value="approved">Approved</option>
                    <option value="assessed">Assessed</option>
                    <option value="paid">Paid</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="installed">Installed</option>
                </select>
                <button id="ncReload" class="px-3 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">Reload</button>
                <button id="ncExport" class="px-3 py-1 rounded-md bg-emerald-600 text-white">Export CSV</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Applicant</th>
                        <th class="px-4 py-2 text-left">Address</th>
                        <th class="px-4 py-2 text-left">Fee Total</th>
                        <th class="px-4 py-2 text-left">Paid At</th>
                        <th class="px-4 py-2 text-left">Schedule Date</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="ncBody" class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-100">
                    <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    (function(){
        function toast(msg, type){ try { if (typeof window.showToast==='function'){ window.showToast(msg, type||'info'); return; } } catch(_){} alert(msg); }
        const body = document.getElementById('ncBody');
        const filter = document.getElementById('ncFilter');
        const search = document.getElementById('ncSearch');
        const reloadBtn = document.getElementById('ncReload');
        const exportBtn = document.getElementById('ncExport');
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const currentRole = @json(auth()->user()->role ?? null);

        // Modals and fields
        // Assign meter
        const amModal = document.getElementById('assignMeterModal');
        const amClose = document.getElementById('amClose');
        const amAccountId = document.getElementById('amAccountId');
        const amAccountName = document.getElementById('amAccountName');
        const amSearch = document.getElementById('amSearch');
        const amSearchBtn = document.getElementById('amSearchBtn');
        const amMeter = document.getElementById('amMeter');
        const amDate = document.getElementById('amDate');
        const amReason = document.getElementById('amReason');
        const amNotes = document.getElementById('amNotes');
        const amSave = document.getElementById('amSave');

        // Replace meter
        const rmModal = document.getElementById('replaceMeterModal');
        const rmClose = document.getElementById('rmClose');
        const rmAccountId = document.getElementById('rmAccountId');
        const rmAccountName = document.getElementById('rmAccountName');
        const rmCurrent = document.getElementById('rmCurrent');
        const rmUnassignDate = document.getElementById('rmUnassignDate');
        const rmReason = document.getElementById('rmReason');
        const rmSearch = document.getElementById('rmSearch');
        const rmSearchBtn = document.getElementById('rmSearchBtn');
        const rmMeter = document.getElementById('rmMeter');
        const rmAssignDate = document.getElementById('rmAssignDate');
        const rmSave = document.getElementById('rmSave');
        let __rmCurrentMeterId = null;

        const assessModal = document.getElementById('assessModal');
        const assessId = document.getElementById('assessId');
        const feeApp = document.getElementById('fee_application');
        const feeInsp = document.getElementById('fee_inspection');
        const feeMat = document.getElementById('fee_materials');
        const feeLab = document.getElementById('fee_labor');
        const feeDep = document.getElementById('meter_deposit');
        const feeTotalDisp = document.getElementById('fee_total_display');
        const assessSave = document.getElementById('assessSave');
        const assessClose = document.getElementById('assessClose');

        const payModal = document.getElementById('payModal');
        const payId = document.getElementById('payId');
        const receiptNo = document.getElementById('receipt_no');
        const paySave = document.getElementById('paySave');
        const payClose = document.getElementById('payClose');

        const schedModal = document.getElementById('scheduleModal');
        const schedId = document.getElementById('schedId');
        const schedDate = document.getElementById('schedule_date');
        const schedSave = document.getElementById('scheduleSave');
        const schedClose = document.getElementById('scheduleClose');

        const installModal = document.getElementById('installModal');
        const installId = document.getElementById('installId');
        const installSave = document.getElementById('installSave');
        const installClose = document.getElementById('installClose');

        function show(el){ if (el){ el.classList.remove('hidden'); el.classList.add('flex'); } }
        function hide(el){ if (el){ el.classList.add('hidden'); el.classList.remove('flex'); } }

        function parseAmt(v){ var n = parseFloat(v); return isFinite(n) && n>0 ? n : 0; }
        function formatPhp(n){ try { return new Intl.NumberFormat('en-PH',{ style:'currency', currency:'PHP' }).format(n); } catch(_) { return '₱' + (n.toFixed? n.toFixed(2): n); } }
        function updateFeeTotal(){
            var total = parseAmt(feeApp?.value)||0;
            total += parseAmt(feeInsp?.value)||0;
            total += parseAmt(feeMat?.value)||0;
            total += parseAmt(feeLab?.value)||0;
            total += parseAmt(feeDep?.value)||0;
            if (feeTotalDisp) feeTotalDisp.textContent = formatPhp(total);
        }

        async function fetchApps(){
            const params = new URLSearchParams();
            if (filter && filter.value) params.set('status', filter.value);
            const res = await fetch('/api/connections' + (params.toString()? ('?'+params.toString()):''));
            if (!res.ok) throw new Error('fetch failed');
            return await res.json();
        }
        function rowActions(app){
            const s = app.status;
            const id = app.id;
            const actions = [];
            if (s==='registered' || s==='inspected'){ actions.push(`<button data-act="approve" data-id="${id}" class="text-blue-600">Approve</button>`); }
            if (s==='approved' || s==='assessed'){ actions.push(`<button data-act="assess" data-id="${id}" class="text-emerald-600">Assess</button>`); }
            if (s==='assessed'){ actions.push(`<button data-act="pay" data-id="${id}" class="text-amber-600">Pay</button>`); }
            if (s==='paid' || s==='scheduled'){ actions.push(`<button data-act="schedule" data-id="${id}" class="text-purple-600">Schedule</button>`); }
            if (s==='scheduled' || s==='installed'){ actions.push(`<button data-act="install" data-id="${id}" class="text-indigo-600">Install</button>`); }
            return actions.join(' \u2022 ');
        }
        function toNumber(v){ var n = parseFloat(v); return isFinite(n)? n : 0; }
        function fmtDate(d){ if (!d) return ''; try { return String(d).slice(0,10); } catch(_){ return ''; } }
        function fmtPhp(n){ try { return new Intl.NumberFormat('en-PH',{ style:'currency', currency:'PHP' }).format(n); } catch(_){ return '₱'+(n?.toFixed?n.toFixed(2):n); } }
        function render(items){
            const list = Array.isArray(items?.data) ? items.data : [];
            const q = (search?.value||'').toLowerCase().trim();
            const filtered = list.filter(app => {
                if (!q) return true;
                const a = (app.applicant_name||'').toLowerCase();
                const addr = (app.address||'').toLowerCase();
                return a.includes(q) || addr.includes(q);
            });

        // Latest application badges and actions per customer row
        async function fetchLatestApp(custId){
            const res = await fetch(`/api/applications/latest?customer_id=${encodeURIComponent(custId)}`, { headers:{ 'Accept':'application/json' } });
            if (!res.ok) return null; const data = await res.json().catch(()=>({}));
            return data?.application || null;
        }
        function renderBadges(host, app){
            if (!host) return;
            if (!app){ host.innerHTML = '<span class="text-gray-400">No application</span>'; return; }
            const sBadge = `<span class="px-2 py-0.5 rounded-full ${app.status==='approved'?'bg-emerald-100 text-emerald-700':(app.status==='registered'?'bg-gray-100 text-gray-700':'bg-blue-100 text-blue-700')}">${app.status}</span>`;
            const scoreBadge = typeof app.score==='number' ? `<span class="px-2 py-0.5 rounded ${app.score>=80?'bg-green-100 text-green-700':(app.score>=60?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700')}">Score ${app.score}</span>` : '';
            const riskBadge = app.risk_level ? `<span class="px-2 py-0.5 rounded ${app.risk_level==='low'?'bg-green-100 text-green-700':(app.risk_level==='medium'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700')}">${app.risk_level}</span>` : '';
            host.innerHTML = [sBadge, scoreBadge, riskBadge].filter(Boolean).join(' ');
        }
        function attachAppActions(row, app){
            const rescore = row.querySelector('.app-rescore-btn');
            const approve = row.querySelector('.app-approve-btn');
            const reject = row.querySelector('.app-reject-btn');
            const viewkyc = row.querySelector('.app-viewkyc-link');
            if (!app){ [rescore,approve,reject,viewkyc].forEach(el=>el&&el.classList.add('hidden')); return; }
            if (rescore){ rescore.dataset.appId = app.id; rescore.classList.remove('hidden'); }
            if (approve){ approve.dataset.appId = app.id; approve.classList.remove('hidden'); }
            if (reject){ reject.dataset.appId = app.id; reject.classList.remove('hidden'); }
            if (viewkyc){ viewkyc.href = `/applications/${app.id}`; viewkyc.classList.remove('hidden'); }
        }
        async function hydrateRows(){
            const rows = document.querySelectorAll('tr[data-cust-id]');
            for (const row of rows){
                const id = row.getAttribute('data-cust-id');
                const host = row.querySelector('.app-badges');
                try{
                    const app = await fetchLatestApp(id);
                    renderBadges(host, app);
                    attachAppActions(row, app);
                } catch(_){ if (host) host.innerHTML = '<span class="text-gray-400">—</span>'; }
            }
        }
        hydrateRows();

        // Inline actions handlers
        document.addEventListener('click', async function(e){
            const rescore = e.target?.closest?.('.app-rescore-btn');
            const approve = e.target?.closest?.('.app-approve-btn');
            const reject = e.target?.closest?.('.app-reject-btn');
            try{
                if (rescore){
                    const id = rescore.getAttribute('data-app-id');
                    if (!id) return; await fetch(`/api/applications/${id}/score`, { method:'POST', headers:{ 'Accept':'application/json', 'X-CSRF-TOKEN': token||'' } });
                    toast('Re-scored','success'); hydrateRows(); return;
                }
                if (approve){
                    const id = approve.getAttribute('data-app-id');
                    if (!id) return; const r = await fetch(`/api/applications/${id}/approve`, { method:'PUT', headers:{ 'Accept':'application/json','Content-Type':'application/json', 'X-CSRF-TOKEN': token||'' }, body: JSON.stringify({ auto_verify: false }) });
                    if (!r.ok) throw 0; toast('Approved','success'); hydrateRows(); return;
                }
                if (reject){
                    const id = reject.getAttribute('data-app-id');
                    if (!id) return; const reason = prompt('Reason (optional)');
                    const r = await fetch(`/api/applications/${id}/reject`, { method:'PUT', headers:{ 'Accept':'application/json','Content-Type':'application/json', 'X-CSRF-TOKEN': token||'' }, body: JSON.stringify({ reason }) });
                    if (!r.ok) throw 0; toast('Rejected','success'); hydrateRows(); return;
                }
            } catch(_){ toast('Action failed','error'); }
        });
            if (filtered.length===0){ body.innerHTML = '<tr><td colspan="8" class="px-4 py-4 text-center text-gray-500">No applications.</td></tr>'; return; }
            body.innerHTML = filtered.map(app => {
                const total = (toNumber(app.fee_application)+toNumber(app.fee_inspection)+toNumber(app.fee_materials)+toNumber(app.fee_labor)+toNumber(app.meter_deposit));
                return `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer" data-app-id="${app.id}">
                    <td class="px-4 py-2">${app.application_code || app.id}</td>
                    <td class="px-4 py-2">${app.applicant_name||''}</td>
                    <td class="px-4 py-2">${app.address||''}</td>
                    <td class="px-4 py-2">${total>0? fmtPhp(total): '—'}</td>
                    <td class="px-4 py-2">${fmtDate(app.paid_at)}</td>
                    <td class="px-4 py-2">${fmtDate(app.schedule_date)}</td>
                    <td class="px-4 py-2"><span class="px-2 py-1 rounded-full text-[11px] ${badgeClass(app.status)}">${app.status}</span></td>
                    <td class="px-4 py-2 text-right">${rowActions(app)}</td>
                </tr>`;
            }).join('');
        }
        function badgeClass(s){
            switch(s){
                case 'registered': return 'bg-gray-100 text-gray-700';
                case 'inspected': return 'bg-blue-100 text-blue-700';
                case 'approved': return 'bg-emerald-100 text-emerald-700';
                case 'assessed': return 'bg-amber-100 text-amber-700';
                case 'paid': return 'bg-purple-100 text-purple-700';
                case 'scheduled': return 'bg-indigo-100 text-indigo-700';
                case 'installed': return 'bg-green-100 text-green-700';
                default: return 'bg-gray-100 text-gray-700';
            }
        }
        async function load(){
            try{ const data = await fetchApps(); render(data.items); } catch(e){ body.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">Failed to load.</td></tr>'; }
        }
        if (reloadBtn) reloadBtn.addEventListener('click', load);
        if (filter) filter.addEventListener('change', load);
        if (search) search.addEventListener('input', function(){
            // Re-render using last fetched data by calling load() then filtering; simple approach: call load then render will apply search.
            // To avoid extra network on each input, we can cache the last payload.
        });

        document.addEventListener('click', async function(ev){
            const t = ev.target; if (!t || !t.getAttribute) return;
            const act = t.getAttribute('data-act'); const id = t.getAttribute('data-id'); if (!act || !id) return;
            try{
                if (act==='approve'){
                    const res = await fetch(`/api/connections/${id}/approve`, { method:'PUT', headers:{ 'X-CSRF-TOKEN': token||'', 'Accept':'application/json' } });
                    if (!res.ok) throw 0; toast('Approved','success'); load();
                } else if (act==='assess'){
                    assessId.value = id; feeApp.value='0'; feeInsp.value='0'; feeMat.value='0'; feeLab.value='0'; feeDep.value='0'; updateFeeTotal(); show(assessModal);
                } else if (act==='pay'){
                    payId.value = id; receiptNo.value='';
                    const appEl = document.getElementById('payApplicant');
                    const stEl = document.getElementById('payStatus');
                    const fee = { app:document.getElementById('pFeeApp'), insp:document.getElementById('pFeeInsp'), mat:document.getElementById('pFeeMat'), lab:document.getElementById('pFeeLab'), dep:document.getElementById('pFeeDep'), total:document.getElementById('pFeeTotal') };
                    const hint = document.getElementById('payHint');
                    const link = document.getElementById('payModuleLink');
                    const saveBtn = document.getElementById('paySave');
                    saveBtn.disabled = true;
                    show(payModal);
                    try{
                        const res = await fetch(`/api/connections/${id}`, { headers:{ 'Accept':'application/json' } });
                        if (!res.ok) throw 0;
                        const data = await res.json();
                        const app = data?.application || {};
                        const status = String(app.status||'').toLowerCase();
                        if (aiId) aiId.textContent = app.application_code || app.id || '—';
                        if (appEl) appEl.textContent = app.applicant_name || '—';
                        if (stEl) { stEl.textContent = app.status || '—'; stEl.className = 'text-xs inline-flex items-center px-2 py-0.5 rounded-full '+(status==='assessed'?'bg-amber-100 text-amber-700':(status==='paid'?'bg-purple-100 text-purple-700':'bg-gray-100 text-gray-700')); }
                        function fmt(n){ try { return new Intl.NumberFormat('en-PH',{ style:'currency', currency:'PHP' }).format(parseFloat(n||0)); } catch(_){ return '₱'+(parseFloat(n||0).toFixed(2)); } }
                        if (fee.app) fee.app.textContent = fmt(app.fee_application);
                        if (fee.insp) fee.insp.textContent = fmt(app.fee_inspection);
                        if (fee.mat) fee.mat.textContent = fmt(app.fee_materials);
                        if (fee.lab) fee.lab.textContent = fmt(app.fee_labor);
                        if (fee.dep) fee.dep.textContent = fmt(app.meter_deposit);
                        const total = (parseFloat(app.fee_application||0)+parseFloat(app.fee_inspection||0)+parseFloat(app.fee_materials||0)+parseFloat(app.fee_labor||0)+parseFloat(app.meter_deposit||0));
                        if (fee.total) fee.total.textContent = fmt(total);
                        if (link) link.href = `/payment?application_id=${encodeURIComponent(id)}`;
                        const roleOK = ['admin','cashier'].includes(String(currentRole||'').toLowerCase());
                        const canPay = roleOK && status==='assessed';
                        saveBtn.disabled = !canPay;
                        if (hint) hint.classList.toggle('hidden', canPay);
                    } catch(_){ /* keep disabled */ }
                    setTimeout(()=>{ try{ receiptNo.focus(); }catch(_){} }, 50);
                } else if (act==='schedule'){
                    schedId.value = id; schedDate.value=''; show(schedModal);
                } else if (act==='install'){
                    installId.value = id; show(installModal);
                }
            } catch(e){ toast('Action failed','error'); }
        });

        // Modal handlers
        // Assign Meter open buttons
        document.addEventListener('click', function(e){
            const btn = e.target?.closest?.('.assign-meter-btn');
            if (!btn) return;
            const status = (btn.getAttribute('data-cust-status')||'').toLowerCase();
            if (status !== 'active') { toast('Complete registration and verification before assigning a meter.','warning'); return; }
            const accountId = btn.getAttribute('data-account-id');
            const accountName = btn.getAttribute('data-account-name');
            if (amAccountId) amAccountId.value = accountId || '';
            if (amAccountName) amAccountName.value = accountName || '';
            if (amDate){
                const today = new Date();
                const y = today.getFullYear();
                const m = String(today.getMonth()+1).padStart(2,'0');
                const d = String(today.getDate()).padStart(2,'0');
                amDate.value = `${y}-${m}-${d}`;
            }
            if (amSearch) amSearch.value = '';
            if (amMeter) { amMeter.innerHTML = '<option value="">Loading...</option>'; }
            show(amModal);
            loadMeters();
        });
        if (amClose) amClose.addEventListener('click', ()=>hide(amModal));
        if (amModal){ amModal.addEventListener('click', function(ev){ if (ev.target === amModal) hide(amModal); }); }
        async function loadMeters(){
            try{
                const q = amSearch?.value?.trim() || '';
                const url = '/admin/meters/api?status=inventory' + (q? ('&q='+encodeURIComponent(q)):'');
                const res = await fetch(url, { headers:{ 'Accept':'application/json' } });
                if (!res.ok) throw 0;
                const data = await res.json();
                const items = Array.isArray(data.items) ? data.items : [];
                if (!amMeter) return;
                amMeter.innerHTML = items.length? items.map(m=>`<option value="${m.id}">${m.serial} (${m.size||'-'} ${m.type||''})</option>`).join('') : '<option value="">No inventory meters found</option>';
            } catch(_) {
                if (amMeter) amMeter.innerHTML = '<option value="">Failed to load</option>';
            }
        }
        if (amSearchBtn){ amSearchBtn.addEventListener('click', function(ev){ ev.preventDefault(); loadMeters(); }); }
        if (amSave){
            amSave.addEventListener('click', async function(){
                const meterId = amMeter?.value;
                const accountId = amAccountId?.value;
                const assignedAt = amDate?.value;
                if (!meterId){ toast('Select a meter','warning'); return; }
                if (!accountId){ toast('Missing customer','error'); return; }
                if (!assignedAt){ toast('Assigned date required','warning'); return; }
                amSave.disabled = true; const old = amSave.textContent; amSave.textContent = 'Assigning...';
                try{
                    const payload = { account_id: Number(accountId), assigned_at: assignedAt, reason: amReason?.value||null, notes: amNotes?.value||null };
                    const res = await fetch(`/admin/meters/${meterId}/assign`, { method:'POST', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token||'' }, body: JSON.stringify(payload) });
                    if (!res.ok) throw 0;
                    toast('Meter assigned','success');
                    hide(amModal);
                } catch(_){ toast('Assign failed','error'); } finally { amSave.disabled=false; amSave.textContent=old; }
            });
        }

        // Replace Meter handlers
        document.addEventListener('click', async function(e){
            // Verify Customer
            const verifyBtn = e.target?.closest?.('.verify-customer-btn');
            if (verifyBtn){
                const id = verifyBtn.getAttribute('data-cust-id');
                if (!id) return;
                try{
                    const res = await fetch(`/api/customer/${id}/verify`, { method:'PUT', headers:{ 'Accept':'application/json', 'X-CSRF-TOKEN': token||'' } });
                    if (!res.ok) throw 0;
                    const data = await res.json().catch(()=>({}));
                    toast(data.message||'Customer verified','success');
                    // Update UI: enable assign/replace in this row
                    const row = verifyBtn.closest('tr');
                    if (row){
                        row.querySelectorAll('.assign-meter-btn, .replace-meter-btn').forEach(el=>{
                            el.classList.remove('opacity-50','cursor-not-allowed');
                            el.setAttribute('data-cust-status','Active');
                            el.removeAttribute('title');
                        });
                        // Remove the verify button
                        verifyBtn.remove();
                        // If there's a status cell/pill, try to update text
                        const statusPill = row.querySelector('span.rounded-full');
                        if (statusPill) statusPill.textContent = 'Active';
                    }
                } catch(_){ toast('Failed to verify','error'); }
                return;
            }
            const btn = e.target?.closest?.('.replace-meter-btn');
            if (!btn) return;
            const status = (btn.getAttribute('data-cust-status')||'').toLowerCase();
            if (status !== 'active') { toast('Complete registration and verification before replacing a meter.','warning'); return; }
            const accountId = btn.getAttribute('data-account-id');
            const accountName = btn.getAttribute('data-account-name');
            if (rmAccountId) rmAccountId.value = accountId || '';
            if (rmAccountName) rmAccountName.value = accountName || '';
            // default dates
            const today = new Date();
            const y = today.getFullYear();
            const m = String(today.getMonth()+1).padStart(2,'0');
            const d = String(today.getDate()).padStart(2,'0');
            if (rmUnassignDate) rmUnassignDate.value = `${y}-${m}-${d}`;
            if (rmAssignDate) rmAssignDate.value = `${y}-${m}-${d}`;
            __rmCurrentMeterId = null;
            if (rmCurrent) rmCurrent.value = 'Loading...';
            if (rmSearch) rmSearch.value = '';
            if (rmMeter) rmMeter.innerHTML = '<option value="">Loading...</option>';
            show(rmModal);
            try{
                const res = await fetch(`/admin/meters/current?account_id=${encodeURIComponent(accountId)}`, { headers:{ 'Accept':'application/json' } });
                const data = await res.json().catch(()=>({}));
                const item = data?.item;
                __rmCurrentMeterId = item?.id || null;
                if (rmCurrent) rmCurrent.value = item? `${item.serial} (${item.size||'-'} ${item.type||''})` : 'No current meter';
            } catch(_) {
                if (rmCurrent) rmCurrent.value = 'Failed to load';
            }
            loadMetersForReplace();
        });
        if (rmClose) rmClose.addEventListener('click', ()=>hide(rmModal));
        if (rmModal){ rmModal.addEventListener('click', function(ev){ if (ev.target === rmModal) hide(rmModal); }); }
        async function loadMetersForReplace(){
            try{
                const q = rmSearch?.value?.trim() || '';
                const url = '/admin/meters/api?status=inventory' + (q? ('&q='+encodeURIComponent(q)):'');
                const res = await fetch(url, { headers:{ 'Accept':'application/json' } });
                if (!res.ok) throw 0;
                const data = await res.json();
                const items = Array.isArray(data.items) ? data.items : [];
                if (!rmMeter) return;
                rmMeter.innerHTML = items.length? items.map(m=>`<option value="${m.id}">${m.serial} (${m.size||'-'} ${m.type||''})</option>`).join('') : '<option value="">No inventory meters found</option>';
            } catch(_) {
                if (rmMeter) rmMeter.innerHTML = '<option value="">Failed to load</option>';
            }
        }
        if (rmSearchBtn){ rmSearchBtn.addEventListener('click', function(ev){ ev.preventDefault(); loadMetersForReplace(); }); }
        if (rmSave){
            rmSave.addEventListener('click', async function(){
                const oldId = __rmCurrentMeterId;
                const newId = rmMeter?.value;
                const accountId = rmAccountId?.value;
                const unassignAt = rmUnassignDate?.value;
                const assignAt = rmAssignDate?.value;
                const reason = rmReason?.value || 'Replacement';
                if (!newId){ toast('Select a new meter','warning'); return; }
                if (!accountId){ toast('Missing customer','error'); return; }
                if (!assignAt || !unassignAt){ toast('Dates are required','warning'); return; }
                rmSave.disabled = true; const oldText = rmSave.textContent; rmSave.textContent = 'Replacing...';
                try{
                    if (oldId){
                        const res1 = await fetch(`/admin/meters/${oldId}/unassign`, { method:'POST', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token||'' }, body: JSON.stringify({ unassigned_at: unassignAt, reason }) });
                        if (!res1.ok) throw 0;
                    }
                    const payload = { account_id: Number(accountId), assigned_at: assignAt, reason };
                    const res2 = await fetch(`/admin/meters/${newId}/assign`, { method:'POST', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token||'' }, body: JSON.stringify(payload) });
                    if (!res2.ok) throw 0;
                    toast('Meter replaced','success');
                    hide(rmModal);
                } catch(_){ toast('Replace failed','error'); } finally { rmSave.disabled=false; rmSave.textContent=oldText; }
            });
        }
        if (assessClose){ assessClose.addEventListener('click', ()=>hide(assessModal)); }
        [feeApp, feeInsp, feeMat, feeLab, feeDep].forEach(function(el){ if (el){ el.addEventListener('input', updateFeeTotal); }});
        if (assessSave){ assessSave.addEventListener('click', async function(){
            const id = assessId.value; assessSave.disabled = true; const old = assessSave.textContent; assessSave.textContent='Saving...';
            try {
                const payload = {
                    fee_application: parseFloat(feeApp.value||'0')||0,
                    fee_inspection: parseFloat(feeInsp.value||'0')||0,
                    fee_materials: parseFloat(feeMat.value||'0')||0,
                    fee_labor: parseFloat(feeLab.value||'0')||0,
                    meter_deposit: parseFloat(feeDep.value||'0')||0,
                };
                const res = await fetch(`/api/connections/${id}/assess`, { method:'PUT', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token||'' }, body: JSON.stringify(payload) });
                if (!res.ok) throw 0; toast('Assessed','success'); hide(assessModal); load();
            } catch(_) { toast('Save failed','error'); } finally { assessSave.disabled=false; assessSave.textContent=old; }
        }); }

        if (payClose){ payClose.addEventListener('click', ()=>hide(payModal)); }
        if (paySave){ paySave.addEventListener('click', async function(){
            const id = payId.value; if (!receiptNo.value){ toast('Receipt is required','warning'); return; }
            paySave.disabled = true; const old = paySave.textContent; paySave.textContent='Saving...';
            try {
                const res = await fetch(`/api/connections/${id}/pay`, { method:'PUT', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token||'' }, body: JSON.stringify({ payment_receipt_no: receiptNo.value }) });
                if (!res.ok) throw 0; toast('Marked as paid','success'); hide(payModal); load();
            } catch(_) { toast('Save failed','error'); } finally { paySave.disabled=false; paySave.textContent=old; }
        }); }

        if (receiptNo){
            receiptNo.addEventListener('keydown', function(e){ if (e.key==='Enter'){ e.preventDefault(); if (!paySave.disabled) paySave.click(); } });
        }

        if (schedClose){ schedClose.addEventListener('click', ()=>hide(schedModal)); }
        if (schedSave){ schedSave.addEventListener('click', async function(){
            const id = schedId.value; if (!schedDate.value){ toast('Schedule date required','warning'); return; }
            schedSave.disabled = true; const old = schedSave.textContent; schedSave.textContent='Saving...';
            try {
                const res = await fetch(`/api/connections/${id}/schedule`, { method:'PUT', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token||'' }, body: JSON.stringify({ schedule_date: schedDate.value }) });
                if (!res.ok) throw 0; toast('Scheduled','success'); hide(schedModal); load();
            } catch(_) { toast('Save failed','error'); } finally { schedSave.disabled=false; schedSave.textContent=old; }
        }); }

        if (installClose){ installClose.addEventListener('click', ()=>hide(installModal)); }
        if (installSave){ installSave.addEventListener('click', async function(){
            const id = installId.value; installSave.disabled = true; const old = installSave.textContent; installSave.textContent='Saving...';
            try {
                const res = await fetch(`/api/connections/${id}/install`, { method:'PUT', headers:{ 'Accept':'application/json','X-CSRF-TOKEN': token||'' } });
                if (!res.ok) throw 0; toast('Installed','success'); hide(installModal); load();
            } catch(_) { toast('Save failed','error'); } finally { installSave.disabled=false; installSave.textContent=old; }
        }); }

        // Simple cache to avoid refetch every keystroke
        let __lastItems = null;
        async function load(){
            try{ const data = await fetchApps(); __lastItems = data.items; render(data.items); }
            catch(e){ body.innerHTML = '<tr><td colspan="8" class="px-4 py-4 text-center text-red-500">Failed to load.</td></tr>'; }
        }
        if (search){
            search.addEventListener('input', function(){ if (__lastItems){ render(__lastItems); } });
        }
        if (exportBtn){
            exportBtn.addEventListener('click', function(){
                try{
                    const listContainer = Array.isArray(__lastItems?.data) ? __lastItems.data : (Array.isArray(__lastItems)? __lastItems : []);
                    const q = (search?.value||'').toLowerCase().trim();
                    const filtered = listContainer.filter(app => {
                        if (!q) return true;
                        const a = (app.applicant_name||'').toLowerCase();
                        const addr = (app.address||'').toLowerCase();
                        return a.includes(q) || addr.includes(q);
                    });
                    if (filtered.length===0){ toast('Nothing to export', 'warning'); return; }
                    const rows = [];
                    rows.push(['ID','Applicant','Address','Fee Total','Paid At','Schedule Date','Status']);
                    filtered.forEach(app => {
                        const total = (toNumber(app.fee_application)+toNumber(app.fee_inspection)+toNumber(app.fee_materials)+toNumber(app.fee_labor)+toNumber(app.meter_deposit));
                        rows.push([
                            app.id,
                            app.applicant_name||'',
                            app.address||'',
                            String(total||0),
                            fmtDate(app.paid_at),
                            fmtDate(app.schedule_date),
                            app.status||''
                        ]);
                    });
                    function csvEscape(v){
                        const s = String(v??'');
                        if (/[",\n]/.test(s)) return '"'+s.replace(/"/g,'""')+'"';
                        return s;
                    }
                    const csv = rows.map(r => r.map(csvEscape).join(',')).join('\n');
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    const date = new Date();
                    a.download = `applications_${date.toISOString().slice(0,10)}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(()=>{ document.body.removeChild(a); URL.revokeObjectURL(url); }, 0);
                } catch(e){ toast('Export failed', 'error'); }
            });
        }
        load();
    })();
    </script>
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

        const viewModal = document.getElementById('viewCustomerModal');
        const vcClose = document.getElementById('vcClose');
        const vcCloseBtn = document.getElementById('vcCloseBtn');
        const vcAccountNo = document.getElementById('vcAccountNo');
        const vcStatus = document.getElementById('vcStatus');
        const vcName = document.getElementById('vcName');
        const vcContact = document.getElementById('vcContact');
        const vcAddress = document.getElementById('vcAddress');
        const vcClassification = document.getElementById('vcClassification');
        const vcCreated = document.getElementById('vcCreated');
        const vcExtra = document.getElementById('vcExtra');

        function openModal(){ if (modal){ modal.classList.remove('hidden'); modal.classList.add('flex'); } }
        function closeModal(){ if (modal){ modal.classList.add('hidden'); modal.classList.remove('flex'); } }

        function openViewModal(){ if (viewModal){ viewModal.classList.remove('hidden'); viewModal.classList.add('flex'); } }
        function closeViewModal(){ if (viewModal){ viewModal.classList.add('hidden'); viewModal.classList.remove('flex'); } }

        function statusBadgeClass(status){
            const s = String(status||'').toLowerCase();
            if (s === 'active') return 'bg-green-100 text-green-700';
            if (s === 'inactive' || s === 'disconnected') return 'bg-red-100 text-red-700';
            return 'bg-yellow-100 text-yellow-700';
        }

        async function loadCustomerAndShow(id){
            if (!id || !viewModal) return;
            try{
                if (vcAccountNo) vcAccountNo.textContent = 'Loading…';
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/api/customer/${id}`, { headers:{ 'Accept':'application/json','X-CSRF-TOKEN': token || '' } });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json().catch(()=>({}));
                const cust = data.customer || data || {};
                if (vcAccountNo) vcAccountNo.textContent = cust.account_no || '—';
                if (vcName) vcName.textContent = cust.name || '—';
                if (vcContact) vcContact.textContent = cust.contact_no || '—';
                if (vcAddress) vcAddress.textContent = cust.address || '—';
                if (vcClassification) vcClassification.textContent = cust.classification || '—';
                if (vcCreated) vcCreated.textContent = (cust.created_at || '').toString().slice(0,10) || '—';
                if (vcStatus){
                    vcStatus.textContent = cust.status || '—';
                    vcStatus.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] ' + statusBadgeClass(cust.status);
                }
                if (vcExtra){
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
                    vcExtra.innerHTML = rows.length ? rows.join('') : '<div class="text-gray-400">No additional data.</div>';
                }
                openViewModal();
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
        if (vcClose){ vcClose.addEventListener('click', closeViewModal); }
        if (vcCloseBtn){ vcCloseBtn.addEventListener('click', closeViewModal); }
        if (viewModal){ viewModal.addEventListener('click', function(ev){ if (ev.target === viewModal) closeViewModal(); }); }
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
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Search</label>
                    <input type="text" placeholder="Name, Account No., Address" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Status</label>
                    <select class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm">
                        <option value="">All</option>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Connection Type</label>
                    <select class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm">
                        <option value="">All</option>
                        <option>Residential</option>
                        <option>Commercial</option>
                        <option>Industrial</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Created</label>
                    <input type="date" class="w-full border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-sm" />
                </div>
            </div>
            <div class="flex gap-2">
                <button class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Apply</button>
                <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Reset</button>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
                <button class="px-3 py-2 rounded-md bg-emerald-600 text-white text-sm disabled:opacity-50" :disabled="selected.size===0">Activate</button>
                <button class="px-3 py-2 rounded-md bg-amber-600 text-white text-sm disabled:opacity-50" :disabled="selected.size===0">Deactivate</button>
                <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm disabled:opacity-50" :disabled="selected.size===0">Export CSV</button>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Import CSV</button>
                <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">Merge Duplicates</button>
                <span class="text-xs text-gray-500" x-text="selected.size + ' selected'"></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm js-datatable">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 w-10">
                            <input type="checkbox" @change="all=$event.target.checked; selected = new Set(all?[1,2,3,4,5]:[])" />
                        </th>
                        <th class="px-4 py-2 text-left">Account No.</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Connection</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-100">
                    @forelse($customers as $c)
                    <tr data-cust-id="{{ $c->id }}" data-cust-status="{{ $c->status }}">
                        <td class="px-4 py-2">
                            <input type="checkbox" @change="($event.target.checked?selected.add({{ $c->id }}):selected.delete({{ $c->id }}))" />
                        </td>
                        <td class="px-4 py-2 font-mono">{{ $c->account_no }}</td>
                        <td class="px-4 py-2">{{ $c->name }}</td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-1 text-xs app-badges">
                                <span class="text-gray-400">Loading…</span>
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            @php $color = ($c->status === 'Active') ? 'bg-green-100 text-green-700' : (($c->status === 'Inactive' || $c->status === 'Disconnected') ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'); @endphp
                            <span class="px-2 py-1 rounded-full text-xs {{ $color }}">{{ $c->status ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-2">{{ optional($c->created_at)->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="inline-flex items-center gap-2">
                                <button class="text-blue-600 view-customer-btn" data-cust-id="{{ $c->id }}">View</button>
                                <details class="relative inline-block">
                                    <summary class="list-none px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 cursor-pointer text-xs">Actions ▾</summary>
                                    <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow z-10 py-1">
                                        <div class="px-3 py-1 text-[10px] uppercase text-gray-400">Application</div>
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 app-rescore-btn hidden"
                                            data-cust-id="{{ $c->id }}">
                                            Re-score
                                        </button>
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 app-approve-btn hidden"
                                            data-cust-id="{{ $c->id }}">
                                            Approve
                                        </button>
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 app-reject-btn hidden"
                                            data-cust-id="{{ $c->id }}">
                                            Reject
                                        </button>
                                        <a class="w-full block px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 app-viewkyc-link hidden"
                                            target="_blank">View KYC</a>
                                        <div class="px-3 py-1 text-[10px] uppercase text-gray-400">Customer</div>
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 edit-customer-btn"
                                            data-cust-id="{{ $c->id }}"
                                            data-cust-name="{{ $c->name }}"
                                            data-cust-address="{{ $c->address }}"
                                            data-cust-contact="{{ $c->contact_no }}">
                                            Edit
                                        </button>
                                        @if (strtolower($c->status) !== 'active')
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 verify-customer-btn"
                                            data-cust-id="{{ $c->id }}">
                                            Verify Customer
                                        </button>
                                        @endif
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 assign-meter-btn {{ strtolower($c->status) !== 'active' ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            data-account-id="{{ $c->id }}"
                                            data-account-name="{{ $c->name }}"
                                            data-cust-status="{{ $c->status }}"
                                            title="{{ strtolower($c->status) !== 'active' ? 'Customer not active. Complete registration/verification first.' : '' }}">
                                            Assign Meter
                                        </button>
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 replace-meter-btn {{ strtolower($c->status) !== 'active' ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            data-account-id="{{ $c->id }}"
                                            data-account-name="{{ $c->name }}"
                                            data-cust-status="{{ $c->status }}"
                                            title="{{ strtolower($c->status) !== 'active' ? 'Customer not active. Complete registration/verification first.' : '' }}">
                                            Replace Meter
                                        </button>
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">No customers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-4 text-xs text-gray-500">
            <div>
                @if($customers->total() > 0)
                    Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }}
                @else
                    Showing 0 of 0
                @endif
            </div>
            <div class="flex items-center gap-1">
                {{ $customers->links() }}
            </div>
        </div>
    </div>

    <!-- Customer Details Modal (view only) -->
    <div id="viewCustomerModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-2xl">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-100">Customer Details</h3>
                <button id="vcClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Close</button>
            </div>
            <div class="px-5 py-4 space-y-4 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Account No.</div>
                        <div id="vcAccountNo" class="font-mono font-medium text-gray-900 dark:text-gray-100">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
                        <div id="vcStatus" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-gray-100 text-gray-700">—</div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Name</div>
                        <div id="vcName" class="font-medium text-gray-900 dark:text-gray-100">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Contact No.</div>
                        <div id="vcContact" class="text-gray-800 dark:text-gray-200">—</div>
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Address</div>
                    <div id="vcAddress" class="text-gray-800 dark:text-gray-200">—</div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Connection Type</div>
                        <div id="vcClassification" class="text-gray-800 dark:text-gray-200">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Created At</div>
                        <div id="vcCreated" class="text-gray-800 dark:text-gray-200">—</div>
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Other Fields</div>
                    <div class="max-h-40 overflow-auto border border-gray-200 dark:border-gray-700 rounded-md p-2 text-xs" id="vcExtra">
                        <div class="text-gray-400">No additional data.</div>
                    </div>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button id="vcCloseBtn" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
