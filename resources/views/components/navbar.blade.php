<nav  
    class="fixed top-0 left-0 right-0 md:ml-64 
           backdrop-blur-md bg-white/70 dark:bg-gray-900/80 
           border-b border-gray-200 dark:border-gray-700 
           flex items-center justify-between px-4 z-30"
    style="height: 65px;">

    <!-- Left: Logo -->
    <div class="flex items-center gap-3">
        <button id="toggle-sidebar" 
            class="md:hidden p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none">
            <x-heroicon-o-bars-3 class="w-6 h-6 text-gray-700 dark:text-gray-200"/>
        </button>

        <div class="h-8 w-8 rounded bg-[var(--kpi-primary)] text-white flex items-center justify-center text-xs font-bold select-none" aria-label="Logo">MW</div>
        <div class="leading-tight hidden sm:block">
            <h1 class="text-sm font-semibold text-gray-900 dark:text-white">
                MANAMBULAN WATERWORKS AND SANITATION INC.
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">Staff Portal</p>
        </div>
    </div>

    <!-- Right: Actions -->
    <div class="flex items-center gap-4">

        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode" 
            class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
            <x-heroicon-o-moon class="w-5 h-5 text-gray-700 dark:text-gray-200" x-show="!darkMode"/>
            <x-heroicon-o-sun class="w-5 h-5 text-yellow-400" x-show="darkMode"/>
        </button>

        <!-- Divider -->
        <div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>

        <!-- Notifications (dynamic) -->
        <div class="relative">
            <button id="topbarNotifBell" 
                class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 relative">
                <x-heroicon-o-bell class="w-6 h-6 text-gray-700 dark:text-gray-200" />
                <span id="topbarNotifDot" class="absolute -top-1 -right-1 w-5 h-5 text-[11px] font-bold text-white bg-red-500 rounded-full flex items-center justify-center shadow hidden">0</span>
            </button>
            <div id="topbarNotifMenu" 
                 class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
                <div class="px-4 py-2 text-sm font-semibold">Notifications</div>
                <ul id="topbarNotifList" class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800"></ul>
                
            </div>
        </div>

        <!-- Profile -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" 
                class="flex items-center gap-2 p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-[11px] font-semibold text-gray-700 dark:text-gray-200 select-none" aria-label="Profile">US</div>
                
                <div class="hidden sm:block text-left">
                    @php($isAdmin = optional(auth()->user())->role === 'admin')
                    <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ $isAdmin ? 'Administrator' : (auth()->user()->name ?? 'User') }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $isAdmin ? 'Admin User' : 'Desk Staff' }}</span>
                </div>
                <x-heroicon-o-ellipsis-vertical class="w-5 h-5 text-gray-500 dark:text-gray-300" />
            </button>

            <!-- Dropdown -->
            <div x-show="open" x-transition 
                 @click.away="open = false"
                 class="absolute right-0 mt-2 w-48 
                        bg-white dark:bg-gray-900 
                        rounded-xl shadow-lg 
                        border border-gray-200 dark:border-gray-700 
                        py-2 z-50">
                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <x-heroicon-o-user class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    Profile
                </a>

                @if (Route::has('settings.index'))
                    <a href="{{ route('settings.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                        Settings
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="contents">
                    @csrf
                    <button type="submit" 
                            class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const bell = document.getElementById('topbarNotifBell');
  const dot = document.getElementById('topbarNotifDot');
  const menu = document.getElementById('topbarNotifMenu');
  const list = document.getElementById('topbarNotifList');
  async function load() {
    try {
      const res = await fetch("{{ route('api.notifications.index') }}");
      if (!res.ok) return;
      const data = await res.json();
      const items = data.notifications || [];
      const unread = items.filter(n => !n.read_at).length;
      dot.textContent = unread;
      dot.classList.toggle('hidden', unread === 0);
      list.innerHTML = items.length ? items.map(n => `
        <li class="px-4 py-2 text-sm flex items-start justify-between gap-2">
          <div>
            <p class="font-medium">${n.title}</p>
            <p class="opacity-80">${n.message ?? ''}</p>
          </div>
          ${n.read_at ? '' : `<button data-id="${n.id}" class="markRead text-blue-600 hover:underline">Mark read</button>`}
        </li>`).join('') : '<li class="px-4 py-2 text-sm text-gray-500">No notifications</li>';
      list.querySelectorAll('.markRead').forEach(btn => btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-id');
        await fetch("{{ route('api.notifications.read') }}", { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||'' }, body: JSON.stringify({ id }) });
        load();
      }));
    } catch (e) { /* ignore */ }
  }
  bell?.addEventListener('click', () => menu.classList.toggle('hidden'));
  document.addEventListener('click', (e) => { if (!e.target.closest('#topbarNotifMenu') && !e.target.closest('#topbarNotifBell')) menu.classList.add('hidden'); });
  load();
});
</script>
@endpush
