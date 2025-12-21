<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Meter;
use App\Models\MeterAssignment;
use App\Models\MeterAudit;
use App\Models\Customer;
use App\Models\CustomerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Application\Meter\UseCases\ListMetersUseCase;
use App\Application\Meter\UseCases\CreateMeterUseCase;
use App\Application\Meter\DTO\ListMetersQuery;
use App\Application\Meter\DTO\CreateMeterCommand;
use App\Http\Requests\Meter\StoreMeterRequest;
use Illuminate\Support\Str;

class MeterController extends Controller
{
    public function __construct(
        private ListMetersUseCase $listMeters,
        private CreateMeterUseCase $createMeter,
    ) {}

    public function index(Request $request)
    {
        $result = $this->listMeters->handle(new ListMetersQuery(
            q: $request->get('q'),
            status: $request->get('status'),
            type: $request->get('type'),
            barangay: $request->get('barangay'),
            perPage: 15,
            scope: $request->get('scope', 'eligible'),
        ));

        $inventoryMeters = Meter::query()
            ->where('status', 'inventory')
            ->orderBy('serial')
            ->get(['id', 'serial', 'type', 'size', 'manufacturer', 'seal_no']);

        $installationQueue = CustomerApplication::query()
            ->with(['customer:id,name,account_no,meter_no,meter_size'])
            ->whereIn('status', ['paid', 'Paid', 'scheduled', 'Scheduled', 'installing', 'Installing'])
            ->orderByRaw("CASE WHEN status = 'paid' THEN 0 ELSE 1 END")
            ->orderBy('schedule_date')
            ->limit(25)
            ->get(['id', 'applicant_name', 'address', 'status', 'schedule_date', 'customer_id']);

        $assignmentOptions = CustomerApplication::query()
            ->whereIn('status', ['scheduled', 'installing', 'Scheduled', 'Installing'])
            ->with(['customer:id,name,account_no,address,meter_no'])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('schedule_date')
            ->limit(50)
            ->get(['id', 'customer_id', 'applicant_name', 'address', 'status', 'schedule_date'])
            ->filter(function ($app) {
                $customer = $app->customer;
                $customerId = $customer?->id ?? $app->customer_id;
                if (!$customerId) {
                    return false;
                }
                if ($customer) {
                    if (!empty($customer->meter_no)) {
                        return false;
                    }

                    $hasAssignment = $customer->meterAssignments()
                        ->whereNull('unassigned_at')
                        ->exists();

                    if ($hasAssignment) {
                        return false;
                    }
                }

                return true;
            })
            ->map(function ($app) {
                $customer = $app->customer;
                $customerId = $customer?->id ?? $app->customer_id;
                $status = Str::lower((string) ($customer?->status ?? ''));
                if (in_array($status, ['active','validated','approved'], true)) {
                    return null;
                }
                return [
                    'application_id' => $app->id,
                    'customer_id' => $customerId,
                    'customer_name' => $customer?->name ?? $app->applicant_name,
                    'account_no' => $customer?->account_no,
                    'address' => $customer?->address ?? $app->address,
                    'scheduled_for' => optional($app->schedule_date)->format('M d, Y'),
                    'status' => $app->status,
                ];
            })
            ->filter()
            ->filter(function ($option) {
                return !empty($option['application_id']);
            })
            ->values();

        $assignmentCustomerIds = $assignmentOptions->pluck('customer_id')->filter()->unique()->all();

        $recentCustomers = Customer::query()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id','name','account_no','address','status'])
            ->filter(function ($customer) {
                $status = Str::lower((string) ($customer->status ?? ''));
                return !in_array($status, ['active','validated','approved'], true);
            })
            ->reject(function ($customer) use ($assignmentCustomerIds) {
                return in_array($customer->id, $assignmentCustomerIds, true);
            })
            ->values();

        return view('admin.meters', array_merge($result, [
            'inventoryMeters' => $inventoryMeters,
            'installationQueue' => $installationQueue,
            'assignmentOptions' => $assignmentOptions,
            'recentCustomers' => $recentCustomers,
        ]));
    }

