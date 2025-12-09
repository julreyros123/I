@extends('layouts.app')

@section('title', 'Bill Details - ' . $billingRecord->customer->name)

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10 space-y-8">
    <header class="space-y-2">
        <p class="uppercase tracking-[0.35em] text-xs font-semibold text-slate-400">Invoice</p>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">Bill Details</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Detailed view for {{ $billingRecord->customer->name }} • {{ $billingRecord->invoice_number ?? 'INV-' . str_pad($billingRecord->id, 4, '0', STR_PAD_LEFT) }}</p>
    </header>

    <div class="bg-white dark:bg-gray-900/70 rounded-3xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-800 overflow-hidden">
        <!-- Bill Header -->
        <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white px-8 py-10">
            <span class="absolute inset-0 bg-[url('{{ asset('images/mawasa-logo.png') }}')] opacity-[0.06] bg-center bg-contain pointer-events-none"></span>
            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-1">
                    <h2 class="text-2xl font-semibold">MANAMBULAN WATERWORKS &amp; SANITATION INC.</h2>
                    <p class="text-sm text-slate-300">Brgy. Manambulan Tugbok District, Davao City</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-2xl px-5 py-4 space-y-1 text-sm">
                    <div class="flex items-center justify-between gap-8">
                        <span class="uppercase tracking-widest text-[11px] text-slate-200">Invoice No.</span>
                        <span class="font-semibold">{{ $billingRecord->invoice_number ?? 'INV-' . str_pad($billingRecord->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-8">
                        <span class="uppercase tracking-widest text-[11px] text-slate-200">Prepared By</span>
                        <span class="font-semibold">{{ $billingRecord->prepared_by ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-8">
                        <span class="uppercase tracking-widest text-[11px] text-slate-200">Issued</span>
                        <span class="font-semibold">{{ optional($billingRecord->issued_at ?? $billingRecord->created_at)->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="grid md:grid-cols-2 gap-8 px-8 py-8">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer</h3>
                <div class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-200">
                    <p><span class="font-semibold text-gray-900 dark:text-gray-50">Name:</span> {{ $billingRecord->customer->name }}</p>
                    <p><span class="font-semibold text-gray-900 dark:text-gray-50">Account No:</span> {{ $billingRecord->account_no }}</p>
                    <p><span class="font-semibold text-gray-900 dark:text-gray-50">Address:</span> {{ $billingRecord->customer->address }}</p>
                    <p><span class="font-semibold text-gray-900 dark:text-gray-50">Meter No:</span> {{ $billingRecord->customer->meter_no }}</p>
                    <p><span class="font-semibold text-gray-900 dark:text-gray-50">Meter Size:</span> {{ $billingRecord->customer->meter_size }}</p>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Billing</h3>
                <div class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-200">
                    <p><span class="font-semibold text-gray-900 dark:text-gray-50">Billing Period:</span> {{ $billingRecord->getBillingPeriod() }}</p>
                    <p><span class="font-semibold text-gray-900 dark:text-gray-50">Due Date:</span> {{ $billingRecord->due_date ? $billingRecord->due_date->format('M d, Y') : 'N/A' }}</p>
                    <p><span class="font-semibold text-gray-900 dark:text-gray-50">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $billingRecord->getStatusBadgeClass() }}">
                            {{ $billingRecord->bill_status }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Reading Information -->
        <section class="px-8 pb-8 space-y-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reading Summary</h3>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-slate-50 to-white dark:from-gray-800 dark:to-gray-800/80 rounded-2xl p-5 ring-1 ring-gray-100 dark:ring-gray-700">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Previous Reading</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($billingRecord->previous_reading, 2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-50 to-white dark:from-gray-800 dark:to-gray-800/80 rounded-2xl p-5 ring-1 ring-gray-100 dark:ring-gray-700">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Current Reading</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($billingRecord->current_reading, 2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-sky-50 to-white dark:from-sky-900/20 dark:to-sky-900/10 rounded-2xl p-5 ring-1 ring-sky-100 dark:ring-sky-900/40">
                    <p class="text-xs uppercase tracking-wide text-sky-600 dark:text-sky-200">Consumption</p>
                    <p class="mt-2 text-2xl font-semibold text-sky-700 dark:text-sky-200">{{ number_format($billingRecord->consumption_cu_m, 2) }} m³</p>
                </div>
            </div>
        </section>

        <!-- Charges Breakdown -->
        <section class="px-8 pb-8 space-y-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Charges</h3>
            <div class="grid lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] gap-6">
                <div class="rounded-3xl bg-gray-50 dark:bg-gray-900/60 p-6 ring-1 ring-gray-100 dark:ring-gray-800 space-y-3 text-sm text-gray-700 dark:text-gray-200">
                    <div class="flex justify-between">
                        <span>Water Consumption ({{ number_format($billingRecord->consumption_cu_m, 2) }} m³ × ₱{{ number_format($billingRecord->base_rate, 2) }})</span>
                        <span class="font-semibold">₱{{ number_format($billingRecord->consumption_cu_m * $billingRecord->base_rate, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Maintenance Charge</span>
                        <span class="font-semibold">₱{{ number_format($billingRecord->maintenance_charge, 2) }}</span>
                    </div>
                    @if($billingRecord->overdue_penalty > 0)
                        <div class="flex justify-between text-red-600 dark:text-red-300">
                            <span>Overdue Penalty</span>
                            <span class="font-semibold">₱{{ number_format($billingRecord->overdue_penalty, 2) }}</span>
                        </div>
                    @endif
                    @if($billingRecord->advance_payment > 0)
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-300">
                            <span>Advance Payment (Credit)</span>
                            <span class="font-semibold">-₱{{ number_format($billingRecord->advance_payment, 2) }}</span>
                        </div>
                    @endif
                </div>
                <div class="rounded-3xl bg-slate-900 text-white dark:bg-slate-800 p-6 space-y-3">
                    <p class="text-xs uppercase tracking-wide text-slate-300">Total Amount Due</p>
                    <p class="text-3xl font-semibold">₱{{ number_format($billingRecord->total_amount, 2) }}</p>
                    <p class="text-xs text-slate-300">Status: <span class="font-semibold">{{ $billingRecord->bill_status }}</span></p>
                </div>
            </div>
        </section>

        <!-- Notes -->
        @if($billingRecord->notes)
        <section class="px-8 pb-8">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Notes</h3>
            <div class="rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/30 p-5 text-sm text-amber-900 dark:text-amber-100">
                {{ $billingRecord->notes }}
            </div>
        </section>
        @endif

        @php
            $statusOptions = [
                'Pending',
                'Paid',
                'Outstanding Payment',
                'Notice of Disconnection',
                'Disconnected',
                'Overdue'
            ];
        @endphp

        <!-- Action Buttons -->
        <div class="px-8 pb-8 flex flex-col gap-4 border-t border-gray-100 dark:border-gray-800 pt-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <button onclick="printBill()" title="Print Invoice"
                        class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-500 transition">
                    <x-heroicon-o-printer class="w-4 h-4" /> Print
                </button>

                <form method="POST" action="{{ route('records.billing.status', $billingRecord->id) }}" class="flex items-center gap-3 rounded-xl bg-white dark:bg-gray-900/50 px-4 py-2 ring-1 ring-gray-200 dark:ring-gray-700">
                    @csrf
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                        Status
                        <select name="bill_status" class="mt-1 w-44 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100">
                            @foreach($statusOptions as $status)
                                <option value="{{ $status }}" @selected($billingRecord->bill_status === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
                        <x-heroicon-o-check class="w-4 h-4" /> Save
                    </button>
                </form>
            </div>

            <a href="{{ route('records.billing') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300 transition self-start lg:self-center"
               title="Back to billing records">
                <x-heroicon-o-x-mark class="w-4 h-4" /> Close
            </a>
        </div>
    </div>
</div>

<script>
function printBill() {
    window.open(`/records/billing/{{ $billingRecord->id }}/print`, '_blank');
}
</script>
@endsection
