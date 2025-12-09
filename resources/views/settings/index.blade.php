@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div x-data="themeSettings()" x-init="loadTheme()" class="p-6 max-w-4xl mx-auto">
    <h1 class="text-3xl font-semibold mb-6 text-gray-900 dark:text-gray-100">Settings</h1>

    <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-xl transition-colors duration-300 space-y-8">

        <!-- Feedback -->
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 text-emerald-700 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50/80 text-rose-700 px-4 py-3 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <section class="p-5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-medium text-gray-800 dark:text-gray-200">Dark Mode</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Toggle light or dark theme</p>
                </div>
                <div @click="toggleTheme()" :class="theme === 'dark' ? 'bg-green-500' : 'bg-gray-300'" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 cursor-pointer">
                    <span :class="theme === 'dark' ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-300"></span>
                </div>
            </section>

            <section class="p-5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h2 class="text-base font-medium text-gray-800 dark:text-gray-200 mb-2">Font Size</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Adjust text size for better readability</p>
                <div class="flex items-center gap-2">
                    <button class="flex-1 px-3 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">A-</button>
                    <button class="flex-1 px-3 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">A+</button>
                </div>
            </section>
        </div>

        <section class="border border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50 dark:bg-gray-800 p-6 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Change Password</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Keep your account secure by updating your password regularly.</p>
            </div>

            <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current password</label>
                    <input type="password" id="current_password" name="current_password" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required autocomplete="current-password">
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New password</label>
                        <input type="password" id="password" name="password" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required autocomplete="new-password" minlength="8">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required autocomplete="new-password">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 text-sm font-semibold transition">
                        <x-heroicon-o-key class="w-5 h-5" />
                        Update password
                    </button>
                </div>
            </form>
        </section>
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

