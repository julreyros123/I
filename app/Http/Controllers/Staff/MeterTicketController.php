<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\MeterServiceTicket;
use Illuminate\Http\Request;

class MeterTicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = MeterServiceTicket::with(['meter', 'customer', 'application'])
            ->whereIn('status', ['open', 'in_progress', 'scheduled'])
            ->orderByRaw("CASE WHEN scheduled_visit_at IS NULL THEN 1 ELSE 0 END")
            ->orderByDesc('scheduled_visit_at')
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'ok' => true,
            'items' => $tickets,
        ]);
    }

    public function update(Request $request, MeterServiceTicket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,scheduled,resolved,closed'],
            'scheduled_visit_at' => ['nullable', 'date'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $ticket->fill($data);
        if ($ticket->status === 'resolved' && !$ticket->resolved_at) {
            $ticket->resolved_at = now();
            $ticket->resolved_by = optional($request->user())->id;
        }
        $ticket->save();

        return response()->json([
            'ok' => true,
            'ticket' => $ticket->fresh()->load(['meter', 'customer', 'application']),
        ]);
    }
}
