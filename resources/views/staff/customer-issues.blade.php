@extends('layouts.app')

@section('content')
<div class="font-[Poppins] space-y-6">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <span class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.35em] text-blue-500 dark:text-blue-300">Staff Requests</span>
            <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">Issue Complaints Intake</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-2xl">
                Complaints raised inside the staff workspace land here. Search an account, review their queue, and forward well-documented tickets so the admin team can close the loop quickly.
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span id="ciLastUpdated" class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <x-heroicon-o-clock class="w-4 h-4" />
                <span>Awaiting lookup</span>
            </span>
        </div>
    </header>

    {{-- Search & selection area --}}
    <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl shadow-lg p-6">
        <div class="grid gap-6 lg:grid-cols-[2fr_3fr]">
            <div class="space-y-4">
                <div>
                    <label for="ciSearchInput" class="text-xs font-semibold uppercase tracking-[0.25em] text-gray-500 dark:text-gray-400">Search customer</label>
                    <div class="mt-2 relative">
                        <input id="ciSearchInput" type="text" placeholder="Enter account number, name, or address"
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               autocomplete="off">
                        <div id="ciSearchResults" class="absolute inset-x-0 top-full z-30 hidden mt-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg max-h-60 overflow-y-auto">
                            <div class="p-3 text-xs text-gray-400">Start typing to search…</div>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60 p-4 space-y-3" id="ciSearchHints">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">How the queue works</p>
                    <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1 list-disc list-inside">
                        <li>Use the full account number for fastest results (e.g. <span class="font-semibold text-gray-700 dark:text-gray-200">22-000123-4</span>).</li>
                        <li>Match by name or address if the account number isn’t available.</li>
                        <li>Once selected, you can review open, priority, and resolved complaints immediately.</li>
                    </ul>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60 p-5 space-y-4" id="ciAccountPanel" hidden>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gray-500 dark:text-gray-400">Selected account</p>
                        <h2 id="ciAccountName" class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">—</h2>
                        <p id="ciAccountAddress" class="text-sm text-gray-500 dark:text-gray-400">—</p>
                    </div>
                    <div class="text-right text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <p><span class="font-semibold">Account no.</span> <span id="ciAccountNumber">—</span></p>
                        <p id="ciContactNumber">Contact: —</p>
                        <p id="ciMeterDetails">Meter: —</p>
                    </div>
                </div>
                <dl class="grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-3">
                        <dt class="text-[11px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Open</dt>
                        <dd id="ciMetricOpen" class="text-xl font-semibold text-amber-600 dark:text-amber-300">0</dd>
                    </div>
                    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-3">
                        <dt class="text-[11px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Resolved</dt>
                        <dd id="ciMetricResolved" class="text-xl font-semibold text-emerald-600 dark:text-emerald-300">0</dd>
                    </div>
                    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-3">
                        <dt class="text-[11px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Total</dt>
                        <dd id="ciMetricTotal" class="text-xl font-semibold text-sky-600 dark:text-sky-300">0</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    {{-- Issue form & timeline --}}
    <section class="space-y-6">
        <form id="ciIssueForm" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl shadow-lg p-6 space-y-5" novalidate>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-500 dark:text-blue-300">Log an issue complaint</p>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Create complaint ticket</h2>
                </div>
                <span id="ciFormStatus" class="text-xs text-gray-400">No account selected</span>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Account number</label>
                    <input id="ciFormAccount" name="account_no" type="text" readonly class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ciIssueType" class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Complaint category</label>
                        <select id="ciIssueType" name="issue_type" class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200">
                            <option value="">Select issue type</option>
                            <option>Water quality</option>
                            <option>Service interruption</option>
                            <option>Meter concern</option>
                            <option>Billing dispute</option>
                            <option>Collection or payment</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="ciSeverity" class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Severity</label>
                        <select id="ciSeverity" name="severity" class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200">
                            <option value="normal">Normal</option>
                            <option value="elevated">Elevated</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ciChannel" class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Channel</label>
                        <select id="ciChannel" name="channel" class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200">
                            <option value="">Select channel</option>
                            <option>Walk-in</option>
                            <option>Phone call</option>
                            <option>SMS</option>
                            <option>Email</option>
                            <option>Field visit</option>
                        </select>
                    </div>
                    <div>
                        <label for="ciSubject" class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Short subject (optional)</label>
                        <input id="ciSubject" name="subject" type="text" maxlength="150" placeholder="e.g. Low pressure in Zone 4"
                               class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200">
                    </div>
                </div>
                <div>
                    <label for="ciSummary" class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Complaint summary</label>
                    <textarea id="ciSummary" name="summary" rows="3" maxlength="500" placeholder="Describe the situation concisely"
                              class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200"></textarea>
                </div>
                <div>
                    <label for="ciDetails" class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Detailed notes</label>
                    <textarea id="ciDetails" name="details" rows="5" placeholder="Add extra context, troubleshooting steps performed, or commitments given"
                              class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200"></textarea>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <div id="ciFormErrors" class="text-xs text-rose-500 hidden"></div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 text-sm font-semibold transition">
                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                    Save issue
                </button>
            </div>
        </form>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500 dark:text-gray-400">Recent complaints</p>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Complaint timeline</h2>
                </div>
                <button id="ciRefreshTimeline" type="button" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-full border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition" disabled>
                    <x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh
                </button>
            </div>
            <div id="ciTimeline" class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 px-4 py-6 text-center text-gray-400" id="ciTimelinePlaceholder">
                    Select a customer to review their complaint history.
                </div>
            </div>
        </section>
    </section>
