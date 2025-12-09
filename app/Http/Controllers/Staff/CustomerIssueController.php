<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\ReportSubmitted;
use App\Models\Customer;
use App\Models\CustomerIssueReport;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class CustomerIssueController extends Controller
{
    public function index()
    {
        return view('staff.customer-issues');
    }

    public function searchAccounts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'max:100'],
        ]);

        $q = trim($data['q']);

        if ($q === '') {
            return response()->json(['results' => []]);
        }

        $results = Customer::query()
            ->select(['id', 'account_no', 'name', 'address', 'contact_no', 'meter_no'])
            ->when(method_exists(Customer::class, 'scopeActive'), fn ($qb) => $qb->active())
            ->where(function ($qb) use ($q) {
                $qb->where('account_no', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            })
            ->orderByRaw(
                "CASE 
                    WHEN account_no LIKE ? THEN 0 
                    WHEN name LIKE ? THEN 1 
                    ELSE 2 
                END, name ASC",
                ["{$q}%", "{$q}%"]
            )
            ->limit(10)
            ->get()
            ->map(function (Customer $customer) {
                return [
                    'account_no' => $customer->account_no,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'contact_no' => $customer->contact_no,
                    'meter_no' => $customer->meter_no,
                ];
            });

        return response()->json(['results' => $results]);
    }

    public function accountSnapshot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_no' => ['required', 'string', 'max:50'],
        ]);

        $inputAcct = $data['account_no'];
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $inputAcct);

        $customer = Customer::query()
            ->where('account_no', $inputAcct)
            ->orWhereRaw("REPLACE(REPLACE(account_no,'-',''),' ','') = ?", [$normalized])
            ->first();

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found.',
            ], 404);
        }

        $issues = CustomerIssueReport::query()
            ->with(['documentedBy:id,name'])
            ->where('account_no', $customer->account_no)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        $openCount = $issues->whereIn('status', ['open', 'acknowledged'])->count();
        $resolvedCount = $issues->where('status', 'resolved')->count();

        return response()->json([
            'customer' => [
                'account_no' => $customer->account_no,
                'name' => $customer->name,
                'address' => $customer->address,
                'contact_no' => $customer->contact_no,
                'meter_no' => $customer->meter_no,
                'meter_size' => $customer->meter_size,
            ],
            'issues' => $issues->map(fn (CustomerIssueReport $issue) => [
                'id' => $issue->id,
                'summary' => $issue->summary,
                'subject' => $issue->subject,
                'issue_type' => $issue->issue_type,
                'severity' => $issue->severity,
                'channel' => $issue->channel,
                'status' => $issue->status,
                'is_priority' => $issue->is_priority,
                'details' => $issue->details,
                'created_at' => optional($issue->created_at)->toDateTimeString(),
                'created_human' => optional($issue->created_at)->diffForHumans(),
                'documented_by' => $issue->documentedBy?->name,
            ]),
            'metrics' => [
                'open' => $openCount,
                'resolved' => $resolvedCount,
                'total' => $issues->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_no' => ['required', 'string', 'max:50'],
            'issue_type' => ['required', 'string', 'max:120'],
            'severity' => ['required', Rule::in(['normal', 'elevated', 'critical'])],
            'channel' => ['nullable', 'string', 'max:80'],
            'subject' => ['nullable', 'string', 'max:150'],
            'summary' => ['required', 'string', 'max:500'],
            'details' => ['nullable', 'string'],
        ]);

        $inputAcct = $data['account_no'];
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $inputAcct);

        $customer = Customer::query()
            ->where('account_no', $inputAcct)
            ->orWhereRaw("REPLACE(REPLACE(account_no,'-',''),' ','') = ?", [$normalized])
            ->first();

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found.',
            ], 404);
        }

        $severity = $data['severity'];
        $isPriority = in_array($severity, ['elevated', 'critical']);

        $issue = null;
        $report = null;

        DB::transaction(function () use (&$issue, &$report, $customer, $data, $isPriority, $severity) {
            $issue = CustomerIssueReport::create([
                'account_no' => $customer->account_no,
                'customer_name' => $customer->name,
                'contact_number' => $customer->contact_no,
                'issue_type' => $data['issue_type'],
                'severity' => $severity,
                'channel' => $data['channel'] ?? null,
                'subject' => $data['subject'] ?? null,
                'summary' => $data['summary'],
                'details' => $data['details'] ?? null,
                'status' => 'open',
                'is_priority' => $isPriority,
                'documented_by' => Auth::id(),
            ]);

            $message = sprintf('[Account %s] %s', $customer->account_no, $data['summary']);
            if (!empty($data['details'])) {
                $message .= "\n\nDetails:\n" . trim($data['details']);
            }

            $report = Report::create([
                'user_id' => Auth::id(),
                'message' => $message,
                'category' => $data['issue_type'] ?: 'Customer Issue',
                'other_problem' => $data['subject'] ?? null,
                'status' => 'open',
                'is_priority' => $isPriority,
            ]);
        });

        if ($report) {
            Mail::to(config('mail.from.address', 'admin@example.com'))->send(new ReportSubmitted($report));
        }

        return response()->json([
            'message' => 'Issue recorded successfully and forwarded to admin.',
            'issue' => [
                'id' => $issue?->id,
                'summary' => $issue?->summary,
                'subject' => $issue?->subject,
                'issue_type' => $issue?->issue_type,
                'severity' => $issue?->severity,
                'channel' => $issue?->channel,
                'status' => $issue?->status,
                'is_priority' => $issue?->is_priority,
                'details' => $issue?->details,
                'created_at' => optional($issue?->created_at)->toDateTimeString(),
                'created_human' => optional($issue?->created_at)->diffForHumans(),
                'documented_by' => $issue?->documentedBy?->name ?? Auth::user()?->name,
            ],
        ], 201);
    }
}
