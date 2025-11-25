<nav  
    class="fixed top-0 left-0 right-0 md:ml-64 
           backdrop-blur-md bg-white/70 dark:bg-gray-900/80 
           border-b border-gray-200 dark:border-gray-700 
           flex items-center justify-between px-4 z-30"
    style="height: 65px;">

    <div class="flex items-center gap-3">
        <button id="toggle-sidebar" 
            class="md:hidden p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none">
            <x-heroicon-o-bars-3 class="w-6 h-6 text-gray-700 dark:text-gray-200"/>
        </button>
        <img src="{{ asset('images/mawasa-logo.png') }}" alt="Logo" class="h-8 w-8 rounded">
        <div class="leading-tight hidden sm:block">
            <h1 class="text-sm font-semibold text-gray-900 dark:text-white">
                MANAMBULAN WATERWORKS AND SANITATION INC.
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">Admin Portal</p>
        </div>
    </div>

    <div class="hidden md:flex items-center flex-1 justify-center">
        <div class="relative w-72">
            <input type="text" placeholder="Search"
                class="w-full pl-4 pr-10 py-2 rounded-full 
                       bg-gray-100 dark:bg-gray-800 
                       text-gray-700 dark:text-gray-200 
                       placeholder-gray-400 dark:placeholder-gray-500
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 
                       text-sm shadow-sm">
            <x-heroicon-o-magnifying-glass 
                class="w-5 h-5 absolute right-3 top-1/2 -translate-y-1/2 
                       text-gray-400 dark:text-gray-500" />
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button @click="toggleTheme && toggleTheme()" 
            class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
            <x-heroicon-o-moon class="w-5 h-5 text-gray-700 dark:text-gray-200"/>
        </button>
        <div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" 
                class="flex items-center gap-2 p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                <img src="{{ asset('images/images.jpg') }}" alt="Profile" 
                     class="h-8 w-8 rounded-full border border-gray-200 dark:border-gray-700">
                <div class="hidden sm:block text-left">
                    <span class="block text-sm font-medium text-gray-900 dark:text-white">Administrator</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">Admin User</span>
                </div>
                <x-heroicon-o-ellipsis-vertical class="w-5 h-5 text-gray-500 dark:text-gray-300" />
            </button>
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
