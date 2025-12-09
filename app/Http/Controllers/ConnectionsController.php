<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\CustomerApplication;
use App\Models\Customer;
use App\Models\BillingRecord;
use App\Models\ApplicantPaymentRecord;

class ConnectionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = CustomerApplication::query()
            ->when($request->filled('status'), function($qq) use ($request){
                $qq->where('status', $request->string('status'));
            })
            ->when($request->filled('application_id'), function($qq) use ($request) {
                $qq->where('id', (int) $request->integer('application_id'));
            })
            ->when($request->filled('application_code'), function($qq) use ($request) {
                $raw = trim((string) $request->string('application_code'));
                $digits = preg_replace('/\D+/', '', $raw);
                if ($digits !== '') {
                    $qq->where('id', (int) $digits);
                }
            })
            ->when(!$request->filled('status'), function($qq){
                // By default, hide installed applications whose customer already has a meter assigned
                $qq->where(function($q){
                    $q->where('status', '!=', 'installed')
                      ->orWhereDoesntHave('customer', function($c){
                          $c->whereNotNull('meter_no');
                      });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);
        return response()->json(['ok' => true, 'items' => $q]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $app = CustomerApplication::findOrFail($id);
        return response()->json(['ok' => true, 'application' => $app]);
    }

    // Staff: create application (registration)
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'applicant_name' => ['required','string','max:255'],
            'address' => ['nullable','string','max:255'],
            'contact_no' => ['nullable','string','max:50'],
            'documents' => ['nullable','array'],
        ]);
        $app = CustomerApplication::create([
            'applicant_name' => $request->string('applicant_name'),
            'address' => $request->string('address') ?: null,
            'contact_no' => $request->string('contact_no') ?: null,
            'documents' => $request->input('documents') ?: null,
            'status' => 'registered',
            'created_by' => optional($request->user())->id,
        ]);
        return response()->json(['ok' => true, 'application' => $app]);
    }

    // Staff: record site inspection
    public function inspection(Request $request, $id): JsonResponse
    {
        $request->validate([
            'inspection_date' => ['required','date'],
            'inspection_notes' => ['nullable','string'],
        ]);
        $app = CustomerApplication::findOrFail($id);
        abort_unless($request->user(), 401);
        if (!in_array($app->status, ['registered','inspected'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid status for inspection'], 422);
        }
        $app->inspection_date = $request->date('inspection_date');
        $app->inspection_notes = $request->string('inspection_notes') ?: null;
        $app->inspected_by = $request->user()->id;
        $app->status = 'inspected';
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }

    // Admin: approve application (validation & eligibility for payment)
    public function approve(Request $request, $id): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $app = CustomerApplication::findOrFail($id);

        // Allow approval from registered, inspected, or already-approved states
        if (!in_array($app->status, ['registered', 'inspected', 'approved'], true)) {
            return response()->json(['ok' => false, 'message' => 'Invalid status for approval'], 422);
        }

        // Duplicate application check: same name + address on another active application
        $hasDuplicate = CustomerApplication::query()
            ->where('id', '!=', $app->id)
            ->where('applicant_name', $app->applicant_name)
            ->where('address', $app->address)
            ->whereIn('status', ['registered', 'inspected', 'approved', 'assessed', 'paid', 'scheduled', 'installed'])
            ->exists();

        if ($hasDuplicate) {
            return response()->json([
                'ok' => false,
                'message' => 'Duplicate application detected for this applicant and address.',
            ], 422);
        }

        // Unpaid billing history check: any customer with same name & address having problematic bill status
        $hasUnpaidHistory = BillingRecord::query()
            ->whereHas('customer', function ($q) use ($app) {
                $q->where('name', $app->applicant_name)
                  ->where('address', $app->address);
            })
            ->whereIn('bill_status', ['Outstanding Payment', 'Overdue', 'Notice of Disconnection', 'Disconnected'])
            ->exists();

        if ($hasUnpaidHistory) {
            return response()->json([
                'ok' => false,
                'message' => 'Applicant has a history of unpaid or disconnected bills.',
            ], 422);
        }

        // Passed validation: mark as approved (eligible for assessment & payment)
        $app->approved_by = $request->user()->id;
        $app->approved_at = now();
        $app->status = 'approved';
        $app->save();

        return response()->json(['ok' => true, 'application' => $app]);
    }

    // Admin/Cashier/Staff: assess fees
    public function assess(Request $request, $id): JsonResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin','cashier','staff'], true), 403);
        $request->validate([
            'fee_application' => ['nullable','numeric','min:0'],
            'fee_inspection' => ['nullable','numeric','min:0'],
            'fee_materials' => ['nullable','numeric','min:0'],
            'fee_labor' => ['nullable','numeric','min:0'],
            'meter_deposit' => ['nullable','numeric','min:0'],
        ]);
        $app = CustomerApplication::findOrFail($id);
        if (!in_array($app->status, ['approved','assessed'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid status for assessment'], 422);
        }
        $app->fee_application = (float) $request->input('fee_application', 0);
        $app->fee_inspection = (float) $request->input('fee_inspection', 0);
        $app->fee_materials = (float) $request->input('fee_materials', 0);
        $app->fee_labor = (float) $request->input('fee_labor', 0);
        $app->meter_deposit = (float) $request->input('meter_deposit', 0);
        $app->fee_total = $app->fee_application + $app->fee_inspection + $app->fee_materials + $app->fee_labor + $app->meter_deposit;
        $app->status = 'assessed';
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }

    // Admin/Cashier/Staff: mark payment
    public function pay(Request $request, $id): JsonResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin','cashier','staff'], true), 403);

        $data = $request->validate([
            'amount_due' => ['required','numeric','min:0'],
            'amount_tendered' => ['required','numeric','min:0'],
            'change_given' => ['nullable','numeric','min:0'],
            'fee_items' => ['required','array','min:1'],
            'fee_items.*.code' => ['required','string','max:50'],
            'fee_items.*.name' => ['required','string','max:150'],
            'fee_items.*.amount' => ['required','numeric','min:0'],
        ]);

        $application = CustomerApplication::findOrFail($id);

        if (!in_array($application->status, ['approved', 'assessed', 'waiting_payment'], true)) {
            return response()->json(['ok' => false, 'message' => 'Invalid status for payment'], 422);
        }

        if ($application->status === 'paid') {
            return response()->json(['ok' => false, 'message' => 'Application already marked as paid.'], 422);
        }

        $amountDue = round((float) $data['amount_due'], 2);
        $amountTendered = round((float) $data['amount_tendered'], 2);
        if ($amountTendered < $amountDue) {
            return response()->json(['ok' => false, 'message' => 'Amount tendered is insufficient.'], 422);
        }

        $feeBreakdown = collect($data['fee_items'] ?? [])->map(function ($item) {
            return [
                'code' => (string) $item['code'],
                'name' => (string) $item['name'],
                'amount' => round((float) $item['amount'], 2),
            ];
        })->values();

        $calculatedDue = round($feeBreakdown->sum('amount'), 2);
        if ($calculatedDue <= 0) {
            return response()->json(['ok' => false, 'message' => 'Fee totals must be greater than zero.'], 422);
        }

        if (abs($calculatedDue - $amountDue) > 0.01) {
            return response()->json(['ok' => false, 'message' => 'Subtotal mismatch detected. Please refresh and try again.'], 422);
        }

        $change = max(0, round($amountTendered - $amountDue, 2));
        if (isset($data['change_given']) && abs($change - round((float) $data['change_given'], 2)) > 0.01) {
            return response()->json(['ok' => false, 'message' => 'Change computation mismatch.'], 422);
        }

        $invoiceNumber = sprintf('APP-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(4)));

        $record = DB::transaction(function () use ($application, $amountDue, $amountTendered, $change, $feeBreakdown, $invoiceNumber, $request) {
            $application->fee_application = $amountDue;
            $application->fee_inspection = 0.0;
            $application->fee_total = $amountDue;
            $application->payment_receipt_no = $invoiceNumber;
            $application->paid_at = now();
            $application->paid_by = optional($request->user())->id;
            $application->status = 'paid';
            $application->save();

            return ApplicantPaymentRecord::create([
                'customer_application_id' => $application->id,
                'invoice_number' => $invoiceNumber,
                'amount_due' => $amountDue,
                'amount_tendered' => $amountTendered,
                'change_given' => $change,
                'fee_breakdown' => $feeBreakdown->toArray(),
                'processed_by' => optional($request->user())->id,
            ]);
        });

        $application->refresh();

        return response()->json([
            'ok' => true,
            'application' => $application,
            'invoice_number' => $invoiceNumber,
            'change' => $change,
            'applicant_payment_record_id' => $record->id,
        ]);
    }

    // Staff: schedule installation
    public function schedule(Request $request, $id): JsonResponse
    {
        abort_unless($request->user(), 401);
        $request->validate([
            'schedule_date' => ['required','date']
        ]);
        $app = CustomerApplication::findOrFail($id);
        if (!in_array($app->status, ['paid','scheduled'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid status for scheduling'], 422);
        }
        $app->schedule_date = $request->date('schedule_date');
        $app->scheduled_by = $request->user()->id;
        $app->status = 'scheduled';
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }

    // Staff: log meter details prior to installation
    public function meterDetails(Request $request, $id): JsonResponse
    {
        abort_unless($request->user(), 401);
        $data = $request->validate([
            'meter_no' => ['required','string','max:100'],
            'meter_size' => ['nullable','string','max:50'],
            'notes' => ['nullable','string','max:1000'],
        ]);

        $app = CustomerApplication::with('customer')->findOrFail($id);
        if (!in_array($app->status, ['scheduled', 'installing'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Meter details can only be logged for scheduled installations.',
            ], 422);
        }

        $meterNo = trim($data['meter_no']);
        $customer = $app->customer;
        $duplicateMeter = Customer::query()
            ->where('meter_no', $meterNo)
            ->when($customer, function ($qb) use ($customer) {
                $qb->where('id', '!=', $customer->id);
            })
            ->exists();
        if ($duplicateMeter) {
            return response()->json([
                'ok' => false,
                'message' => 'Meter number already assigned to another customer.',
            ], 422);
        }

        $docs = $app->documents ?? [];
        $docs['assigned_meter_no'] = $meterNo;
        $docs['assigned_meter_size'] = $data['meter_size'] ?? null;
        if (!empty($data['notes'])) {
            $docs['installation_notes'] = $data['notes'];
        }
        $docs['meter_details_logged_at'] = now()->toISOString();

        $app->documents = $docs;
        $app->status = 'installing';
        $app->save();

        return response()->json(['ok' => true, 'application' => $app]);
    }

    // Staff: mark installed
    public function install(Request $request, $id): JsonResponse
    {
        abort_unless($request->user(), 401);
        $app = CustomerApplication::with('customer')->findOrFail($id);
        if (!in_array($app->status, ['installing', 'installed'], true)) {
            return response()->json(['ok' => false, 'message' => 'Invalid status for installation'], 422);
        }
        $docs = $app->documents ?? [];
        $meterNo = $docs['assigned_meter_no'] ?? null;
        $meterSize = $docs['assigned_meter_size'] ?? null;

        if (!$meterNo) {
            return response()->json([
                'ok' => false,
                'message' => 'Meter details must be recorded before marking installation complete.',
            ], 422);
        }

        $customer = $app->customer;

        DB::transaction(function () use ($app, $request, $meterNo, $meterSize, &$customer, &$docs) {
            $app->installed_at = now();
            $app->installed_by = $request->user()->id;
            $app->status = 'installed';
            $docs['installation_completed_at'] = $app->installed_at->toISOString();
            $app->documents = $docs;

            if (!$customer) {
                $customer = Customer::query()
                    ->where('name', $app->applicant_name)
                    ->where('address', $app->address)
                    ->first();

                if (!$customer) {
                    $generator = new \App\Services\AccountNumberGenerator();
                    $accountNo = $generator->next();

                    $customer = Customer::create([
                        'account_no' => $accountNo,
                        'name' => $app->applicant_name,
                        'address' => $app->address,
                        'contact_no' => $app->contact_no,
                        'status' => 'Pending',
                        'previous_reading' => 0,
                    ]);
                }

                if ($customer) {
                    $app->customer_id = $customer->id;
                }
            }

            if ($customer) {
                $customer->meter_no = $meterNo;
                if (!empty($meterSize)) {
                    $customer->meter_size = $meterSize;
                }
                $customer->status = 'Active';
                $customer->save();
            }

            $app->save();
        });

        return response()->json(['ok' => true, 'application' => $app->fresh('customer')]);
    }
}
