@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-7xl mx-auto p-8 space-y-8 font-sans">
    
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Toggle Buttons -->
    <div class="flex justify-end space-x-3">
        <button id="existingBtn" onclick="showPanel('existing')"
            class="px-5 h-[30px] rounded-[5px] text-sm font-medium 
                   bg-blue-600 text-white shadow hover:bg-blue-700 transition">
            Existing Customer
        </button>
        <button id="newBtn" onclick="showPanel('new')"
            class="px-5 h-[30px] rounded-[5px] text-sm font-medium 
                   bg-gray-200 text-gray-800 hover:bg-gray-300 shadow
                   dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600 transition">
            New Customer
        </button>
    </div>

    <!-- Existing Customer Section -->
    <div id="existingPanel" class="space-y-6">
        <!-- Register Section -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100 tracking-wide">
                Customer Register
            </h2>
            <style>
                /* Overflow Menu Styles */
                .overflow-menu-btn {
                    transition: all 0.2s ease;
                }
                
                .overflow-menu-btn:hover {
                    transform: scale(1.05);
                }
                
                .overflow-menu {
                    animation: fadeIn 0.15s ease-out;
                }
                
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(-5px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .overflow-menu {
                        width: 44rem; /* w-44 equivalent */
                        right: -1rem; /* Adjust positioning on mobile */
                    }
                }
                
                /* Ensure proper z-index stacking */
                .customer-card {
                    position: relative;
                    z-index: 1;
                }
                
                .overflow-menu {
                    z-index: 50;
                }
            </style>
            <form class="flex space-x-3 mb-6" id="searchForm">
                <input type="text" name="search" placeholder="Search by name, address, or account no."
                    id="searchInput"
                    class="flex-1 border rounded-[5px] px-4 h-[50px] text-sm shadow-sm
                           bg-white dark:bg-gray-700
                           text-gray-800 dark:text-gray-100
                           border-gray-300 dark:border-gray-600
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" id="searchBtn"
                    class="bg-blue-600 text-white px-5 h-[50px] rounded-[5px] text-sm font-medium 
                           hover:bg-blue-700 transition shadow">
                    Search
                </button>
                <button type="button" id="clearBtn" style="display: none;"
                    class="bg-gray-500 text-white px-5 h-[50px] rounded-[5px] text-sm font-medium 
                           hover:bg-gray-600 transition shadow flex items-center">
                    Clear
                </button>
            </form>

            <!-- Dynamic Results with Overflow Menu -->
            <div id="customersContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse (($customers ?? []) as $c)
                <div class="customer-card bg-gray-50 dark:bg-gray-700 border dark:border-gray-600 
                            rounded-[5px] p-5 shadow-md hover:shadow-lg transition relative flex flex-col h-full"
                     data-account-no="{{ $c->account_no }}"
                     data-name="{{ $c->name }}"
                     data-address="{{ $c->address }}"
                     data-meter-no="{{ $c->meter_no }}"
                     data-meter-size="{{ $c->meter_size }}"
                     data-status="{{ $c->status }}">
                    <!-- Card Header with Menu -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-lg text-gray-800 dark:text-gray-100 truncate">{{ $c->name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            Acct. {{ $c->account_no }} - 
                            <span class="text-green-600 dark:text-green-400 font-medium">Active</span>
                        </p>
                        </div>
                        
                        <!-- Overflow Menu -->
                        <div class="relative ml-3 flex-shrink-0">
                            <button class="overflow-menu-btn p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                    onclick="event.stopPropagation(); toggleOverflowMenu(this)"
                                    data-account-no="{{ $c->account_no }}"
                                    data-name="{{ $c->name }}"
                                    data-address="{{ $c->address }}"
                                    data-meter-no="{{ $c->meter_no }}"
                                    data-meter-size="{{ $c->meter_size }}"
                                    data-status="{{ $c->status }}">
                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="overflow-menu absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg z-10 hidden">
                                <div class="py-1">
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            onclick="handleTransferOwnership(this)">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                            Transfer Ownership
                                        </div>
                                    </button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            onclick="handleReconnectService(this)">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            Reconnect Service
                                        </div>
                                    </button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            onclick="handleUpdateMeter(this)">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Update Meter
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="flex-1 flex flex-col">
                        <p class="text-sm text-gray-500 dark:text-gray-400 flex-1">{{ $c->address }}</p>
                    </div>
                </div>
                @empty
                <div class="col-span-2 text-center py-8">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No customers found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            @if($search ?? false)
                                Try adjusting your search terms.
                            @else
                                No customers are currently registered.
                            @endif
                        </p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- New Customer Section -->
    <div id="newPanel" class="hidden">
        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100 tracking-wide flex items-center gap-2">
                <!-- Heroicon: UserPlus -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18 9v6m3-3h-6m-2-4a4 4 0 11-8 0 4 4 0 018 0zm-6 8a6 6 0 0112 0H7z" />
                </svg>
                Register New Account
            </h2>

            <form method="POST" action="{{ route('register.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @csrf
                <!-- First Name -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}"
                           class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                                  bg-white dark:bg-gray-700
                                  text-gray-800 dark:text-gray-100
                                  border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-500 @enderror"
                           required>
                    @error('first_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                           class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                                  bg-white dark:bg-gray-700
                                  text-gray-800 dark:text-gray-100
                                  border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 @error('last_name') border-red-500 @enderror"
                           required>
                    @error('last_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Address</label>
                    <textarea name="address" rows="3" value="{{ old('address') }}"
                              class="w-full border rounded-xl px-4 py-2 text-sm shadow-sm
                                     bg-white dark:bg-gray-700
                                     text-gray-800 dark:text-gray-100
                                     border-gray-300 dark:border-gray-600
                                     focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">{{ old('address', 'Barangay Manambulan, Davao City') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}"
                           class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                                  bg-white dark:bg-gray-700
                                  text-gray-800 dark:text-gray-100
                                  border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 @error('contact_number') border-red-500 @enderror">
                    @error('contact_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}"
                           class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                                  bg-white dark:bg-gray-700
                                  text-gray-800 dark:text-gray-100
                                  border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-500 @enderror"
                           required>
                    @error('start_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Meter No. -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Meter No.</label>
                    <input type="text" name="meter_no" value="{{ old('meter_no') }}"
                           class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                                  bg-white dark:bg-gray-700
                                  text-gray-800 dark:text-gray-100
                                  border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 @error('meter_no') border-red-500 @enderror">
                    @error('meter_no')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Meter Size -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Meter Size</label>
                    <select name="meter_size"
                            class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                                   bg-white dark:bg-gray-700
                                   text-gray-800 dark:text-gray-100
                                   border-gray-300 dark:border-gray-600
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 @error('meter_size') border-red-500 @enderror">
                        <option value="">Select Size</option>
                        <option value="1/2\"" {{ old('meter_size') == '1/2"' ? 'selected' : '' }}>1/2"</option>
                        <option value="3/4\"" {{ old('meter_size') == '3/4"' ? 'selected' : '' }}>3/4"</option>
                        <option value="1\"" {{ old('meter_size') == '1"' ? 'selected' : '' }}>1"</option>
                        <option value="2\"" {{ old('meter_size') == '2"' ? 'selected' : '' }}>2"</option>
                    </select>
                    @error('meter_size')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Connection Classification -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                        Connection Classification
                    </label>
                    <select name="classification"
                            class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                                   bg-white dark:bg-gray-700
                                   text-gray-800 dark:text-gray-100
                                   border-gray-300 dark:border-gray-600
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 @error('classification') border-red-500 @enderror"
                            required>
                        <option value="">Select Classification</option>
                        <option value="Residential" {{ old('classification') == 'Residential' ? 'selected' : '' }}>Residential</option>
                        <option value="Commercial" {{ old('classification') == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="Industrial" {{ old('classification') == 'Industrial' ? 'selected' : '' }}>Industrial</option>
                        <option value="Agricultural" {{ old('classification') == 'Agricultural' ? 'selected' : '' }}>Agricultural</option>
                    </select>
                    @error('classification')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="col-span-2">
                    <button type="submit" class="w-full bg-blue-600 text-white h-[50px] rounded-xl text-sm font-medium 
                                   hover:bg-blue-700 shadow-md transition flex items-center justify-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M18 9v6m3-3h-6m-2-4a4 4 0 11-8 0 4 4 0 018 0zm-6 8a6 6 0 0112 0H7z" />
                        </svg>
                        <span>Register Account</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Existing Modal -->
<x-modal name="register-existing" :show="false">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Register Existing Customer</h3>
        <form id="registerExistingForm" class="space-y-4">
            <input type="hidden" name="account_no" id="modal_account_no">
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Account No.</label>
                <input type="text" id="modal_account_no_display" class="w-full border rounded px-3 h-[40px] bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Customer Name</label>
                <input type="text" id="modal_customer_name" name="customer_name" class="w-full border rounded px-3 h-[40px]" required>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Initial Previous Reading</label>
                <input type="number" id="modal_prev_reading" name="previous_reading" class="w-full border rounded px-3 h-[40px]" min="0" step="0.01" value="0">
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" class="px-4 h-[40px] rounded bg-gray-300" onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'register-existing'}))">Cancel</button>
                <button type="submit" class="px-4 h-[40px] rounded bg-blue-600 text-white">Save</button>
            </div>
        </form>
    </div>
    <script>
        function openRegisterExistingModal(acct, name){
            document.getElementById('modal_account_no').value = acct;
            document.getElementById('modal_account_no_display').value = acct;
            document.getElementById('modal_customer_name').value = name;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'register-existing' }));
        }
        document.getElementById('registerExistingForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                account_no: document.getElementById('modal_account_no').value,
                customer_name: document.getElementById('modal_customer_name').value,
                previous_reading: parseFloat(document.getElementById('modal_prev_reading').value) || 0
            };
            const res = await fetch("{{ route('customer.attach') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '' },
                body: JSON.stringify(payload)
            });
            if(res.ok){
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'register-existing' }));
                showToast('Customer registered to existing account.', 'success');
            } else {
                showToast('Failed to register customer.', 'error');
            }
        });
    </script>
