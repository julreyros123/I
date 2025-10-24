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
                /* Keep hover highlight and persistent selection */
                .customer-card.selected,
                .customer-card.hovering,
                .customer-card:hover { background-color: #f3f4f6 !important; }
                /* Dark mode highlight */
                .dark .customer-card.selected,
                .dark .customer-card.hovering,
                .dark .customer-card:hover { background-color: #374151 !important; /* gray-700 */ }
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

            <!-- Dynamic Results with Register button -->
            <div id="customersContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse (($customers ?? []) as $c)
                <div class="customer-card bg-gray-50 dark:bg-gray-700 border dark:border-gray-600 
                            rounded-[5px] p-5 shadow-md hover:shadow-lg transition cursor-pointer"
                     data-account-no="{{ $c->account_no }}"
                     data-name="{{ $c->name }}"
                     data-address="{{ $c->address }}"
                     data-meter-no="{{ $c->meter_no }}"
                     data-meter-size="{{ $c->meter_size }}"
                     data-status="{{ $c->status }}"
                     onmouseenter="showProfilePreview(this)"
                     onmouseleave="hideProfilePreview()"
                     onclick="selectCustomerCard(this)">
                    <div>
                        <p class="font-semibold text-lg text-gray-800 dark:text-gray-100">{{ $c->name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            Acct. {{ $c->account_no }} - 
                            <span class="text-green-600 dark:text-green-400 font-medium">Active</span>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $c->address }}</p>
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

        <!-- Profile and Action Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Profile -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">
                <h3 class="text-xl font-semibold mb-5 text-gray-800 dark:text-gray-100">
                    Profile Information
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Account No.</label>
                        <input type="text" id="profile_account_no" readonly
                               class="w-full border rounded-[5px] px-4 h-[30px] text-sm shadow-sm
                                      bg-gray-100 dark:bg-gray-600
                                      text-gray-800 dark:text-gray-100
                                      border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Name</label>
                        <input type="text" id="profile_name" readonly
                               class="w-full border rounded-[5px] px-4 h-[30px] text-sm shadow-sm
                                      bg-gray-100 dark:bg-gray-600
                                      text-gray-800 dark:text-gray-100
                                      border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Address</label>
                        <input type="text" id="profile_address" readonly
                               class="w-full border rounded-[5px] px-4 h-[30px] text-sm shadow-sm
                                      bg-gray-100 dark:bg-gray-600
                                      text-gray-800 dark:text-gray-100
                                      border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Meter No.</label>
                        <input type="text" id="profile_meter_no" readonly
                               class="w-full border rounded-[5px] px-4 h-[30px] text-sm shadow-sm
                                      bg-gray-100 dark:bg-gray-600
                                      text-gray-800 dark:text-gray-100
                                      border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Meter Size</label>
                        <input type="text" id="profile_meter_size" readonly
                               class="w-full border rounded-[5px] px-4 h-[30px] text-sm shadow-sm
                                      bg-gray-100 dark:bg-gray-600
                                      text-gray-800 dark:text-gray-100
                                      border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Status</label>
                        <input type="text" id="profile_status" readonly
                               class="w-full border rounded-[5px] px-4 h-[30px] text-sm shadow-sm
                                      bg-gray-100 dark:bg-gray-600
                                      text-gray-800 dark:text-gray-100
                                      border-gray-300 dark:border-gray-600">
                    </div>
                </div>
            </div>

            <!-- Action -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">
                <h3 class="text-xl font-semibold mb-5 text-gray-800 dark:text-gray-100">
                    Actions
                </h3>
                <div class="space-y-4">
                    <button onclick="clearProfile()" class="w-full bg-orange-600 text-white h-[50px] rounded-[5px] text-sm font-medium 
                                   hover:bg-orange-700 shadow transition">
                        Clear Selection
                    </button>
                    <button class="w-full bg-blue-600 text-white h-[50px] rounded-[5px] text-sm font-medium 
                                   hover:bg-blue-700 shadow transition">
                        Transfer Ownership
                    </button>
                    <button class="w-full bg-cyan-600 text-white h-[50px] rounded-[5px] text-sm font-medium 
                                   hover:bg-cyan-700 shadow transition">
                        Reconnect Service
                    </button>
                    <button class="w-full bg-gray-200 dark:bg-gray-600 
                                   text-gray-800 dark:text-gray-100 
                                   h-[50px] rounded-[5px] text-sm font-medium hover:bg-gray-300 
                                   dark:hover:bg-gray-500 shadow transition">
                        Update Meter
                    </button>
                </div>
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
                alert('Customer registered to existing account.');
            } else {
                alert('Failed to register customer.');
            }
        });
    </script>
</x-modal>

