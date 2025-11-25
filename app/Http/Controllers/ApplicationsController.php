<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerApplication;
use App\Services\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationsController extends Controller
{
    public function index(Request $request)
    {
        $q = CustomerApplication::query()
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->string('status')))
            ->when($request->filled('decision'), fn($qq) => $qq->where('decision', $request->string('decision')))
            ->when($request->filled('risk'), fn($qq) => $qq->where('risk_level', $request->string('risk')))
            ->orderByDesc('created_at');
        $applications = $q->limit(200)->get();
        return view('applications.index', compact('applications'));
    }

    public function show($id)
    {
        $app = CustomerApplication::findOrFail($id);
        return view('applications.show', compact('app'));
    }

    public function latest(Request $request): JsonResponse
    {
        $request->validate(['customer_id' => ['required','integer','exists:customers,id']]);
        $cid = (int) $request->customer_id;
        $app = CustomerApplication::where('customer_id', $cid)
            ->orderByDesc('created_at')
            ->first(['id','customer_id','status','score','risk_level','decision','created_at']);
        if (!$app) return response()->json(['ok' => true, 'application' => null]);
        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function score(Request $request, $id, ScoringService $scoring): JsonResponse
    {
        $app = CustomerApplication::findOrFail($id);
        $result = $scoring->score($app);
        $app->score = $result['score'];
        $app->score_breakdown = $result['breakdown'];
        $app->risk_level = $result['risk_level'];
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function approve(Request $request, $id): JsonResponse
    {
        $request->validate(['auto_verify' => ['nullable','boolean']]);
        $app = CustomerApplication::findOrFail($id);
        $app->decision = 'approve';
        $app->status = 'approved';
        $app->reviewed_by = optional($request->user())->id;
        $app->reviewed_at = now();
        $app->save();

        if ($request->boolean('auto_verify') && $app->customer_id) {
            $cust = Customer::find($app->customer_id);
            if ($cust) { $cust->status = 'Active'; $cust->save(); }
        }
        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function reject(Request $request, $id): JsonResponse
    {
        $request->validate(['reason' => ['nullable','string','max:500']]);
        $app = CustomerApplication::findOrFail($id);
        $app->decision = 'reject';
        $app->status = 'rejected';
        $app->reviewed_by = optional($request->user())->id;
        $app->reviewed_at = now();
        $flags = $app->risk_flags ?: [];
        if ($request->filled('reason')) {
            $flags[] = ['type' => 'reject_reason', 'note' => (string)$request->reason, 'at' => now()->toISOString()];
        }
        $app->risk_flags = $flags;
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function inspect(Request $request, $id, ScoringService $scoring): JsonResponse
    {
        $data = $request->validate([
            'inspection_date' => ['required','date'],
            'inspected_by' => ['nullable','string','max:100'],
            'inspection_notes' => ['nullable','string','max:1000'],
        ]);
        $app = CustomerApplication::findOrFail($id);
        $app->inspection_date = $data['inspection_date'];
        $app->inspected_by = $data['inspected_by'] ?? optional($request->user())->name;
        $app->inspection_notes = $data['inspection_notes'] ?? null;
        // Optional: mark status to 'inspected' if still registered/pending
        if (in_array($app->status, ['registered','pending','approved'])) {
            $app->status = 'inspected';
        }
        // Re-score with inspection boost
        $res = $scoring->score($app);
        $app->score = $res['score'];
        $app->score_breakdown = $res['breakdown'];
        $app->risk_level = $res['risk_level'];
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function schedule(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'schedule_date' => ['required','date'],
        ]);
        $app = CustomerApplication::findOrFail($id);
        $app->schedule_date = $data['schedule_date'];
        $app->scheduled_by = optional($request->user())->id;
        // move status to scheduled if approved
        if (in_array($app->status, ['approved','inspected'])) {
            $app->status = 'scheduled';
        }
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function installed(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'installed_at' => ['required','date'],
            'installed_by' => ['nullable','string','max:100'],
        ]);
        $app = CustomerApplication::findOrFail($id);
        $app->installed_at = $data['installed_at'];
        $app->installed_by = $data['installed_by'] ?? optional($request->user())->name;
        $app->status = 'installed';
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }
}
