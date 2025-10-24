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
        body {
            font-family: 'Poppins', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-300">

    {{-- Navbar --}}
    @include('components.navbar')

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Main Content --}}
    <main class="ml-64 pt-16 min-h-screen transition-all duration-300"
          :class="{ 'ml-0': window.innerWidth < 768 }">

        {{-- Add a card wrapper here instead of forcing the whole main to be card-like --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            @yield('content')
        </div>
    </main>

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

