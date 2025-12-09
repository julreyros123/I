<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // optional, if you want auth protection
    }

    public function index()
    {
        return view('settings.index');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput(['current_password' => '']);
        }

        $user->password = Hash::make($validated['password']);
        $user->setRememberToken(str()->random(60));
        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'module' => 'Account',
            'action' => 'PASSWORD_UPDATED',
            'description' => $user->name . ' updated their password',
            'target_type' => get_class($user),
            'target_id' => $user->id,
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ],
        ]);

        Auth::login($user); // refresh remember token session

        return back()->with('status', 'Password updated successfully.');
    }
}
