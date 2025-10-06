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
            background: linear-gradient(135deg, #e0f2fe, #f9fafb, #dbeafe);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

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
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="login-panel shadow-lg rounded-lg p-6 mx-auto border">
        <!-- Logo -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gray-300 rounded-full mx-auto mb-2"></div>
            <h1 class="text-xl font-bold text-blue-700">MAWASA</h1>
            <p class="text-xs text-gray-500">Manambulan Water and Sanitation</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.custom') }}" class="space-y-4" id="loginForm">
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

            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="email"
                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
                    value="{{ old('email') }}">
            </div>

            <!-- Password with Eye Icon -->
            <div class="relative">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password"
                    class="w-full px-3 pr-10 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
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

            <!-- Remember me -->
            <div class="flex items-center space-x-2 mb-4">
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

            <!-- Sample Staff Autofill -->
            <div class="mb-2 text-center">
                <button type="button" id="sampleStaffBtn" class="text-xs text-blue-700 underline">Use sample staff account</button>
                <p class="text-[11px] text-gray-500">Email: staff@mawasa.com · Password: password123</p>
            </div>

            <!-- Login Button -->
            <div class="text-center">
                <button type="submit"
                    class="w-[350px] h-[40px] bg-blue-500 hover:bg-blue-600 text-white rounded-md text-base transition">
                    Login
                </button>
            </div>

            <p class="text-xs text-center text-gray-500">Staff and administrator login</p>
        </form>
    </div>

    <script>
        // ======= PASSWORD TOGGLE =======
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            this.querySelector('svg').style.color = isPassword ? '#2563eb' : '#9ca3af';
        });

        // ======= REMEMBER ME =======
        window.addEventListener('DOMContentLoaded', () => {
            const email = localStorage.getItem('savedEmail');
            const password = localStorage.getItem('savedPassword');
            const remember = localStorage.getItem('rememberMe') === 'true';

            if (remember && email && password) {
                document.getElementById('username').value = email;
                document.getElementById('password').value = password;
                document.getElementById('remember').checked = true;
            }

            document.getElementById('loginForm').addEventListener('submit', () => {
                const remember = document.getElementById('remember').checked;
                if (remember) {
                    localStorage.setItem('savedEmail', document.getElementById('username').value);
                    localStorage.setItem('savedPassword', document.getElementById('password').value);
                    localStorage.setItem('rememberMe', 'true');
                } else {
                    localStorage.removeItem('savedEmail');
                    localStorage.removeItem('savedPassword');
                    localStorage.removeItem('rememberMe');
                }
            });
        });

        // ======= SAMPLE STAFF AUTOFILL =======
        document.getElementById('sampleStaffBtn').addEventListener('click', () => {
            document.getElementById('username').value = 'staff@mawasa.com';
            document.getElementById('password').value = 'password123';
        });
    </script>
</body>
</html>






