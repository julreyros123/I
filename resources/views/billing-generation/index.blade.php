@extends('layouts.app')

@section('title', 'Bill Generation')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8 font-[Poppins] space-y-6">
    <div class="bg-gradient-to-br from-sky-600 via-sky-500 to-indigo-500 text-white rounded-2xl shadow-lg p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <p class="uppercase tracking-[0.35em] text-xs font-semibold text-sky-100">Staff Utility</p>
                <h1 class="text-3xl md:text-4xl font-semibold">Generate Customer Bill</h1>
                <p class="text-sm text-sky-100 max-w-xl">Capture readings, charges, and issue polished invoices in seconds. Calculations update instantly as you type.</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-xl p-4 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-8">
                    <span class="uppercase tracking-widest text-[11px] text-sky-100">Invoice Date</span>
                    <span id="issuedAtPreview" class="font-medium">{{ now()->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center justify-between gap-8">
                    <span class="uppercase tracking-widest text-[11px] text-sky-100">Prepared By</span>
                    <span id="preparedByPreview" class="font-medium">{{ auth()->user()->name ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div id="alertBox" class="hidden rounded-xl border px-4 py-3 text-sm font-medium"></div>

    <div class="grid grid-cols-[minmax(0,2fr)_minmax(0,1fr)] gap-6">
        <div class="bg-white dark:bg-gray-900/70 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-800 overflow-hidden">
            <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Bill Details</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Provide account information and meter readings. Totals adjust automatically.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Fields marked with <span class="text-sky-500">●</span> are required</div>
                </div>
            </div>

            <div class="px-6 py-6 space-y-6">
                <div class="grid md:grid-cols-12 gap-5">
                    <div class="md:col-span-6 space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice Number <span class="text-sky-500">●</span></label>
                        <div class="flex items-center gap-2">
                            <x-ui.input id="invoice_number" class="uppercase" placeholder="INV-20251204-1023" />
                            <button type="button" id="refreshInvoice" class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-600 hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-900/40 dark:text-sky-200">New</button>
                        </div>
                        <p class="text-[11px] text-gray-400">Automatically generated, but you may override before saving.</p>
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice Date</label>
                        <x-ui.input id="issued_at" type="date" value="{{ now()->format('Y-m-d') }}" />
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Person In Charge</label>
                        <x-ui.input id="prepared_by" value="Staff" placeholder="Staff" />
                    </div>
                </div>

                <div class="grid md:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] gap-5">
                    <div class="space-y-6">
                        <section class="space-y-4">
                            <header class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Customer Account</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Lookup the consumer before recording the reading.</p>
                                </div>
                                <x-primary-button id="loadPrev" type="button" class="w-auto px-4 py-2 text-xs">Load Previous</x-primary-button>
                            </header>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Account Number <span class="text-sky-500">●</span></label>
                                <x-ui.input id="account_no" placeholder="22-000187" class="uppercase tracking-wide" />
                                <p class="text-[11px] text-gray-400">Format: 22-XXXXXX (optional trailing -X check digit)</p>
                            </div>
                        </section>

                        <section class="space-y-4">
                            <header>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Meter Readings</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Consumption is computed from the difference.</p>
                            </header>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Previous Reading <span class="text-sky-500">●</span></label>
                                    <x-ui.input id="previous_reading" type="number" min="0" step="0.01" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Current Reading <span class="text-sky-500">●</span></label>
                                    <x-ui.input id="current_reading" type="number" min="0" step="0.01" />
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Base Rate (₱/m³)</label>
                                    <x-ui.input id="base_rate" type="number" min="0" step="0.01" value="25" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Maintenance Charge (₱)</label>
                                    <x-ui.input id="maintenance_charge" type="number" min="0" step="0.01" value="0" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Overdue Penalty (₱)</label>
                                    <x-ui.input id="overdue_penalty" type="number" min="0" step="0.01" value="0" />
                                </div>
                            </div>
                        </section>

                        <section class="space-y-4">
                            <header>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Billing Period</h3>
                            </header>
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">From</label>
                                    <x-ui.input id="date_from" type="date" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">To</label>
                                    <x-ui.input id="date_to" type="date" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Auto Due Date</label>
                                    <div class="h-11 flex items-center px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/60" id="dueDatePreview">—</div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-4">
                        <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-slate-50 to-white p-6 shadow-sm dark:border-gray-800 dark:from-gray-900 dark:to-gray-900/80 flex flex-col h-full lg:sticky lg:top-24 lg:self-start">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Live Summary</h3>
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold text-sky-600 dark:bg-sky-900/40 dark:text-sky-200">Instant</span>
                            </div>
                            <dl class="space-y-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Account Preview</dt>
                                    <dd id="accountPreview" class="font-medium text-gray-800 dark:text-gray-100">—</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Consumption</dt>
                                    <dd id="consumptionDisplay" class="font-semibold text-slate-900 dark:text-slate-100">0.00 m³</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Subtotal</dt>
                                    <dd id="subtotalDisplay" class="font-semibold text-slate-900 dark:text-slate-100">₱0.00</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Maintenance</dt>
                                    <dd id="maintenanceDisplay" class="text-slate-900 dark:text-slate-100">₱0.00</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Penalty</dt>
                                    <dd id="penaltyDisplay" class="text-red-600 dark:text-red-300">₱0.00</dd>
                                </div>
                            </dl>
                            <div class="mt-5 rounded-xl bg-slate-900 text-white dark:bg-slate-800 px-4 py-3">
                                <p class="text-[11px] uppercase tracking-wide text-slate-300">Total Amount Due</p>
                                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-stretch sm:gap-0">
                                    <p id="totalDisplay" class="flex-1 text-2xl font-semibold text-gray-900 bg-white px-5 h-[48px] rounded-lg sm:rounded-r-none inline-flex items-center justify-center min-w-[10rem]">₱0.00</p>
                                    <x-primary-button id="saveBillBtn" type="button" class="px-6 h-[48px] flex items-center justify-center rounded-lg sm:rounded-l-none sm:rounded-r-lg">Save Bill</x-primary-button>
                                </div>
                            </div>
                            <div class="mt-4 text-[11px] text-gray-400">Status defaults to <span class="font-semibold text-sky-500">Pending</span> until the customer settles their balance.</div>
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5 text-sm text-emerald-900 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-900/30 dark:text-emerald-100">
                            <div class="flex items-center gap-2 font-semibold text-emerald-700 dark:text-emerald-200">
                                <x-heroicon-o-sparkles class="w-5 h-5" /> No extra clicks
                            </div>
                            <p class="mt-2 text-sm">As soon as you update a number, the bill preview refreshes. Save when everything looks right.</p>
                        </div>
                    </aside>
                </div>

                <div class="hidden">
                    <input type="hidden" id="consumption" />
                    <input type="hidden" id="subtotal_value" />
                    <input type="hidden" id="total_value" />
                </div>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Review the summary before saving. Saving immediately posts the bill to the Billing Records table as <span class="font-semibold text-sky-500">Pending</span>.</p>
                    <x-primary-button id="saveBillBtn" type="button" class="w-full md:w-auto px-6">Save Bill</x-primary-button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-900/70 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-800 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Guidelines</h2>
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-sky-500"></span><span>Use the <strong>Load Previous</strong> button to quickly recall last period readings (coming soon).</span></li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-sky-500"></span><span>Invoice numbers are unique – regenerate if someone already used the same sequence.</span></li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-sky-500"></span><span>Total amount follows: <code>(Current - Previous) × Base Rate + Maintenance + Penalty</code>.</span></li>
                </ul>
            </div>

            <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 text-gray-200">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-document-text class="w-8 h-8 text-sky-400" />
                    <div class="space-y-2">
                        <h3 class="text-sm font-semibold">Billing Statement Preview</h3>
                        <p class="text-xs text-gray-400">Once saved, the enhanced invoice layout highlights this invoice number, the staff in charge, and a refined usage graph.</p>
                        <a href="{{ route('records.billing') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-sky-300 hover:text-sky-200">View billing records <x-heroicon-o-arrow-up-right class="w-4 h-4" /></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const statusEndpoint = '{{ route('api.billing.status') }}';
  const $ = id => document.getElementById(id);
  const alertBox = $('alertBox');

  const fields = ['previous_reading','current_reading','base_rate','maintenance_charge','overdue_penalty','account_no','date_from','date_to','prepared_by','issued_at'];

  const state = {
    accountPreview: $('accountPreview'),
    issuedAtPreview: $('issuedAtPreview'),
    preparedByPreview: $('preparedByPreview'),
    dueDatePreview: $('dueDatePreview'),
    consumptionInput: $('consumption'),
    subtotalInput: $('subtotal_value'),
    totalInput: $('total_value'),
    consumptionDisplay: $('consumptionDisplay'),
    subtotalDisplay: $('subtotalDisplay'),
    maintenanceDisplay: $('maintenanceDisplay'),
    penaltyDisplay: $('penaltyDisplay'),
    totalDisplay: $('totalDisplay'),
  };

  function showAlert(message, type = 'success') {
    alertBox.classList.remove('hidden');
    alertBox.textContent = message;
    alertBox.className = '';
    alertBox.classList.add('rounded-xl','px-4','py-3','text-sm','font-medium','transition','duration-200');
    if (type === 'error') {
      alertBox.classList.add('bg-red-50','border-red-200','text-red-700','dark:bg-red-900/30','dark:border-red-800','dark:text-red-100');
    } else if (type === 'warning') {
      alertBox.classList.add('bg-amber-50','border-amber-200','text-amber-700','dark:bg-amber-900/30','dark:border-amber-800','dark:text-amber-100');
    } else {
      alertBox.classList.add('bg-emerald-50','border-emerald-200','text-emerald-700','dark:bg-emerald-900/30','dark:border-emerald-800','dark:text-emerald-100');
    }
    setTimeout(() => alertBox.classList.add('hidden'), 3500);
  }

  function formatCurrency(value) {
    return '₱' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function sanitizeNumber(value, fallback = 0) {
    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  }

  function isValidAccount(value) {
    return /^22-[0-9]{6}(-[0-9])?$/i.test((value || '').trim());
  }

  function pad(num) {
    return num.toString().padStart(2, '0');
  }

  function formatInputDate(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
  }

  function generateInvoiceNumber() {
    const now = new Date();
    const base = `INV-${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}`;
    const random = Math.floor(1000 + Math.random() * 9000);
    return `${base}-${random}`;
  }

  function updateDueDate() {
    const dateTo = $('date_to').value;
    if (!dateTo) {
      state.dueDatePreview.textContent = 'End of month';
      return;
    }
    try {
      const due = new Date(dateTo);
      if (Number.isNaN(due.getTime())) throw new Error('Invalid date');
      state.dueDatePreview.textContent = due.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    } catch (_) {
      state.dueDatePreview.textContent = '—';
    }
  }

  function syncPeriodEndWithStart(options = {}) {
    const { force = false } = options;
    const fromEl = $('date_from');
    const toEl = $('date_to');
    if (!fromEl || !toEl) return;

    const rawFrom = (fromEl.value || '').trim();
    if (!rawFrom) return;

    const start = new Date(`${rawFrom}T00:00:00`);
    if (Number.isNaN(start.getTime())) return;

    const rawTo = (toEl.value || '').trim();
    let shouldUpdate = force || !rawTo || toEl.dataset.autoFilled === 'true';

    if (!shouldUpdate && rawTo) {
      const currentTo = new Date(`${rawTo}T00:00:00`);
      if (Number.isNaN(currentTo.getTime()) || currentTo < start) {
        shouldUpdate = true;
      }
    }

    if (!shouldUpdate) return;

    const endOfMonth = new Date(start.getFullYear(), start.getMonth() + 1, 0);
    toEl.value = formatInputDate(endOfMonth);
    toEl.dataset.autoFilled = 'true';

    updateDueDate();
    checkBillingStatus();
  }

  async function checkBillingStatus() {
    const accountField = $('account_no');
    if (!accountField) return;
    const accountNo = (accountField.value || '').trim().toUpperCase();
    if (!isValidAccount(accountNo)) {
      return;
    }

    const dateTo = $('date_to')?.value;
    const issuedAt = $('issued_at')?.value;
    const targetDate = dateTo || issuedAt || null;

    try {
      const qs = new URLSearchParams({ account_no: accountNo });
      if (targetDate) {
        qs.append('target_date', targetDate);
      }
      const res = await fetch(`${statusEndpoint}?${qs.toString()}`, {
        headers: { 'Accept': 'application/json' }
      });
      const data = await res.json();
      if (!res.ok || !data.ok) {
        throw new Error(data.error || 'Unable to verify billing status for this account.');
      }

      if ((data.messages || []).length) {
        const tone = data.has_unpaid_for_cycle ? 'error' : 'warning';
        showAlert(data.messages.join(' '), tone);
      }
    } catch (err) {
      showAlert(err.message || 'Failed to verify billing status.', 'error');
    }
  }

  function calculate() {
    const previous = sanitizeNumber($('previous_reading').value);
    const current = sanitizeNumber($('current_reading').value);
    const baseRate = sanitizeNumber($('base_rate').value, 25);
    const maintenance = sanitizeNumber($('maintenance_charge').value);
    const penalty = sanitizeNumber($('overdue_penalty').value);

    const consumption = Math.max(0, current - previous);
    const subtotal = consumption * baseRate;
    const total = subtotal + maintenance + penalty;

    state.consumptionInput.value = consumption.toFixed(2);
    state.subtotalInput.value = subtotal.toFixed(2);
    state.totalInput.value = total.toFixed(2);

    state.consumptionDisplay.textContent = `${consumption.toFixed(2)} m³`;
    state.subtotalDisplay.textContent = formatCurrency(subtotal);
    state.maintenanceDisplay.textContent = formatCurrency(maintenance);
    state.penaltyDisplay.textContent = formatCurrency(penalty);
    state.totalDisplay.textContent = formatCurrency(total);

    const account = ($('account_no').value || '').trim().toUpperCase();
    state.accountPreview.textContent = account || '—';

    state.preparedByPreview.textContent = ($('prepared_by').value || '—').trim();

    const issuedAt = $('issued_at').value;
    if (issuedAt) {
      const displayDate = new Date(issuedAt);
      state.issuedAtPreview.textContent = displayDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    }

    updateDueDate();
  }

  function hydrateDefaults() {
    const invoiceField = $('invoice_number');
    if (!invoiceField.value) {
      invoiceField.value = generateInvoiceNumber();
    }
    syncPeriodEndWithStart({ force: true });
    calculate();
  }

  $('refreshInvoice').addEventListener('click', () => {
    $('invoice_number').value = generateInvoiceNumber();
    showAlert('Generated a fresh invoice number.');
  });

  const accountField = $('account_no');
  if (accountField) {
    ['blur','change'].forEach(evt => accountField.addEventListener(evt, checkBillingStatus));
  }

  const dateToField = $('date_to');
  if (dateToField) {
    const markManualTo = () => { dateToField.dataset.autoFilled = 'false'; };
    dateToField.addEventListener('input', markManualTo);
    dateToField.addEventListener('change', markManualTo);
    dateToField.addEventListener('change', () => {
      updateDueDate();
      checkBillingStatus();
    });
  }

  const dateFromField = $('date_from');
  if (dateFromField) {
    dateFromField.addEventListener('input', () => {
      dateFromField.dataset.autoFilled = 'false';
    });
    dateFromField.addEventListener('change', () => {
      dateFromField.dataset.autoFilled = 'false';
      syncPeriodEndWithStart({ force: true });
      calculate();
    });
  }

  fields.forEach(id => {
    const el = $(id);
    if (!el) return;

    if (id === 'date_from' || id === 'date_to') {
      el.dataset.autoFilled = el.dataset.autoFilled || 'true';
    }

    el.addEventListener('input', calculate);
    el.addEventListener('change', calculate);
  });

  $('saveBillBtn').addEventListener('click', async () => {
    const accountNo = ($('account_no').value || '').trim().toUpperCase();
    if (!isValidAccount(accountNo)) {
      return showAlert('Invalid account number. Use 22-XXXXXX with an optional -X suffix (e.g., 22-000187 or 22-000187-1).', 'error');
    }

    const previous = sanitizeNumber($('previous_reading').value);
    const current = sanitizeNumber($('current_reading').value);
    if (!(current > previous)) {
      return showAlert('Current reading must be higher than the previous reading.', 'error');
    }

    const invoiceNumber = ($('invoice_number').value || '').trim().toUpperCase();
    const preparedBy = ($('prepared_by').value || '').trim();
    const issuedAt = $('issued_at').value ? new Date($('issued_at').value) : null;

    const payload = {
      invoice_number: invoiceNumber,
      prepared_by: preparedBy,
      issued_at: issuedAt ? issuedAt.toISOString() : null,
      account_no: accountNo,
      previous_reading: previous,
      current_reading: current,
      consumption_cu_m: sanitizeNumber(state.consumptionInput.value),
      base_rate: sanitizeNumber($('base_rate').value, 25),
      maintenance_charge: sanitizeNumber($('maintenance_charge').value),
      overdue_penalty: sanitizeNumber($('overdue_penalty').value),
      vat: 0,
      total_amount: sanitizeNumber(state.totalInput.value),
      date_from: $('date_from').value || null,
      date_to: $('date_to').value || null,
    };

    const btn = $('saveBillBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving…';

    try {
      const response = await fetch('{{ route('api.billing.store') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();
      if (!response.ok || !data.ok) {
        throw new Error(data.error || 'Unable to save bill');
      }

      showAlert(`Bill saved successfully. Invoice ${data.invoice_number || invoiceNumber} is now pending.`, 'success');
      setTimeout(() => {
        window.location.href = '{{ route('records.billing') }}';
      }, 1200);
    } catch (error) {
      showAlert(error.message || 'Failed to save the bill.', 'error');
      btn.disabled = false;
      btn.textContent = originalText;
      return;
    }

    btn.textContent = originalText;
  });

  $('loadPrev').addEventListener('click', () => {
    showAlert('Previous readings lookup will be enabled soon. For now, enter values manually.', 'success');
  });

  hydrateDefaults();
})();
</script>
@endsection