</x-modal>

<!-- Transfer Ownership Modal -->
<x-modal name="transfer-ownership" :show="false">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Transfer Ownership</h3>
        <form id="transferOwnershipForm" class="space-y-4">
            <input type="hidden" name="account_no" id="transfer_account_no">
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Account No.</label>
                <input type="text" id="transfer_account_no_display" class="w-full border rounded px-3 h-[40px] bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Current Owner</label>
                <input type="text" id="transfer_current_owner" class="w-full border rounded px-3 h-[40px] bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">New Owner Full Name</label>
                <input type="text" id="transfer_new_owner" name="new_name" class="w-full border rounded px-3 h-[40px]" required>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
                <textarea id="transfer_notes" name="notes" rows="3" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" class="px-4 h-[40px] rounded bg-gray-300" onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'transfer-ownership'}))">Cancel</button>
                <button type="submit" class="px-4 h-[40px] rounded bg-blue-600 text-white">Transfer</button>
            </div>
        </form>
    </div>
    <script>
        function openTransferOwnershipModal(acct, name){
            document.getElementById('transfer_account_no').value = acct;
            document.getElementById('transfer_account_no_display').value = acct;
            document.getElementById('transfer_current_owner').value = name;
            document.getElementById('transfer_new_owner').value = name;
            document.getElementById('transfer_notes').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'transfer-ownership' }));
        }

        document.getElementById('transferOwnershipForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                account_no: document.getElementById('transfer_account_no').value,
                new_name: document.getElementById('transfer_new_owner').value,
                notes: document.getElementById('transfer_notes').value || null
            };

            try {
                const res = await fetch("{{ route('customer.transfer') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '' },
                    body: JSON.stringify(payload)
                });

                    if (res.ok) {
                    const data = await res.json();
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'transfer-ownership' }));
                    showToast(data.message || 'Ownership transferred', 'success');
                    // Update card name if present on page
                    const btn = document.querySelector(`[data-account-no='${payload.account_no}']`);
                    if (btn) {
                        btn.setAttribute('data-name', data.customer.name);
                        const nameEl = btn.parentElement.querySelector('p');
                        if (nameEl) nameEl.textContent = data.customer.name;
                    }
                } else {
                    const err = await res.json().catch(() => ({}));
                    showToast(err.message || 'Failed to transfer ownership', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Network error while transferring ownership', 'error');
            }
        });
    </script>
