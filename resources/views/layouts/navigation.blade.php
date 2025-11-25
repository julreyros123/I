<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @php
                        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
                        $dashboardUrl = $isAdmin ? route('admin.dashboard') : route('dashboard');
                        $dashboardActive = $isAdmin ? request()->routeIs('admin.dashboard') : request()->routeIs('dashboard');
                    @endphp
                    <a href="{{ $dashboardUrl }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="$dashboardUrl" :active="$dashboardActive">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Notifications + Settings -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Bell -->
                <div class="relative mr-4">
                    <button id="notifBell" class="relative inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-700 dark:text-gray-200">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V8.25A6.75 6.75 0 004.5 8.25v1.5A8.967 8.967 0 013.689 15.77c1.733.64 3.56 1.085 5.454 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span id="notifDot" class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-red-500 hidden"></span>
                    </button>
                    <div id="notifMenu" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                        <div class="p-3 border-b border-gray-100 dark:border-gray-700 text-sm font-semibold">Notifications</div>
                        <ul id="notifList" class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700"></ul>
                    </div>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="$dashboardUrl" :active="$dashboardActive">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function(){
    const bell = document.getElementById('notifBell');
    const dot = document.getElementById('notifDot');
    const menu = document.getElementById('notifMenu');
    const list = document.getElementById('notifList');
    async function loadNotifs(){
        const res = await fetch("{{ route('api.notifications.index') }}");
        if(!res.ok) return;
        const data = await res.json();
        const items = data.notifications || [];
        dot.classList.toggle('hidden', items.filter(n => !n.read_at).length === 0);
        list.innerHTML = items.map(n => `
            <li class="p-3 text-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-medium">${n.title}</p>
                        <p class="opacity-80">${n.message ?? ''}</p>
                    </div>
                    ${n.read_at ? '' : `<button data-id="${n.id}" class="markRead text-blue-600 hover:underline">Mark read</button>`}
                </div>
            </li>`).join('');
        list.querySelectorAll('.markRead').forEach(btn => btn.addEventListener('click', async ()=>{
            const id = btn.getAttribute('data-id');
            await fetch("{{ route('api.notifications.read') }}", {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''}, body: JSON.stringify({id})});
            loadNotifs();
        }));
    }
    bell?.addEventListener('click', () => menu.classList.toggle('hidden'));
    document.addEventListener('click', (e) => { if(!e.target.closest('#notifMenu') && !e.target.closest('#notifBell')) menu.classList.add('hidden'); });
    loadNotifs();
});
</script>
@endpush