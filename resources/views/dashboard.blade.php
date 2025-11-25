@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-8 font-[Inter] space-y-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-3">
        <p class="text-xs text-gray-600 dark:text-gray-400">Guide: Enter Account No. in the format 01-123456-7 and click Search to view a quick summary.</p>
        <div class="flex gap-3">
            <x-ui.input id="dashSearchAccount" placeholder="22-123456-1" dense="true" class="flex-1" />
            <x-primary-button type="button" id="dashSearchBtn">Search</x-primary-button>
        </div>
        <div id="dashAlert" class="hidden p-3 rounded text-sm"></div>
    </div>

    <div id="acctSummary" class="hidden bg-white dark:bg-gray-800 rounded-xl shadow p-4">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Account No.</p>
                <p id="sumAcc" class="text-base font-medium text-gray-900 dark:text-white">—</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Customer Name</p>
                <p id="sumName" class="text-base font-medium text-gray-900 dark:text-white">—</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Address</p>
                <p id="sumAddr" class="text-base font-medium text-gray-900 dark:text-white">—</p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 mt-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Today's Progress</h3>
                <button type="button" id="dashProgressAdjust" class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">Adjust</button>
            </div>
            <div id="dashProgressRadial" class="w-full h-72"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Newly Added Customers</h3>
            </div>
            <div class="space-y-3" id="dashNewCustomers">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100">Demo Customer A</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">22-000001-1</div>
                    </div>
                    <span class="text-xs text-gray-400">Today 09:15</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100">Demo Customer B</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">22-000002-1</div>
                    </div>
                    <span class="text-xs text-gray-400">Today 10:02</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100">Demo Customer C</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">22-000003-1</div>
                    </div>
                    <span class="text-xs text-gray-400">Yesterday 16:41</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts" defer></script>
<script>
const dashAlert = document.getElementById('dashAlert');
function dashShow(msg, type='error'){
  dashAlert.classList.remove('hidden','bg-red-100','text-red-700','bg-green-100','text-green-700');
  if(type==='error'){ dashAlert.classList.add('bg-red-100','text-red-700'); } else { dashAlert.classList.add('bg-green-100','text-green-700'); }
  dashAlert.textContent = msg; setTimeout(()=>dashAlert.classList.add('hidden'),3000);
}

const dInput = document.getElementById('dashSearchAccount');
const btn = document.getElementById('dashSearchBtn');
const sumAcc = document.getElementById('sumAcc');
const sumName = document.getElementById('sumName');
const sumAddr = document.getElementById('sumAddr');
const acctSummary = document.getElementById('acctSummary');

function dFormat(v){ const d=(v||'').replace(/\D+/g,'').slice(0,9); const a=d.slice(0,2), b=d.slice(2,8), c=d.slice(8,9); let o=a; if(b) o+='-'+b; else if(a.length===2 && d.length>2) o+='-'; if(c) o+='-'+c; else if(b.length===6 && d.length>8) o+='-'; return o; }
function dValid(v){ return /^22-[0-9]{6}-[0-9]$/.test(v||''); }

// UX helpers
function setLoading(is){
  btn.disabled = is;
  btn.textContent = is ? 'Searching...' : 'Search';
}
function clearSummary(){
  sumAcc.textContent = '—';
  sumName.textContent = '—';
  sumAddr.textContent = '—';
  acctSummary.classList.add('hidden');
}
function showSummary(data, account){
  sumAcc.textContent = account;
  sumName.textContent = (data.customer?.name) || '—';
  sumAddr.textContent = (data.customer?.address) || '—';
  acctSummary.classList.remove('hidden');
}

// Input formatting
dInput.addEventListener('input',()=>{ dInput.value = dFormat(dInput.value); });

// Enter to search
dInput.addEventListener('keydown', (e)=>{ if(e.key === 'Enter'){ e.preventDefault(); btn.click(); }});

// Request cancelation, timeout, and cache
let dashCtrl = null; let dashTimeoutId = null; let lastQuery = ''; const dashCache = new Map();