</x-modal>

<!-- Reconnect Service Modal -->
<x-modal name="reconnect-service" :show="false">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Reconnect Service</h3>
        <form id="reconnectServiceForm" class="space-y-4">
            <input type="hidden" name="account_no" id="reconnect_account_no">
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Account No.</label>
                <input type="text" id="reconnect_account_no_display" class="w-full border rounded px-3 h-[40px] bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Customer Name</label>
                <input type="text" id="reconnect_customer_name" class="w-full border rounded px-3 h-[40px] bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
                <textarea id="reconnect_notes" name="notes" rows="3" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" class="px-4 h-[40px] rounded bg-gray-300" onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'reconnect-service'}))">Cancel</button>
                <button type="submit" class="px-4 h-[40px] rounded bg-blue-600 text-white">Reconnect</button>
            </div>
        </form>
    </div>
    <script>
        function openReconnectServiceModal(acct, name){
            document.getElementById('reconnect_account_no').value = acct;
            document.getElementById('reconnect_account_no_display').value = acct;
            document.getElementById('reconnect_customer_name').value = name;
            document.getElementById('reconnect_notes').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reconnect-service' }));
        }

        document.getElementById('reconnectServiceForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                account_no: document.getElementById('reconnect_account_no').value,
                notes: document.getElementById('reconnect_notes').value || null
            };

            try {
                const res = await fetch("{{ route('customer.reconnect') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '' },
                    body: JSON.stringify(payload)
                });

                    if (res.ok) {
                    const data = await res.json();
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'reconnect-service' }));
                    showToast(data.message || 'Service reconnected', 'success');
                    // Update status badge if present
                    const btn = document.querySelector(`[data-account-no='${payload.account_no}']`);
                    if (btn) {
                        const statusEl = btn.parentElement.querySelector('.text-green-600');
                        if (statusEl) statusEl.textContent = 'Active';
                    }
                } else {
                    const err = await res.json().catch(() => ({}));
                    showToast(err.message || 'Failed to reconnect service', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Network error while reconnecting service', 'error');
            }
        });
    </script>
