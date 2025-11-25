<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // our custom login blade
    }

    public function customLogin(Request $request)
{
    $request->validate([
        'email' => ['required','email'],
        'password' => ['required','string']
    ]);

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $user = Auth::user();

        // Block: sample staff account must not be used with administrator role
        if ($request->input('role') === 'admin' && $user->email === 'staff@mawasa.com') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['email' => 'This account cannot sign in as administrator.'])->withInput();
        }
        

        // After successful login, always go to the admin dashboard
        return redirect()->route('admin.dashboard');
    }

    return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