    public function store(StoreMeterRequest $request)
    {
        $data = $request->validated();
        $meter = $this->createMeter->handle(
            new CreateMeterCommand($data),
            optional(auth()->user())->id
        );

        ActivityLog::create([
            'user_id' => optional(auth()->user())->id,
            'module' => 'Meters',
            'action' => 'METER_ADDED',
            'description' => sprintf('Added meter %s to inventory', $meter->serial ?? 'unknown'),
            'target_type' => Meter::class,
            'target_id' => $meter->id ?? null,
            'meta' => [
                'serial' => $meter->serial ?? null,
                'status' => $meter->status ?? null,
                'size' => $meter->size ?? null,
                'type' => $meter->type ?? null,
                'manufacturer' => $meter->manufacturer ?? null,
                'seal_no' => $meter->seal_no ?? null,
            ],
        ]);

        return redirect()->back()->with('success','Meter created.');
    }

    public function update(Request $request, Meter $meter)
    {
        $data = $request->validate([
            'type' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'seal_no' => 'nullable|string|max:255',
            'status' => 'required|in:inventory,installed,active,maintenance,inactive,retired',
            'install_date' => 'nullable|date',
            'location_address' => 'nullable|string|max:1000',
            'barangay' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $before = $meter->getOriginal();
        $meter->update($data);
        MeterAudit::create([
            'meter_id' => $meter->id,
            'action' => 'update',
            'changed_by' => optional(auth()->user())->id,
            'from_json' => $before,
            'to_json' => $meter->fresh()->toArray(),
        ]);
        return redirect()->back()->with('success','Meter updated.');
    }

    public function destroy(Meter $meter)
    {
        MeterAudit::create([
            'meter_id' => $meter->id,
            'action' => 'delete',
            'changed_by' => optional(auth()->user())->id,
            'from_json' => $meter->toArray(),
        ]);
        $meter->delete();
        return redirect()->back()->with('success','Meter deleted.');
    }

    public function assign(Request $request, Meter $meter)
    {
        $data = $request->validate([
            'account_id' => 'nullable|integer|exists:customers,id',
            'application_id' => 'nullable|integer|exists:customer_applications,id',
            'assigned_at' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Resolve customer/application context
        $customer = null;
        $targetApp = null;

        if (!empty($data['account_id'])) {
            $customer = Customer::find($data['account_id']);
        }

        if (!empty($data['application_id'])) {
            $targetApp = CustomerApplication::with('customer')->find($data['application_id']);
            if ($targetApp && $targetApp->customer) {
                $customer = $customer ?: $targetApp->customer;
            }
        }

        if (!$customer && $targetApp && $targetApp->customer) {
            $customer = $targetApp->customer;
        }

        if (!$customer) {
            return back()->withErrors([
                'account_id' => 'Select a customer or ensure the scheduled application is linked to a verified customer account.',
            ])->withInput();
        }

        $data['account_id'] = $customer->id;

        if (!$targetApp) {
            $targetApp = CustomerApplication::where('customer_id', $customer->id)
                ->orderByDesc('created_at')
                ->first();
        }

        if (!$targetApp) {
            return back()->withErrors([
                'account_id' => 'Cannot assign meter: no eligible application was found for this customer.',
            ])->withInput();
        }

        $customerStatus = Str::lower((string) ($customer->status ?? ''));
        if (in_array($customerStatus, ['active','validated','approved'], true)) {
            return back()->withErrors([
                'account_id' => 'Meters can only be assigned to newly registered customers awaiting validation.',
            ])->withInput();
        }

        $alreadyMetered = !empty($customer->meter_no)
            || MeterAssignment::query()
                ->where('account_id', $customer->id)
                ->whereNull('unassigned_at')
                ->exists();

        if ($alreadyMetered) {
            $message = 'This customer already has a meter assigned. Unassign it first before linking a new one.';
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'code' => 'CUSTOMER_ALREADY_METERED',
                ], 422);
            }

            return back()->withErrors([
                'account_id' => $message,
            ])->withInput();
        }

        $hasPaid = !is_null($targetApp->paid_at);
        $stageOk = in_array($targetApp->status, ['paid', 'scheduled', 'installed'], true);

        if (!($hasPaid && $stageOk)) {
            return back()->withErrors([
                'account_id' => 'Cannot assign meter: application fees are not fully paid or application is not yet in a paid state.',
            ])->withInput();
        }

        DB::transaction(function() use ($meter, $data, $targetApp) {
            MeterAssignment::where('meter_id',$meter->id)->whereNull('unassigned_at')->update([
                'unassigned_at' => now(),
                'unassigned_by' => optional(auth()->user())->id,
            ]);
            MeterAssignment::create([
                'meter_id' => $meter->id,
                'account_id' => $data['account_id'],
                'assigned_at' => $data['assigned_at'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'assigned_by' => optional(auth()->user())->id,
            ]);
            $before = $meter->toArray();
            $meter->update([
                'current_account_id' => $data['account_id'],
                'status' => 'active',
                'install_date' => $meter->install_date ?? now(),
            ]);
            MeterAudit::create([
                'meter_id' => $meter->id,
                'action' => 'assign',
                'changed_by' => optional(auth()->user())->id,
                'from_json' => $before,
                'to_json' => $meter->fresh()->toArray(),
                'reason' => $data['reason'] ?? null,
            ]);

            // Also update the linked customer with meter details and activate them
            $customer = Customer::find($data['account_id']);
            if ($customer) {
                $customer->meter_no = $meter->serial;
                $customer->meter_size = $meter->size;
                $customer->status = 'Active';
                $customer->save();
            }

            if ($targetApp) {
                $docs = is_array($targetApp->documents) ? $targetApp->documents : [];
                $docs['assigned_meter_no'] = $meter->serial;
                $docs['assigned_meter_size'] = $meter->size;
                $docs['installation_completed_at'] = now()->toISOString();

                $targetApp->documents = $docs;
                $targetApp->status = 'installed';
                $targetApp->installed_at = now();
                $targetApp->installed_by = optional(auth()->user())->id;
                $targetApp->save();
            }

        });

        return redirect()->back()->with('success','Meter assigned.');
    }

    public function unassign(Request $request, Meter $meter)
    {
        $data = $request->validate([
            'unassigned_at' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function() use ($meter, $data) {
            MeterAssignment::where('meter_id',$meter->id)->whereNull('unassigned_at')->update([
                'unassigned_at' => $data['unassigned_at'],
                'unassigned_by' => optional(auth()->user())->id,
            ]);
            $before = $meter->toArray();
            $meter->update(['current_account_id' => null, 'status' => 'installed']);
            MeterAudit::create([
                'meter_id' => $meter->id,
                'action' => 'unassign',
                'changed_by' => optional(auth()->user())->id,
                'from_json' => $before,
                'to_json' => $meter->fresh()->toArray(),
                'reason' => $data['reason'] ?? null,
            ]);
        });

        return redirect()->back()->with('success','Meter unassigned.');
    }

    public function export(Request $request)
    {
        $query = Meter::query();
        if ($s = $request->get('q')) {
            $query->where(function($q) use ($s) {
                $q->where('serial','like','%'.$s.'%')
                  ->orWhere('location_address','like','%'.$s.'%')
                  ->orWhere('barangay','like','%'.$s.'%');
            });
        }
        if ($status = $request->get('status')) { $query->where('status',$status); }
        if ($type = $request->get('type')) { $query->where('type',$type); }
        if ($brgy = $request->get('barangay')) { $query->where('barangay',$brgy); }

        $filename = 'meters_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $columns = ['serial','status','type','size','barangay','location_address','last_reading_value','last_reading_at','current_account_id'];
        $callback = function() use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            $query->chunk(100, function($rows) use ($out, $columns) {
                foreach ($rows as $r) {
                    $row = [];
                    foreach ($columns as $c) { $row[] = data_get($r, $c); }
                    fputcsv($out, $row);
                }
            });
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function apiIndex(Request $request)
    {
        $q = Meter::query();
        if ($status = $request->get('status')) {
            $q->where('status', $status);
        }
        if ($s = $request->get('q')) {
            $q->where(function($w) use ($s){
                $w->where('serial','like','%'.$s.'%')
                  ->orWhere('location_address','like','%'.$s.'%')
                  ->orWhere('barangay','like','%'.$s.'%');
            });
        }
        $q->orderBy('serial');
        $meters = $q->limit(100)->get(['id','serial','size','type','status']);
        return response()->json([ 'items' => $meters ]);
    }

    public function apiCurrentByAccount(Request $request)
    {
        $accountId = $request->get('account_id');
        if (!$accountId) {
            return response()->json(['item' => null]);
        }
        $meter = Meter::where('current_account_id', $accountId)
            ->orderByDesc('updated_at')
            ->first(['id','serial','size','type','status','current_account_id']);
        return response()->json(['item' => $meter]);
    }
}
