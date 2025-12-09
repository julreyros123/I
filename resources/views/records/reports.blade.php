@extends('layouts.app')

@section('title', 'Records - Reports')

@section('content')
    @php
        $systemCategories = ['UI bug', 'Delay issue', 'Billing problem', 'Login issue', 'Other'];
        $customerCategories = ['Water quality', 'Service interruption', 'Meter concern', 'Billing dispute', 'Collection or payment', 'Other'];
        $reportType = old('report_type', 'system');
        $category = old('category', '');
    @endphp

    <div class="max-w-2xl mx-auto p-6 text-sm space-y-5">
        <header class="space-y-1">
            <p class="text-xs uppercase tracking-[0.3em] text-blue-500">Staff desk</p>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Submit a report</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">Flag a system glitch inside the portal or document a customer complaint for admin follow-up.</p>
        </header>

        @if (session('success'))
            <div class="mb-2 p-3 rounded-2xl bg-emerald-100 text-emerald-800 border border-emerald-200 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reports.store') }}" class="space-y-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl shadow p-6">
            @csrf

            <div>
                <span class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">What kind of report is this?</span>
                <div class="inline-flex rounded-full bg-gray-100 dark:bg-gray-800 p-1 text-xs font-semibold">
                    <label class="flex items-center gap-2 px-3 py-1.5 rounded-full cursor-pointer {{ $reportType === 'system' ? 'bg-white dark:bg-gray-900 shadow text-blue-600 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400' }}">
                        <input type="radio" name="report_type" value="system" {{ $reportType === 'system' ? 'checked' : '' }} class="hidden">
                        System issue
                    </label>
                    <label class="flex items-center gap-2 px-3 py-1.5 rounded-full cursor-pointer {{ $reportType === 'customer' ? 'bg-white dark:bg-gray-900 shadow text-emerald-600 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">
                        <input type="radio" name="report_type" value="customer" {{ $reportType === 'customer' ? 'checked' : '' }} class="hidden">
                        Customer complaint
                    </label>
                </div>
                @error('report_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div id="systemCategories" class="space-y-2" style="display: none;">
                    <span class="block text-xs font-semibold text-gray-600 dark:text-gray-300">System issue category</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($systemCategories as $label)
                            <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 cursor-pointer {{ $category === $label ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-400 dark:border-blue-500 text-blue-700 dark:text-blue-200' : 'text-gray-700 dark:text-gray-200' }}">
                                <input type="radio" name="category" value="{{ $label }}" {{ $category === $label ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div id="customerCategories" class="space-y-2" style="display: none;">
                    <span class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Customer complaint category</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($customerCategories as $label)
                            <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 cursor-pointer {{ $category === $label ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-400 dark:border-emerald-500 text-emerald-700 dark:text-emerald-200' : 'text-gray-700 dark:text-gray-200' }}">
                                <input type="radio" name="category" value="{{ $label }}" {{ $category === $label ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @error('category')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror

            <div id="customerReferenceField" class="space-y-1" style="display: none;">
                <label for="customer_reference" class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Customer reference (account no. or name) <span class="text-red-500">*</span></label>
                <input type="text" id="customer_reference" name="customer_reference" value="{{ old('customer_reference') }}" class="w-full border rounded-xl px-3 py-2 text-sm {{ $errors->has('customer_reference') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800" placeholder="e.g. 22-000123-1 or Juan Dela Cruz">
                @error('customer_reference')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div id="otherWrapper" style="display: none;">
                <label for="other_problem" class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Other label</label>
                <input type="text" id="other_problem" name="other_problem" value="{{ old('other_problem') }}" class="w-full border rounded-xl px-3 py-2 text-sm {{ $errors->has('other_problem') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800" placeholder="Short title for this issue">
                @error('other_problem')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="message" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Describe what happened</label>
                <textarea id="message" name="message" rows="6" class="w-full border rounded-xl px-3 py-2 text-sm {{ $errors->has('message') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800" placeholder="Provide as much detail as possible...">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                    <x-heroicon-o-paper-airplane class="w-4 h-4" />
                    Submit report
                </button>
            </div>
        </form>
    </div>

    <script>
        (function() {
            const typeInputs = document.querySelectorAll('input[name="report_type"]');
            const systemGroup = document.getElementById('systemCategories');
            const customerGroup = document.getElementById('customerCategories');
            const customerReference = document.getElementById('customerReferenceField');
            const otherWrapper = document.getElementById('otherWrapper');
            const categoryInputs = document.querySelectorAll('input[name="category"]');

            function currentType() {
                const checked = document.querySelector('input[name="report_type"]:checked');
                return checked ? checked.value : 'system';
            }

            function toggleGroups() {
                const type = currentType();
                systemGroup.style.display = type === 'system' ? 'block' : 'none';
                customerGroup.style.display = type === 'customer' ? 'block' : 'none';
                customerReference.style.display = type === 'customer' ? 'block' : 'none';
                maybeToggleOther();
            }

            function maybeToggleOther() {
                const selectedCategory = document.querySelector('input[name="category"]:checked');
                const show = selectedCategory && selectedCategory.value === 'Other';
                otherWrapper.style.display = show ? 'block' : 'none';
                if (!show) {
                    const otherInput = document.getElementById('other_problem');
                    if (otherInput) otherInput.value = show ? otherInput.value : otherInput.value;
                }
            }

            typeInputs.forEach(input => {
                input.addEventListener('change', () => {
                    // Clear category when switching types to avoid mismatch
                    categoryInputs.forEach(radio => { radio.checked = false; });
                    toggleGroups();
                });
            });

            categoryInputs.forEach(input => {
                input.addEventListener('change', maybeToggleOther);
            });

            toggleGroups();
        })();
    </script>
@endsection