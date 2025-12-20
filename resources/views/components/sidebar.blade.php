{{-- Sidebar --}}
<div 
    x-data="{ open: false }"
    class="w-64 bg-[var(--kpi-primary)] 
           text-white dark:bg-gray-950 
           dark:text-gray-200 min-h-screen flex flex-col 
           fixed left-0 top-0 z-20 font-inter"
    id="sidebar"
    x-cloak
>
    {{-- Logo / Header --}}
    <div class="h-[65px] px-6 border-b border-[var(--kpi-secondary)] dark:border-gray-800 flex items-center gap-3">
        <img src="{{ asset('images/mawasa-logo.png') }}" alt="MAWASA Logo" 
             class="h-10 w-10 rounded-lg shadow-md">
        <div class="flex flex-col">
            <h1 class="text-base font-semibold tracking-wide uppercase text-white dark:text-gray-100">MAWASA</h1>
            <p class="leading-tight text-[11px] text-white/80 dark:text-gray-400">
                Brgy. Manambulan Tugbok District, Davao City
            </p>
        </div>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 px-4 py-6">
        <p class="uppercase text-white/80 dark:text-gray-500 mb-4 text-xs font-medium tracking-wider">
            Menu
        </p>
    
        <ul class="space-y-2 text-sm">
            {{-- Dashboard --}}
            <li>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[rgb(var(--kpi-secondary-rgb)/0.55)] dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-home class="w-5 h-5 text-white/80 dark:text-gray-400" />
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- Admin links removed from staff sidebar (admin has its own interface) --}}

            {{-- Register --}}
            <li>
                @php($registerUrl = \Illuminate\Support\Facades\Route::has('register.index') ? route('register.index') : url('/register'))
                <a href="{{ $registerUrl }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[rgb(var(--brand-700-rgb)/0.55)] dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-white/80 dark:text-gray-400" />
                    <span>Register</span>
                </a>
            </li>

            {{-- Customer --}}
            <li>
                <a href="{{ route('customer.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[rgb(var(--brand-700-rgb)/0.55)] dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-user-group class="w-5 h-5 text-white/80 dark:text-gray-400" />
                    <span>Customer</span>
                </a>
            </li>

            {{-- Customer Issues Intake --}}
            <li>
                <a href="{{ route('staff.customer-issues.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[rgb(var(--brand-700-rgb)/0.55)] dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-white/80 dark:text-gray-400" />
                    <span>Customer Issues</span>
                </a>
            </li>

            {{-- Billing --}}
            <li>
                <a href="{{ route('billing.management') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[rgb(var(--brand-700-rgb)/0.55)] dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-white/80 dark:text-gray-400" />
                    <span>Billing</span>
                </a>
            </li>

            {{-- Payment --}}
            <li>
                <a href="{{ route('payment.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[rgb(var(--brand-700-rgb)/0.55)] dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-credit-card class="w-5 h-5 text-white/80 dark:text-gray-400" />
                    <span>Payment</span>
                </a>
            </li>

            {{-- Records Dropdown --}}
            <li class="relative">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg 
                               hover:bg-[rgb(var(--kpi-secondary-rgb)/0.55)] dark:hover:bg-gray-800 transition-all duration-200 ease-in-out font-medium">
                    <span class="flex items-center gap-3">
                        <x-heroicon-o-document-text class="w-5 h-5 text-white/80 dark:text-gray-400" />
                        <span>Records</span>
                    </span>
                    <svg :class="open ? 'rotate-180' : ''"
                         class="w-4 h-4 transform transition-all duration-200 ease-in-out"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Dropdown --}}
                <ul x-show="open" x-transition
                    class="mt-2 ml-6 space-y-1 bg-[rgb(var(--kpi-secondary-rgb)/0.92)] dark:bg-gray-950 
                           border border-[var(--kpi-secondary)] dark:border-gray-800 
                           rounded-2xl shadow-lg overflow-hidden p-1">
                    <li>
                        <a href="{{ route('records.billing') }}"
                           class="block px-3 py-2 rounded-xl transition-all duration-200 ease-in-out
                                  {{ request()->routeIs('records.billing') ? 'bg-white/20 text-white shadow-inner shadow-white/20' : 'text-white/80 hover:bg-white/10' }}">
                           Billing Records
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('records.billing.archived') }}"
                           class="block px-3 py-2 rounded-xl transition-all duration-200 ease-in-out
                                  {{ request()->routeIs('records.billing.archived') ? 'bg-white/20 text-white shadow-inner shadow-white/20' : 'text-white/80 hover:bg-white/10' }}">
                           Archived Billing
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('records.payments') }}"
                           class="block px-3 py-2 rounded-xl transition-all duration-200 ease-in-out
                                  {{ request()->routeIs('records.payments') ? 'bg-white/20 text-white shadow-inner shadow-white/20' : 'text-white/80 hover:bg-white/10' }}">
                           Payment Records
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

   {{-- Report Issue --}}
