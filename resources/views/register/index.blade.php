@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-1 pb-5 space-y-5 font-sans">
    
    <!-- Success/Error Messages handled via modal -->
    @php
        $feedbackType = null;
        $feedbackMessage = null;
        $feedbackDetails = [];
        $feedbackAccount = session('register_account_no');

        if ($errors->any()) {
            $feedbackType = 'error';
            $feedbackMessage = 'Please review the highlighted fields before resubmitting.';
            $feedbackDetails = $errors->all();
        } elseif (session('error')) {
            $feedbackType = 'error';
            $feedbackMessage = session('error');
        } elseif (session('success')) {
            $feedbackType = 'success';
            $feedbackMessage = session('success');
            if ($feedbackAccount) {
                $feedbackDetails[] = 'Temporary account number: ' . $feedbackAccount;
            }
        }
    @endphp

    <div id="register-feedback-data"
         data-type="{{ $feedbackType ?? '' }}"
         data-message="{{ $feedbackMessage ?? '' }}"
         data-account="{{ $feedbackAccount ?? '' }}"
         data-details='@json($feedbackDetails ?? [])'></div>

    <div id="register-feedback-modal" class="fixed inset-0 z-50 hidden print:hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-2xl p-6 space-y-4">
            <div class="flex items-start gap-3">
                <div data-feedback-icon class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-500/15 text-blue-600 dark:bg-blue-400/15 dark:text-blue-200">
                    <x-heroicon-o-information-circle data-icon="info" class="w-6 h-6" />
                    <x-heroicon-o-check-circle data-icon="success" class="w-6 h-6 hidden" />
                    <x-heroicon-o-exclamation-triangle data-icon="error" class="w-6 h-6 hidden" />
                </div>
                <div class="space-y-2 flex-1">
                    <div class="flex items-start justify-between gap-3">
                        <h3 data-feedback-title class="text-base font-semibold text-gray-800 dark:text-gray-100"></h3>
                        <button type="button" id="registerFeedbackClose" class="rounded-full p-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                    <p data-feedback-message class="text-sm text-gray-600 dark:text-gray-300"></p>
                    <ul data-feedback-details class="space-y-1 text-xs text-gray-500 dark:text-gray-400"></ul>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" id="registerFeedbackPrimary" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 text-white px-4 py-2 text-sm font-semibold hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <x-heroicon-o-check class="w-4 h-4" />
                    <span>Okay</span>
                </button>
            </div>
        </div>
    </div>

    <!-- New Connection Wizard -->
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">New Customer Registration</h2>
        <div class="text-xs text-gray-500 dark:text-gray-400">Step <span id="wizStepNo">1</span> of <span id="wizStepTotal">2</span></div>
    </div>

    <!-- Removed Existing Customer Section -->

    <!-- Wizard Container -->
    <div id="newPanel">
        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8">
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">Fill the steps below. ID verification is required before any meter can be installed.</p>

            <div id="ncAlert" class="hidden mb-4"></div>

            <form id="newCustomerForm" method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <!-- Step 1: Personal Info + Address & Contact -->
                <section data-step="1" class="space-y-5">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-900/40 shadow-sm p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 tracking-wide uppercase">Applicant Details</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Basic information for identifying the new customer.</p>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium uppercase tracking-wide text-blue-600 dark:text-blue-300">
                                <span class="w-2 h-2 rounded-full bg-blue-500 dark:bg-blue-400 animate-pulse"></span> Required Section
                            </span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">First Name</label>
                                <x-ui.input name="first_name" :value="old('first_name')" required />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="first_name">First name is required</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">Last Name</label>
                                <x-ui.input name="last_name" :value="old('last_name')" required />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="last_name">Last name is required</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-900/40 shadow-sm p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 tracking-wide uppercase">Service Address</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Provide the full location where the service will be installed.</p>
                            </div>
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">Optional fields help us locate the property faster.</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">Barangay</label>
                                <x-ui.input name="barangay" :value="old('barangay')" required />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="barangay">Barangay is required</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">City / Municipality</label>
                                <x-ui.input name="city" :value="old('city')" required />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="city">City is required</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">Province</label>
                                <x-ui.input name="province" :value="old('province')" required />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="province">Province is required</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">Street / Block &amp; Lot <span class="font-normal text-gray-400">(optional)</span></label>
                                <x-ui.input name="street_block_lot" :value="old('street_block_lot')" placeholder="e.g., Mabini St., Blk 3 Lot 12" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">Purok</label>
                                <select name="zone_purok" class="w-full h-[42px] rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-100">
                                    <option value="">Select Purok</option>
                                    <option value="Purok 1 - Sto. Niño" @selected(old('zone_purok') === 'Purok 1 - Sto. Niño')>Purok 1 - Sto. Niño</option>
                                    <option value="Purok 2 - Shanghai" @selected(old('zone_purok') === 'Purok 2 - Shanghai')>Purok 2 - Shanghai</option>
                                    <option value="Purok 3 - Maligaya" @selected(old('zone_purok') === 'Purok 3 - Maligaya')>Purok 3 - Maligaya</option>
                                    <option value="Purok 4 - New Bohol" @selected(old('zone_purok') === 'Purok 4 - New Bohol')>Purok 4 - New Bohol</option>
                                    <option value="Purok 5 - Kawayan" @selected(old('zone_purok') === 'Purok 5 - Kawayan')>Purok 5 - Kawayan</option>
                                    <option value="Purok 6 - Talisay" @selected(old('zone_purok') === 'Purok 6 - Talisay')>Purok 6 - Talisay</option>
                                </select>
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="zone_purok">Please select a purok</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">Postal Code <span class="font-normal text-gray-400">(optional)</span></label>
                                <x-ui.input name="postal_code" :value="old('postal_code')" placeholder="e.g., 4217" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-900/40 shadow-sm p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 tracking-wide uppercase">Contact Details</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">We’ll reach out using this information for updates and verification.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">Contact Number <span class="font-normal text-gray-400">(optional)</span></label>
                                <x-ui.input name="contact_number" :value="old('contact_number')" placeholder="e.g., 09XX-XXX-XXXX" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Step 2: ID Verification (KYC) -->
                <section data-step="2" class="space-y-5 hidden">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-900/40 shadow-sm p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 tracking-wide uppercase">Primary Identification</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Submit the ID that the applicant will present during installation.</p>
                            </div>
                            <span class="text-[11px] font-medium text-amber-600 dark:text-amber-300">Ensure the ID is valid and clearly readable.</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">ID Type</label>
                                <select name="id_type" class="w-full border rounded-lg px-3 h-[42px] bg-white dark:bg-gray-700 dark:text-gray-100 border-gray-300 dark:border-gray-600" required>
                                    <option value="">Select ID Type</option>
                                    <option>PhilSys</option>
                                    <option>Driver's License</option>
                                    <option>Passport</option>
                                    <option>SSS</option>
                                    <option>UMID</option>
                                    <option>PRC</option>
                                </select>
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="id_type">ID Type is required</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-300">ID Number</label>
                                <x-ui.input name="id_number" :value="old('id_number')" required />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="id_number">ID Number is required</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-900/40 shadow-sm p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 tracking-wide uppercase">Supporting Images</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upload clear, well-lit photos. Optional uploads greatly speed up verification.</p>
                            </div>
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">Accepted formats: JPG, PNG, WEBP (max 5MB each)</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                            <div>
                                <label class="block text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">ID Front <span class="font-normal text-gray-400">(optional)</span></label>
                                <input type="file" name="id_front" accept="image/*" class="w-full text-xs file:mr-3 file:px-3 file:py-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-200" />
                                <img alt="Preview" data-preview="id_front" class="mt-2 w-40 h-28 object-cover rounded border border-gray-200 dark:border-gray-700 hidden" />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="id_front">ID Front is required</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">ID Back <span class="font-normal text-gray-400">(optional)</span></label>
                                <input type="file" name="id_back" accept="image/*" class="w-full text-xs file:mr-3 file:px-3 file:py-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-200" />
                                <img alt="Preview" data-preview="id_back" class="mt-2 w-40 h-28 object-cover rounded border border-gray-200 dark:border-gray-700 hidden" />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="id_back">ID Back is required</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">Selfie holding ID <span class="font-normal text-gray-400">(optional)</span></label>
                                <input type="file" name="selfie" accept="image/*" class="w-full text-xs file:mr-3 file:px-3 file:py-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-200" />
                                <img alt="Preview" data-preview="selfie" class="mt-2 w-40 h-28 object-cover rounded border border-gray-200 dark:border-gray-700 hidden" />
                                <p class="text-red-500 text-xs mt-1 hidden" data-err="selfie">Selfie is required</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-900/40 shadow-sm p-5">
                        <div class="flex flex-wrap items-start gap-3">
                            <input type="checkbox" name="consent" value="1" class="mt-1" required />
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Compliance Confirmation</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">I confirm that all provided information and documents are authentic and may be verified by the utility.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Wizard Controls -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" id="wizPrev" class="px-4 h-[40px] rounded-md text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 disabled:opacity-50">Back</button>
                    <div class="flex items-center gap-2">
                        <button type="button" id="wizNext" class="px-5 h-[40px] rounded-md text-sm bg-blue-600 text-white">Next</button>
                        <x-primary-button id="wizSubmit" type="submit" class="h-[40px] hidden">Submit Application</x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function(){
            var form = document.getElementById('newCustomerForm');
            if (!form) return;
            form.addEventListener('submit', async function(e){
                e.preventDefault();
                var btn = form.querySelector('button[type="submit"], [type="submit"]');
                var old = btn ? btn.innerHTML : '';
                if (btn){ btn.disabled = true; btn.innerHTML = 'Submitting...'; }
                try{
                    var first = form.querySelector('[name="first_name"]')?.value?.trim() || '';
                    var last = form.querySelector('[name="last_name"]')?.value?.trim() || '';
                    var brgy = form.querySelector('[name="barangay"]')?.value?.trim() || '';
                    var city = form.querySelector('[name="city"]')?.value?.trim() || '';
                    var prov = form.querySelector('[name="province"]')?.value?.trim() || '';
                    var address = [brgy, city, prov].filter(Boolean).join(', ');
                    var contact = form.querySelector('[name="contact_number"]')?.value?.trim() || '';
                    var applicant = (first + ' ' + last).trim() || first || last;
                    var errName = document.getElementById('nc_err_name');
                    var errAddr = document.getElementById('nc_err_address');
                    if (errName) errName.classList.add('hidden');
                    if (errAddr) errAddr.classList.add('hidden');
                    var hasError = false;
                    if (!applicant){ hasError = true; if (errName) errName.classList.remove('hidden'); }
                    if (!(brgy && city && prov)){ hasError = true; if (errAddr) errAddr.classList.remove('hidden'); }
                    if (hasError){ throw new Error('Please fix the highlighted fields.'); }
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch('/api/connections',{
                        method:'POST',
                        headers:{ 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ applicant_name: applicant, address: address || null, contact_no: contact || null })
                    });
                    if (!res.ok) {
                        const err = await res.json().catch(()=>({ message: 'Failed to submit application' }));
                        throw new Error(err.message || 'Failed to submit application');
                    }
                    const data = await res.json().catch(()=>({}));
                    var alertHost = document.getElementById('ncAlert');
                    if (alertHost){
                        var ref = data?.id || data?.application?.id || '';
                        alertHost.innerHTML = `<div class="rounded-md border border-green-300 bg-green-50 text-green-800 px-4 py-3 text-sm">Application submitted successfully${ref?` (Ref #${ref})`:''}. Redirecting to dashboard...</div>`;
                        alertHost.classList.remove('hidden');
                    }
                    showToast('New Connection application submitted', 'success');
                    form.reset();
                    setTimeout(function(){ window.location.href = "{{ route('dashboard') }}"; }, 1200);
                } catch(err){
                    console.error(err);
                    showToast(err.message || 'Failed to submit application', 'error');
                } finally {
                    if (btn){ btn.disabled = false; btn.innerHTML = old; }
                }
            });
        })();
    </script>
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
                <x-secondary-button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'register-existing'}))">Cancel</x-secondary-button>
                <x-primary-button type="submit">Save</x-primary-button>
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
                <x-secondary-button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'transfer-ownership'}))">Cancel</x-secondary-button>
                <x-primary-button type="submit">Transfer</x-primary-button>
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
                <x-secondary-button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal',{detail:'reconnect-service'}))">Cancel</x-secondary-button>
                <x-primary-button type="submit">Reconnect</x-primary-button>
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

