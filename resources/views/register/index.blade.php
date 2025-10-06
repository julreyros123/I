@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-7xl mx-auto p-8 space-y-8 font-sans">
    
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
            <form class="flex space-x-3 mb-6">
                <input type="text" placeholder="Search by name, address, or account no."
                    class="flex-1 border rounded-[5px] px-4 h-[50px] text-sm shadow-sm
                           bg-white dark:bg-gray-700
                           text-gray-800 dark:text-gray-100
                           border-gray-300 dark:border-gray-600
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                    class="bg-blue-600 text-white px-5 h-[50px] rounded-[5px] text-sm font-medium 
                           hover:bg-blue-700 transition shadow">
                    Search
                </button>
            </form>

            <!-- Dynamic Results with Register button -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach (($customers ?? []) as $c)
                <div class="bg-gray-50 dark:bg-gray-700 border dark:border-gray-600 
                            rounded-[5px] p-5 shadow-md hover:shadow-lg transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-lg text-gray-800 dark:text-gray-100">{{ $c->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                Acct. {{ $c->account_no }} - 
                                <span class="text-green-600 dark:text-green-400 font-medium">Active</span>
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $c->address }}</p>
                        </div>
                        <button 
                            class="ml-3 px-3 h-[32px] rounded-[6px] bg-blue-600 hover:bg-blue-700 text-white text-xs"
                            onclick="openRegisterExistingModal('{{ $c->account_no }}', @js($c->name))">
                            Register
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Profile and Action Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Profile -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">
                <h3 class="text-xl font-semibold mb-5 text-gray-800 dark:text-gray-100">
                    Profile Information
                </h3>
                <form class="space-y-4">
                    @foreach (['Account No.', 'Name', 'Address', 'Meter No.', 'Meter Size', 'Status'] as $field)
                        <div>
                            <label class="block text-sm font-medium mb-1 
                                           text-gray-700 dark:text-gray-300">{{ $field }}</label>
                            <input type="text" 
                                   class="w-full border rounded-[5px] px-4 h-[30px] text-sm shadow-sm
                                          bg-white dark:bg-gray-700
                                          text-gray-800 dark:text-gray-100
                                          border-gray-300 dark:border-gray-600
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    @endforeach
                </form>
            </div>

            <!-- Action -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">
                <h3 class="text-xl font-semibold mb-5 text-gray-800 dark:text-gray-100">
                    Actions
                </h3>
                <div class="space-y-4">
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

        <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Full Name -->
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Full Name</label>
                <input type="text"
                       class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                              bg-white dark:bg-gray-700
                              text-gray-800 dark:text-gray-100
                              border-gray-300 dark:border-gray-600
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Address -->
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Address</label>
                <textarea rows="3"
                          class="w-full border rounded-xl px-4 py-2 text-sm shadow-sm
                                 bg-white dark:bg-gray-700
                                 text-gray-800 dark:text-gray-100
                                 border-gray-300 dark:border-gray-600
                                 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Contact Number -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Contact Number</label>
                <input type="text"
                       class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                              bg-white dark:bg-gray-700
                              text-gray-800 dark:text-gray-100
                              border-gray-300 dark:border-gray-600
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Start Date</label>
                <input type="date"
                       class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                              bg-white dark:bg-gray-700
                              text-gray-800 dark:text-gray-100
                              border-gray-300 dark:border-gray-600
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Meter No. -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Meter No.</label>
                <input type="text"
                       class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                              bg-white dark:bg-gray-700
                              text-gray-800 dark:text-gray-100
                              border-gray-300 dark:border-gray-600
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Meter Size -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Meter Size</label>
                <select class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                               bg-white dark:bg-gray-700
                               text-gray-800 dark:text-gray-100
                               border-gray-300 dark:border-gray-600
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Size</option>
                    <option>1/2"</option>
                    <option>3/4"</option>
                    <option>1"</option>
                    <option>2"</option>
                </select>
            </div>

            <!-- Connection Classification -->
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Connection Classification
                </label>
                <select class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                               bg-white dark:bg-gray-700
                               text-gray-800 dark:text-gray-100
                               border-gray-300 dark:border-gray-600
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Classification</option>
                    <option>Residential</option>
                    <option>Commercial</option>
                    <option>Industrial</option>
                    <option>Agricultural</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="col-span-2">
                <button class="w-full bg-blue-600 text-white h-[50px] rounded-xl text-sm font-medium 
                               hover:bg-blue-700 shadow-md transition flex items-center justify-center space-x-2">
                    <!-- Heroicon: UserPlus -->
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
</script>
@endsection
