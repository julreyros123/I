<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\Customer;
use App\Models\CustomerApplication;
use App\Services\ScoringService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApplicationsController extends Controller
{
    public function index(Request $request)
    {
        $statusOptions = [
            'registered' => 'Registered',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'assessed' => 'Assessed',
            'waiting_payment' => 'Waiting for Payment',
            'paid' => 'Paid',
            'scheduled' => 'Scheduled',
            'installing' => 'Installing',
            'installed' => 'Installed',
            'rejected' => 'Rejected',
        ];
        $decisionOptions = [
            'approve' => 'Approve',
            'review' => 'Review',
            'reject' => 'Reject',
        ];

        $filters = Validator::make($request->all(), [
            'status' => ['nullable', Rule::in(array_keys($statusOptions))],
            'decision' => ['nullable', Rule::in(array_keys($decisionOptions))],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ])->validate();

        $query = CustomerApplication::query()
            ->with('customer')
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['decision']), fn ($q) => $q->where('decision', $filters['decision']))
            ->when(isset($filters['search']), function ($q) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('applicant_name', 'like', $term)
                        ->orWhere('application_code', 'like', $term)
                        ->orWhere('address', 'like', $term);
                });
            })
            ->orderByDesc('created_at');

        if ($request->get('export') === 'csv') {
            return $this->exportApplicantsCsv(clone $query);
        }

        $perPage = $filters['per_page'] ?? 15;
        $applications = $query->paginate($perPage)->withQueryString();

        $statusCounts = CustomerApplication::select('status', DB::raw('COUNT(*) as total'))
            ->whereIn('status', ['registered', 'pending', 'waiting_payment', 'paid', 'scheduled', 'installing'])
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.applicants.index', [
            'applications' => $applications,
            'filters' => $filters,
            'statusOptions' => $statusOptions,
            'decisionOptions' => $decisionOptions,
            'statusCounts' => $statusCounts,
            'perPageOptions' => [10, 15, 25, 50, 100],
        ]);
    }

    public function show($id)
    {
        $app = CustomerApplication::with('customer')->findOrFail($id);

        $normalizedName = trim(mb_strtolower($app->applicant_name ?? ''));
        $normalizedAddress = trim(mb_strtolower($app->address ?? ''));

        $duplicateCustomer = null;
        if ($normalizedName !== '' && $normalizedAddress !== '') {
            $duplicateCustomer = Customer::query()
                ->whereRaw('LOWER(name) = ?', [$normalizedName])
                ->whereRaw('LOWER(address) = ?', [$normalizedAddress])
                ->when($app->customer, fn ($q) => $q->where('id', '!=', $app->customer->id))
                ->first(['id', 'account_no', 'status', 'address', 'name']);
        }

        $relatedApplications = CustomerApplication::query()
            ->where('id', '!=', $app->id)
            ->when($app->applicant_name, fn ($q, $name) => $q->where('applicant_name', $name))
            ->when($app->address, fn ($q, $address) => $q->where('address', $address))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $unsettledRecords = BillingRecord::query()
            ->with(['customer:id,name,account_no,status'])
            ->whereHas('customer', function ($q) use ($app) {
                $q->where('name', $app->applicant_name)
                  ->where('address', $app->address);
            })
            ->whereIn('bill_status', ['Outstanding Payment', 'Overdue', 'Notice of Disconnection', 'Disconnected'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'customer_id', 'bill_status', 'total_amount', 'due_date', 'created_at']);

        $riskFlags = collect($app->risk_flags ?? [])
            ->map(function ($flag) {
                if (is_array($flag)) {
                    return $flag;
                }
                return ['type' => 'note', 'note' => (string) $flag];
            })
            ->take(5);

        return view('admin.applicants.show', compact('app', 'duplicateCustomer', 'relatedApplications', 'unsettledRecords', 'riskFlags'));
    }

    protected function exportApplicantsCsv($query)
    {
        $filename = 'admin_applicants_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Application ID',
                'Applicant',
                'Customer Name',
                'Address',
                'Status',
                'Decision',
                'Score',
                'Risk Level',
                'Fee Total',
                'Paid At',
                'Schedule Date',
                'Created At',
            ]);

            $query->cloneWithout(['orders'])->orderBy('id')->chunk(250, function ($chunk) use ($handle) {
                foreach ($chunk as $app) {
                    fputcsv($handle, [
                        $app->application_code ?? sprintf('APP-%06d', $app->id),
                        $app->applicant_name,
                        optional($app->customer)->name,
                        $app->address,
                        $app->status ? ucfirst(str_replace('_', ' ', $app->status)) : '',
                        $app->decision ? ucfirst($app->decision) : '',
                        is_null($app->score) ? '' : $app->score,
                        $app->risk_level ? ucfirst($app->risk_level) : '',
                        $app->fee_total ?? '',
                        optional($app->paid_at)->format('Y-m-d H:i'),
                        optional($app->schedule_date)->format('Y-m-d'),
                        optional($app->created_at)->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
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
        $app = CustomerApplication::with('customer')->findOrFail($id);

        if (!in_array($app->status, ['registered', 'pending', 'approved'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Application must be newly registered or pending before approval.',
            ], 422);
        }

        if ($this->hasActiveDuplicate($app)) {
            return response()->json([
                'ok' => false,
                'message' => 'An active customer already exists with the same name and address. Investigate before approving.',
            ], 422);
        }

        DB::transaction(function () use ($app, $request) {
            $userId = optional($request->user())->id;
            $app->decision = 'approve';
            $app->status = 'approved';
            $app->reviewed_by = $userId;
            $app->reviewed_at = now();
            $app->approved_by = $userId;
            $app->approved_at = now();
            $app->save();
        });

        $app->refresh();

        if ($request->boolean('auto_verify') && $app->customer) {
            $app->customer->status = 'Active';
            $app->customer->save();
        }

        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function assess(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'fee_application' => ['nullable','numeric','min:0'],
            'fee_materials' => ['nullable','numeric','min:0'],
            'fee_labor' => ['nullable','numeric','min:0'],
            'meter_deposit' => ['nullable','numeric','min:0'],
        ]);

        $app = CustomerApplication::findOrFail($id);
        if (!in_array($app->status, ['approved', 'waiting_payment'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Only approved applications can be assessed.',
            ], 422);
        }

        $app->fee_application = (float) ($data['fee_application'] ?? 0);
        $app->fee_inspection = 0.0;
        $app->fee_materials = (float) ($data['fee_materials'] ?? 0);
        $app->fee_labor = (float) ($data['fee_labor'] ?? 0);
        $app->meter_deposit = (float) ($data['meter_deposit'] ?? 0);
        $app->fee_total = $app->fee_application + $app->fee_materials + $app->fee_labor + $app->meter_deposit;
        $app->status = 'waiting_payment';
        $app->save();

        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function markPaid(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'payment_receipt_no' => ['required','string','max:100'],
            'paid_at' => ['nullable','date'],
        ]);

        $app = CustomerApplication::findOrFail($id);
        if (!in_array($app->status, ['waiting_payment', 'approved'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Application must be assessed before recording payment.',
            ], 422);
        }

        DB::transaction(function () use ($app, $data, $request) {
            $app->payment_receipt_no = $data['payment_receipt_no'];
            $app->paid_at = isset($data['paid_at']) ? Carbon::parse($data['paid_at']) : now();
            $app->paid_by = optional($request->user())->id;
            $app->status = 'paid';
            $app->save();
        });

        return response()->json(['ok' => true, 'application' => $app->fresh()]);
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
        if (!in_array($app->status, ['paid','scheduled','installing'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Only paid applications can be scheduled for installation.',
            ], 422);
        }

        $app->schedule_date = $data['schedule_date'];
        $app->scheduled_by = optional($request->user())->id;
        if ($app->status === 'paid') {
            $app->status = 'scheduled';
        }
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function startInstallation(Request $request, $id): JsonResponse
    {
        $app = CustomerApplication::with('customer')->findOrFail($id);
        if ($app->status !== 'scheduled') {
            return response()->json([
                'ok' => false,
                'message' => 'Installation can only start from the scheduled state.',
            ], 422);
        }

        $app->status = 'installing';
        $app->save();

        return response()->json(['ok' => true, 'application' => $app]);
    }

    public function installed(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'installed_at' => ['required','date'],
            'installed_by' => ['nullable','string','max:100'],
            'meter_no' => ['nullable','string','max:100'],
            'meter_size' => ['nullable','string','max:50'],
            'initial_reading' => ['nullable','numeric','min:0'],
        ]);
        $app = CustomerApplication::with('customer')->findOrFail($id);

        if ($app->status !== 'installing') {
            return response()->json([
                'ok' => false,
                'message' => 'Application must be marked as installing before confirming installation.',
            ], 422);
        }

        $customer = $app->customer;
        $docs = $app->documents ?? [];
        $meterNo = $data['meter_no'] ?? ($docs['assigned_meter_no'] ?? null);
        $meterSize = $data['meter_size'] ?? ($docs['assigned_meter_size'] ?? null);

        if (!$meterNo) {
            return response()->json([
                'ok' => false,
                'message' => 'Meter number is required to complete installation.',
            ], 422);
        }

        if ($customer) {
            $duplicateMeter = Customer::query()
                ->where('meter_no', $meterNo)
                ->where('id', '!=', $customer->id)
                ->exists();
            if ($duplicateMeter) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Meter number already assigned to a different customer.',
                ], 422);
            }
        }

        DB::transaction(function () use ($app, $customer, $data, $request) {
            $app->installed_at = Carbon::parse($data['installed_at']);
            $app->installed_by = $data['installed_by'] ?? optional($request->user())->name;
            $app->status = 'installed';
            $docs = $app->documents ?? [];
            $docs['installation_completed_at'] = $app->installed_at->toISOString();
            $app->documents = $docs;
            $app->save();

            if ($customer) {
                $customer->meter_no = $data['meter_no'] ?? ($docs['assigned_meter_no'] ?? null);
                if (array_key_exists('meter_size', $data)) {
                    $customer->meter_size = $data['meter_size'];
                } elseif (!empty($docs['assigned_meter_size'])) {
                    $customer->meter_size = $docs['assigned_meter_size'];
                }
                if (array_key_exists('initial_reading', $data) && $data['initial_reading'] !== null) {
                    $customer->previous_reading = (float) $data['initial_reading'];
                }
                $customer->status = 'Active';
                $customer->save();
            }
        });

        return response()->json(['ok' => true, 'application' => $app->fresh('customer')]);
    }

    protected function hasActiveDuplicate(CustomerApplication $app): bool
    {
        $name = trim(mb_strtolower($app->applicant_name ?? ''));
        $address = trim(mb_strtolower($app->address ?? ''));

        if ($name === '' || $address === '') {
            return false;
        }

        return Customer::query()
            ->whereRaw('LOWER(name) = ?', [$name])
            ->whereRaw('LOWER(address) = ?', [$address])
            ->where('status', 'Active')
            ->exists();
    }
}