<!-- Wizard Script -->
<script>
    (function(){
        const dataHost = document.getElementById('register-feedback-data');
        const modal = document.getElementById('register-feedback-modal');
        const primaryBtn = document.getElementById('registerFeedbackPrimary');
        const closeBtn = document.getElementById('registerFeedbackClose');
        const titleEl = modal?.querySelector('[data-feedback-title]');
        const messageEl = modal?.querySelector('[data-feedback-message]');
        const detailsEl = modal?.querySelector('[data-feedback-details]');
        const iconHost = modal?.querySelector('[data-feedback-icon]');

        function setIcon(type){
            if (!iconHost) return;
            iconHost.querySelectorAll('[data-icon]').forEach(el => el.classList.add('hidden'));
            const iconMap = {
                success: 'success',
                error: 'error',
                info: 'info'
            };
            const key = iconMap[type] || 'info';
            const target = iconHost.querySelector(`[data-icon="${key}"]`);
            if (target) target.classList.remove('hidden');
            iconHost.className = 'flex h-11 w-11 items-center justify-center rounded-full ' + (
                type === 'success' ? 'bg-emerald-500/15 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-200'
                : type === 'error' ? 'bg-rose-500/15 text-rose-600 dark:bg-rose-400/15 dark:text-rose-200'
                : 'bg-blue-500/15 text-blue-600 dark:bg-blue-400/15 dark:text-blue-200'
            );
        }

        function openFeedbackModal(){
            if (!modal || !dataHost) return;
            const type = dataHost.dataset.type;
            const message = dataHost.dataset.message || '';
            if (!type || !message) return;
            const details = (() => {
                try { return JSON.parse(dataHost.dataset.details || '[]'); }
                catch (e) { return []; }
            })();

            const titles = {
                success: 'Registration submitted',
                error: 'Action required',
                info: 'Notice'
            };

            if (titleEl) titleEl.textContent = titles[type] || 'Notice';
            if (messageEl) messageEl.textContent = message;
            if (detailsEl) {
                detailsEl.innerHTML = '';
                details.forEach(text => {
                    const li = document.createElement('li');
                    li.textContent = text;
                    detailsEl.appendChild(li);
                });
                detailsEl.classList.toggle('hidden', details.length === 0);
            }
            setIcon(type);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            primaryBtn?.focus();
        }

        function closeFeedbackModal(){
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        primaryBtn?.addEventListener('click', closeFeedbackModal);
        closeBtn?.addEventListener('click', closeFeedbackModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) closeFeedbackModal();
        });

        openFeedbackModal();

        const sections = Array.from(document.querySelectorAll('[data-step]'));
        const stepNo = document.getElementById('wizStepNo');
        const stepTotal = document.getElementById('wizStepTotal');
        const prev = document.getElementById('wizPrev');
        const next = document.getElementById('wizNext');
        const submit = document.getElementById('wizSubmit');
        const form = document.getElementById('newCustomerForm');
        const alertHost = document.getElementById('ncAlert');
        let step = 1;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        async function checkDuplicatesByNameAddress(){
            const first = form.querySelector('[name="first_name"]')?.value?.trim() || '';
            const last = form.querySelector('[name="last_name"]')?.value?.trim() || '';
            const brgy = form.querySelector('[name="barangay"]')?.value?.trim() || '';
            const city = form.querySelector('[name="city"]')?.value?.trim() || '';
            const prov = form.querySelector('[name="province"]')?.value?.trim() || '';
            const name = (first + ' ' + last).trim();
            const address = [brgy, city, prov].filter(Boolean).join(', ');
            if (!name || !address) return { customers: [], applications: [] };
            const url = `/api/customer/duplicates?name=${encodeURIComponent(name)}&address=${encodeURIComponent(address)}`;
            const res = await fetch(url, { headers:{ 'Accept':'application/json' } });
            if (!res.ok) return { customers: [], applications: [] };
            return await res.json();
        }

        async function checkDuplicatesByIdNumber(){
            const idno = form.querySelector('[name="id_number"]')?.value?.trim() || '';
            if (!idno) return { customers: [], applications: [] };
            const url = `/api/customer/duplicates?id_number=${encodeURIComponent(idno)}`;
            const res = await fetch(url, { headers:{ 'Accept':'application/json' } });
            if (!res.ok) return { customers: [], applications: [] };
            return await res.json();
        }

        function showWarn(html){
            if (!alertHost) return;
            alertHost.innerHTML = `<div class="rounded-md border border-yellow-300 bg-yellow-50 text-yellow-800 px-4 py-3 text-sm">${html}</div>`;
            alertHost.classList.remove('hidden');
        }

        function clearWarn(){ if (alertHost){ alertHost.innerHTML=''; alertHost.classList.add('hidden'); } }

        function showStep(n){
            sections.forEach(s => s.classList.toggle('hidden', Number(s.getAttribute('data-step')) !== n));
            if (stepNo) stepNo.textContent = String(n);
            if (stepTotal) stepTotal.textContent = String(sections.length);
            if (prev) prev.disabled = (n === 1);
            if (next) next.classList.toggle('hidden', n === sections.length);
            if (submit) submit.classList.toggle('hidden', n !== sections.length);
        }

        function validateStep(n){
            const current = sections.find(s => Number(s.getAttribute('data-step')) === n);
            if (!current) return true;
            let ok = true;
            const required = current.querySelectorAll('[name][required]');
            required.forEach(el => {
                const name = el.getAttribute('name');
                const err = current.querySelector(`[data-err="${name}"]`);
                let valid = true;
                if (el.type === 'file') { valid = el.files && el.files.length > 0; }
                else if (el.type === 'checkbox') { valid = el.checked; }
                else { valid = !!(el.value && el.value.trim()); }
                if (err) err.classList.toggle('hidden', valid);
                if (!valid) ok = false;
            });
            return ok;
        }

        if (prev) prev.addEventListener('click', () => { if (step > 1) { clearWarn(); step--; showStep(step); } });
        if (next) next.addEventListener('click', async () => {
            if (!validateStep(step)) return;
            if (step === 1){
                try{
                    const d = await checkDuplicatesByNameAddress();
                    if ((d.customers && d.customers.length) || (d.applications && d.applications.length)){
                        const c = d.customers?.length||0; const a = d.applications?.length||0;
                        showWarn(`Potential duplicates found: ${c} customer(s), ${a} application(s). Proceed if you are sure. <a href=\"/applications\" class=\"underline\">View Applications</a>`);
                    } else { clearWarn(); }
                } catch(_){ /* ignore */ }
            }
            step = Math.min(sections.length, step+1);
            showStep(step);
        });
        if (form) form.addEventListener('submit', async (e) => {
            if (!validateStep(step)) { e.preventDefault(); return; }
            try{
                const d = await checkDuplicatesByIdNumber();
                if (d.applications && d.applications.length){
                    const go = confirm('Another application with the same ID number exists. Do you still want to submit?');
                    if (!go){ e.preventDefault(); return; }
                }
            } catch(_){ /* ignore */ }
        });

        // Image previews
        function bindPreview(name){
            const input = form.querySelector(`[name="${name}"]`);
            const img = form.querySelector(`[data-preview="${name}"]`);
            if (!input || !img) return;
            input.addEventListener('change', () => {
                const f = input.files?.[0];
                if (!f){ img.classList.add('hidden'); img.src=''; return; }
                const url = URL.createObjectURL(f);
                img.src = url; img.classList.remove('hidden');
            });
        }
        bindPreview('id_front');
        bindPreview('id_back');
        bindPreview('selfie');
        showStep(step);
    })();
