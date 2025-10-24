<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $rows = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();
        return response()->json(['ok' => true, 'notifications' => $rows]);
    }

    public function markRead(Request $request)
    {
        $request->validate(['id' => ['required','integer','exists:notifications,id']]);
        $n = Notification::where('id', $request->id)->where('user_id', $request->user()->id)->firstOrFail();
        $n->read_at = now();
        $n->save();
        return response()->json(['ok' => true]);
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'title' => ['required','string','max:255'],
            'message' => ['nullable','string'],
        ]);
        abort_unless($request->user()->role === 'admin', 403);
        $staff = User::where('role', '!=', 'admin')->get(['id']);
        foreach ($staff as $s) {
            Notification::create([
                'user_id' => $s->id,
                'title' => $request->title,
                'message' => $request->message,
            ]);
        }
        return response()->json(['ok' => true]);
    }

    public function recent(Request $request)
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $rows = Notification::select('title','message', DB::raw('MAX(created_at) as created_at'), DB::raw('COUNT(*) as audience'))
            ->groupBy('title','message')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();
        return response()->json(['ok' => true, 'items' => $rows]);
    }
}


