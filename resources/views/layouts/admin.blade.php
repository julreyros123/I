<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="themeSettings()" 
      x-init="loadTheme()" 
      class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MAWASA Admin Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        html, body { min-height: 100vh; }
        body, input, textarea, button { font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        button, input[type="button"], input[type="submit"], input[type="reset"] { font-size: 0.85rem; }
        body { background-color: #d1d5db; /* gray-300, slightly darker */ }
        /* KPI icon sizing utility (customizable via --kpi-icon-size) */
        .kpi-icon { width: var(--kpi-icon-size, 2.75rem); height: var(--kpi-icon-size, 2.75rem); }
        .kpi-icon > svg { width: 60%; height: 60%; }
        .dark html, .dark body {
            background: radial-gradient(1200px 800px at 20% 10%, #0b1f3a 0%, transparent 60%),
                        radial-gradient(1000px 700px at 85% 20%, #0b213f 0%, transparent 60%),
                        linear-gradient(135deg, #0b1220 0%, #0b1f3a 45%, #061a2b 100%) !important;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
        }
    </style>
</head>
<body class="text-gray-900 dark:text-gray-100 transition-colors duration-300">

    {{-- Admin Navbar --}}
    <x-admin.navbar />

    {{-- Admin Sidebar --}}
    <x-admin.sidebar />

    {{-- Main Content --}}
    <main class="md:ml-64 pt-16 min-h-screen transition-all duration-300">
        <div class="w-full mx-auto px-6 py-10">
            @yield('content')
        </div>
    </main>

    @stack('modals')

    <div id="toast-container" class="fixed top-6 right-6 z-50 space-y-2 pointer-events-none"></div>

    <div class="fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden" 
         id="overlay" 
         style="display: none;" 
         @click="document.getElementById('sidebar').style.transform = 'translateX(-100%)'; this.style.display = 'none';">
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('overlay');
        var toggle = document.getElementById('toggle-sidebar');
        function openSidebar(){ if(!sidebar) return; sidebar.style.transform = 'translateX(0)'; if(overlay){ var sw = sidebar.getBoundingClientRect ? sidebar.getBoundingClientRect().width : 256; overlay.style.left = sw + 'px'; overlay.style.display = 'block'; } }
        function closeSidebar(){ if(!sidebar) return; sidebar.style.transform = 'translateX(-100%)'; if(overlay){ overlay.style.display = 'none'; overlay.style.left = '0'; } }
        if (toggle) toggle.addEventListener('click', openSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && window.innerWidth < 768) closeSidebar(); });
        window.addEventListener('resize', function(){ if (!sidebar) return; if (window.innerWidth >= 768){ sidebar.style.transform = ''; if (overlay){ overlay.style.display = 'none'; overlay.style.left = '0'; } } else { closeSidebar(); } });
        if (window.innerWidth < 768){ closeSidebar(); } else { sidebar.style.transform = ''; if (overlay){ overlay.style.display = 'none'; overlay.style.left = '0'; } }
        document.querySelectorAll('table').forEach(function(tbl){ if (tbl.closest('.table-responsive-wrapper')) return; var wrapper = document.createElement('div'); wrapper.className = 'table-responsive-wrapper overflow-x-auto -mx-4 md:mx-0 relative'; tbl.classList.add('min-w-max'); tbl.parentNode.insertBefore(wrapper, tbl); wrapper.appendChild(tbl); var hint = document.createElement('div'); hint.className = 'scroll-hint pointer-events-none absolute right-2 top-2 text-[11px] bg-black/50 text-white px-2 py-1 rounded md:hidden'; hint.textContent = 'Scroll →'; wrapper.appendChild(hint); function updateHint(){ var canScroll = wrapper.scrollWidth > wrapper.clientWidth; var atEnd = (wrapper.scrollLeft + wrapper.clientWidth) >= (wrapper.scrollWidth - 1); hint.style.display = (canScroll && !atEnd) ? 'block' : 'none'; } wrapper.addEventListener('scroll', updateHint); window.addEventListener('resize', updateHint); updateHint(); });
    });
    </script>

    <script>
    (function(){
        function openModal(modal){
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            const autofocus = modal.querySelector('[data-modal-autofocus] input, [data-modal-autofocus] select, [data-modal-autofocus] textarea');
            if (autofocus) {
                setTimeout(() => autofocus.focus(), 50);
            }
        }

        function closeModal(modal){
            if (!modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        }

        document.addEventListener('click', function(event){
            const trigger = event.target.closest('[data-open-modal]');
            if (trigger) {
                const targetId = trigger.getAttribute('data-open-modal');
                const modal = document.getElementById(targetId);
                openModal(modal);
                event.preventDefault();
                return;
            }

            const dismiss = event.target.closest('[data-modal-dismiss]');
            if (dismiss) {
                const modal = dismiss.closest('[data-modal]');
                closeModal(modal);
                event.preventDefault();
                return;
            }
        });

        document.addEventListener('click', function(event){
            const backdrop = event.target.closest('[data-modal-backdrop]');
            if (backdrop) {
                const modal = backdrop.closest('[data-modal]');
                closeModal(modal);
            }
        });

        document.addEventListener('keydown', function(event){
            if (event.key !== 'Escape') return;
            document.querySelectorAll('[data-modal]:not(.hidden)').forEach(closeModal);
        });

        document.addEventListener('change', function(event){
            const select = event.target;
            if (!(select instanceof HTMLSelectElement)) return;

            if (select.hasAttribute('data-app-select')) {
                const accountTarget = select.dataset.accountTarget;
                const applicationTarget = select.dataset.applicationTarget;
                const fallbackReset = select.dataset.fallbackReset;

                const accountInput = accountTarget ? document.querySelector(accountTarget) : null;
                const applicationInput = applicationTarget ? document.querySelector(applicationTarget) : null;
                const selectedOption = select.selectedOptions[0];

                if (accountInput) {
                    accountInput.value = select.value || '';
                }
                if (applicationInput) {
                    applicationInput.value = selectedOption ? selectedOption.getAttribute('data-application-id') || '' : '';
                }
                if (fallbackReset) {
                    const fallbackSelect = document.querySelector(fallbackReset);
                    if (fallbackSelect instanceof HTMLSelectElement) {
                        fallbackSelect.selectedIndex = 0;
                    }
                }
            } else if (select.hasAttribute('data-fallback-select')) {
                const accountTarget = select.dataset.accountTarget;
                const applicationTarget = select.dataset.applicationTarget;
                const scheduledReset = select.dataset.scheduledReset;

                const accountInput = accountTarget ? document.querySelector(accountTarget) : null;
                const applicationInput = applicationTarget ? document.querySelector(applicationTarget) : null;

                if (accountInput) {
                    accountInput.value = select.value || '';
                }
                if (applicationInput) {
                    applicationInput.value = '';
                }
                if (scheduledReset) {
                    const scheduledSelect = document.querySelector(scheduledReset);
                    if (scheduledSelect instanceof HTMLSelectElement) {
                        scheduledSelect.selectedIndex = 0;
                    }
                }
            }
        });
    })();
    </script>

    <script>
    (function(){
      const container = document.getElementById('toast-container');
      function iconFor(type){ switch(type){ case 'success': return '✅'; case 'error': return '❌'; case 'warning': return '⚠️'; default: return 'ℹ️'; } }
      function colorClasses(type){ switch(type){ case 'success': return ['bg-green-600','bg-green-700','bg-green-500']; case 'error': return ['bg-red-600','bg-red-700','bg-red-500']; case 'warning': return ['bg-yellow-600','bg-yellow-700','bg-yellow-500']; default: return ['bg-blue-600','bg-blue-700','bg-blue-500']; } }
      function showToast(type, message, opts){ const duration = Math.max(1500, Math.min(10000, (opts?.duration||3000))); if (!container) return; const [bg, hover, bar] = colorClasses(type); const wrap = document.createElement('div'); wrap.className = 'pointer-events-auto w-[320px] sm:w-[360px] shadow-lg rounded-lg overflow-hidden ring-1 ring-black/5 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 translate-y-0 opacity-100 transition duration-200'; wrap.innerHTML = `
          <div class="flex items-start gap-3 px-4 py-3">
            <div class="text-lg leading-none">${iconFor(type)}</div>
            <div class="flex-1 text-sm">${message}</div>
            <button class="ml-2 inline-flex items-center justify-center w-7 h-7 rounded hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Close">✖</button>
          </div>
          <div class="h-1 ${bar}"></div>
        `; const closeBtn = wrap.querySelector('button'); const barEl = wrap.lastElementChild; let start = performance.now(); let paused = false; let elapsed = 0; let rafId; function tick(now){ if (paused) { rafId = requestAnimationFrame(tick); return; } const t = Math.min(1, (now - start + elapsed) / duration); barEl.style.width = ((1 - t) * 100) + '%'; barEl.style.transition = 'width 0s'; if (t >= 1) { dismiss(); } else { rafId = requestAnimationFrame(tick); } } function dismiss(){ cancelAnimationFrame(rafId); wrap.style.opacity = '0'; wrap.style.transform = 'translateY(-4px)'; setTimeout(()=>wrap.remove(), 180); } wrap.addEventListener('mouseenter', ()=>{ paused = true; elapsed += performance.now() - start; }); wrap.addEventListener('mouseleave', ()=>{ paused = false; start = performance.now(); }); let touchStartX = null; wrap.addEventListener('touchstart', (e)=>{ touchStartX = e.touches[0].clientX; }, {passive:true}); wrap.addEventListener('touchmove', (e)=>{ if (touchStartX == null) return; const dx = e.touches[0].clientX - touchStartX; wrap.style.transform = `translateX(${dx}px)`; wrap.style.opacity = String(Math.max(0.3, 1 - Math.abs(dx)/240)); }, {passive:true}); wrap.addEventListener('touchend', ()=>{ const current = wrap.style.transform || ''; const m = current.match(/translateX\(([-0-9.]+)px\)/); const dx = m ? Math.abs(parseFloat(m[1])) : 0; if (dx > 120) dismiss(); else { wrap.style.transform = ''; wrap.style.opacity = '1'; } }); container.prepend(wrap); barEl.style.width = '100%'; rafId = requestAnimationFrame(tick); }
      window.showToast = showToast;
      const nativeAlert = window.alert; window.alert = function(msg){ try { showToast('info', String(msg)); } catch(_) { nativeAlert(msg); } };
    })();
    </script>

    <script>
    if (
        localStorage.getItem('theme') === 'dark' ||
        (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    </script>

    <style>[x-cloak] { display: none !important; }</style>

    <script>
    function themeSettings() {
        return {
            theme: 'light',
            loadTheme() { this.theme = localStorage.getItem('theme') || 'light'; this.applyTheme(); },
            toggleTheme() { this.theme = this.theme === 'dark' ? 'light' : 'dark'; localStorage.setItem('theme', this.theme); this.applyTheme(); },
            applyTheme() { if (this.theme === 'dark') { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); } }
        }
    }
    </script>
</body>
@stack('scripts')
<!-- Simple-DataTables (CDN) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" defer></script>
<script>
window.initDatatables = function(){ /* disabled globally */ };
document.addEventListener('DOMContentLoaded', function(){ /* no-op */ });
</script>
<script>
// Auto-initialize on dynamically added tables
(function(){ /* disabled */ })();
</script>
<style>
/* Simple-DataTables dark mode overrides */
.dark .dataTable-wrapper .dataTable-top,
.dark .dataTable-wrapper .dataTable-bottom {
  background-color: transparent !important;
  color: #e5e7eb !important; /* slate-200 */
}
.dark .dataTable-wrapper .dataTable-input,
.dark .dataTable-wrapper .dataTable-selector {
  background-color: #0f172a !important; /* slate-900 */
  color: #e2e8f0 !important; /* slate-200 */
  border-color: #334155 !important; /* slate-700 */
  box-shadow: none !important;
}
.dark .dataTable-top .dataTable-search input.dataTable-input,
.dark .dataTable-top .dataTable-dropdown select.dataTable-selector {
  background-color: #0f172a !important;
  color: #e2e8f0 !important;
  border-color: #334155 !important;
}
.dark .dataTable-top .dataTable-dropdown select.dataTable-selector option {
  background-color: #0b1220 !important; /* slightly darker for options */
  color: #e2e8f0 !important;
}
.dark .dataTable-wrapper .dataTable-input::placeholder { color: #94a3b8 !important; /* slate-400 */ }
.dark .dataTable-wrapper .dataTable-dropdown label { color: #cbd5e1 !important; /* slate-300 */ }
.dark .dataTable-wrapper .dataTable-input:focus,
.dark .dataTable-wrapper .dataTable-selector:focus {
  outline: 2px solid #3b82f6 !important; /* blue-500 */
  border-color: #3b82f6 !important;
}
.dark .dataTable-pagination li a {
  background-color: #111827 !important; /* gray-900 */
  color: #e5e7eb !important;
  border-color: #374151 !important;
}
.dark .dataTable-pagination li.active a {
  background-color: #2563eb !important; /* blue-600 */
  color: #ffffff !important;
  border-color: #2563eb !important;
}
.dark .dataTable-pagination li a:hover { background-color: #1f2937 !important; }
.dark table.dataTable-table { background-color: transparent !important; }
.dark table.dataTable-table thead th {
  background-color: rgba(55,65,81,0.6) !important; /* gray-700 */
  color: #d1d5db !important;
}
.dark table.dataTable-table,
.dark table.dataTable-table th,
.dark table.dataTable-table td {
  border-color: #374151 !important; /* gray-700 */
}
.dark table.dataTable-table tbody tr:hover { background-color: rgba(31,41,55,0.5) !important; }

/* Softer zebra rows (applies to all tables unless .no-zebra is added) */
table:not(.no-zebra) tbody tr:nth-child(odd) { background-color: #f8fafc; /* slate-50 */ }
table:not(.no-zebra) tbody tr:nth-child(even) { background-color: #f1f5f9; /* slate-100 */ }
table:not(.no-zebra) tbody tr:hover { background-color: #e2e8f0; /* slate-200 */ }
.dark table:not(.no-zebra) tbody tr:nth-child(odd) { background-color: rgba(59,130,246,0.12); /* blue-500 @ 12% */ }
.dark table:not(.no-zebra) tbody tr:nth-child(even) { background-color: rgba(59,130,246,0.20); /* blue-500 @ 20% */ }
.dark table:not(.no-zebra) tbody tr:hover { background-color: rgba(59,130,246,0.28); /* blue-500 @ 28% */ }

/* Blue header and top accent for all tables (opt-out with .no-accent) */
table:not(.no-accent){ border-top: 4px solid #2563eb; /* blue-600 */ border-collapse: separate; border-spacing: 0; }
table:not(.no-accent) thead th {
  background-color: #1d4ed8; /* blue-700 */
  color: #ffffff !important;
}
.dark table:not(.no-accent){ border-top-color: #3b82f6; /* blue-500 */ }
.dark table:not(.no-accent) thead th {
  background-color: #1e3a8a; /* blue-800 */
  color: #e5e7eb !important; /* slate-200 */
}
</style>
<style>
/* Global light theme surface softening (keeps dark mode intact) */
:root { --soft-surface: #f3f4f6; --soft-surface-2: #e5e7eb; --soft-hover: #e5e7eb; }
.bg-white:not(input):not(textarea):not(select) {
  background-color: var(--soft-surface) !important;
  box-shadow: 0 12px 30px rgba(15,23,42,0.12);
  border: 1px solid #e5e7eb; /* gray-200 */
}
.bg-gray-50:not(input):not(textarea):not(select) {
  background-color: var(--soft-surface) !important;
  box-shadow: 0 10px 24px rgba(15,23,42,0.10);
  border: 1px solid #e5e7eb;
}
.bg-gray-100:not(input):not(textarea):not(select) {
  background-color: var(--soft-surface-2) !important;
  box-shadow: 0 8px 20px rgba(15,23,42,0.08);
  border: 1px solid #e5e7eb;
}
.hover\:bg-gray-100:hover { background-color: var(--soft-hover) !important; }
/* Keep dark mode surfaces without extra heavy shadows */
.dark .bg-white { background-color: #0f172a !important; box-shadow: 0 10px 24px rgba(15,23,42,0.65); }
/* Do not apply panel shadows to form controls */
input.bg-white, textarea.bg-white, select.bg-white,
input.bg-gray-50, textarea.bg-gray-50, select.bg-gray-50,
input.bg-gray-100, textarea.bg-gray-100, select.bg-gray-100 {
  box-shadow: none !important;
}
</style>
</html>