</x-modal>

<!-- Toggle Script -->
<script>
    let searchTimeout = null;

    function showPanel(panel) {
        const existingPanel = document.getElementById('existingPanel');
        const newPanel = document.getElementById('newPanel');
        const existingBtn = document.getElementById('existingBtn');
        const newBtn = document.getElementById('newBtn');

        if (panel === 'existing') {
            existingPanel.classList.remove('hidden');
            newPanel.classList.add('hidden');
            existingBtn.classList.add('bg-blue-600','text-white');
            newBtn.classList.remove('bg-green-600','text-white');
            newBtn.classList.add('bg-gray-200','dark:bg-gray-700');
        } else {
            newPanel.classList.remove('hidden');
            existingPanel.classList.add('hidden');
            newBtn.classList.add('bg-green-600','text-white');
            existingBtn.classList.remove('bg-blue-600','text-white');
            existingBtn.classList.add('bg-gray-200','dark:bg-gray-700');
        }
    }

    // Dynamic search functionality
    function performSearch() {
        const searchTerm = document.getElementById('searchInput').value.trim();
        const clearBtn = document.getElementById('clearBtn');
        
        if (searchTerm) {
            clearBtn.style.display = 'block';
        } else {
            clearBtn.style.display = 'none';
        }

        // Clear existing timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Set new timeout for search
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('register.search') }}?search=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    updateCustomersList(data.customers);
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        }, 300); // 300ms delay for better UX
    }

    // Update customers list with search results
    function updateCustomersList(customers) {
        const container = document.getElementById('customersContainer');
        
        if (customers.length === 0) {
            container.innerHTML = `
                <div class="col-span-2 text-center py-8">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No customers found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms.</p>
                    </div>
                </div>
            `;
            return;
        }

        container.innerHTML = customers.map(customer => `
            <div class="customer-card bg-gray-50 dark:bg-gray-700 border dark:border-gray-600 
                        rounded-[5px] p-5 shadow-md hover:shadow-lg transition relative flex flex-col h-full"
                 data-account-no="${customer.account_no}"
                 data-name="${customer.name}"
                 data-address="${customer.address}"
                 data-meter-no="${customer.meter_no || ''}"
                 data-meter-size="${customer.meter_size || ''}"
                 data-status="${customer.status}">
                <!-- Card Header with Menu -->
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-lg text-gray-800 dark:text-gray-100 truncate">${customer.name}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            Acct. ${customer.account_no} - 
                            <span class="text-green-600 dark:text-green-400 font-medium">Active</span>
                        </p>
                    </div>
                    
                    <!-- Overflow Menu -->
                    <div class="relative ml-3 flex-shrink-0">
                        <button class="overflow-menu-btn p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                onclick="event.stopPropagation(); toggleOverflowMenu(this)"
                        data-account-no="${customer.account_no}"
                        data-name="${customer.name}"
                        data-address="${customer.address}"
                        data-meter-no="${customer.meter_no || ''}"
                        data-meter-size="${customer.meter_size || ''}"
                                data-status="${customer.status}">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="overflow-menu absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg z-10 hidden">
                            <div class="py-1">
                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        onclick="handleTransferOwnership(this)">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                        Transfer Ownership
                                    </div>
                                </button>
                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        onclick="handleReconnectService(this)">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Reconnect Service
                                    </div>
                                </button>
                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        onclick="handleUpdateMeter(this)">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Update Meter
                                    </div>
                    </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card Content -->
                <div class="flex-1 flex flex-col">
                    <p class="text-sm text-gray-500 dark:text-gray-400 flex-1">${customer.address}</p>
                </div>
            </div>
        `).join('');
    }

    // Overflow Menu Functions
    function toggleOverflowMenu(button) {
        // Close all other menus first
        document.querySelectorAll('.overflow-menu').forEach(menu => {
            if (menu !== button.nextElementSibling) {
                menu.classList.add('hidden');
            }
        });
        
        // Toggle current menu
        const menu = button.nextElementSibling;
        menu.classList.toggle('hidden');
    }

    function handleTransferOwnership(button) {
        const menu = button.closest('.overflow-menu');
        const menuBtn = menu.previousElementSibling;
        const accountNo = menuBtn.getAttribute('data-account-no');
        const name = menuBtn.getAttribute('data-name');
        
        // Close menu
        menu.classList.add('hidden');
        
        // Show confirmation and handle action
        // Open modal to capture new owner name and notes
        openTransferOwnershipModal(accountNo, name);
    }

    function handleReconnectService(button) {
        const menu = button.closest('.overflow-menu');
        const menuBtn = menu.previousElementSibling;
        const accountNo = menuBtn.getAttribute('data-account-no');
        const name = menuBtn.getAttribute('data-name');
        
        // Close menu
        menu.classList.add('hidden');
        
        // Show confirmation and handle action
        // Open modal to capture optional notes and confirm reconnect
        openReconnectServiceModal(accountNo, name);
    }

    function handleUpdateMeter(button) {
        const menu = button.closest('.overflow-menu');
        const menuBtn = menu.previousElementSibling;
        const accountNo = menuBtn.getAttribute('data-account-no');
        const name = menuBtn.getAttribute('data-name');
        const meterNo = menuBtn.getAttribute('data-meter-no');
        const meterSize = menuBtn.getAttribute('data-meter-size');
        
        // Close menu
        menu.classList.add('hidden');
        
        // Show confirmation and handle action
        if (confirm(`Update meter for ${name} (Account: ${accountNo})?\nCurrent Meter: ${meterNo} (${meterSize})`)) {
            // Here you would typically make an API call or show a modal
            showToast('Update Meter functionality would be implemented here', 'info');
        }
    }

    // Close overflow menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.overflow-menu-btn') && !e.target.closest('.overflow-menu')) {
            document.querySelectorAll('.overflow-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });


    // Clear search
    function clearSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('clearBtn').style.display = 'none';
        performSearch(); // This will load all customers
    }

    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Search input event listener
        document.getElementById('searchInput').addEventListener('input', performSearch);
        
        // Search button event listener
        document.getElementById('searchBtn').addEventListener('click', performSearch);
        
        // Clear button event listener
        document.getElementById('clearBtn').addEventListener('click', clearSearch);
        
        // Enter key on search input
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    });
</script>
@endsection

<script>
    // showToast helper uses the global toast container in the layout
    function showToast(message, type = 'info', timeout = 4000) {
        const container = document.getElementById('toast-container');
        if (!container) return alert(message);
        const toast = document.createElement('div');
        const bg = type === 'error' ? 'bg-red-500' : (type === 'success' ? 'bg-green-500' : 'bg-blue-500');
        toast.className = `${bg} text-white px-4 py-2 rounded shadow-md max-w-sm pointer-events-auto`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, timeout);
    }
</script>

