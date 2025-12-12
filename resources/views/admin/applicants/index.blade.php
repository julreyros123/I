@extends('layouts.admin')

@section('title', 'Applicant Queue')

@section('content')
<div class="w-full mx-auto px-4 sm:px-6 py-4 sm:py-6 lg:py-8 font-[Poppins] space-y-6">
    <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-6 shadow-lg shadow-gray-200/60 dark:shadow-none">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">New Applicant Workspace</h1>
                <p class="text-sm/relaxed text-gray-500 dark:text-gray-400">Track every new connection request, validate requirements, and act fast on pending approvals.</p>
            </div>
            <div class="hidden"></div>
        </div>
        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $summary = [
                    ['key' => 'registered', 'label' => 'Awaiting Review', 'accent' => 'bg-white/15'],
                    ['key' => 'waiting_payment', 'label' => 'Waiting Payment', 'accent' => 'bg-white/20'],
                    ['key' => 'scheduled', 'label' => 'Scheduled Installs', 'accent' => 'bg-white/20'],
                    ['key' => 'installing', 'label' => 'Installing Now', 'accent' => 'bg-white/25'],
                ];
            @endphp
            @foreach($summary as $card)
                <div class="rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-4">
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-[0.18em]">{{ $card['label'] }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $statusCounts[$card['key']] ?? 0 }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50/80 text-rose-700 px-4 py-3">
            <div class="flex gap-3">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-rose-500" />
                <div>
                    <p class="font-semibold text-sm">Invalid filter selections detected.</p>
                    <ul class="mt-1 text-xs list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700/60 overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Applicant Queue</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $applications->firstItem() ?? 0 }} – {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} applicants.</p>
            </div>
            <form method="GET" class="flex flex-wrap items-center gap-2 text-xs sm:text-sm w-full lg:w-auto">
                <div class="relative flex-1 min-w-[160px]">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search applicant or address" class="w-full h-10 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 pl-10 pr-3 text-sm text-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-300/60" />
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 inset-y-0 my-auto" />
                </div>
                <select name="status" class="h-10 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-200">
                    <option value="">All Status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="decision" class="h-10 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-200">
                    <option value="">All Decisions</option>
                    @foreach($decisionOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['decision'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="per_page" class="h-10 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-700 dark:text-gray-200">
                    @foreach($perPageOptions as $size)
                        <option value="{{ $size }}" @selected(($filters['per_page'] ?? null) == $size)>{{ $size }}/page</option>
                    @endforeach
                </select>
                <button class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow hover:bg-blue-700">Apply</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/70 text-gray-600 dark:text-gray-300 uppercase text-[11px] tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">ID</th>
                        <th class="px-5 py-3 text-left">Applicant</th>
                        <th class="px-5 py-3 text-left">Barangay</th>
                        <th class="px-5 py-3 text-left">Total Fees</th>
                        <th class="px-5 py-3 text-left">Paid At</th>
                        <th class="px-5 py-3 text-left">Schedule Date</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-800 dark:text-gray-100">
                    @forelse($applications as $app)
                        @php
                            $appCode = $app->application_code ?? sprintf('APP-%06d', $app->id);
                            $barangay = Str::of($app->address ?? '—')->before(',')->limit(40);
                            $statusPills = [
                                'registered' => 'bg-slate-100 text-slate-700',
                                'pending' => 'bg-slate-100 text-slate-700',
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'assessed' => 'bg-amber-100 text-amber-700',
                                'waiting_payment' => 'bg-orange-100 text-orange-700',
                                'paid' => 'bg-indigo-100 text-indigo-700',
                                'scheduled' => 'bg-sky-100 text-sky-700',
                                'installing' => 'bg-purple-100 text-purple-700',
                                'installed' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-rose-100 text-rose-700',
                            ];
                            $statusClass = $statusPills[$app->status] ?? 'bg-gray-100 text-gray-700';
                            $feeTotal = collect([
                                $app->fee_application,
                                $app->fee_inspection,
                                $app->fee_materials,
                                $app->fee_labor,
                                $app->meter_deposit,
                            ])->filter()->sum();
                            $actionsLocked = in_array($app->status, ['approved','assessed','waiting_payment','paid','scheduled','installing','installed'], true);
                        @endphp
                        <tr class="hover:bg-blue-50/70 dark:hover:bg-gray-800/60 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $appCode }}</td>
                            <td class="px-5 py-3">
                                <div class="flex flex-col">
                                    <a href="{{ route('admin.applicants.show', $app->id) }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">{{ $app->applicant_name ?? '—' }}</a>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Submitted {{ optional($app->created_at)->format('M d, Y • g:i A') ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">
                                <button type="button" class="nc-address-btn text-blue-600 dark:text-blue-400 hover:underline" data-full-address="{{ $app->address }}">{{ $barangay }}</button>
                            </td>
                            <td class="px-5 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $feeTotal ? '₱'.number_format($feeTotal, 2) : '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">{{ optional($app->paid_at)->format('M d, Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">{{ optional($app->schedule_date)->format('M d, Y') ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ Str::headline($app->status ?? '—') }}
                                    </span>
                                    @if($app->decision)
                                        <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ strtoupper($app->decision) }}</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2 text-xs font-medium">
                                    <a href="{{ route('admin.applicants.show', $app->id) }}" class="inline-flex items-center gap-1 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <x-heroicon-o-eye class="w-4 h-4" /> Review
                                    </a>
                                    <button class="approve-btn inline-flex items-center justify-center rounded-xl w-9 h-9 {{ $actionsLocked ? 'bg-emerald-200 text-white/70 cursor-not-allowed pointer-events-none' : 'bg-emerald-600 text-white hover:bg-emerald-500' }}" data-id="{{ $app->id }}" {{ $actionsLocked ? 'data-disabled=true' : '' }} title="Approve">
                                        <x-heroicon-o-check class="w-4 h-4" />
                                        <span class="sr-only">Approve</span>
                                    </button>
                                    <button class="reject-btn inline-flex items-center justify-center rounded-xl w-9 h-9 {{ $actionsLocked ? 'bg-rose-200 text-white/70 cursor-not-allowed pointer-events-none' : 'bg-rose-600 text-white hover:bg-rose-500' }}" data-id="{{ $app->id }}" {{ $actionsLocked ? 'data-disabled=true' : '' }} title="Reject">
                                        <x-heroicon-o-no-symbol class="w-4 h-4" />
                                        <span class="sr-only">Reject</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-gray-300" />
                                    <p class="font-medium">No applications match the current filters.</p>
                                    <p class="text-xs">Try adjusting your filters or clearing the search to see all applicants.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <div>
                Showing {{ $applications->firstItem() ?? 0 }} – {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} results
            </div>
            <div class="flex items-center gap-2">
                {{ $applications->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '';
    async function handle(url, method, body){
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
        try {
            if (approve) {
                await handle(`/api/applications/${approve.dataset.id}/approve`, 'PUT', { auto_verify: false });
            }
            if (reject) {
                if (reject.dataset.disabled) {
                    return;
                }
                const confirmed = window.confirm('Rejecting will send this application back to the applicant and cannot be undone. Continue?');
                if (!confirmed) {
                    return;
                }
                const reason = window.prompt('Reason for rejection (optional):');
                await handle(`/api/applications/${reject.dataset.id}/reject`, 'PUT', { reason });
            }
        } catch (error) {
            window.alert(error?.message || 'Action failed. Please try again.');
        }
    });
})();
</script>
@endpush
@endsection
