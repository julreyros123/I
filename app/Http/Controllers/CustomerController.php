<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Services\AccountNumberGenerator;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('created_at', 'desc')->paginate(20);
        return view('customer.index', compact('customers'));
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
            'meter_no' => $validated['meter_no'] ?? null,
            'meter_size' => $validated['meter_size'] ?? null,
            'status' => $validated['status'] ?? 'Active',
            'previous_reading' => (float)($validated['previous_reading'] ?? 0),
        ]);

        return response()->json(['ok' => true, 'customer' => $customer]);
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