<div x-data="reportIssueModal()" class="p-4 border-t border-[var(--kpi-secondary)] dark:border-gray-800">
    <!-- Trigger Button -->
    <button @click="openModal('system')"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg 
                   hover:bg-[rgb(var(--kpi-secondary-rgb)/0.55)] dark:hover:bg-gray-800 
                   transition-all duration-200 ease-in-out font-medium text-sm">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-400 dark:text-yellow-500" />
        <span>Report Issue</span>
    </button>

    <!-- Modal Overlay -->
    <div x-show="showModal"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
         x-transition>
        
        <!-- Modal Box -->
        <div @click.away="closeModal"
             class="bg-white dark:bg-gray-900 rounded-xl shadow-lg w-full max-w-2xl p-6 space-y-5">

            <header class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Submit a report</h2>
                    <button @click="closeModal" class="rounded-full p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Let us know if you spotted a system glitch or a customer-raised concern.</p>
            </header>

            <div class="flex items-center gap-2 bg-gray-100 dark:bg-gray-800 rounded-full p-1 text-sm font-medium">
                <button type="button" @click="setTab('system')"
                        :class="tab === 'system' ? 'bg-white dark:bg-gray-900 shadow text-blue-600 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400'"
                        class="flex-1 px-3 py-2 rounded-full transition">
                    System issue
                </button>
                <button type="button" @click="setTab('customer')"
                        :class="tab === 'customer' ? 'bg-white dark:bg-gray-900 shadow text-emerald-600 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400'"
                        class="flex-1 px-3 py-2 rounded-full transition">
                    Customer complaint
                </button>
            </div>

            <template x-if="tab === 'system'">
                <section class="bg-blue-50/70 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl p-4 text-xs text-blue-700 dark:text-blue-200 space-y-2">
                    <p class="font-semibold">System issue</p>
                    <p>Use this when something inside the staff portal is misbehaving—slow pages, wrong calculations, missing features, or login glitches.</p>
                </section>
            </template>
            <template x-if="tab === 'customer'">
                <section class="bg-emerald-50/70 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-2xl p-4 text-xs text-emerald-700 dark:text-emerald-200 space-y-2">
                    <p class="font-semibold">Customer complaint</p>
                    <p>Log complaints escalated by a customer or field team. Capture their account number or name so admin can trace it quickly.</p>
                </section>
            </template>

            <!-- ✅ Report Issue Form -->
            <form method="POST" action="{{ route('reports.store') }}" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="report_type" :value="tab">

                <div class="grid md:grid-cols-2 gap-4">
                    <div x-show="tab === 'system'" class="space-y-2" x-cloak>
                        <span class="block text-xs font-semibold text-gray-700 dark:text-gray-300">What best describes the problem?</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="option in systemCategories" :key="option">
                                <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 cursor-pointer hover:border-blue-400" :class="category === option ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-400 dark:border-blue-500 text-blue-700 dark:text-blue-200' : 'text-gray-700 dark:text-gray-200'">
                                    <input type="radio" name="category" :value="option" x-model="category" class="text-blue-600 focus:ring-blue-500">
                                    <span x-text="option"></span>
                                </label>
                            </template>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <input type="radio" name="category" value="Other" x-model="category" class="text-blue-600 focus:ring-blue-500">
                            <span>Something else</span>
                        </label>
                    </div>

                    <div x-show="tab === 'customer'" class="space-y-2" x-cloak>
                        <span class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Customer issue category</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="option in customerCategories" :key="option">
                                <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 cursor-pointer hover:border-emerald-400" :class="category === option ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-400 dark:border-emerald-500 text-emerald-700 dark:text-emerald-200' : 'text-gray-700 dark:text-gray-200'">
                                    <input type="radio" name="category" :value="option" x-model="category" class="text-emerald-600 focus:ring-emerald-500">
                                    <span x-text="option"></span>
                                </label>
                            </template>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <input type="radio" name="category" value="Other" x-model="category" class="text-emerald-600 focus:ring-emerald-500">
                            <span>Other customer concern</span>
                        </label>
                    </div>

                    <div x-show="tab === 'customer'" class="space-y-2" x-cloak>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Customer reference <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_reference" x-model="customerReference" placeholder="Account number or customer name"
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                    </div>
                </div>

                <div x-show="shouldShowOtherInput" x-cloak>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Give the issue a short label</label>
                    <input type="text" name="other_problem" x-model="otherLabel" placeholder="Short summary (max 255 chars)"
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Describe what happened</label>
                    <textarea name="message" rows="5" x-model="message"
                              class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100"
                              placeholder="Tell us what you observed, steps to reproduce, or commitments made"></textarea>
                </div>

                <p x-show="formError" x-text="formError" class="text-xs text-red-500"></p>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeModal"
                            class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition">
                        Cancel
                    </button>
                    <button type="submit" @click.prevent="submitForm"
                            class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition">
                        Submit
                    </button>
                </div>
            </form>
            <!-- ✅ End of Report Issue Form -->
        </div>
    </div>
