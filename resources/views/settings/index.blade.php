@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div x-data="themeSettings()" x-init="loadTheme()" class="p-6 max-w-4xl mx-auto">
    <h1 class="text-3xl font-semibold mb-6 text-gray-900 dark:text-gray-100">Settings</h1>

    <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-xl transition-colors duration-300 space-y-6">
        
        <!-- Intro -->
        <p class="text-gray-600 dark:text-gray-300">
            Customize your preferences below.
        </p>

        <!-- Settings grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Dark Mode -->
            <div class="p-5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-medium text-gray-800 dark:text-gray-200">Dark Mode</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Toggle light or dark theme</p>
                </div>
                <!-- iOS Toggle -->
                <div 
                    @click="toggleTheme()" 
                    :class="theme === 'dark' ? 'bg-green-500' : 'bg-gray-300'" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 cursor-pointer"
                >
                    <span 
                        :class="theme === 'dark' ? 'translate-x-6' : 'translate-x-1'" 
                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-300"
                    ></span>
                </div>
            </div>

            <!-- Font Size -->
            <div class="p-5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h2 class="text-base font-medium text-gray-800 dark:text-gray-200 mb-2">Font Size</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Adjust text size for better readability</p>
                
                <!-- Segmented Control -->
                <div class="flex items-center gap-2">
                    <button class="flex-1 px-3 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        A-
                    </button>
                    <button class="flex-1 px-3 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        A+
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Theme Script -->
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
            const nav = document.querySelector('nav');
            if (this.theme === 'dark') {
                document.documentElement.classList.add('dark');
                if (nav) {
                    nav.classList.remove('bg-white/70', 'border-gray-200');
                    nav.classList.add('bg-gray-900', 'border-gray-800');
                }
            } else {
                document.documentElement.classList.remove('dark');
                if (nav) {
                    nav.classList.remove('bg-gray-900', 'border-gray-800');
                    nav.classList.add('bg-white/70', 'border-gray-200');
                }
            }
        }
    }
}
</script>
@endsection