</script>

<!-- Toggle Script -->
<script>
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

    // Auto-capitalize first letter of each word for name/address-like fields
    function titleCase(input){
        if (!input) return; 
        input.addEventListener('input', () => {
            const val = input.value;
            // Only transform casing, keep cursor position stable where possible
            const tc = val.replace(/\b(\p{L})(\p{L}*)/gu, (m, a, b) => a.toUpperCase() + (b || '').toLowerCase());
            if (tc !== val) input.value = tc;
        });
    }
    // Attach to relevant fields
    document.addEventListener('DOMContentLoaded', () => {
        ['first_name','last_name','contact_number'].forEach(n => {
            const el = document.querySelector(`[name="${n}"]`);
            if (el && (n !== 'contact_number')) titleCase(el);
        });
        const addr = document.querySelector('[name="address"]');
        if (addr) titleCase(addr);
        const form = document.getElementById('newCustomerForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                const req = {
                    first_name: form.querySelector('[name="first_name"]').value.trim(),
                    last_name: form.querySelector('[name="last_name"]').value.trim(),
                    address: form.querySelector('[name="address"]').value.trim(),
                    start_date: form.querySelector('[name="start_date"]').value.trim(),
                    meter_no: form.querySelector('[name="meter_no"]').value.trim(),
                    meter_size: form.querySelector('[name="meter_size"]').value.trim(),
                    classification: form.querySelector('[name="classification"]').value.trim(),
                };
                const missing = Object.entries(req).filter(([,v]) => !v).map(([k]) => k.replace('_',' '));
                if (missing.length) {
                    e.preventDefault();
                    alert('Please complete all required fields before registering. Missing: ' + missing.join(', '));
                }
            });
        }
    });
</script>

