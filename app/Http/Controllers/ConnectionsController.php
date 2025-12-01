<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerApplication;
use App\Models\Customer;
use App\Models\BillingRecord;

class ConnectionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = CustomerApplication::query()
            ->when($request->filled('status'), function($qq) use ($request){
                $qq->where('status', $request->string('status'));
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
        $request->validate([
            'payment_receipt_no' => ['required','string','max:100']
        ]);
        $app = CustomerApplication::findOrFail($id);
        if (!in_array($app->status, ['approved', 'assessed'], true)) {
            return response()->json(['ok' => false, 'message' => 'Invalid status for payment'], 422);
        }
        $app->payment_receipt_no = $request->string('payment_receipt_no');
        $app->paid_at = now();
        $app->paid_by = $request->user()->id;
        $app->status = 'paid';
        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
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

    // Staff: mark installed
    public function install(Request $request, $id): JsonResponse
    {
        abort_unless($request->user(), 401);
        $app = CustomerApplication::findOrFail($id);
        if (!in_array($app->status, ['scheduled','installed'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid status for installation'], 422);
        }
        $app->installed_at = now();
        $app->installed_by = $request->user()->id;
        $app->status = 'installed';

        // Ensure application is linked to a Customer so installed applicants can be assigned meters
        if (!$app->customer_id) {
            // Try to find an existing customer by name + address
            $customer = Customer::query()
                ->where('name', $app->applicant_name)
                ->where('address', $app->address)
                ->first();

            if (!$customer) {
                // Generate an account number and create a new customer record
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

        $app->save();
        return response()->json(['ok' => true, 'application' => $app]);
    }
}
