<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeterServiceTicket;
use Illuminate\Http\Request;

class MeterServiceTicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = MeterServiceTicket::with(['meter', 'customer', 'application'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'ok' => true,
            'items' => $tickets,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'meter_id' => ['nullable', 'integer', 'exists:meters,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_application_id' => ['nullable', 'integer', 'exists:customer_applications,id'],
            'issue_type' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'scheduled_visit_at' => ['nullable', 'date'],
        ]);

        $ticket = MeterServiceTicket::create(array_merge($data, [
            'reported_by' => optional($request->user())->id,
            'status' => 'open',
        ]));

        return response()->json([
            'ok' => true,
            'ticket' => $ticket->load(['meter', 'customer', 'application']),
        ]);
    }

    public function update(Request $request, MeterServiceTicket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,scheduled,resolved,closed'],
            'scheduled_visit_at' => ['nullable', 'date'],
            'resolved_at' => ['nullable', 'date'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $ticket->fill($data);
        if ($ticket->status === 'resolved' && !$ticket->resolved_at) {
            $ticket->resolved_at = now();
        }
        if ($ticket->status === 'resolved') {
            $ticket->resolved_by = optional($request->user())->id;
        }
        $ticket->save();

        return response()->json([
            'ok' => true,
            'ticket' => $ticket->fresh()->load(['meter', 'customer', 'application']),
        ]);
    }
}