</div>

@once
    @push('scripts')
<script>
    function reportIssueModal() {
        return {
            showModal: false,
            tab: 'system',
            category: '',
            customerReference: '',
            otherLabel: '',
            message: '',
            formError: '',
            systemCategories: ['UI bug', 'Delay issue', 'Billing problem', 'Login issue'],
            customerCategories: ['Water quality', 'Service interruption', 'Meter concern', 'Billing dispute', 'Collection or payment'],
            openModal(type = 'system') {
                this.resetForm();
                this.tab = type;
                this.showModal = true;
            },
            closeModal() {
                this.showModal = false;
                this.formError = '';
            },
            setTab(type) {
                if (this.tab === type) return;
                this.tab = type;
                this.category = '';
                this.customerReference = '';
                this.otherLabel = '';
                this.formError = '';
            },
            get shouldShowOtherInput() {
                return this.category === 'Other';
            },
            resetForm() {
                this.category = '';
                this.customerReference = '';
                this.otherLabel = '';
                this.message = '';
                this.formError = '';
            },
            submitForm() {
                this.formError = '';
                if (!this.category) {
                    this.formError = 'Please choose a category.';
                    return;
                }
                if (this.tab === 'customer' && this.customerReference.trim() === '') {
                    this.formError = 'Customer complaints need an account number or name.';
                    return;
                }
                if (this.shouldShowOtherInput && this.otherLabel.trim() === '') {
                    this.formError = 'Please provide a short label for the "Other" issue.';
                    return;
                }
                if (this.message.trim() === '') {
                    this.formError = 'Please describe the issue so we can help.';
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('reports.store') }}';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (!tokenMeta) {
                    this.formError = 'Unable to submit because the CSRF token is missing.';
                    return;
                }
                csrf.value = tokenMeta.getAttribute('content');
                form.appendChild(csrf);

                const addField = (name, value) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                };

                addField('report_type', this.tab);
                addField('category', this.category);
                addField('customer_reference', this.customerReference);
                addField('other_problem', this.otherLabel);

                const messageField = document.createElement('textarea');
                messageField.name = 'message';
                messageField.value = this.message;
                form.appendChild(messageField);

                document.body.appendChild(form);
                form.submit();
                this.showModal = false;
            }
        };
    }
</script>
    @endpush
@endonce

</div>