btn.addEventListener('click', async ()=>{
  const account = (dInput.value||'').trim();
  if(!dValid(account)) { dashShow('Invalid account. Use 22-123456-1 format.'); return; }

  // If repeated query, show cached instantly
  if(account === lastQuery && dashCache.has(account)){
    showSummary(dashCache.get(account), account);
    return;
  }

  // New search: prepare UI and cancel any in-flight
  clearSummary();
  setLoading(true);
  if(dashCtrl){ try { dashCtrl.abort(); } catch(_){} }
  dashCtrl = new AbortController();

  // Auto-timeout after 8s
  if(dashTimeoutId) clearTimeout(dashTimeoutId);
  dashTimeoutId = setTimeout(()=>{ try{ dashCtrl.abort(); }catch(_){ } }, 8000);

  try{
    const res = await fetch(`/api/payment/search-customer`,{
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ account_no: account }),
      signal: dashCtrl.signal
    });
    clearTimeout(dashTimeoutId);
    dashTimeoutId = null;
    if(!res.ok){ setLoading(false); dashShow('Account not found.'); return; }
    const data = await res.json();
    lastQuery = account; dashCache.set(account, data);
    showSummary(data, account);
  }catch(e){
    if(e.name === 'AbortError'){
      // Silently ignore aborted request unless no new one started
    } else {
      dashShow('Failed to search. Please try again.');
    }
  } finally {
    setLoading(false);
  }
});

// Progress (frontend only, with localStorage and graceful fallback)
document.addEventListener('DOMContentLoaded', function(){
  function loadProgress(){
    var t = parseFloat(localStorage.getItem('staff.progress.target'));
    var c = parseFloat(localStorage.getItem('staff.progress.completed'));
    return { target: isFinite(t)&&t>0 ? t : 20, completed: isFinite(c)&&c>=0 ? c : 12 };
  }
  function ensureApex(cb){
    if (window.ApexCharts) { cb(true); return; }
    if (window.__dashApexLoading) { setTimeout(()=>ensureApex(cb), 300); return; }
    window.__dashApexLoading = true;
    var s = document.createElement('script');
    s.src = 'https://unpkg.com/apexcharts@3.44.0/dist/apexcharts.min.js';
    s.onload = function(){ window.__dashApexLoading = false; cb(!!window.ApexCharts); };
    s.onerror = function(){ window.__dashApexLoading = false; cb(false); };
    document.head.appendChild(s);
  }

  function renderRadial(){
    var el = document.getElementById('dashProgressRadial');
    if (!el) return;
    var s = loadProgress();
    var pct = s.target>0 ? (s.completed/s.target)*100 : 0;
    if (window.ApexCharts){
      if (window._dashRadial){ try { window._dashRadial.destroy(); } catch(_){} }
      var dark = document.documentElement.classList.contains('dark');
      // Match Analytics blues: light #2563eb, dark #60a5fa
      var col = dark ? '#60a5fa' : '#2563eb';
      var opt = {
        chart: { type: 'radialBar', height: 220, sparkline: { enabled: true } },
        series: [Math.max(0, Math.min(100, pct))],
        labels: ['Progress'],
        plotOptions: { radialBar: { hollow: { size: '58%' }, track: { background: dark ? '#1f2937':'#f3f4f6' }, dataLabels: { name: { fontSize:'12px', offsetY: 20, color: dark?'#cbd5e1':'#475569' }, value: { fontSize:'26px', fontWeight:700, formatter: v => Math.round(v)+'%' }}}},
        colors: [col]
      };
      try { window._dashRadial = new ApexCharts(el, opt); window._dashRadial.render(); } catch(_){}
    } else {
      var tried = 0;
      ensureApex(function(ok){
        if (ok) { renderRadial(); return; }
        tried++;
        if (tried < 2) { setTimeout(renderRadial, 400); return; }
        var dark = document.documentElement.classList.contains('dark');
        el.innerHTML = '<div style="height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;">\
          <div style="font-size:28px;font-weight:700;color:'+ (dark?'#e5e7eb':'#111827') +'">'+ Math.round(Math.max(0, Math.min(100, pct))) +'%</div>\
          <div style="font-size:12px;color:'+ (dark?'#94a3b8':'#6b7280') +'">Progress (no charts)</div>\
        </div>';
      });
    }
  }
  renderRadial();
  new MutationObserver(renderRadial).observe(document.documentElement, { attributes:true, attributeFilter:['class'] });
  var adj = document.getElementById('dashProgressAdjust');
  if (adj){
    adj.addEventListener('click', function(){
      var s = loadProgress();
      var t = prompt('Daily Target', String(s.target));
      if (t===null) return; var tn = parseFloat(t); if (!isFinite(tn) || tn<=0) return;
      var c = prompt('Completed Today', String(s.completed));
      if (c===null) return; var cn = parseFloat(c); if (!isFinite(cn) || cn<0) return;
      localStorage.setItem('staff.progress.target', String(tn));
      localStorage.setItem('staff.progress.completed', String(cn));
      renderRadial();
    });
  }
});
</script>
@endsection

