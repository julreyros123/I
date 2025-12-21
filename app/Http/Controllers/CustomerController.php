<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Models\TransferReconnectAudit;
use App\Services\AccountNumberGenerator;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function index()
    {
        $q = trim((string) request()->get('q', ''));
        $status = request()->get('status');

        $customers = Customer::query()
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('account_no', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->when($status, function($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('customer.index', compact('customers', 'q', 'status'));
    }

    /**
     * Show a single customer as JSON (for admin views).
     */
    public function show(Request $request, $id): JsonResponse
    {
        abort_unless($request->user(), 403);
        $customer = Customer::findOrFail($id);

        $transferAuditRecords = TransferReconnectAudit::query()
            ->where('account_no', $customer->account_no)
            ->where('action', 'transfer')
            ->with('performedByUser:id,name')
            ->orderByDesc('performed_at')
            ->orderByDesc('created_at')
            ->get();

        $transferHistory = $transferAuditRecords->map(function (TransferReconnectAudit $audit) {
            return [
                'id' => $audit->id,
                'old_value' => $audit->old_value,
                'new_value' => $audit->new_value,
                'notes' => $audit->notes,
                'performed_at' => optional($audit->performed_at)->toDateTimeString(),
                'performed_by' => optional($audit->performedByUser)->name,
            ];
        })->values();

        $originalOwnerRecord = $transferAuditRecords->last();
        $originalOwner = $originalOwnerRecord
            ? ($originalOwnerRecord->old_value ?: $originalOwnerRecord->new_value)
            : ($customer->name ?? null);

        return response()->json([
            'ok' => true,
            'customer' => $customer,
            'transfer_history' => $transferHistory,
            'original_owner' => $originalOwner,
        ]);
    }

    /**
     * Verify a customer: set status to Active.
     */
    public function verify(Request $request, $id): JsonResponse
    {
        abort_unless($request->user(), 403);
        $customer = Customer::findOrFail($id);
        $customer->status = 'Active';
        $customer->save();
        return response()->json(['ok' => true, 'customer' => $customer, 'message' => 'Customer verified (Active).']);
    }

    /**
     * Duplicate detection by name+address in customers and id_number in customer_applications documents.
     */
    public function duplicates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable','string','max:255'],
            'address' => ['nullable','string','max:255'],
            'id_number' => ['nullable','string','max:100'],
        ]);
        $name = trim((string)($validated['name'] ?? ''));
        $address = trim((string)($validated['address'] ?? ''));
        $idno = trim((string)($validated['id_number'] ?? ''));

        $custMatches = collect();
        if ($name && $address) {
            $custMatches = Customer::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->whereRaw('LOWER(address) = ?', [mb_strtolower($address)])
                ->limit(5)
                ->get(['id','account_no','name','address','status']);
        }

        $appMatches = collect();
        if ($idno) {
            $appMatches = \App\Models\CustomerApplication::query()
                ->where('documents->id_number', $idno)
                ->limit(5)
                ->get(['id','customer_id','applicant_name','address','status','documents']);
        }

        return response()->json([
            'ok' => true,
            'customers' => $custMatches,
            'applications' => $appMatches,
        ]);
    }

    public function attach(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_no' => ['required','string','max:50'],
            'customer_name' => ['required','string','max:255'],
            'previous_reading' => ['nullable','numeric','min:0'],
        ]);

        $customer = Customer::updateOrCreate(
            ['account_no' => $validated['account_no']],
            [
                'name' => $validated['customer_name'],
                'previous_reading' => (float) ($validated['previous_reading'] ?? 0),
                'status' => 'Active',
            ]
        );

        return response()->json([
            'ok' => true,
            'data' => $customer,
            'message' => 'Customer attached to account.'
        ]);
    }

    public function nextAccount(Request $request, AccountNumberGenerator $gen): JsonResponse
    {
        $area = $request->string('area')->toString() ?: null;
        $route = $request->string('route')->toString() ?: null;
        return response()->json(['account_no' => $gen->next($area, $route)]);
    }

    public function findByAccount(Request $request): JsonResponse
    {
        $request->validate(['account_no' => ['required','string','max:50']]);
        $account = $request->string('account_no')->toString();
        $customer = Customer::where('account_no', $account)->first();
        if (!$customer) {
            return response()->json(['ok' => false], 404);
        }
        return response()->json(['ok' => true, 'customer' => $customer]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        abort_unless($request->user() && $request->user()->role === 'admin', 403);
        $validated = $request->validate([
            'name' => ['nullable','string','max:255'],
            'address' => ['nullable','string','max:255'],
            'contact_no' => ['nullable','string','max:50'],
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update(array_filter([
            'name' => $validated['name'] ?? null,
            'address' => $validated['address'] ?? null,
            'contact_no' => $validated['contact_no'] ?? null,
        ], function($v) { return !is_null($v); }));

        return response()->json(['ok' => true, 'customer' => $customer, 'message' => 'Customer updated']);
    }

    public function searchAccounts(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required','string','max:50'],
            'include_all' => ['nullable'],
        ]);
        $query = trim($request->string('q')->toString());

        if ($query === '') {
            return response()->json(['suggestions' => []]);
        }

        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $query);
        $includeAll = $request->boolean('include_all') || optional($request->user())->role === 'admin';

        $customers = Customer::query()
            ->when(!$includeAll, function ($qb) {
                $qb->where('status', 'Active');
            })
            ->where(function ($qb) use ($query, $normalized) {
                $qb->where('account_no', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%");

                if ($normalized !== '' && $normalized !== $query) {
                    $qb->orWhereRaw("REPLACE(REPLACE(account_no,'-',''),' ','') LIKE ?", ["%{$normalized}%"]);
                }
            })
            ->orderByRaw(
                "CASE WHEN name LIKE ? THEN 0 WHEN account_no LIKE ? THEN 1 ELSE 2 END, name ASC",
                ["{$query}%", "{$query}%"]
            )
            ->limit(10)
            ->get(['account_no', 'name', 'address', 'status']);

        return response()->json([
            'suggestions' => $customers->map(function ($customer) {
                return [
                    'account_no' => $customer->account_no,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'status' => $customer->status,
                ];
            })
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_no' => ['required','string','max:50','unique:customers,account_no'],
            'name' => ['required','string','max:255'],
            'address' => ['nullable','string','max:255'],
            'contact_no' => ['nullable','string','max:50'],
            'meter_no' => ['nullable','string','max:100'],
            'meter_size' => ['nullable','string','max:50'],
            'status' => ['nullable','string','max:50'],
            'previous_reading' => ['nullable','numeric','min:0'],
        ]);

        $customer = Customer::create([
            'account_no' => $validated['account_no'],
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'contact_no' => $validated['contact_no'] ?? null,
            'meter_no' => $validated['meter_no'] ?? null,
            'meter_size' => $validated['meter_size'] ?? null,
            'status' => $validated['status'] ?? 'Active',
            'previous_reading' => (float)($validated['previous_reading'] ?? 0),
            'created_by' => optional($request->user())->id,
        ]);

        return response()->json(['ok' => true, 'customer' => $customer]);
    }

    /**
     * Transfer ownership API
     */
    public function transferOwnership(Request $request, \App\Services\TransferOwnershipService $service): JsonResponse
    {
        $validated = $request->validate([
            'account_no' => ['required','string','max:50'],
            'new_name' => ['required','string','max:255'],
            'notes' => ['nullable','string','max:1000'],
        ]);

        $result = $service->transfer($validated['account_no'], $validated['new_name'], $validated['notes'] ?? null);

        if (!$result) {
            return response()->json(['ok' => false, 'message' => 'Account not found'], 404);
        }

        return response()->json(['ok' => true, 'customer' => $result, 'message' => 'Ownership transferred successfully']);
    }

    /**
     * Reconnect service API
     */
    public function reconnectService(Request $request, \App\Services\ReconnectService $service): JsonResponse
    {
        $validated = $request->validate([
            'account_no' => ['required','string','max:50'],
            'notes' => ['nullable','string','max:1000'],
        ]);

        $result = $service->reconnect($validated['account_no'], $validated['notes'] ?? null);

        if (!$result) {
            return response()->json(['ok' => false, 'message' => 'Account not found'], 404);
        }

        return response()->json(['ok' => true, 'customer' => $result, 'message' => 'Service reconnected successfully']);
    }

    /**
     * Staff request for reconnect service (marks customer record for admin follow-up).
     */
    public function requestReconnect(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $customer = Customer::findOrFail($id);

        if (strtolower($customer->status) !== 'disconnected') {
            return response()->json([
                'ok' => false,
                'message' => 'Reconnect request allowed only for disconnected accounts.'
            ], 422);
        }

        if ($customer->reconnect_requested_at) {
            return response()->json([
                'ok' => true,
                'message' => 'Reconnect already requested.',
                'customer' => $customer,
            ]);
        }

        $customer->fill([
            'reconnect_requested_at' => now(),
            'reconnect_requested_by' => $user->id,
        ])->save();

        return response()->json([
            'ok' => true,
            'message' => 'Reconnect request logged for admin review.',
            'customer' => $customer,
        ]);
    }

    // Delete multiple customers
    public function deleteMultiple(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['required', 'integer', 'exists:customers,id'],
        ]);

        try {
            $deletedCount = Customer::whereIn('id', $validated['customer_ids'])->delete();
            
            return response()->json([
                'ok' => true,
                'message' => "Successfully deleted {$deletedCount} customer(s).",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to delete customers: ' . $e->getMessage()
            ], 500);
        }
    }
}