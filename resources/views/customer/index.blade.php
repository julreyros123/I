@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="w-full px-4 sm:px-6 py-4 font-[Poppins]">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 transition-all space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-gray-100">Customers</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Manage customer records and meter/account details.</p>
            </div>
            <x-danger-button id="clearSelectedBtn" type="button" class="hidden">
                Clear Selected
            </x-danger-button>
        </div>

        <!-- Mobile cards -->
        <div class="space-y-3 lg:hidden">
            @forelse ($customers as $c)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/70 shadow-sm p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.16em] text-gray-400">Account No.</p>
                            <p class="font-mono text-sm text-gray-900 dark:text-gray-100">{{ $c->account_no }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                            {{ $c->status === 'Active'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                : ($c->status === 'Disconnected'
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300') }}">
                            {{ $c->status }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.16em] text-gray-400">Name</p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $c->name }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.16em] text-gray-400">Address</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $c->address }}</p>
                    </div>
                    <div class="flex flex-wrap items-start gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.16em] text-gray-400">Application</p>
                            <div class="flex items-center gap-1 app-badges">
                                <span class="text-gray-400">Loading…</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.16em] text-gray-400">Contact</p>
                            <p>{{ $c->contact_no ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.16em] text-gray-400">Created</p>
                            <p>{{ optional($c->created_at)->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-xs">
                            <x-ui.action-button size="xs" variant="neutral" onclick="openViewModal({{ $c->id }}, @js($c->toArray()))">View</x-ui.action-button>
                            <x-ui.action-button size="xs" variant="primary" onclick="openEditModal({{ $c->id }}, @js($c->toArray()))">Edit</x-ui.action-button>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="app-rescore-btn opacity-50 cursor-not-allowed text-[11px]">Re-score</button>
                            <button class="app-approve-btn opacity-50 cursor-not-allowed text-[11px]">Approve</button>
                            <button class="app-reject-btn opacity-50 cursor-not-allowed text-[11px]">Reject</button>
                            <button class="verify-customer-btn text-[11px] {{ strtolower($c->status) === 'active' ? 'opacity-50 cursor-not-allowed' : '' }}" title="{{ strtolower($c->status) === 'active' ? 'Already Active' : '' }}">Verify</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    No customers yet.
                </div>
            @endforelse
        </div>

        <!-- Desktop table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <thead class="bg-gray-50 dark:bg-gray-700/70 text-gray-700 dark:text-gray-200 font-semibold">
                    <tr>
                        <th class="px-4 py-3 border-b border-gray-300 dark:border-gray-600">
                            <input type="checkbox" id="selectAll" class="w-4 h-4 accent-blue-600">
                        </th>
                        <th class="px-5 py-3 border-b border-gray-300 dark:border-gray-600">Account No.</th>
                        <th class="px-5 py-3 border-b border-gray-300 dark:border-gray-600">Name</th>
                        <th class="px-5 py-3 border-b border-gray-300 dark:border-gray-600">Address</th>
                        <th class="px-5 py-3 border-b border-gray-300 dark:border-gray-600">Application</th>
                        <th class="px-5 py-3 border-b border-gray-300 dark:border-gray-600">Contact No.</th>
                        <th class="px-5 py-3 border-b border-gray-300 dark:border-gray-600">Status</th>
                        <th class="px-5 py-3 border-b border-gray-300 dark:border-gray-600">Created At</th>
                        <th class="px-5 py-3 border-b border-gray-300 dark:border-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="customerTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($customers as $c)
                    <tr class="group transition hover:bg-gray-50 dark:hover:bg-gray-700" data-cust-id="{{ $c->id }}" data-cust-status="{{ $c->status }}" data-account-no="{{ $c->account_no }}" data-reconnect-requested="{{ $c->reconnect_requested_at ? '1' : '0' }}">
                        <td class="px-4 py-3"><input type="checkbox" class="rowCheckbox w-4 h-4 accent-blue-600" value="{{ $c->id }}"></td>
                        <td class="px-5 py-3 font-mono text-gray-900 dark:text-gray-100">{{ $c->account_no }}</td>
                        <td class="px-5 py-3 max-w-[220px]">
                            <span class="block truncate" title="{{ $c->name }}">{{ $c->name }}</span>
                        </td>
                        <td class="px-5 py-3 max-w-[320px]">
                            <span class="block truncate" title="{{ $c->address }}">{{ $c->address }}</span>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1 text-xs app-badges">
                                <span class="text-gray-400">Loading…</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 max-w-[180px]">
                            <span class="block truncate" title="{{ $c->contact_no ?? '' }}">{{ $c->contact_no ?? '' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @php $color = $c->status === 'Active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($c->status === 'Disconnected' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'); @endphp
                            <div class="flex flex-col items-start gap-1" data-reconnect-pill>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">{{ $c->status }}</span>
                                @if($c->reconnect_requested_at)
                                    <span class="reconnect-request-pill inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Reconnect requested</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">{{ optional($c->created_at)->format('Y-m-d') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <details class="relative inline-block">
                                    <summary class="list-none px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 cursor-pointer text-xs">Actions ▾</summary>
                                    <div class="absolute right-0 mt-2 w-52 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow z-10 py-1">
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700" onclick="openViewModal({{ $c->id }}, @js($c->toArray()))">View</button>
                                        <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700" onclick="openEditModal({{ $c->id }}, @js($c->toArray()))">Edit</button>
                                        <button class="w-full text-left px-3 py-2 text-xs app-rescore-btn opacity-50 cursor-not-allowed" title="No application yet">Re-score</button>
                                        <button class="w-full text-left px-3 py-2 text-xs app-approve-btn opacity-50 cursor-not-allowed" title="No application yet">Approve</button>
                                        <button class="w-full text-left px-3 py-2 text-xs app-reject-btn opacity-50 cursor-not-allowed" title="No application yet">Reject</button>
                                        <a class="w-full block px-3 py-2 text-xs app-viewkyc-link opacity-50 cursor-not-allowed" title="No application yet" target="_blank">View KYC</a>
                                        <button class="w-full text-left px-3 py-2 text-xs verify-customer-btn {{ strtolower($c->status) === 'active' ? 'opacity-50 cursor-not-allowed' : '' }}" title="{{ strtolower($c->status) === 'active' ? 'Already Active' : '' }}">Verify Customer</button>
                                        @if (strtolower($c->status) === 'disconnected')
                                            <button
                                                class="w-full text-left px-3 py-2 text-xs request-reconnect-btn {{ $c->reconnect_requested_at ? 'opacity-50 cursor-not-allowed text-gray-400' : 'text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700' }}"
                                                data-cust-id="{{ $c->id }}"
                                                data-account-no="{{ $c->account_no }}"
                                                data-requested="{{ $c->reconnect_requested_at ? 'true' : 'false' }}">
                                                {{ $c->reconnect_requested_at ? 'Reconnect requested' : 'Request reconnect' }}
                                            </button>
                                        @endif
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No customers yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($customers->total())
                    Showing
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $customers->firstItem() }}</span>
                    –
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $customers->lastItem() }}</span>
                    of
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $customers->total() }}</span>
                    customers
                @else
                    No customers to display.
                @endif
            </p>
            <div class="sm:ml-auto">
                {{ $customers->onEachSide(1)->links() }}
            </div>
        </div>

    </div>
</div>

<!-- Script Section -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchCustomer');
    const searchForm = document.getElementById('customerSearchForm');
    const selectAll = document.getElementById('selectAll');
    const clearSelectedBtn = document.getElementById('clearSelectedBtn');

    if (searchInput && searchForm) {
        searchInput.addEventListener('search', () => searchForm.submit());
    }

    const suggestionPanel = document.getElementById('customerSuggestions');
    let suggestionController = null;
    let suggestionIndex = -1;
    let latestSuggestionPayload = [];

    function resetSuggestions(){
        if (suggestionPanel){
            suggestionPanel.classList.add('hidden');
            suggestionPanel.innerHTML = '';
        }
        suggestionIndex = -1;
    }

    async function fetchSuggestions(term){
        if (!suggestionPanel || !term){
            resetSuggestions();
            return;
        }
        if (suggestionController){
            suggestionController.abort();
        }
        suggestionController = new AbortController();
        suggestionPanel.innerHTML = '<div class="px-3 py-2 text-xs text-gray-400">Searching…</div>';
        suggestionPanel.classList.remove('hidden');
        try {
            const res = await fetch(`{{ route('customer.searchAccounts') }}?q=${encodeURIComponent(term)}`, {
                headers: { 'Accept': 'application/json' },
                signal: suggestionController.signal,
            });
            if (!res.ok){
                throw new Error('Request failed');
            }
            const payload = await res.json();
            const items = Array.isArray(payload.suggestions) ? payload.suggestions : [];
            if (!items.length){
                suggestionPanel.innerHTML = '<div class="px-3 py-2 text-xs text-gray-400">No matches.</div>';
                return;
            }
            latestSuggestionPayload = items;
            suggestionPanel.innerHTML = items.map((item, idx) => {
                const status = (item.status || '').toLowerCase();
                const pillTone = status === 'active'
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : status === 'disconnected'
                        ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                        : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300';
                return `
                    <button type="button" data-index="${idx}" class="suggestion-item w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-800 focus:bg-blue-50 dark:focus:bg-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <span class="block font-medium">${item.account_no ?? ''}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ${pillTone}">${item.status ?? ''}</span>
                        </div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">${item.name ?? 'Unnamed'} · ${item.address ?? 'No address'}</span>
                    </button>
                `;
            }).join('');
            suggestionIndex = -1;
        } catch (error){
            if (error.name === 'AbortError') return;
            console.error(error);
            suggestionPanel.innerHTML = '<div class="px-3 py-2 text-xs text-rose-500">Unable to load suggestions.</div>';
        }
    }

    if (searchInput){
        searchInput.addEventListener('input', (event) => {
            const value = event.target.value.trim();
            if (value.length < 2){
                resetSuggestions();
                return;
            }
            fetchSuggestions(value);
        });

        searchInput.addEventListener('keydown', (event) => {
            if (suggestionPanel?.classList.contains('hidden')) return;
            const options = suggestionPanel?.querySelectorAll('.suggestion-item') ?? [];
            if (!options.length) return;

            if (['ArrowDown','ArrowUp'].includes(event.key)){
                event.preventDefault();
                if (event.key === 'ArrowDown'){
                    suggestionIndex = (suggestionIndex + 1) % options.length;
                } else {
                    suggestionIndex = (suggestionIndex - 1 + options.length) % options.length;
                }
                options.forEach((btn, idx) => {
                    btn.classList.toggle('bg-blue-50', idx === suggestionIndex);
                    btn.classList.toggle('dark:bg-gray-800', idx === suggestionIndex);
                });
            } else if (event.key === 'Enter' && suggestionIndex >= 0){
                event.preventDefault();
                options[suggestionIndex]?.click();
            } else if (event.key === 'Escape'){
                resetSuggestions();
            }
        });
    }

    suggestionPanel?.addEventListener('mousedown', (event) => {
        const item = event.target.closest('.suggestion-item');
        if (!item) return;
        const index = Number(item.dataset.index);
        const data = latestSuggestionPayload[index] ?? null;
        if (data && searchInput){
            searchInput.value = data.account_no;
            resetSuggestions();
            searchForm?.submit();
        }
    });

    document.addEventListener('click', (event) => {
        if (!suggestionPanel) return;
        if (suggestionPanel.contains(event.target) || event.target === searchInput) return;
        resetSuggestions();
    });

    // ✅ Select All Checkbox
    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
        toggleClearButton();
    });

    // 🎯 Individual Checkbox Change
    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.addEventListener('change', toggleClearButton));

    // 🗑️ Clear Selected Rows (Delete from database)
    clearSelectedBtn.addEventListener('click', async () => {
        const checkedBoxes = document.querySelectorAll('.rowCheckbox:checked');
        if (checkedBoxes.length === 0) return;

        // Confirm deletion
        if (!confirm(`Are you sure you want to delete ${checkedBoxes.length} customer(s)? This action cannot be undone.`)) {
            return;
        }

        // Get customer IDs
        const customerIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        try {
            // Show loading state
            clearSelectedBtn.disabled = true;
            clearSelectedBtn.textContent = 'Deleting...';

            // Send delete request
            const response = await fetch('{{ route("customer.deleteMultiple") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    customer_ids: customerIds
                })
            });

            const result = await response.json();

            if (result.ok) {
                // Remove rows from table
                checkedBoxes.forEach(cb => cb.closest('tr').remove());
                selectAll.checked = false;
                toggleClearButton();
                
                // Show success message
                alert(result.message);
                
                // Reload page to refresh data
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('Failed to delete customers. Please try again.');
        } finally {
            // Reset button state
            clearSelectedBtn.disabled = false;
            clearSelectedBtn.textContent = 'Clear Selected';
        }
    });

    function toggleClearButton() {
        const anyChecked = document.querySelectorAll('.rowCheckbox:checked').length > 0;
        clearSelectedBtn.classList.toggle('hidden', !anyChecked);
    }

    // View Modal
    function openViewModal(id, data) {
        const modal = document.getElementById('viewModal');
        document.getElementById('v_account').innerText = data.account_no || '';
        document.getElementById('v_name').innerText = data.name || '';
        document.getElementById('v_address').innerText = data.address || '';
        document.getElementById('v_contact').innerText = data.contact_no || '';
        document.getElementById('v_status').innerText = data.status || '';
        modal.classList.remove('hidden');
    }
    window.openViewModal = openViewModal;

    // Edit Modal
    function openEditModal(id, data) {
        const modal = document.getElementById('editModal');
        modal.dataset.id = id;
        document.getElementById('e_name').value = data.name || '';
        document.getElementById('e_address').value = data.address || '';
        document.getElementById('e_contact').value = data.contact_no || '';
        modal.classList.remove('hidden');
    }
    window.openEditModal = openEditModal;

    // Close handlers (X buttons, overlay click, Esc)
    document.getElementById('closeView').addEventListener('click', () => document.getElementById('viewModal').classList.add('hidden'));
    document.getElementById('closeEdit').addEventListener('click', () => document.getElementById('editModal').classList.add('hidden'));
    document.getElementById('viewModal').addEventListener('click', (e) => { if (e.target.id === 'viewModal') e.currentTarget.classList.add('hidden'); });
    document.getElementById('editModal').addEventListener('click', (e) => { if (e.target.id === 'editModal') e.currentTarget.classList.add('hidden'); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.getElementById('viewModal').classList.add('hidden');
            document.getElementById('editModal').classList.add('hidden');
        }
    });

    document.getElementById('editForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const modal = document.getElementById('editModal');
        const id = modal.dataset.id;
        const payload = {
            name: document.getElementById('e_name').value.trim(),
            address: document.getElementById('e_address').value.trim(),
            contact_no: document.getElementById('e_contact').value.trim(),
        };
        try {
            const res = await fetch(`{{ route('customer.update', ['id' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER', id), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'Update failed');
            window.location.reload();
        } catch (err) {
            alert(String(err.message || err));
        }
    });

    // ===== Applications badges and quick actions (staff) =====
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    async function fetchLatestApp(customerId){
        const res = await fetch(`/api/applications/latest?customer_id=${encodeURIComponent(customerId)}`, { headers:{ 'Accept':'application/json' } });
        if (!res.ok) return null; const data = await res.json().catch(()=>({}));
        return data?.application || null;
    }
    function renderBadges(host, app){
        if (!host) return;
        if (!app){ host.innerHTML = '<span class="text-gray-400">No application</span>'; return; }
        const palettes = {
            registered: 'bg-gray-100 text-gray-700',
            pending: 'bg-gray-100 text-gray-700',
            approved: 'bg-emerald-100 text-emerald-700',
            assessed: 'bg-amber-100 text-amber-700',
            waiting_payment: 'bg-orange-100 text-orange-700',
            paid: 'bg-indigo-100 text-indigo-700',
            scheduled: 'bg-sky-100 text-sky-700',
            installing: 'bg-purple-100 text-purple-700',
            installed: 'bg-green-100 text-green-700',
            rejected: 'bg-rose-100 text-rose-700'
        };
        const tone = palettes[app.status] || 'bg-gray-100 text-gray-700';
        host.innerHTML = `<span class="px-2 py-0.5 rounded-full ${tone}">${app.status}</span>`;
    }
    function attachAppActions(row, app){
        const rescore = row.querySelector('.app-rescore-btn');
        const approve = row.querySelector('.app-approve-btn');
        const reject = row.querySelector('.app-reject-btn');
        const viewkyc = row.querySelector('.app-viewkyc-link');
        const verifyBtn = row.querySelector('.verify-customer-btn');
        if (!app){
            return;
        }
        const locked = ['approved','assessed','waiting_payment','paid','scheduled','installing','installed'].includes(String(app.status || '').toLowerCase());
        if (rescore){
            rescore.dataset.appId = app.id;
            if (locked){
                rescore.classList.add('opacity-50','cursor-not-allowed');
                rescore.dataset.disabled = 'true';
                rescore.title = 'Actions disabled after approval';
            } else {
                rescore.classList.remove('opacity-50','cursor-not-allowed');
                delete rescore.dataset.disabled;
                rescore.removeAttribute('title');
            }
        }
        if (approve){
            approve.dataset.appId = app.id;
            if (locked){
                approve.classList.add('opacity-50','cursor-not-allowed');
                approve.dataset.disabled = 'true';
                approve.title = 'Already approved';
            } else {
                approve.classList.remove('opacity-50','cursor-not-allowed');
                delete approve.dataset.disabled;
                approve.removeAttribute('title');
            }
        }
        if (reject){
            reject.dataset.appId = app.id;
            if (locked){
                reject.classList.add('opacity-50','cursor-not-allowed');
                reject.dataset.disabled = 'true';
                reject.title = 'Already approved';
            } else {
                reject.classList.remove('opacity-50','cursor-not-allowed');
                delete reject.dataset.disabled;
                reject.removeAttribute('title');
            }
        }
        if (viewkyc){ viewkyc.href = `/applications/${app.id}`; viewkyc.classList.remove('opacity-50','cursor-not-allowed'); viewkyc.removeAttribute('title'); }
        // Verify button visibility stays based on customer status in blade
    }
    async function hydrateRows(){
        const rows = document.querySelectorAll('tr[data-cust-id]');
        for (const row of rows){
            const id = row.getAttribute('data-cust-id');
            const host = row.querySelector('.app-badges');
            // Fallback timeout so 'Loading…' doesn't persist
            let replaced = false;
            const t = setTimeout(()=>{ if (host && /Loading/gi.test(host.textContent||'')) { host.innerHTML = '<span class="text-gray-400">No data</span>'; replaced = true; } }, 2500);
            try{
                const app = await fetchLatestApp(id);
                renderBadges(host, app);
                attachAppActions(row, app);
            } catch(_){ if (host) host.innerHTML = '<span class="text-gray-400">—</span>'; }
            finally { clearTimeout(t); }
        }
    }
    hydrateRows();

    // Re-hydrate when tab gains focus (in case session expired/rehydrated)
    document.addEventListener('visibilitychange', () => { if (!document.hidden) hydrateRows(); });

    // Inline action handlers
    document.addEventListener('click', async (e) => {
        const rescore = e.target?.closest?.('.app-rescore-btn');
        const approve = e.target?.closest?.('.app-approve-btn');
        const reject = e.target?.closest?.('.app-reject-btn');
        const verify = e.target?.closest?.('.verify-customer-btn');
        try{
            if (rescore){
                if (rescore.classList.contains('cursor-not-allowed')) return;
                const id = rescore.getAttribute('data-app-id');
                if (!id) return; const r = await fetch(`/api/applications/${id}/score`, { method:'POST', headers:{ 'Accept':'application/json','X-CSRF-TOKEN': token } });
                if (!r.ok) throw 0; hydrateRows(); return;
            }
            if (approve){
                if (approve.classList.contains('cursor-not-allowed')) return;
                const id = approve.getAttribute('data-app-id');
                if (!id) return; const r = await fetch(`/api/applications/${id}/approve`, { method:'PUT', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token }, body: JSON.stringify({ auto_verify: false }) });
                if (!r.ok) throw 0; hydrateRows(); return;
            }
            if (reject){
                if (reject.classList.contains('cursor-not-allowed')) return;
                const id = reject.getAttribute('data-app-id');
                if (!id) return; const reason = prompt('Reason (optional)');
                const r = await fetch(`/api/applications/${id}/reject`, { method:'PUT', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token }, body: JSON.stringify({ reason }) });
                if (!r.ok) throw 0; hydrateRows(); return;
            }
            if (verify){
                if (verify.classList.contains('cursor-not-allowed')) return;
                const row = verify.closest('tr');
                const cid = row?.getAttribute('data-cust-id');
                if (!cid) return;
                const r = await fetch(`/api/customer/${cid}/verify`, { method:'PUT', headers:{ 'Accept':'application/json','X-CSRF-TOKEN': token } });
                if (!r.ok) throw 0;
                // Update status pill
                const pill = row.querySelector('td:nth-child(7) span');
                if (pill){ pill.textContent = 'Active'; pill.className = pill.className.replace(/bg-.*?text-.*?( |$)/, 'bg-green-100 text-green-700 '); }
                // Hide verify button
                verify.classList.add('opacity-50','cursor-not-allowed');
                verify.title = 'Already Active';
            }
        } catch(_){ alert('Action failed'); }
    });
});
</script>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center" role="dialog" aria-modal="true">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Customer Details</h3>
            <button id="closeView" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300 hover:text-blue-600 px-2 py-1 rounded">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <div class="space-y-2 text-sm text-gray-800 dark:text-gray-200">
            <p><span class="font-medium">Account No:</span> <span id="v_account"></span></p>
            <p><span class="font-medium">Name:</span> <span id="v_name"></span></p>
            <p><span class="font-medium">Address:</span> <span id="v_address"></span></p>
            <p><span class="font-medium">Contact No:</span> <span id="v_contact"></span></p>
            <p><span class="font-medium">Status:</span> <span id="v_status"></span></p>
        </div>
    </div>
    </div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center" data-id="" role="dialog" aria-modal="true">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Edit</h3>
            <button id="closeEdit" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300 hover:text-blue-600 px-2 py-1 rounded">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <form id="editForm" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Name</label>
                <input type="text" id="e_name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Address</label>
                <input type="text" id="e_address" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Contact No.</label>
                <input type="text" id="e_contact" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
            </div>
            <div class="text-right">
                <x-primary-button type="submit">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Save Changes
                </x-primary-button>
            </div>
        </form>
    </div>
    </div>
@endsection

