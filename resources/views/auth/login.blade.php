<!DOCTYPE html> 
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MAWASA Login</title>
    @vite('resources/css/app.css')
    <style>
        body {
            min-height: 100vh;
            position: relative; /* create stacking context so ::before sits under content */
            background-color: #0b1220; /* fallback */
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow: hidden;
        }
        /* Blurred, darkened background using the provided image */
        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        body::before {
            background: url("{{ asset('images/ChatGPT Image Nov 11, 2025, 05_58_23 PM.png') }}") center/cover no-repeat, #0b1220;
            filter: blur(6px) brightness(0.6) saturate(1.05);
            transform: scale(1.02);
        }
        body::after {
            background: rgba(2, 6, 23, 0.35);
            z-index: 0;
        }
        /* Ensure page content is above background layers */
        body > * { position: relative; z-index: 1; }

        .login-panel {
            width: 400px !important;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            background-color: #ffffff;
            color: #111827;
            border: 1px solid #d1d5db;
            height: 45px; /* ✅ adjusted height */
        }

        /* ✅ Fix for Eye Icon Alignment (moved slightly down) */
        .eye-icon {
            position: absolute;
            right: 3px; 
            bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            width: 35px;
            color: #9ca3af;
            transition: color 0.2s ease;
        }

        .eye-icon:hover {
            color: #2563eb;
        }

        /* ✅ Checkbox Styling */
        input[type="checkbox"] {
            accent-color: #2563eb;
            cursor: pointer;
        }
        /* Custom logo size for fine control */
        .logo-custom { width: 80px; height: 80px; }
        @media (min-width: 768px){ .logo-custom { width: 160px; height: 160px; } }
    </style>
</head>
<body class="min-h-screen">
    <div class="w-full min-h-screen flex flex-col items-center justify-center px-4">
        <div class="login-panel shadow-lg rounded-lg p-6 mx-auto border">
        <!-- Logo -->
        <div class="text-center mb-4" role="banner" aria-label="MAWASA">
            <img src="{{ asset('images/ChatGPT Image Nov 11, 2025, 04_40_23 PM.png') }}" alt="MAWASA Logo" class="logo-custom rounded-full mx-auto shadow-lg" />
        </div>

        <!-- Login Form -->
        <h2 class="text-xl font-semibold text-gray-900 mb-1 text-center">Sign in to your account</h2>
        <p class="text-sm text-gray-500 mb-4 text-center">Use your MAWASA credentials</p>
        @php($loginAction = \Illuminate\Support\Facades\Route::has('login.custom') ? route('login.custom') : url('/login'))
        <form method="POST" action="{{ $loginAction }}" class="space-y-4" id="loginForm">
            @csrf

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-sm">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Email -->
            <div>
                <label for="email" class="block mb-1 text-sm font-medium text-gray-700">Email</label>
                <div class="flex shadow-sm rounded-md">
                    <span class="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7.5 9.172 12.5a3 3 0 0 0 4.243 0L18.5 7.5M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/>
                        </svg>
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        autocomplete="email"
                        required
                        class="rounded-none rounded-r-md block w-full px-3 py-2.5 bg-white border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                        value="{{ old('email') }}">
                </div>
            </div>

            <!-- Password with Eye Icon -->
            <div class="relative">
                <label for="password" class="block mb-1 text-sm font-medium text-gray-700">Password</label>
                <div class="flex shadow-sm rounded-md relative">
                    <span class="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 11V7a5 5 0 0 0-10 0v4M6 11h12a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        class="rounded-none rounded-r-md block w-full px-3 pr-10 py-2.5 bg-white border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                    <div class="eye-icon" id="togglePassword">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5
                                c4.478 0 8.268 2.943 9.542 7
                                -1.274 4.057-5.064 7-9.542 7
                                -4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Remember + Forgot Password -->
            <div class="flex items-center justify-between mb-1 mt-1">
                <div class="flex items-center space-x-2">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        id="remember"
                        class="h-4 w-4 rounded border border-gray-300 bg-white 
                               checked:bg-blue-600 checked:border-blue-600 
                               focus:ring-2 focus:ring-blue-500 focus:ring-offset-0 
                               transition-all duration-200 accent-blue-600"
                    >
                    <label for="remember" class="text-sm text-gray-600">Remember me</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700">Forgot password?</a>
                @endif
            </div>

            <!-- Login Button with icon and spinner -->
            <div class="text-center">
                <button type="submit" id="loginBtn"
                    class="w-full h-[40px] bg-blue-500 hover:bg-blue-600 text-white rounded-md text-base transition inline-flex items-center justify-center gap-2">
                    <!-- Lock Icon (outline) -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 10-9 0v3M5.25 10.5H18.75A2.25 2.25 0 0121 12.75v6A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75v-6A2.25 2.25 0 015.25 10.5z" />
                    </svg>
                    <span id="loginBtnLabel">Login</span>
                    <!-- Spinner (Flowbite-style) -->
                    <svg id="loginBtnSpinner" aria-hidden="true" class="hidden w-5 h-5 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                      <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 26.026C84.9175 29.3625 86.7997 32.9973 88.1811 36.841C89.083 39.1687 91.5421 40.6781 93.9676 39.0409Z" fill="#93c5fd"/>
                    </svg>
                </button>
            </div>

            <p class="text-xs text-center text-gray-500">Authorized personnel only</p>
        </form>
        </div>
    </div>

    <script>
        // ======= PASSWORD TOGGLE =======
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            this.querySelector('svg').style.color = isPassword ? '#2563eb' : '#9ca3af';
        });
        // ======= REMEMBER ME (minimal) =======
        window.addEventListener('DOMContentLoaded', () => {
            const email = localStorage.getItem('savedEmail');
            const remember = localStorage.getItem('rememberMe') === 'true';

            if (remember && email) {
                const e = document.getElementById('email');
                if (e) e.value = email;
                const r = document.getElementById('remember');
                if (r) r.checked = true;
            }

            const form = document.getElementById('loginForm');
            if (form) {
                form.addEventListener('submit', () => {
                    const r = document.getElementById('remember');
                    if (r && r.checked) {
                        localStorage.setItem('savedEmail', document.getElementById('email').value);
                        localStorage.setItem('rememberMe', 'true');
                    } else {
                        localStorage.removeItem('savedEmail');
                        localStorage.removeItem('rememberMe');
                    }
                    // Show spinner and disable button
                    const btn = document.getElementById('loginBtn');
                    const spn = document.getElementById('loginBtnSpinner');
                    const lbl = document.getElementById('loginBtnLabel');
                    if (btn){ btn.disabled = true; btn.classList.add('opacity-70','cursor-not-allowed'); }
                    if (spn){ spn.classList.remove('hidden'); }
                    if (lbl){ lbl.textContent = 'Logging in…'; }
                });
            }
        });
    </script>
</body>
</html>






