<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        ));
        return view('admin.meters', $result);
    }

    public function store(StoreMeterRequest $request)
    {
        $data = $request->validated();
        $this->createMeter->handle(
            new CreateMeterCommand($data),
            optional(auth()->user())->id
        );
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
            'account_id' => 'required|integer|exists:customers,id',
            'assigned_at' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Guard: only allow meter assignment when latest application fees are paid
        $customer = Customer::find($data['account_id']);
        if ($customer) {
            $app = CustomerApplication::where('customer_id', $customer->id)
                ->orderByDesc('created_at')
                ->first();

            $feeTotal = $app?->fee_total ?? 0;
            $hasPaid = $app && $feeTotal > 0 && !is_null($app->paid_at);
            $stageOk = $app && in_array($app->status, ['paid', 'scheduled', 'installed'], true);

            if (!($hasPaid && $stageOk)) {
                return back()->withErrors([
                    'account_id' => 'Cannot assign meter: application fees are not fully paid or application is not yet in a paid/scheduled/installed state.',
                ])->withInput();
            }
        }

        DB::transaction(function() use ($meter, $data) {
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
            $meter->update(['current_account_id' => $data['account_id'], 'status' => 'active']);
            MeterAudit::create([
                'meter_id' => $meter->id,
                'action' => 'assign',
                'changed_by' => optional(auth()->user())->id,
                'from_json' => $before,
                'to_json' => $meter->fresh()->toArray(),
                'reason' => $data['reason'] ?? null,
            ]);
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

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:meters,id',
            'status' => 'required|in:inventory,installed,active,maintenance,inactive,retired',
        ]);
        $count = 0;
        foreach ($data['ids'] as $id) {
            $m = Meter::find($id);
            if (!$m) continue;
            $before = $m->toArray();
            $m->update(['status' => $data['status']]);
            MeterAudit::create([
                'meter_id' => $m->id,
                'action' => 'status_change',
                'changed_by' => optional(auth()->user())->id,
                'from_json' => $before,
                'to_json' => $m->fresh()->toArray(),
                'reason' => 'bulk',
            ]);
            $count++;
        }
        return redirect()->back()->with('success', "Updated status for $count meters.");
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $file = $request->file('file');
        $h = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($h);
        $created = 0; $updated = 0;
        while (($row = fgetcsv($h)) !== false) {
            $data = array_combine($header, $row);
            if (!isset($data['serial']) || empty($data['serial'])) continue;
            $meter = Meter::firstOrNew(['serial' => $data['serial']]);
            $before = $meter->exists ? $meter->toArray() : null;
            $meter->fill([
                'status' => $data['status'] ?? $meter->status ?? 'inventory',
                'type' => $data['type'] ?? null,
                'size' => $data['size'] ?? null,
                'barangay' => $data['barangay'] ?? null,
                'location_address' => $data['location_address'] ?? null,
            ]);
            $meter->save();
            MeterAudit::create([
                'meter_id' => $meter->id,
                'action' => $before ? 'update' : 'import',
                'changed_by' => optional(auth()->user())->id,
                'from_json' => $before,
                'to_json' => $meter->fresh()->toArray(),
            ]);
            $meter->wasRecentlyCreated ? $created++ : $updated++;
        }
        fclose($h);
        return redirect()->back()->with('success', "Import complete. Created: $created, Updated: $updated");
    }

    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="meters_template.csv"',
        ];
        $columns = ['serial','status','type','size','barangay','location_address'];
        $callback = function() use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
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
