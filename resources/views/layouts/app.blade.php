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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Use Inter as main font and keep font-family consistent */
        body, input, textarea, button {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">

    {{-- Navbar --}}
    @include('components.navbar')

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Main Content --}}
    <main class="md:ml-64 pt-16 min-h-screen transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 py-10">
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
</body>
@stack('scripts')
</html>

