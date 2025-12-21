<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="themeSettings()" 
      x-init="loadTheme()" 
      class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MAWASA Staff Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Use Inter as main font and keep font-family consistent */
        body, input, textarea, button {
            font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        /* System-wide button text sizing */
        button,
        input[type="button"],
        input[type="submit"],
        input[type="reset"] {
            font-size: 0.85rem; /* slightly smaller than default */
        }
        /* Global background */
        body,
        .dark body {
            background-color: #d1d5db; /* gray-300, slightly darker */
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
</head>
<body class="text-gray-900 dark:text-gray-100 transition-colors duration-300">

    {{-- Navbar --}}
    @include('components.navbar')

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Main Content --}}
    <main class="md:ml-64 pt-16 min-h-screen transition-all duration-300">
        <div class="w-full mx-auto px-6 py-6 lg:py-8">
            @yield('content')
        </div>
    </main>

    {{-- Global toast container so all pages can use showToast() --}}
    <div id="toast-container" class="fixed top-6 right-6 z-50 space-y-2 pointer-events-none"></div>

    {{-- Mobile Overlay for Sidebar --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden" 
         id="overlay" 
         style="display: none;" 
         @click="document.getElementById('sidebar').style.transform = 'translateX(-100%)'; this.style.display = 'none';">
    </div>

    <script>
    // Minimal, non-intrusive sidebar controls (matches current inline behavior)
    document.addEventListener('DOMContentLoaded', function(){
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('overlay');
        var toggle = document.getElementById('toggle-sidebar');

        function openSidebar(){
            if(!sidebar) return;
            sidebar.style.transform = 'translateX(0)';
            if(overlay){
                // place overlay to the right of the sidebar so it won't cover it
                var sw = sidebar.getBoundingClientRect ? sidebar.getBoundingClientRect().width : 256;
                overlay.style.left = sw + 'px';
                overlay.style.display = 'block';
            }
        }
        function closeSidebar(){
            if(!sidebar) return;
            sidebar.style.transform = 'translateX(-100%)';
            if(overlay){ overlay.style.display = 'none'; overlay.style.left = '0'; }
        }

        if (toggle) toggle.addEventListener('click', openSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        // ESC closes sidebar on mobile only
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' && window.innerWidth < 768) closeSidebar();
        });

        // Reset state on resize (desktop keeps sidebar visible, overlay hidden)
        window.addEventListener('resize', function(){
            if (!sidebar) return;
            if (window.innerWidth >= 768){
                sidebar.style.transform = '';
                if (overlay){ overlay.style.display = 'none'; overlay.style.left = '0'; }
            } else {
                // On entering mobile, ensure it's closed by default
                closeSidebar();
            }
        });

        // Initial state: closed on mobile, visible on desktop
        if (window.innerWidth < 768){
            closeSidebar();
        } else {
            sidebar.style.transform = '';
            if (overlay){ overlay.style.display = 'none'; overlay.style.left = '0'; }
        }

        // Enhance tables: wrap for horizontal scroll and show a scroll hint on mobile
        document.querySelectorAll('table').forEach(function(tbl){
            if (tbl.closest('.table-responsive-wrapper')) return;
            var wrapper = document.createElement('div');
            wrapper.className = 'table-responsive-wrapper overflow-x-auto w-full relative';
            // Ensure table stretches to available width while keeping scroll for overflow
            tbl.classList.add('min-w-full','w-full');
            tbl.parentNode.insertBefore(wrapper, tbl);
            wrapper.appendChild(tbl);

            // Add a small scroll hint for mobile
            var hint = document.createElement('div');
            hint.className = 'scroll-hint pointer-events-none absolute right-2 top-2 text-[11px] bg-black/50 text-white px-2 py-1 rounded md:hidden';
            hint.textContent = 'Scroll →';
            wrapper.appendChild(hint);
            function updateHint(){
                var canScroll = wrapper.scrollWidth > wrapper.clientWidth;
                var atEnd = (wrapper.scrollLeft + wrapper.clientWidth) >= (wrapper.scrollWidth - 1);
                hint.style.display = (canScroll && !atEnd) ? 'block' : 'none';
            }
            wrapper.addEventListener('scroll', updateHint);
            window.addEventListener('resize', updateHint);
            updateHint();
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
      function normalizeText(t){
        return String(t || '').replace(/\s+/g, ' ').trim().toLowerCase();
      }
      function escapeHtml(str){
        return String(str || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
      }
      function escapeRegExp(str){
        return String(str || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      }
      function highlightHtml(text, query){
        var t = String(text || '');
        var q = String(query || '').trim();
        if (!q) return escapeHtml(t);
        try {
          var re = new RegExp('(' + escapeRegExp(q) + ')', 'ig');
          return escapeHtml(t).replace(re, '<mark class="bg-yellow-200/70 dark:bg-yellow-400/30 rounded px-0.5">$1</mark>');
        } catch (e) {
          return escapeHtml(t);
        }
      }
      function parseMaybeNumber(s){
        var raw = String(s || '').trim();
        if (!raw) return null;
        var cleaned = raw
          .replace(/[,]/g, '')
          .replace(/\u20B1/g, '')
          .replace(/%/g, '')
          .replace(/[^0-9.\-]/g, '');
        if (!cleaned || cleaned === '-' || cleaned === '.' || cleaned === '-.' ) return null;
        var n = Number(cleaned);
        return isFinite(n) ? n : null;
      }
      function parseMaybeDate(s){
        var raw = String(s || '').trim();
        if (!raw) return null;
        var d = new Date(raw);
        if (isFinite(d)) return d.getTime();
        return null;
      }
      function getCellText(row, idx){
        var cell = row && row.children ? row.children[idx] : null;
        return cell ? cell.textContent : '';
      }
      function tableHasBodyRows(tbl){
        var body = tbl.tBodies && tbl.tBodies[0];
        return !!(body && body.rows && body.rows.length);
      }

      function hasExistingSearchUi(wrapper){
        if (!wrapper) return false;
        var container = wrapper.parentElement;
        if (!container || !container.querySelector) return false;
        return !!container.querySelector('form input[type="search"], form input[name="q"], form input[name="search"], form input[type="text"][name="q"], form input[type="text"][name="search"]');
      }

      function enhanceTable(tbl){
        if (!tbl || tbl.dataset.tableEnhanced === 'true') return;
        if (tbl.classList.contains('no-filter') || tbl.classList.contains('no-sort') || tbl.classList.contains('no-filter-sort')) return;
        if (!tbl.tHead || !tbl.tHead.rows || !tbl.tHead.rows.length) return;
        if (!tableHasBodyRows(tbl)) return;

        tbl.dataset.tableEnhanced = 'true';

        var wrapper = tbl.closest('.table-responsive-wrapper') || tbl.parentElement;
        if (!wrapper) return;

        var skipSearchUi = hasExistingSearchUi(wrapper);

        var controlBar = document.createElement('div');
        controlBar.className = 'table-filterbar flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-3 px-2 sm:px-4';

        var input = null;
        if (!skipSearchUi){
          var left = document.createElement('div');
          left.className = 'flex-1';
          var label = document.createElement('label');
          label.className = 'block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1';
          label.textContent = 'Search';
          input = document.createElement('input');
          input.type = 'search';
          input.placeholder = 'Type to filter rows...';
          input.className = 'w-full h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500';
          left.appendChild(label);
          left.appendChild(input);
          controlBar.appendChild(left);
        }

        var right = document.createElement('div');
        right.className = 'flex flex-wrap items-end gap-2';

        var sortLabel = document.createElement('label');
        sortLabel.className = 'block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1 w-full md:w-auto';
        sortLabel.textContent = 'Sort';

        var colSelect = document.createElement('select');
        colSelect.className = 'px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100';

        var dirSelect = document.createElement('select');
        dirSelect.className = 'px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100';
        dirSelect.innerHTML = '<option value="asc">Ascending</option><option value="desc">Descending</option>';

        var ths = Array.from(tbl.tHead.querySelectorAll('th'));
        ths.forEach(function(th, idx){
          var txt = (th.textContent || '').trim();
          if (!txt) txt = 'Column ' + (idx + 1);
          var opt = document.createElement('option');
          opt.value = String(idx);
          opt.textContent = txt;
          colSelect.appendChild(opt);
          th.style.cursor = 'pointer';
          th.title = 'Click to sort';
        });

        right.appendChild(sortLabel);
        right.appendChild(colSelect);
        right.appendChild(dirSelect);

        controlBar.appendChild(right);
        wrapper.insertBefore(controlBar, wrapper.firstChild);

        var tbody = tbl.tBodies[0];
        var allRows = Array.from(tbody.rows);
        allRows.forEach(function(r){
          r.dataset.__filterText = normalizeText(r.textContent);
          Array.from(r.cells || []).forEach(function(cell){
            if (cell.children && cell.children.length) return;
            if (cell.dataset.__origText != null) return;
            cell.dataset.__origText = cell.textContent;
          });
        });

        function applyFilter(){
          var rawQ = String(input.value || '').trim();
          var q = normalizeText(rawQ);
          allRows.forEach(function(r){
            var ok = !q || (r.dataset.__filterText || '').indexOf(q) !== -1;
            r.style.display = ok ? '' : 'none';
            if (!ok) return;
            Array.from(r.cells || []).forEach(function(cell){
              if (cell.children && cell.children.length) return;
              if (cell.dataset.__origText == null) return;
              if (!q){
                cell.textContent = cell.dataset.__origText;
              } else {
                cell.innerHTML = highlightHtml(cell.dataset.__origText, rawQ);
              }
            });
          });
        }

        function applySort(){
          var idx = Number(colSelect.value || 0);
          var dir = (dirSelect.value || 'asc');
          var factor = dir === 'desc' ? -1 : 1;

          var rows = Array.from(tbody.rows);
          rows.sort(function(a,b){
            var av = getCellText(a, idx);
            var bv = getCellText(b, idx);

            var an = parseMaybeNumber(av);
            var bn = parseMaybeNumber(bv);
            if (an !== null && bn !== null) return (an - bn) * factor;

            var ad = parseMaybeDate(av);
            var bd = parseMaybeDate(bv);
            if (ad !== null && bd !== null) return (ad - bd) * factor;

            return av.localeCompare(bv, undefined, { numeric: true, sensitivity: 'base' }) * factor;
          });
          rows.forEach(function(r){ tbody.appendChild(r); });
        }

        if (input){
          var filterRaf = 0;
          input.addEventListener('input', function(){
            if (filterRaf) cancelAnimationFrame(filterRaf);
            filterRaf = requestAnimationFrame(applyFilter);
          });
        }
        colSelect.addEventListener('change', applySort);
        dirSelect.addEventListener('change', applySort);

        ths.forEach(function(th, idx){
          th.addEventListener('click', function(){
            var currentIdx = Number(colSelect.value || 0);
            if (currentIdx === idx){
              dirSelect.value = (dirSelect.value === 'asc') ? 'desc' : 'asc';
            } else {
              colSelect.value = String(idx);
              dirSelect.value = 'asc';
            }
            applySort();
          });
        });
      }

      document.querySelectorAll('table').forEach(enhanceTable);
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
      var prefetched = new Set();
      function isSameOrigin(url){
        try {
          var u = new URL(url, window.location.href);
          return u.origin === window.location.origin;
        } catch (e) {
          return false;
        }
      }
      function shouldPrefetch(a){
        if (!a || !a.href) return false;
        if (a.target && a.target !== '_self') return false;
        if (a.hasAttribute('download')) return false;
        if (!isSameOrigin(a.href)) return false;
        if (a.href.indexOf('#') !== -1) return false;
        return true;
      }
      function prefetchHref(href){
        if (!href || prefetched.has(href)) return;
        prefetched.add(href);
        var l = document.createElement('link');
        l.rel = 'prefetch';
        l.href = href;
        l.as = 'document';
        document.head.appendChild(l);
      }
      document.addEventListener('pointerenter', function(e){
        var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!a || !shouldPrefetch(a)) return;
        prefetchHref(a.href);
      }, true);

      document.addEventListener('touchstart', function(e){
        var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!a || !shouldPrefetch(a)) return;
        prefetchHref(a.href);
      }, { passive: true });
    });
    </script>

    <!-- Global Toasts: showToast(type, message, { duration }) and alert() override -->
    <script>
    (function(){
      const container = document.getElementById('toast-container');
      function iconFor(type){
        switch(type){
          case 'success': return '✅';
          case 'error': return '❌';
          case 'warning': return '⚠️';
          default: return 'ℹ️';
        }
      }
      function colorClasses(type){
        switch(type){
          case 'success': return ['bg-green-600','bg-green-700','bg-green-500'];
          case 'error': return ['bg-red-600','bg-red-700','bg-red-500'];
          case 'warning': return ['bg-yellow-600','bg-yellow-700','bg-yellow-500'];
          default: return ['bg-blue-600','bg-blue-700','bg-blue-500'];
        }
      }
      function showToast(type, message, opts){
        const duration = Math.max(1500, Math.min(10000, (opts?.duration||3000)));
        if (!container) return; // fail-safe
        const [bg, hover, bar] = colorClasses(type);
        const wrap = document.createElement('div');
        wrap.className = 'pointer-events-auto w-[320px] sm:w-[360px] shadow-lg rounded-lg overflow-hidden ring-1 ring-black/5 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 translate-y-0 opacity-100 transition duration-200';
        wrap.innerHTML = `
          <div class="flex items-start gap-3 px-4 py-3">
            <div class="text-lg leading-none">${iconFor(type)}</div>
            <div class="flex-1 text-sm">${message}</div>
            <button class="ml-2 inline-flex items-center justify-center w-7 h-7 rounded hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Close">✖</button>
          </div>
          <div class="h-1 ${bar}"></div>
        `;
        const closeBtn = wrap.querySelector('button');
        const barEl = wrap.lastElementChild;
        let start = performance.now();
        let paused = false; let elapsed = 0; let rafId;
        function tick(now){
          if (paused) { rafId = requestAnimationFrame(tick); return; }
          const t = Math.min(1, (now - start + elapsed) / duration);
          barEl.style.width = ((1 - t) * 100) + '%';
          barEl.style.transition = 'width 0s';
          if (t >= 1) { dismiss(); } else { rafId = requestAnimationFrame(tick); }
        }
        function dismiss(){
          cancelAnimationFrame(rafId);
          wrap.style.opacity = '0';
          wrap.style.transform = 'translateY(-4px)';
          setTimeout(()=>wrap.remove(), 180);
        }
        // Hover to pause
        wrap.addEventListener('mouseenter', ()=>{ paused = true; elapsed += performance.now() - start; });
        wrap.addEventListener('mouseleave', ()=>{ paused = false; start = performance.now(); });
        // Close button
        closeBtn.addEventListener('click', dismiss);
        // Swipe to dismiss on mobile
        let touchStartX = null;
        wrap.addEventListener('touchstart', (e)=>{ touchStartX = e.touches[0].clientX; }, {passive:true});
        wrap.addEventListener('touchmove', (e)=>{
          if (touchStartX == null) return;
          const dx = e.touches[0].clientX - touchStartX;
          wrap.style.transform = `translateX(${dx}px)`;
          wrap.style.opacity = String(Math.max(0.3, 1 - Math.abs(dx)/240));
        }, {passive:true});
        wrap.addEventListener('touchend', ()=>{
          const current = wrap.style.transform || '';
          const m = current.match(/translateX\(([-0-9.]+)px\)/);
          const dx = m ? Math.abs(parseFloat(m[1])) : 0;
          if (dx > 120) dismiss(); else { wrap.style.transform = ''; wrap.style.opacity = '1'; }
        });
        // Insert and start
        container.prepend(wrap);
        // Start progress bar full width
        barEl.style.width = '100%';
        rafId = requestAnimationFrame(tick);
      }
      window.showToast = showToast;
      // Non-blocking, friendlier alert
      const nativeAlert = window.alert;
      window.alert = function(msg){ try { showToast('info', String(msg)); } catch(_) { nativeAlert(msg); } };
    })();
    </script>

    {{-- Alpine.js --}}
    <script>
    // Apply theme before Tailwind renders (no flash)
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


    {{-- Theme Manager --}}
    <script>
    function themeSettings() {
        return {
            theme: 'light',
            loadTheme() {
                this.theme = localStorage.getItem('theme') || 'light';
                this.applyTheme();
            },
            toggleTheme() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', this.theme);
                this.applyTheme();
            },
            applyTheme() {
                if (this.theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        }
    }
    </script>
    <!-- Simple-DataTables (CDN) for Staff/App layout -->
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
    /* Simple-DataTables dark mode overrides for staff/app */
    .dark .dataTable-wrapper .dataTable-top,
    .dark .dataTable-wrapper .dataTable-bottom {
      background-color: transparent !important;
      color: #e5e7eb !important;
    }
    .dark .dataTable-wrapper .dataTable-input,
    .dark .dataTable-wrapper .dataTable-selector {
      background-color: #1f2937 !important;
      color: #e5e7eb !important;
      border-color: #374151 !important;
    }
    .dark .dataTable-wrapper .dataTable-input::placeholder { color: #9ca3af !important; }
    .dark .dataTable-wrapper .dataTable-dropdown label { color: #d1d5db !important; }
    .dark .dataTable-pagination li a {
      background-color: #111827 !important;
      color: #e5e7eb !important;
      border-color: #374151 !important;
    }
    .dark .dataTable-pagination li.active a {
      background-color: var(--brand-600) !important;
      color: #ffffff !important;
      border-color: var(--brand-600) !important;
    }
    .dark .dataTable-pagination li a:hover { background-color: #1f2937 !important; }
    .dark table.dataTable-table { background-color: transparent !important; }
    .dark table.dataTable-table thead th {
      background-color: rgba(55,65,81,0.6) !important;
      color: #d1d5db !important;
    }
    .dark table.dataTable-table,
    .dark table.dataTable-table th,
    .dark table.dataTable-table td {
      border-color: #374151 !important;
    }
    .dark table { background-color: transparent !important; }
    .dark th, .dark td { border-color: #374151 !important; }
    /* Softer zebra rows (applies to all tables unless .no-zebra is added) */
    table:not(.no-zebra) tbody tr:nth-child(odd) { background-color: #f8fafc; /* slate-50 */ }
    table:not(.no-zebra) tbody tr:nth-child(even) { background-color: #f1f5f9; /* slate-100 */ }
    table:not(.no-zebra) tbody tr:hover { background-color: #e2e8f0; /* slate-200 */ }
    .dark table:not(.no-zebra) tbody tr:nth-child(odd) { background-color: rgba(59,130,246,0.12); /* blue-500 @ 12% */ }
    .dark table:not(.no-zebra) tbody tr:nth-child(even) { background-color: rgba(59,130,246,0.20); /* blue-500 @ 20% */ }
    .dark table:not(.no-zebra) tbody tr:hover { background-color: rgba(59,130,246,0.28); /* blue-500 @ 28% */ }
    /* Blue header and top accent for all tables (opt-out with .no-accent) */
    table:not(.no-accent){ border-top: 4px solid var(--brand-600); border-collapse: separate; border-spacing: 0; }
    table:not(.no-accent) thead th {
      background-color: var(--brand-700);
      color: #ffffff !important;
    }
    .dark table:not(.no-accent){ border-top-color: var(--brand-500); }
    .dark table:not(.no-accent) thead th {
      background-color: var(--brand-900);
      color: #e5e7eb !important;
    }
    </style>
    </body>
    @stack('scripts')
    </html>
