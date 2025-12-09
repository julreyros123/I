@extends('layouts.admin')

@section('title', 'Applicant Details')

@section('content')
@php
    $approvedStatuses = ['approved', 'waiting_payment', 'paid', 'scheduled', 'installing', 'installed'];
    $isApproved = in_array($app->status, $approvedStatuses, true) || $app->decision === 'approve';
@endphp
<div class="w-full mx-auto px-4 sm:px-6 py-5 lg:py-8 font-[Poppins] space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Application {{ $app->application_code ?? ('APP-'.str_pad($app->id, 6, '0', STR_PAD_LEFT)) }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Filed {{ optional($app->created_at)->format('M d, Y g:i A') ?? '—' }}</p>
        </div>
        <a href="{{ route('admin.applicants.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-500">
            <x-heroicon-o-arrow-left class="w-4 h-4" /> Back to queue
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <section class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Applicant Information</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Applicant</dt>
                        <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $app->applicant_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Contact</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $app->contact_no ?? '—' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $app->address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="inline-flex items-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200 px-2 py-0.5 text-xs font-semibold">{{ \Illuminate\Support\Str::headline($app->status ?? 'unknown') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Decision</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $app->decision ? ucfirst($app->decision) : '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Supporting Documents</h2>
                @php($docs = $app->documents ?? [])
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-2">
                        <div class="text-xs uppercase tracking-wide text-gray-400">ID Front</div>
                        @if(!empty($docs['id_front']))
                            <a href="{{ asset('storage/'.$docs['id_front']) }}" target="_blank" class="block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                <img src="{{ asset('storage/'.$docs['id_front']) }}" alt="ID Front" class="w-full h-40 object-cover">
                            </a>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Not uploaded</p>
                        @endif
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs uppercase tracking-wide text-gray-400">ID Back</div>
                        @if(!empty($docs['id_back']))
                            <a href="{{ asset('storage/'.$docs['id_back']) }}" target="_blank" class="block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                <img src="{{ asset('storage/'.$docs['id_back']) }}" alt="ID Back" class="w-full h-40 object-cover">
                            </a>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Not uploaded</p>
                        @endif
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Selfie</div>
                        @if(!empty($docs['selfie']))
                            <a href="{{ asset('storage/'.$docs['selfie']) }}" target="_blank" class="block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                <img src="{{ asset('storage/'.$docs['selfie']) }}" alt="Selfie" class="w-full h-40 object-cover">
                            </a>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Not uploaded</p>
                        @endif
                    </div>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <span>ID Type: {{ $docs['id_type'] ?? '—' }}</span>
                    <span class="mx-2">|</span>
                    <span>ID Number: {{ $docs['id_number'] ?? '—' }}</span>
                </div>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Timeline</h2>
                    <span class="text-xs text-gray-400">Latest milestones</span>
                </div>
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <li><span class="font-medium">Registered:</span> {{ optional($app->created_at)->format('M d, Y') ?? '—' }}</li>
                    <li><span class="font-medium">Approved:</span> {{ optional($app->approved_at)->format('M d, Y g:i A') ?? '—' }}</li>
                    <li><span class="font-medium">Paid:</span> {{ optional($app->paid_at)->format('M d, Y g:i A') ?? '—' }}</li>
                    <li><span class="font-medium">Schedule:</span> {{ optional($app->schedule_date)->format('M d, Y') ?? 'Not scheduled' }}</li>
                    <li><span class="font-medium">Installed:</span> {{ optional($app->installed_at)->format('M d, Y g:i A') ?? '—' }}</li>
                </ul>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Risk Review</h2>
                    <span class="inline-flex items-center gap-2 text-xs font-semibold {{ $duplicateCustomer || ($unsettledRecords->isNotEmpty()) ? 'text-red-600' : 'text-emerald-600' }}">
                        <span class="w-2 h-2 rounded-full {{ $duplicateCustomer || ($unsettledRecords->isNotEmpty()) ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                        {{ $duplicateCustomer || ($unsettledRecords->isNotEmpty()) ? 'Action required' : 'No critical issues' }}
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 rounded-xl {{ $duplicateCustomer ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' }}">
                        <div class="mt-0.5">
                            <x-heroicon-o-user-group class="w-5 h-5 {{ $duplicateCustomer ? 'text-red-500 dark:text-red-300' : 'text-emerald-500 dark:text-emerald-300' }}" />
                        </div>
                        <div class="text-sm">
                            <p class="font-semibold text-gray-800 dark:text-gray-100">Duplicate account check</p>
                            @if($duplicateCustomer)
                                <p class="text-gray-600 dark:text-gray-300 mt-1">Matching customer found: <span class="font-semibold">{{ $duplicateCustomer->name }}</span> (Account #{{ $duplicateCustomer->account_no }} — {{ $duplicateCustomer->status }}).</p>
                                <a href="{{ route('admin.customers') }}" class="text-xs font-medium text-blue-600 hover:text-blue-500">Open customer directory →</a>
                            @else
                                <p class="text-gray-600 dark:text-gray-300 mt-1">No existing customer with the same name and address was detected.</p>
                            @endif
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-gray-900/40 border border-slate-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Unsettled billing history</p>
                            @if($unsettledRecords->isNotEmpty())
                                <span class="text-xs font-medium text-red-600">{{ $unsettledRecords->count() }} record{{ $unsettledRecords->count() === 1 ? '' : 's' }}</span>
                            @else
                                <span class="text-xs font-medium text-emerald-500">Clear</span>
                            @endif
                        </div>
                        <div class="mt-3 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                            @if($unsettledRecords->isNotEmpty())
                                @foreach($unsettledRecords as $bill)
                                    <div class="flex items-start justify-between rounded-lg border border-red-100 dark:border-red-800 bg-red-50/60 dark:bg-red-900/30 px-3 py-2">
                                        <div>
                                            <p class="font-semibold text-red-600 dark:text-red-200">{{ $bill->bill_status }}</p>
                                            <p>Due {{ optional($bill->due_date)->format('M d, Y') ?? '—' }} · ₱{{ number_format($bill->total_amount, 2) }}</p>
                                        </div>
                                        <span class="text-[11px] text-gray-500">{{ optional($bill->created_at)->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            @else
                                <p>No unresolved bills linked to this applicant.</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Related applications</p>
                            @if($relatedApplications->isNotEmpty())
                                <span class="text-xs text-gray-500">{{ $relatedApplications->count() }} record{{ $relatedApplications->count() === 1 ? '' : 's' }}</span>
                            @endif
                        </div>
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/60 divide-y divide-gray-100 dark:divide-gray-800">
                            @if($relatedApplications->isNotEmpty())
                                @foreach($relatedApplications as $item)
                                    <div class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ $item->application_code }}</span>
                                            <span class="text-[11px] text-gray-500">{{ optional($item->created_at)->format('M d, Y') ?? '—' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex items-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200 px-2 py-0.5">{{ \Illuminate\Support\Str::headline($item->status) }}</span>
                                            @if($item->decision)
                                                <span class="text-[11px] uppercase tracking-wide text-gray-500">{{ $item->decision }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="px-3 py-4 text-xs text-gray-500">No previous applications with identical details.</div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notes & violations</p>
                        <div class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                            @if(isset($riskFlags) && $riskFlags->isNotEmpty())
                                @foreach($riskFlags as $flag)
                                    <div class="rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50/70 dark:bg-amber-900/30 px-3 py-2">
                                        <p class="font-medium text-amber-700 dark:text-amber-200 uppercase tracking-wide text-[11px]">{{ \Illuminate\Support\Str::headline($flag['type'] ?? 'note') }}</p>
                                        <p class="mt-1">{{ $flag['note'] ?? '—' }}</p>
                                    </div>
                                @endforeach
                            @else
                                <p>No recorded violations or manual flags.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Finalize outcome once duplicate and billing checks are cleared.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="approve-btn inline-flex items-center gap-2 rounded-lg bg-emerald-600 text-white px-4 py-2 hover:bg-emerald-500 {{ $isApproved ? 'opacity-60 cursor-not-allowed pointer-events-none' : '' }}" data-id="{{ $app->id }}" @if($isApproved) disabled @endif>
                            <x-heroicon-o-check class="w-5 h-5" />
                            <span class="text-sm font-semibold">Approve</span>
                        </button>
                        <button class="reject-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 {{ $isApproved ? 'bg-gray-300 text-gray-500 cursor-not-allowed pointer-events-none' : 'bg-rose-600 text-white hover:bg-rose-500' }}" data-id="{{ $app->id }}" @if($isApproved) data-disabled="true" disabled title="Already approved" @endif>
                            <x-heroicon-o-no-symbol class="w-5 h-5" />
                            <span class="text-sm font-semibold">Reject</span>
                        </button>
                        @if($app->customer_id)
                            <button class="verify-btn inline-flex items-center justify-center rounded-full w-10 h-10 bg-blue-600 text-white hover:bg-blue-500" data-cid="{{ $app->customer_id }}" title="Verify customer">
                                <x-heroicon-o-shield-check class="w-5 h-5" />
                                <span class="sr-only">Verify customer</span>
                            </button>
                        @endif
                    </div>
                </div>
            </section>

            @if($app->customer)
                <section class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Existing customer match</h2>
                        <span class="text-xs text-gray-400">Auto-linked</span>
                    </div>
                    <dl class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between"><span>Name</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $app->customer->name }}</span></div>
                        <div class="flex justify-between"><span>Account #</span><span>{{ $app->customer->account_no }}</span></div>
                        <div class="flex justify-between"><span>Status</span><span>{{ $app->customer->status }}</span></div>
                        <div class="flex justify-between"><span>Meter No</span><span>{{ $app->customer->meter_no ?? '—' }}</span></div>
                    </dl>
                    <a href="{{ route('admin.customers') }}" class="inline-flex items-center gap-2 text-xs font-medium text-blue-600 hover:text-blue-500">
                        View in customer directory
                        <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                    </a>
                </section>
            @endif
        </aside>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '';
    async function send(url, method, body){
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        };
        if (body) {
            headers['Content-Type'] = 'application/json';
        }

        const res = await fetch(url, {
            method,
            headers,
            body: body ? JSON.stringify(body) : undefined,
        });

        if (!res.ok) {
            let message = 'Action failed. Please try again.';
            try {
                const data = await res.json();
                if (data?.message) {
                    message = data.message;
                } else if (data?.errors) {
                    const errors = Object.values(data.errors).flat();
                    if (errors.length) {
                        message = errors.join('\n');
                    }
                }
            } catch (_) {}
            throw new Error(message);
        }

        window.location.reload();
    }

    document.addEventListener('click', async (event) => {
        const approve = event.target.closest?.('.approve-btn');
        const reject = event.target.closest?.('.reject-btn');
        const verify = event.target.closest?.('.verify-btn');
        try {
            if (approve) {
                await send(`/api/applications/${approve.dataset.id}/approve`, 'PUT', { auto_verify: false });
            }
            if (reject) {
                if (reject.dataset.disabled || reject.hasAttribute('disabled')) {
                    return;
                }
                const reason = window.prompt('Optional reason for rejection:');
                await send(`/api/applications/${reject.dataset.id}/reject`, 'PUT', { reason });
            }
            if (verify) {
                await send(`/api/customer/${verify.dataset.cid}/verify`, 'PUT');
            }
        } catch (error) {
            window.alert(error?.message || 'Action failed. Please try again.');
        }
    });
})();
</script>
@endpush
@endsection