</div>

@push('scripts')
<script>
(function(){
    const searchInput = document.getElementById('ciSearchInput');
    const searchResults = document.getElementById('ciSearchResults');
    const accountPanel = document.getElementById('ciAccountPanel');
    const accountName = document.getElementById('ciAccountName');
    const accountAddress = document.getElementById('ciAccountAddress');
    const accountNumber = document.getElementById('ciAccountNumber');
    const contactNumber = document.getElementById('ciContactNumber');
    const meterDetails = document.getElementById('ciMeterDetails');
    const metrics = {
        open: document.getElementById('ciMetricOpen'),
        resolved: document.getElementById('ciMetricResolved'),
        total: document.getElementById('ciMetricTotal'),
    };
    const lastUpdated = document.getElementById('ciLastUpdated');
    const timeline = document.getElementById('ciTimeline');
    const timelinePlaceholder = document.getElementById('ciTimelinePlaceholder');
    const refreshTimelineBtn = document.getElementById('ciRefreshTimeline');
    const form = document.getElementById('ciIssueForm');
    const formAccount = document.getElementById('ciFormAccount');
    const formStatus = document.getElementById('ciFormStatus');
    const formErrors = document.getElementById('ciFormErrors');

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let debounceTimer = null;
    let selectedAccount = null;
    let lastSnapshot = null;
    let currentSearchController = null;
    let latestResults = [];
    let pendingSnapshotAccount = null;

    const normalizeAccount = (value = '') => value.replace(/[^A-Za-z0-9]/g, '').toLowerCase();

    function resetPanels(){
        selectedAccount = null;
        lastSnapshot = null;
        accountPanel.hidden = true;
        metrics.open.textContent = '0';
        metrics.resolved.textContent = '0';
        metrics.total.textContent = '0';
        accountName.textContent = '—';
        accountAddress.textContent = '—';
        accountNumber.textContent = '—';
        contactNumber.textContent = 'Contact: —';
        meterDetails.textContent = 'Meter: —';
        formAccount.value = '';
        formStatus.textContent = 'No account selected';
        refreshTimelineBtn.disabled = true;
        timeline.innerHTML = '';
        timeline.appendChild(timelinePlaceholder.cloneNode(true));
        lastUpdated.querySelector('span')?.remove();
        lastUpdated.textContent = '';
        lastUpdated.appendChild(document.createTextNode('Awaiting lookup'));
    }

    function selectAccount(item){
        if (!item || !item.account_no) {
            return;
        }

        const normalizedTarget = normalizeAccount(item.account_no);
        if (
            normalizeAccount(selectedAccount) === normalizedTarget ||
            normalizeAccount(pendingSnapshotAccount) === normalizedTarget
        ) {
            searchResults.classList.add('hidden');
            searchInput.value = item.account_no;
            return;
        }

        searchResults.classList.add('hidden');
        searchInput.value = item.account_no;
        loadSnapshot(item.account_no);
    }

    function maybeAutoSelect(query){
        const normalizedQuery = normalizeAccount(query);
        if (normalizedQuery.length < 4) {
            return;
        }

        if (
            normalizeAccount(selectedAccount) === normalizedQuery ||
            normalizeAccount(pendingSnapshotAccount) === normalizedQuery
        ) {
            return;
        }

        const exact = latestResults.find(item => normalizeAccount(item.account_no) === normalizedQuery);
        if (exact) {
            selectAccount(exact);
        }
    }

    function showResults(items){
        latestResults = items;
        searchResults.innerHTML = '';
        if (!items.length){
            const empty = document.createElement('div');
            empty.className = 'p-3 text-xs text-gray-400';
            empty.textContent = 'No matches found';
            searchResults.appendChild(empty);
            searchResults.classList.remove('hidden');
            return;
        }

        items.forEach(item => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'w-full text-left px-4 py-3 text-sm hover:bg-blue-50 dark:hover:bg-gray-800 transition flex flex-col gap-1';
            option.innerHTML = `<span class="font-semibold text-gray-800 dark:text-gray-100">${item.account_no}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">${item.name ?? '—'}</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">${item.address ?? ''}</span>`;
            option.addEventListener('click', () => {
                selectAccount(item);
            });
            searchResults.appendChild(option);
        });
        searchResults.classList.remove('hidden');
    }

    async function searchAccounts(query){
        if (currentSearchController){
            currentSearchController.abort();
        }
        latestResults = [];
        if (!query){
            searchResults.classList.add('hidden');
            return;
        }
        const controller = new AbortController();
        currentSearchController = controller;
        searchResults.innerHTML = '<div class="p-3 text-xs text-gray-400">Searching…</div>';
        searchResults.classList.remove('hidden');
        try {
            const res = await fetch(`{{ route('api.staff.customer-issues.search') }}?q=${encodeURIComponent(query)}`, {
                signal: controller.signal,
            });
            if (!res.ok) throw new Error('Search failed');
            const data = await res.json();
            if (currentSearchController !== controller) return;
            const items = Array.isArray(data.results) ? data.results : [];
            showResults(items);
            maybeAutoSelect(query);
        } catch (error){
            if (error.name === 'AbortError'){
                return;
            }
            console.error(error);
            searchResults.innerHTML = '<div class="p-3 text-xs text-rose-500">Unable to search right now.</div>';
            searchResults.classList.remove('hidden');
        }
    }

    async function loadSnapshot(accountNo){
        const normalizedTarget = normalizeAccount(accountNo);
        if (!normalizedTarget) {
            return;
        }

        if (normalizeAccount(pendingSnapshotAccount) === normalizedTarget) {
            return;
        }

        pendingSnapshotAccount = accountNo;
        resetPanels();
        formStatus.textContent = 'Loading…';
        try {
            const res = await fetch(`{{ route('api.staff.customer-issues.snapshot') }}?account_no=${encodeURIComponent(accountNo)}`);
            if (!res.ok){
                const err = await res.json().catch(() => ({}));
                throw new Error(err.error || 'Failed to retrieve account');
            }
            const data = await res.json();
            selectedAccount = data.customer.account_no;
            lastSnapshot = data;
            accountPanel.hidden = false;
            accountName.textContent = data.customer.name || '—';
            accountAddress.textContent = data.customer.address || '—';
            accountNumber.textContent = data.customer.account_no || '—';
            contactNumber.textContent = `Contact: ${data.customer.contact_no || '—'}`;
            meterDetails.textContent = `Meter: ${data.customer.meter_no || '—'} ${data.customer.meter_size ? '(' + data.customer.meter_size + ')' : ''}`;
            metrics.open.textContent = data.metrics.open ?? 0;
            metrics.resolved.textContent = data.metrics.resolved ?? 0;
            metrics.total.textContent = data.metrics.total ?? 0;
            formAccount.value = data.customer.account_no;
            formStatus.textContent = 'Ready to document issue';
            refreshTimelineBtn.disabled = false;
            renderTimeline(data.issues || []);
            updateLastUpdated();
        } catch (error){
            console.error(error);
            formStatus.textContent = 'Unable to load account';
            showToast?.('Failed to load account: ' + error.message, 'error');
        } finally {
            pendingSnapshotAccount = null;
        }
    }

    const formatStatus = (value) => {
        if (!value) return '—';
        return value.replace(/_/g, ' ').replace(/\b\w/g, (match) => match.toUpperCase());
    };

    function renderTimeline(entries){
        timeline.innerHTML = '';
        if (!entries.length){
            const empty = document.createElement('div');
            empty.className = 'rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 px-4 py-6 text-center text-gray-400';
            empty.textContent = 'No documented complaints yet.';
            timeline.appendChild(empty);
            return;
        }

        entries.forEach(issue => {
            const card = document.createElement('article');
            card.className = 'rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4';
            const priorityClass = issue.is_priority
                ? 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-900/30 dark:text-red-200 dark:border-red-700'
                : 'bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';
            const statusClass = issue.status === 'resolved'
                ? 'bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-800'
                : 'bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-700';
            const severityClass = issue.severity === 'critical'
                ? 'bg-rose-50 text-rose-600 border border-rose-200 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-200'
                : issue.severity === 'elevated'
                    ? 'bg-amber-50 text-amber-600 border border-amber-200 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-200'
                    : 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800/60 dark:border-slate-700 dark:text-slate-200';

            card.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.3em]">
                            <span class="px-2 py-1 rounded-full ${priorityClass}">${issue.is_priority ? 'Priority' : 'Standard'}</span>
                            <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-700">${issue.issue_type || 'Customer Issue'}</span>
                            <span class="px-2 py-1 rounded-full ${statusClass}">${formatStatus(issue.status)}</span>
                            <span class="px-2 py-1 rounded-full ${severityClass}">${issue.severity ? formatStatus(issue.severity) : 'Normal'}</span>
                        </div>
                        <h3 class="mt-2 text-base font-semibold text-gray-900 dark:text-gray-100">${issue.subject ? issue.subject : issue.summary}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">${issue.summary}</p>
                        ${issue.details ? `<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">${issue.details}</p>` : ''}
                        ${issue.channel ? `<p class="mt-3 text-xs text-gray-400">Channel: ${issue.channel}</p>` : ''}
                    </div>
                    <div class="text-right text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <span class="block font-semibold text-gray-700 dark:text-gray-200">${issue.created_at ?? '—'}</span>
                        <span>${issue.created_human ?? ''}</span>
                        <span>Filed by: ${issue.documented_by ?? '—'}</span>
                    </div>
                </div>`;
            timeline.appendChild(card);
        });
    }

    function updateLastUpdated(){
        if (!lastSnapshot || !lastSnapshot.issues?.length) {
            lastUpdated.querySelector('span')?.remove();
            lastUpdated.innerHTML = '<x-heroicon-o-clock class="w-4 h-4" /> No timeline yet';
            return;
        }
        const latest = lastSnapshot.issues[0];
        lastUpdated.innerHTML = '';
        const icon = document.createElement('span');
        icon.className = 'inline-flex items-center';
        icon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z"/></svg>';
        const text = document.createElement('span');
        text.textContent = ` Latest entry ${latest.created_human ?? ''}`;
        lastUpdated.append(icon, text);
    }

    function setFormLoading(isLoading){
        const submit = form.querySelector('button[type="submit"]');
        submit.disabled = isLoading;
        submit.classList.toggle('opacity-70', isLoading);
        submit.textContent = isLoading ? 'Saving…' : 'Save issue';
    }

    searchInput?.addEventListener('input', (e) => {
        const value = e.target.value.trim();
        if (debounceTimer) clearTimeout(debounceTimer);
        if (!value){
            searchResults.classList.add('hidden');
            return;
        }
        debounceTimer = setTimeout(() => searchAccounts(value), 120);
    });

    searchInput?.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') {
            return;
        }

        const value = searchInput.value.trim();
        if (!value){
            return;
        }

        e.preventDefault();
        const normalizedValue = normalizeAccount(value);
        if (latestResults.length){
            const exact = latestResults.find(item => normalizeAccount(item.account_no) === normalizedValue);
            selectAccount(exact ?? latestResults[0]);
            return;
        }

        if (normalizedValue.length >= 4 && /\d/.test(normalizedValue)){
            selectAccount({ account_no: value });
        } else {
            searchAccounts(value);
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchResults.contains(e.target) && e.target !== searchInput){
            searchResults.classList.add('hidden');
        }
    });

    refreshTimelineBtn?.addEventListener('click', () => {
        if (selectedAccount){
            loadSnapshot(selectedAccount);
        }
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        formErrors.classList.add('hidden');
        formErrors.textContent = '';

        if (!selectedAccount){
            formErrors.textContent = 'Select an account before documenting an issue.';
            formErrors.classList.remove('hidden');
            return;
        }

        const payload = {
            account_no: formAccount.value,
            issue_type: document.getElementById('ciIssueType').value,
            severity: document.getElementById('ciSeverity').value,
            channel: document.getElementById('ciChannel').value,
            subject: document.getElementById('ciSubject').value,
            summary: document.getElementById('ciSummary').value,
            details: document.getElementById('ciDetails').value,
        };

        if (!payload.issue_type){
            formErrors.textContent = 'Please select an issue type.';
            formErrors.classList.remove('hidden');
            return;
        }
        if (!payload.summary.trim()){
            formErrors.textContent = 'Summary is required.';
            formErrors.classList.remove('hidden');
            return;
        }

        setFormLoading(true);
        try {
            const res = await fetch(`{{ route('api.staff.customer-issues.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok){
                if (res.status === 422 && data.errors){
                    formErrors.innerHTML = Object.values(data.errors).map(err => `<div>${err}</div>`).join('');
                    formErrors.classList.remove('hidden');
                } else {
                    throw new Error(data.error || data.message || 'Unable to save issue');
                }
                return;
            }
            showToast?.('Customer issue recorded successfully.');
            document.getElementById('ciIssueType').value = '';
            document.getElementById('ciSeverity').value = 'normal';
            document.getElementById('ciChannel').value = '';
            document.getElementById('ciSubject').value = '';
            document.getElementById('ciSummary').value = '';
            document.getElementById('ciDetails').value = '';
            loadSnapshot(selectedAccount);
        } catch (error){
            console.error(error);
            showToast?.(error.message || 'Failed to save issue', 'error');
        } finally {
            setFormLoading(false);
        }
    });

    resetPanels();
})();
</script>
@endpush
@endsection
