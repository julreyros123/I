@extends('layouts.app')

@section('title', 'Archived Billing Records')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8 font-[Poppins]">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 sm:p-6 lg:p-8 space-y-4">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-3">
                <div>
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-100">Archived Billing Records</h2>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">Search deleted bills and optionally restore or purge them.</p>
                    @isset($archivedCount)
                        <p class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-500 mt-1">Total archived: <span class="font-semibold">{{ number_format($archivedCount) }}</span></p>
                    @endisset
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('records.billing.export-archived', array_filter(['q' => $q ?? null])) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs sm:text-sm rounded-lg border border-amber-500 text-amber-600 dark:text-amber-400 hover:bg-amber-500 hover:text-white dark:hover:text-white font-semibold transition">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" /> Export CSV
                    </a>
                </div>
            </div>

            <!-- Search + filters -->
            <form method="GET" class="flex flex-col gap-3" id="archivedSearchForm">
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="hidden sm:inline-flex text-[11px] uppercase tracking-wide text-gray-400 ml-auto">Showing {{ $records->total() }} result{{ $records->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="w-full lg:max-w-xl flex items-stretch">
                    <div class="relative flex flex-1 overflow-hidden rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/60 focus-within:ring-2 focus-within:ring-blue-400/60" data-archived-autocomplete="container">
                        <div class="flex items-center px-3 text-gray-400">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input type="text" name="q" value="{{ $q ?? '' }}"
                               class="flex-1 px-3 py-2 bg-transparent text-sm text-gray-900 dark:text-gray-100 focus:outline-none"
                               placeholder="Search by account no. or customer name"
                               autocomplete="off"
                               data-archived-autocomplete="input">
                        <button type="submit" class="h-full px-4 text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white transition-colors rounded-none rounded-r-xl">Search</button>
                        <div class="absolute z-30 inset-x-0 top-full mt-1 hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 shadow-2xl text-sm overflow-hidden" data-archived-autocomplete="panel">
                            <div class="py-3 text-center text-xs text-gray-400 dark:text-gray-500" data-archived-autocomplete="empty">Start typing a customer name or account number</div>
                            <ul class="max-h-64 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800" data-archived-autocomplete="list"></ul>
                            <div class="hidden py-3 text-center text-xs text-gray-400 dark:text-gray-500" data-archived-autocomplete="loading">Searching…</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto rounded-3xl border border-gray-200 dark:border-gray-700">
            <table class="w-full min-w-[820px] text-sm text-left text-gray-700 dark:text-gray-200">
                <thead class="bg-gradient-to-r from-sky-600 to-blue-500 text-white font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase">Deleted At</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase">Account No.</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase">Customer Name</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase text-right">Total</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase">Status</th>
                        <th class="px-6 py-3 text-xs tracking-wide uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($records as $r)
                    <tr class="bg-white even:bg-slate-50 dark:bg-gray-900 dark:even:bg-gray-900/60 hover:bg-blue-50/70 dark:hover:bg-gray-800 transition">
                        <td class="px-6 py-4 align-top text-xs text-gray-500 dark:text-gray-400">{{ optional($r->deleted_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 align-top text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $r->account_no }}</td>
                        <td class="px-6 py-4 align-top">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $r->customer->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[220px]">{{ $r->customer->address ?? 'Address unavailable' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top text-right text-sm font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($r->total_amount, 2) }}</td>
                        <td class="px-6 py-4 align-top">
                            @php
                                $statusClass = match($r->bill_status) {
                                    'Paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
                                    'Outstanding Payment' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-200',
                                    'Overdue' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-200',
                                    'Notice of Disconnection' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
                                    'Disconnected' => 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
                                    default => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $r->bill_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top text-right space-x-2">
                            <form action="{{ route('records.billing.restore', $r->id) }}" method="POST" class="inline">
                                @csrf
                                <button class="px-3 py-1 rounded-full text-xs bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm" onclick="return confirm('Restore this record?');">Restore</button>
                            </form>
                            <form action="{{ route('records.billing.force', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete this record?');">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 rounded-full text-xs bg-red-500 hover:bg-red-600 text-white shadow-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No archived records.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">Showing {{ $records->firstItem() ?? 0 }}-{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records</p>
            <div class="self-start sm:self-auto">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const container = document.querySelector('[data-archived-autocomplete="container"]');
    if (!container) return;

    const input = container.querySelector('[data-archived-autocomplete="input"]');
    const panel = container.querySelector('[data-archived-autocomplete="panel"]');
    const list = container.querySelector('[data-archived-autocomplete="list"]');
    const emptyState = container.querySelector('[data-archived-autocomplete="empty"]');
    const loadingState = container.querySelector('[data-archived-autocomplete="loading"]');
    const form = document.getElementById('archivedSearchForm');

    let debounce;
    let activeIndex = -1;
    let buttons = [];
    let suggestions = [];

    function hidePanel(resetEmpty = true){
        if (!panel) return;
        panel.classList.add('hidden');
        if (list) list.innerHTML = '';
        buttons = [];
        suggestions = [];
        activeIndex = -1;
        if (loadingState) loadingState.classList.add('hidden');
        if (emptyState && resetEmpty) {
            emptyState.textContent = 'Start typing a customer name or account number';
            emptyState.classList.remove('hidden');
        }
    }

    function showPanel(){
        if (!panel) return;
        panel.classList.remove('hidden');
    }

    function setActive(index){
        activeIndex = index;
        buttons.forEach((btn, idx) => {
            if (idx === index) {
                btn.classList.add('bg-blue-50', 'dark:bg-blue-900/30');
            } else {
                btn.classList.remove('bg-blue-50', 'dark:bg-blue-900/30');
            }
        });
    }

    function chooseSuggestion(index){
        const selected = suggestions[index];
        if (!selected) return;
        if (input) {
            input.value = selected.account_no || selected.name || '';
        }
        hidePanel(false);
        if (form) form.submit();
    }

    function renderSuggestions(items, term){
        if (!list || !emptyState) return;
        list.innerHTML = '';
        buttons = [];
        suggestions = items;
        activeIndex = -1;

        if (!items.length) {
            emptyState.textContent = `No customers match “${term}”.`;
            emptyState.classList.remove('hidden');
            showPanel();
            return;
        }

        emptyState.classList.add('hidden');

        items.forEach((item, index) => {
            const li = document.createElement('li');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.index = String(index);
            btn.className = 'w-full text-left px-4 py-3 flex flex-col gap-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition focus:outline-none';

            const statusLabel = (item.status || '').toLowerCase();
            let statusClasses = 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300';
            if (statusLabel === 'active') {
                statusClasses = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200';
            } else if (statusLabel === 'disconnected') {
                statusClasses = 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200';
            }

            btn.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="space-y-1">
                        <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">${item.account_no || '—'}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">${item.name || 'No name on file'}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold ${statusClasses}">
                        ${item.status || '—'}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">${item.address || 'No address on file'}</p>
            `;

            btn.addEventListener('click', () => chooseSuggestion(index));
            li.appendChild(btn);
            list.appendChild(li);
            buttons.push(btn);
        });

        showPanel();
    }

    async function fetchSuggestions(term){
        if (!panel) return;
        const url = new URL("{{ route('customer.searchAccounts') }}", window.location.origin);
        url.searchParams.set('q', term);
        url.searchParams.set('include_all', '1');

        if (loadingState) loadingState.classList.remove('hidden');
        if (emptyState) emptyState.classList.add('hidden');
        showPanel();

        try {
            const res = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Request failed');
            const payload = await res.json();
            const results = Array.isArray(payload.suggestions) ? payload.suggestions : [];
            renderSuggestions(results, term);
        } catch (error) {
            if (emptyState) {
                emptyState.textContent = 'Unable to load suggestions right now.';
                emptyState.classList.remove('hidden');
            }
            showPanel();
        } finally {
            if (loadingState) loadingState.classList.add('hidden');
        }
    }

    if (input) {
        input.addEventListener('input', () => {
            clearTimeout(debounce);
            const term = input.value.trim();
            if (term.length < 2) {
                hidePanel(false);
                return;
            }
            debounce = setTimeout(() => fetchSuggestions(term), 250);
        });

        input.addEventListener('focus', () => {
            const term = input.value.trim();
            if (term.length >= 2) {
                fetchSuggestions(term);
            }
        });

        input.addEventListener('keydown', (event) => {
            if (!panel || panel.classList.contains('hidden')) return;
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (!buttons.length) return;
                const next = (activeIndex + 1) % buttons.length;
                setActive(next);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (!buttons.length) return;
                const prev = activeIndex <= 0 ? buttons.length - 1 : activeIndex - 1;
                setActive(prev);
            } else if (event.key === 'Enter') {
                if (activeIndex >= 0) {
                    event.preventDefault();
                    chooseSuggestion(activeIndex);
                }
            } else if (event.key === 'Escape') {
                hidePanel(false);
            }
        });
    }

    document.addEventListener('click', (event) => {
        if (!container) return;
        if (!container.contains(event.target)) {
            hidePanel(false);
        }
    });

    panel?.addEventListener('mouseenter', () => setActive(-1));
});
</script>
@endpush
