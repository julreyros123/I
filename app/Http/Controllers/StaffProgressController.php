<?php

namespace App\Http\Controllers;

use App\Models\StaffProgress;
use App\Models\BillEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class StaffProgressController extends Controller
{
    public function today(Request $request)
    {
        $userId = Auth::id();
        $date = now()->toDateString();
        $sp = StaffProgress::firstOrCreate(
            ['staff_id' => $userId, 'date' => $date],
            ['target' => 0, 'completed' => 0]
        );

        $rate = $sp->target > 0 ? round(($sp->completed / max(1, $sp->target)) * 100) : 0;
        return response()->json([
            'date' => $sp->date,
            'target' => (int)$sp->target,
            'completed' => (int)$sp->completed,
            'rate' => $rate,
            'notes' => $sp->notes,
        ]);
    }

    public function updateToday(Request $request)
    {
        $data = $request->validate([
            'target' => 'nullable|integer|min:0|max:65535',
            'completed' => 'nullable|integer|min:0|max:65535',
            'notes' => 'nullable|string'
        ]);
        $userId = Auth::id();
        $date = now()->toDateString();
        $sp = StaffProgress::firstOrCreate(
            ['staff_id' => $userId, 'date' => $date],
            ['target' => 0, 'completed' => 0]
        );

        if (array_key_exists('target', $data)) {
            $sp->target = $data['target'];
        }
        if (array_key_exists('completed', $data)) {
            $sp->completed = min($data['completed'], $sp->target ?? $data['completed']);
        }
        if (array_key_exists('notes', $data)) {
            $sp->notes = $data['notes'];
        }
        $sp->save();

        $rate = $sp->target > 0 ? round(($sp->completed / max(1, $sp->target)) * 100) : 0;
        return response()->json([
            'date' => $sp->date,
            'target' => (int)$sp->target,
            'completed' => (int)$sp->completed,
            'rate' => $rate,
            'notes' => $sp->notes,
        ]);
    }

    public function breakdown(Request $request)
    {
        $userId = Auth::id();
        $date = now()->toDateString();
        $sp = StaffProgress::firstOrCreate(
            ['staff_id' => $userId, 'date' => $date],
            ['target' => 0, 'completed' => 0]
        );

        // Count today's bill events by type for this staff
        $eventsToday = BillEvent::query()
            ->whereDate('created_at', $date)
            ->where('staff_id', $userId)
            ->select('type')
            ->get()
            ->groupBy('type');

        $generated = ($eventsToday['generated']->count() ?? 0);
        $delivered = ($eventsToday['delivered']->count() ?? 0);

        $done = $delivered; // delivered counted as done
        $inProgress = max(0, $generated - $delivered); // generated not yet delivered
        $toDo = max(0, (int)$sp->target - $done);

        return response()->json([
            'target' => (int)$sp->target,
            'completed' => (int)$sp->completed,
            'done' => (int)$done,
            'in_progress' => (int)$inProgress,
            'to_do' => (int)$toDo,
        ]);
    }

    public function resetToday(Request $request)
    {
        $userId = Auth::id();
        $date = now()->toDateString();

        // Zero out today's staff progress
        $sp = StaffProgress::firstOrCreate(
            ['staff_id' => $userId, 'date' => $date],
            ['target' => 0, 'completed' => 0]
        );
        $sp->target = 0;
        $sp->completed = 0;
        $sp->notes = null;
        $sp->save();

        // Remove today's bill events for this staff
        BillEvent::where('staff_id', $userId)->whereDate('created_at', $date)->delete();

        return response()->json([
            'ok' => true,
            'target' => 0,
            'completed' => 0,
            'done' => 0,
            'in_progress' => 0,
            'to_do' => 0,
        ]);
    }
}
