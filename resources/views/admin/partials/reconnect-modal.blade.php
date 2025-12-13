<div id="reconnectCustomerModal" class="hidden fixed inset-0 z-40 items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl sm:max-w-3xl mx-4 overflow-hidden transform transition duration-200 scale-95 opacity-0 flex flex-col max-h-[92vh]" data-reconnect-dialog>
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reconnect Service</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Review the required steps before confirming the reconnection.</p>
            </div>
            <button type="button" id="closeReconnectModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <span class="sr-only">Close</span>
                &times;
            </button>
        </div>
        <div class="px-6 py-5 space-y-5 overflow-y-auto flex-1">
            <input type="hidden" id="reconnectAccountInput" />
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-300">Customer</p>
                <div class="mt-2 grid gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Account Number</p>
                        <p id="reconnectAccountDisplay" class="text-sm font-semibold text-gray-900 dark:text-gray-100">—</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer Name</p>
                        <p id="reconnectCustomerNameDisplay" class="text-sm font-semibold text-gray-900 dark:text-gray-100">—</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-gray-900">
                <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reconnection process</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Mark each stage as completed to enable the confirmation button.</p>
                </div>

                <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-center justify-between text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-2">
                        <span id="reconnectProgressLabel">0 of 7 steps completed</span>
                        <span id="reconnectProgressPercent">0%</span>
                    </div>
                    <div class="h-2 w-full bg-gray-200 dark:bg-gray-800 rounded-full overflow-hidden">
                        <div id="reconnectProgressFill" class="h-2 bg-blue-500 rounded-full transition-all duration-300" style="width:0%;"></div>
                    </div>
                </div>

                @php
                    $processSteps = [
                        [
                            'title' => 'Intake & Verification',
                            'detail' => 'Log the request, confirm customer identity, review outstanding balances and disconnection reason.',
                        ],
                        [
                            'title' => 'Filing & Initial Payment',
                            'detail' => 'Issue and record the ₱100 filing fee; open the reconnect ticket.',
                        ],
                        [
                            'title' => 'Site & Technical Assessment',
                            'detail' => 'Schedule the estimator/technician visit and document any required repairs or safety issues.',
                        ],
                        [
                            'title' => 'Approval & Order of Payment',
                            'detail' => 'Supervisor reviews documents, approves reconnection, and generates the detailed order of payment.',
                        ],
                        [
                            'title' => 'Final Payment & Scheduling',
                            'detail' => 'Collect final charges, update the ticket, and coordinate the installation schedule with field crews.',
                        ],
                        [
                            'title' => 'Execution & Quality Check',
                            'detail' => 'Reconnect service or reinstall meter, test for leaks, and capture completion photos with technician sign-off.',
                        ],
                        [
                            'title' => 'Close-out & Notifications',
                            'detail' => 'Set account status to Active, resume billing, and notify the customer of reconnection and next billing date.',
                        ],
                    ];
                @endphp

                <ol class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($processSteps as $index => $step)
                        <li class="reconnect-step-item px-4 py-4 bg-white/90 dark:bg-gray-900/80 transition-colors duration-200">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" class="reconnect-step-checkbox mt-1 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500"
                                    data-step-index="{{ $index }}">
                                <div class="space-y-1">
                                    <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Step {{ $index + 1 }}</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $step['title'] }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-snug">{{ $step['detail'] }}</p>
                                </div>
                            </label>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div>
                <label for="reconnectNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
                <textarea id="reconnectNotes" class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                <span class="font-semibold text-gray-700 dark:text-gray-200">Note:</span> Completing these steps will set the customer status to <span class="text-green-600 dark:text-green-400">Active</span>.
            </div>
            <div class="flex gap-3">
                <button type="button" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100" id="cancelReconnectBtn">Cancel</button>
                <button type="button" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed" id="confirmReconnectBtn" disabled>
                    <span>Confirm Reconnection</span>
                </button>
            </div>
        </div>
    </div>
</div>
