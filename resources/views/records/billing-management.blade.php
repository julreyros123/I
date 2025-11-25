@extends('layouts.app')

@section('title', 'Billing Management')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="mb-4">
        <p class="text-gray-600 text-xs">Generate a bill by entering an account number. Customer info loads automatically.</p>
    </div>

    <!-- Staff Bill Generation UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 ring-1 ring-gray-200 space-y-5">
        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Generate Bill</h2>
        <div id="bmAlert" class="hidden p-3 rounded text-sm"></div>

        <div class="grid md:grid-cols-3 gap-4">
            <div class="md:col-span-3">
                <label class="block text-sm text-gray-600 dark:text-gray-400">Account No.</label>
                <x-ui.input id="bm_account_no" placeholder="22-123456-1" />
                <p class="text-xs text-gray-500 mt-1">Enter a valid account number. Customer details will auto-fill.</p>
            </div>
        </div>

        <!-- Auto customer info -->
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Customer Name</label>
                <x-ui.input id="bm_name" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Address</label>
                <x-ui.input id="bm_address" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Meter No.</label>
                <x-ui.input id="bm_meter_no" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Meter Size</label>
                <x-ui.input id="bm_meter_size" readonly />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Previous Reading</label>
                <x-ui.input id="bm_previous_reading" type="number" min="0" step="0.01" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Current Reading</label>
                <x-ui.input id="bm_current_reading" type="number" min="0" step="0.01" />
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Base Rate (₱/m³)</label>
                <x-ui.input id="bm_base_rate" type="number" min="0" step="0.01" value="25" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Maintenance Charge (₱)</label>
                <x-ui.input id="bm_maintenance_charge" type="number" min="0" step="0.01" value="0" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Overdue Penalty (₱)</label>
                <x-ui.input id="bm_overdue_penalty" type="number" min="0" step="0.01" value="0" />
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Billing From</label>
                <x-ui.input id="bm_date_from" type="date" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Billing To</label>
                <x-ui.input id="bm_date_to" type="date" />
            </div>
            <div class="flex items-end">
                <x-secondary-button id="bm_compute" type="button" class="w-full justify-center">Compute</x-secondary-button>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Consumption (m³)</label>
                <x-ui.input id="bm_consumption" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Subtotal (₱)</label>
                <x-ui.input id="bm_subtotal" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Total Amount (₱)</label>
                <x-ui.input id="bm_total" readonly class="font-semibold" />
            </div>
        </div>

        <div class="flex justify-end pt-4 mt-2 border-t border-gray-100 dark:border-gray-700">
            <x-primary-button id="bm_save" type="button">Save Bill</x-primary-button>
        </div>
    </div>

    <!-- Existing cards/table hidden -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 hidden">
        <a href="{{ request()->fullUrlWithQuery(['generated' => '0']) }}" class="block relative rounded-xl shadow-md p-6 overflow-hidden bg-yellow-50 {{ ($generated ?? '') === '0' ? 'ring-2 ring-yellow-400' : '' }}">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-tr from-yellow-100/40 to-transparent"></div>
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 bg-transparent rounded-lg z-0 ring-1 ring-white/5">
                    <x-heroicon-o-clock class="w-6 h-6 text-yellow-500" />
                </div>
                <div class="ml-4 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-600 truncate">Pending Bills</p>
                    <p id="pendingCount" class="text-2xl font-bold text-gray-900 leading-tight mt-1">{{ $stats['pending_generate'] }}</p>
                </div>
            </div>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['generated' => '1']) }}" class="block relative rounded-xl shadow-md p-6 overflow-hidden bg-green-50 {{ ($generated ?? '') === '1' ? 'ring-2 ring-green-400' : '' }}">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-tr from-green-100/40 to-transparent"></div>
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 bg-transparent rounded-lg z-0 ring-1 ring-white/5">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-500" />
                </div>
                <div class="ml-4 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-600 truncate">Total Generated</p>
                    <p id="generatedCount" class="text-2xl font-bold text-gray-900 leading-tight mt-1">{{ $stats['generated'] }}</p>
                </div>
            </div>
        </a>
    </div>

    <div class="bg-white/90 rounded-2xl shadow-md p-6 ring-1 ring-gray-200 backdrop-blur hidden">
        <!-- Search and Filter -->
        <p class="text-xs text-gray-500 mb-2">Tip: Use the status dropdown to filter Outstanding, Overdue, Notice of Disconnection, Disconnected, or Paid accounts.</p>
        <form method="GET" class="mb-6 flex flex-col md:flex-row gap-3">
            <div class="flex-1">
                <x-ui.input name="q" :value="$q ?? ''" placeholder="Search by account no., customer name, or address" />
            </div>
            <div class="md:w-56">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm 
                                            focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white 
                                            text-gray-800">
                    <option value="">All Status</option>
                    <option value="Outstanding Payment" {{ $status === 'Outstanding Payment' ? 'selected' : '' }}>Outstanding Payment</option>
                    <option value="Overdue" {{ $status === 'Overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="Notice of Disconnection" {{ $status === 'Notice of Disconnection' ? 'selected' : '' }}>Notice of Disconnection</option>
                    <option value="Disconnected" {{ $status === 'Disconnected' ? 'selected' : '' }}>Disconnected</option>
                    <option value="Paid" {{ $status === 'Paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            @if(($generated ?? '') !== '')
            <input type="hidden" name="generated" value="{{ $generated }}">
            <a href="{{ request()->fullUrlWithQuery(['generated' => '']) }}" class="inline-flex items-center px-3 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">Clear Filter</a>
            @endif
            <x-secondary-button type="submit">Search</x-secondary-button>
        </form>

        <!-- Bills Table -->
        <div class="overflow-x-auto bg-white rounded-xl table-responsive-wrapper">
            <table class="w-full min-w-full text-sm text-left text-gray-700 rounded-lg overflow-hidden bg-white">
                <thead class="bg-gray-50 text-gray-700 font-semibold">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <input id="selectAll" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span>Customer Name</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200">Account No.</th>
                        <th class="px-6 py-3 border-b border-gray-200">Address</th>
                        <th class="px-6 py-3 border-b border-gray-200">Billing Period</th>
                        <th class="px-6 py-3 border-b border-gray-200">Due Date</th>
                        <th class="px-6 py-3 border-b border-gray-200">Status</th>
                        <th class="px-6 py-3 border-b border-gray-200">Total Bill</th>
                        <th class="px-6 py-3 border-b border-gray-200">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($records as $record)
                    <tr class="group transition hover:bg-gray-50 {{ $record->is_generated ? 'locked-row' : '' }}" data-id="{{ $record->id }}">
                        <td class="px-6 py-3 font-medium">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" class="row-check w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" value="{{ $record->id }}" {{ $record->is_generated ? 'disabled' : '' }}>
                                <span class="relative pl-0.5 {{ $record->is_generated ? 'opacity-60' : '' }}">
                                    <span class="absolute left-[-16px] top-1/2 -translate-y-1/2 w-1 h-6 rounded bg-blue-500/0 group-hover:bg-blue-500/80 transition"></span>
                                    {{ $record->customer->name ?? '—' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-3">{{ $record->account_no }}</td>
                        <td class="px-6 py-3">{{ $record->customer->address ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $record->getBillingPeriod() }}</td>
                        <td class="px-6 py-3">{{ $record->due_date ? $record->due_date->format('Y-m-d') : '—' }}</td>
                        <td class="px-6 py-3">
                            @php
                                $status = $record->bill_status;
                                $cls = match($status){
                                    'Paid' => 'bg-green-100 text-green-700',
                                    'Outstanding Payment' => 'bg-yellow-100 text-yellow-700',
                                    'Overdue' => 'bg-orange-100 text-orange-700',
                                    'Notice of Disconnection' => 'bg-red-100 text-red-700',
                                    'Disconnected' => 'bg-gray-200 text-gray-800',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                                $days = ($record->due_date && now()->greaterThan($record->due_date)) ? $record->due_date->diffInDays(now()) : 0;
                            @endphp
                            <span class="inline-flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $cls }}">{{ $status }}</span>
                                @if($days > 0)
                                <span class="px-2 py-0.5 rounded text-xs bg-red-50 text-red-700 border border-red-200">{{ $days }}d overdue</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-3 font-semibold text-green-600">
                            ₱{{ number_format($record->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center space-x-2">
                                <button onclick="generateBill({{ $record->id }})" title="Generate & Print Bill"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-blue-600 hover:text-white transition {{ $record->is_generated ? 'opacity-60 cursor-not-allowed hover:bg-white hover:text-gray-400' : '' }}" {{ $record->is_generated ? 'disabled' : '' }}>
                                    <x-heroicon-o-printer class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                            No billing records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <x-primary-button id="bulkGenerateBtn" type="button" class="disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <x-heroicon-o-printer class="w-4 h-4" />
                Generate Selected
            </x-primary-button>
            
        </div>
    </div>
</div>

<script>
(function(){
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const $ = id => document.getElementById(id);
  const alertBox = $('bmAlert');
  function showAlert(msg, type='success'){
    alertBox.classList.remove('hidden','bg-green-100','text-green-700','bg-red-100','text-red-700');
    if(type==='error'){ alertBox.classList.add('bg-red-100','text-red-700'); } else { alertBox.classList.add('bg-green-100','text-green-700'); }
    alertBox.textContent = String(msg);
    setTimeout(()=>alertBox.classList.add('hidden'), 3000);
  }
  function formatPeso(n){ return '₱'+(Number(n||0).toFixed(2)); }
  function isValidAcct(v){ return /^[A-Za-z0-9-]{3,}$/.test((v||'').trim()); }
  function normalizeAcct(v){ return (v||'').replace(/[^A-Za-z0-9]/g,''); }

  async function autoLoadAccount(account){
    if(!isValidAcct(account)) return;
    try{
      const res = await fetch('/api/payment/search-customer', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ account_no: account })});
      if(!res.ok) throw 0;
      const data = await res.json();
      $('bm_name').value = data.customer?.name || '';
      $('bm_address').value = data.customer?.address || '';
      $('bm_meter_no').value = data.customer?.meter_no || '';
      $('bm_meter_size').value = (data.customer?.meter_size || '').toString().replace(/\\+/g,'');
      // Use bill.previous_reading if available; fallback to customer.previous_reading
      const latest = data.latest_bill || null;
      $('bm_previous_reading').value = (latest && latest.previous_reading != null)
        ? Number(latest.previous_reading).toFixed(2)
        : (data.customer?.previous_reading != null ? Number(data.customer.previous_reading).toFixed(2) : '');
      if (latest && latest.current_reading != null) {
        $('bm_current_reading').placeholder = Number(latest.current_reading).toFixed(2);
      }
    }catch(_){ showAlert('Failed to load customer details','error'); }
  }

  // Debounced auto-load on account input
  let debounce;
  $('bm_account_no').addEventListener('input', (e)=>{
    const v = e.target.value || '';
    clearTimeout(debounce);
    debounce = setTimeout(()=>{ if(isValidAcct(v)) autoLoadAccount(v); }, 350);
  });
  // Removed Load Previous button: auto-load occurs on input

  async function compute(){
    const payload = {
      previous_reading: parseFloat($('bm_previous_reading').value||0),
      current_reading: parseFloat($('bm_current_reading').value||0),
      maintenance_charge: parseFloat($('bm_maintenance_charge').value||0),
      base_rate: parseFloat($('bm_base_rate').value||25),
    };
    try{
      const res = await fetch('{{ route('api.billing.compute') }}',{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token }, body: JSON.stringify(payload) });
      const data = await res.json();
      $('bm_consumption').value = (data.consumption_cu_m ?? 0).toFixed(2);
      $('bm_subtotal').value = formatPeso(data.formatted?.subtotal ?? data.subtotal ?? 0);
      $('bm_total').value = formatPeso(data.formatted?.total ?? data.total ?? 0);
    }catch(_){ showAlert('Failed to compute','error'); }
  }
  ['bm_previous_reading','bm_current_reading','bm_maintenance_charge','bm_base_rate'].forEach(id=>{ const el=$(id); if(el) el.addEventListener('input', compute); });
  $('bm_compute').addEventListener('click', compute);

  $('bm_save').addEventListener('click', async ()=>{
    const account_no = ($('bm_account_no').value||'').trim();
    if(!isValidAcct(account_no)) return showAlert('Invalid account number.','error');
    const prev = parseFloat($('bm_previous_reading').value||0);
    const curr = parseFloat($('bm_current_reading').value||0);
    if(!(curr>prev)) return showAlert('Current reading must be greater than previous.','error');
    const payload = {
      account_no,
      previous_reading: prev,
      current_reading: curr,
      consumption_cu_m: parseFloat($('bm_consumption').value||0),
      base_rate: parseFloat($('bm_base_rate').value||25),
      maintenance_charge: parseFloat($('bm_maintenance_charge').value||0),
      overdue_penalty: parseFloat($('bm_overdue_penalty').value||0),
      vat: 0,
      total_amount: parseFloat(String(($('bm_total').value||'').replace(/[^0-9.\-]/g,''))||0),
      date_from: $('bm_date_from').value||null,
      date_to: $('bm_date_to').value||null,
    };
    try{
      const btn = $('bm_save'); const before = btn.textContent; btn.disabled = true; btn.textContent = 'Saving...';
      const res = await fetch('{{ route('api.billing.store') }}',{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token, 'Accept':'application/json'}, body: JSON.stringify(payload) });
      const data = await res.json();
      if(!res.ok || !data.ok) throw new Error(data.error||'Failed');
      showAlert('Bill saved successfully!');
      btn.textContent = before; btn.disabled = false;
    }catch(e){ showAlert(e.message||'Failed to save bill','error'); const btn=$('bm_save'); btn.disabled=false; btn.textContent='Save Bill'; }
  });
})();
</script>
<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black/40 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 ring-1 ring-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Bill Status</h3>
        <form id="statusForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="billStatus" name="bill_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                                               focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white 
                                                               text-gray-800">
                    <option value="Outstanding Payment">Outstanding Payment</option>
                    <option value="Overdue">Overdue</option>
                    <option value="Notice of Disconnection">Notice of Disconnection</option>
                    <option value="Disconnected">Disconnected</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="billNotes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                                                      focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white 
                                                                      text-gray-800" placeholder="Optional notes..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <x-secondary-button type="button" onclick="closeStatusModal()">Cancel</x-secondary-button>
                <x-primary-button type="submit">Update Status</x-primary-button>
            </div>
        </form>
    </div>
</div>

<style>
  .locked-row { filter: blur(0.3px) grayscale(20%); opacity: 0.6; pointer-events: none; }
  /* Inherit global background from layout; keep cards white individually */
</style>
<script>
let currentBillId = null;

function generateBill(id) {
    // Reuse bulk-generate flow for a single item to ensure locking
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('records.billing.bulk-generate') }}';
    form.target = '_blank';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrf);

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'ids[]';
    input.value = id;
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    form.remove();
}

function updateStatus(id, currentStatus) {
    currentBillId = id;
    document.getElementById('billStatus').value = currentStatus;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    currentBillId = null;
}

function printBill(id) {
    window.open(`/records/billing/${id}/print`, '_blank');
}

document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`/records/billing/${currentBillId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                bill_status: formData.get('bill_status'),
                notes: formData.get('notes')
            })
        });

// Lightweight, no-API counter helpers
function getCount(elId){ const el = document.getElementById(elId); return el ? (parseInt(el.textContent.replace(/[^0-9]/g,'')||'0',10)||0) : 0; }
function setCount(elId, val){ const el = document.getElementById(elId); if (el) el.textContent = String(val); }
function bumpCounts(deltaPending, deltaGenerated){
  setCount('pendingCount', Math.max(0, getCount('pendingCount') + deltaPending));
  setCount('generatedCount', Math.max(0, getCount('generatedCount') + deltaGenerated));
}

// Bulk selection and generation
const selectAll = document.getElementById('selectAll');
const rowChecks = () => Array.from(document.querySelectorAll('.row-check'));
const bulkBtn = document.getElementById('bulkGenerateBtn');

function refreshBulkState() {
  const selectable = rowChecks().filter(ch => !ch.disabled);
  const anyChecked = selectable.some(ch => ch.checked);
  bulkBtn.disabled = !anyChecked;
  if (selectable.length) {
    selectAll.checked = selectable.every(ch => ch.checked);
    selectAll.indeterminate = !selectAll.checked && anyChecked;
  } else {
    selectAll.checked = false;
    selectAll.indeterminate = false;
  }
}

if (selectAll) {
  selectAll.addEventListener('change', () => {
    rowChecks().forEach(ch => { if (!ch.disabled) ch.checked = selectAll.checked; });
    refreshBulkState();
  });
}

rowChecks().forEach(ch => ch.addEventListener('change', refreshBulkState));
refreshBulkState();

bulkBtn.addEventListener('click', () => {
  const ids = rowChecks().filter(ch => ch.checked && !ch.disabled).map(ch => ch.value);
  if (!ids.length) return;
  // Create and submit a form to open batch print in new tab
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ route('records.billing.bulk-generate') }}';
  form.target = '_blank';

  const csrf = document.createElement('input');
  csrf.type = 'hidden';
  csrf.name = '_token';
  csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  form.appendChild(csrf);

  ids.forEach(id => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'ids[]';
    input.value = id;
    form.appendChild(input);
  });

  document.body.appendChild(form);
  form.submit();
  form.remove();
  // Optimistic UI: blur selected rows and adjust counters locally
  ids.forEach(id => {
    const tr = document.querySelector(`tr[data-id="${id}"]`);
    if (tr) tr.classList.add('locked-row');
  });
  bumpCounts(-ids.length, +ids.length);
});
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error updating status: ' + result.message);
        }
    } catch (error) {
        alert('Error updating status: ' + error.message);
    }
});
</script>
@endsection