<!-- Toggle Script -->
<script>
    let selectedCustomer = null;
    let selectedCard = null;
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
                        rounded-[5px] p-5 shadow-md hover:shadow-lg transition cursor-pointer"
                 data-account-no="${customer.account_no}"
                 data-name="${customer.name}"
                 data-address="${customer.address}"
                 data-meter-no="${customer.meter_no || ''}"
                 data-meter-size="${customer.meter_size || ''}"
                 data-status="${customer.status}"
                 onmouseenter="showProfilePreview(this)"
                 onmouseleave="hideProfilePreview()"
                 onclick="selectCustomerCard(this)">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-lg text-gray-800 dark:text-gray-100">${customer.name}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            Acct. ${customer.account_no} - 
                            <span class="text-green-600 dark:text-green-400 font-medium">Active</span>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${customer.address}</p>
                    </div>
                    <button 
                        class="ml-3 px-3 h-[32px] rounded-[6px] bg-blue-600 hover:bg-blue-700 text-white text-xs register-btn"
                        data-account-no="${customer.account_no}"
                        data-name="${customer.name}"
                        data-address="${customer.address}"
                        data-meter-no="${customer.meter_no || ''}"
                        data-meter-size="${customer.meter_size || ''}"
                        data-status="${customer.status}"
                        onclick="selectCustomer(this)">
                        Register
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Show profile preview on hover
    function showProfilePreview(card) {
        // If not selected, highlight only on hover
        if (!card.classList.contains('selected')) {
            card.classList.add('hovering');
        }
        const accountNo = card.getAttribute('data-account-no');
        const name = card.getAttribute('data-name');
        const address = card.getAttribute('data-address');
        const meterNo = card.getAttribute('data-meter-no');
        const meterSize = card.getAttribute('data-meter-size');
        const status = card.getAttribute('data-status');

        // Update profile fields with preview data
        document.getElementById('profile_account_no').value = accountNo || '';
        document.getElementById('profile_name').value = name || '';
        document.getElementById('profile_address').value = address || '';
        document.getElementById('profile_meter_no').value = meterNo || '';
        document.getElementById('profile_meter_size').value = meterSize || '';
        document.getElementById('profile_status').value = status || '';
    }

    // Hide profile preview
    function hideProfilePreview() {
        // Remove hover highlight
        document.querySelectorAll('.customer-card.hovering').forEach(function(c){
            c.classList.remove('hovering');
        });
        // Only clear if no customer is selected
        if (!selectedCustomer) {
            clearProfile();
        }
    }

    // Select customer card and keep highlight; clicking again on same card clears
    function selectCustomerCard(card) {
        if (selectedCard === card) {
            // Toggle off
            clearProfile();
            return;
        }
        // Remove previous selection
        if (selectedCard) {
            selectedCard.classList.remove('selected');
        }
        // Set new selection
        card.classList.add('selected');
        selectedCard = card;
        // Store selected customer
        selectedCustomer = {
            accountNo: card.getAttribute('data-account-no'),
            name: card.getAttribute('data-name'),
            address: card.getAttribute('data-address'),
            meterNo: card.getAttribute('data-meter-no'),
            meterSize: card.getAttribute('data-meter-size'),
            status: card.getAttribute('data-status')
        };
        // Show profile info
        document.getElementById('profile_account_no').value = selectedCustomer.accountNo || '';
        document.getElementById('profile_name').value = selectedCustomer.name || '';
        document.getElementById('profile_address').value = selectedCustomer.address || '';
        document.getElementById('profile_meter_no').value = selectedCustomer.meterNo || '';
        document.getElementById('profile_meter_size').value = selectedCustomer.meterSize || '';
        document.getElementById('profile_status').value = selectedCustomer.status || '';
    }

    // Select customer and hide register button completely
    function selectCustomer(button) {
        // Get customer data from button attributes
        const accountNo = button.getAttribute('data-account-no');
        const name = button.getAttribute('data-name');
        const address = button.getAttribute('data-address');
        const meterNo = button.getAttribute('data-meter-no');
        const meterSize = button.getAttribute('data-meter-size');
        const status = button.getAttribute('data-status');

        // Store selected customer
        selectedCustomer = {
            accountNo, name, address, meterNo, meterSize, status
        };

        // Auto-fill profile information
        document.getElementById('profile_account_no').value = accountNo || '';
        document.getElementById('profile_name').value = name || '';
        document.getElementById('profile_address').value = address || '';
        document.getElementById('profile_meter_no').value = meterNo || '';
        document.getElementById('profile_meter_size').value = meterSize || '';
        document.getElementById('profile_status').value = status || '';

        // Hide the register button completely
        button.style.display = 'none';

        // Open the registration modal with pre-filled data
        openRegisterExistingModal(accountNo, name);
    }

    // Clear profile information
    function clearProfile() {
        // Remove selection highlight
        if (selectedCard) {
            selectedCard.classList.remove('selected');
            selectedCard = null;
        }
        selectedCustomer = null;
        document.getElementById('profile_account_no').value = '';
        document.getElementById('profile_name').value = '';
        document.getElementById('profile_address').value = '';
        document.getElementById('profile_meter_no').value = '';
        document.getElementById('profile_meter_size').value = '';
        document.getElementById('profile_status').value = '';

        // Reset selected customer
        selectedCustomer = null;

        // Show all register buttons again
        document.querySelectorAll('.register-btn').forEach(btn => {
            btn.style.display = 'block';
        });
    }

    // Click outside to clear selection
    document.addEventListener('click', function(e){
        const card = e.target.closest('.customer-card');
        if (!card && selectedCard) {
            clearProfile();
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

