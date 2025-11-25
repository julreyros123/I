@extends('layouts.app')

@section('title', 'Bill Generation')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-8 font-[Poppins] space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 space-y-4">
        <h1 class="text-base font-semibold text-gray-800 dark:text-gray-100">Generate Bill</h1>

        <div id="alertBox" class="hidden p-3 rounded text-sm"></div>

        <div class="grid md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 dark:text-gray-400">Account No.</label>
                <x-ui.input id="account_no" placeholder="22-123456-1" />
                <p class="text-xs text-gray-500 mt-1">Enter a valid account number.</p>
            </div>
            <div class="flex items-end">
                <x-primary-button id="loadPrev" type="button" class="w-full justify-center">Load Previous</x-primary-button>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Previous Reading</label>
                <x-ui.input id="previous_reading" type="number" min="0" step="0.01" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Current Reading</label>
                <x-ui.input id="current_reading" type="number" min="0" step="0.01" />
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Base Rate (₱/m³)</label>
                <x-ui.input id="base_rate" type="number" min="0" step="0.01" value="25" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Maintenance Charge (₱)</label>
                <x-ui.input id="maintenance_charge" type="number" min="0" step="0.01" value="0" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Overdue Penalty (₱)</label>
                <x-ui.input id="overdue_penalty" type="number" min="0" step="0.01" value="0" />
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Billing From</label>
                <x-ui.input id="date_from" type="date" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Billing To</label>
                <x-ui.input id="date_to" type="date" />
            </div>
            <div class="flex items-end">
                <x-secondary-button id="computeBtn" type="button" class="w-full justify-center">Compute</x-secondary-button>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Consumption (m³)</label>
                <x-ui.input id="consumption" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Subtotal (₱)</label>
                <x-ui.input id="subtotal" readonly />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400">Total Amount (₱)</label>
                <x-ui.input id="total" readonly class="font-semibold" />
            </div>
        </div>

        <div class="flex justify-end">
            <x-primary-button id="saveBillBtn" type="button">Save Bill</x-primary-button>
        </div>
    </div>
</div>

<script>
(function(){
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const $ = id => document.getElementById(id);
  const alertBox = $('alertBox');
  function showAlert(msg, type='success'){
    alertBox.classList.remove('hidden','bg-green-100','text-green-700','bg-red-100','text-red-700');
    if(type==='error'){ alertBox.classList.add('bg-red-100','text-red-700'); } else { alertBox.classList.add('bg-green-100','text-green-700'); }
    alertBox.textContent = String(msg);
    setTimeout(()=>alertBox.classList.add('hidden'), 3000);
  }
  function formatPeso(n){ return '₱'+(Number(n||0).toFixed(2)); }
  function isValidAcct(v){ return /^22-[0-9]{6}-[0-9]$/.test(v||''); }

  $('computeBtn').addEventListener('click', async ()=>{
    const payload = {
      previous_reading: parseFloat($('previous_reading').value||0),
      current_reading: parseFloat($('current_reading').value||0),
      maintenance_charge: parseFloat($('maintenance_charge').value||0),
      base_rate: parseFloat($('base_rate').value||25),
    };
    try{
      const res = await fetch('{{ route('api.billing.compute') }}',{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token}, body: JSON.stringify(payload) });
      if(!res.ok) throw 0;
      const data = await res.json();
      $('consumption').value = (data.consumption_cu_m ?? 0).toFixed(2);
      $('subtotal').value = formatPeso(data.formatted?.subtotal ?? data.subtotal ?? 0);
      $('total').value = formatPeso(data.formatted?.total ?? data.total ?? 0);
      showAlert('Computed');
    }catch(_){ showAlert('Failed to compute.', 'error'); }
  });

  $('saveBillBtn').addEventListener('click', async ()=>{
    const account_no = ($('account_no').value||'').trim();
    if(!isValidAcct(account_no)) return showAlert('Invalid account no. Use 22-123456-1 format.','error');
    const prev = parseFloat($('previous_reading').value||0);
    const curr = parseFloat($('current_reading').value||0);
    if(!(curr>prev)) return showAlert('Current reading must be greater than previous.','error');
    const payload = {
      account_no,
      previous_reading: prev,
      current_reading: curr,
      consumption_cu_m: parseFloat($('consumption').value||0),
      base_rate: parseFloat($('base_rate').value||25),
      maintenance_charge: parseFloat($('maintenance_charge').value||0),
      overdue_penalty: parseFloat($('overdue_penalty').value||0),
      vat: 0,
      total_amount: parseFloat(String(($('total').value||'').replace(/[^0-9.\-]/g,''))||0),
      date_from: $('date_from').value||null,
      date_to: $('date_to').value||null,
    };
    try{
      const btn = $('saveBillBtn'); const old = btn.textContent; btn.disabled = true; btn.textContent = 'Saving...';
      const res = await fetch('{{ route('api.billing.store') }}',{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token, 'Accept':'application/json'}, body: JSON.stringify(payload) });
      const data = await res.json();
      if(!res.ok || !data.ok) throw new Error(data.error||'Failed');
      showAlert('Bill saved successfully!');
      btn.textContent = old; btn.disabled = false;
    }catch(e){
      showAlert(e.message||'Failed to save bill','error');
      const btn = $('saveBillBtn'); btn.disabled = false; btn.textContent = 'Save Bill';
    }
  });

  $('loadPrev').addEventListener('click', async ()=>{
    // Optional: could hit a small API; for now just notify user to fill readings
    showAlert('Enter previous/current readings, then Compute.', 'success');
  });
})();
</script>
@endsection
