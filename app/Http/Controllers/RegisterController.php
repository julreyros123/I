<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Register;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $customers = Customer::active()
            ->when($search, function ($query, $search) {
                return $query->search($search);
            })
            ->orderBy('account_no')
            ->limit(20)
            ->get();
            
        return view('register.index', compact('customers', 'search'));
    }

    // API endpoint for dynamic search
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        
        $customers = Customer::active()
            ->when($search, function ($query, $search) {
                return $query->search($search);
            })
            ->orderBy('account_no')
            ->limit(20)
            ->get();
            
        return response()->json([
            'customers' => $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'account_no' => $customer->account_no,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'meter_no' => $customer->meter_no,
                    'meter_size' => $customer->meter_size,
                    'status' => $customer->status,
                ];
            })
        ]);
    }

    public function new()
    {
        return view('register.new');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_number' => 'nullable|string|max:50',
            'start_date' => 'required|date|before_or_equal:today',
            'meter_no' => 'nullable|string|max:255',
            'meter_size' => 'nullable|string|max:50',
            'classification' => 'required|string|in:Residential,Commercial,Industrial,Agricultural',
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'address.required' => 'Address is required.',
            'start_date.required' => 'Start date is required.',
            'start_date.before_or_equal' => 'Start date cannot be in the future.',
            'classification.required' => 'Connection classification is required.',
            'classification.in' => 'Please select a valid connection classification.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate account number
            $accountNo = $this->generateAccountNumber();

            // Create customer record
            $customer = Customer::create([
                'account_no' => $accountNo,
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'address' => trim($request->address),
                'meter_no' => $request->meter_no ? trim($request->meter_no) : null,
                'meter_size' => $request->meter_size ?: null,
                'status' => 'Active',
                'previous_reading' => 0,
            ]);

            // Create register record
            Register::create([
                'account_no' => $accountNo,
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'address' => trim($request->address),
                'contact_no' => $request->contact_number ? trim($request->contact_number) : null,
                'connection_classification' => $request->classification,
                'status' => 'Active',
            ]);

            DB::commit();

            return redirect()->route('register.index')
                ->with('success', 'Customer registered successfully with account number: ' . $accountNo);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'Failed to register customer: ' . $e->getMessage())->withInput();
        }
    }

    private function generateAccountNumber()
    {
        // Generate a unique account number
        // Format: YYMMDD + random 4 digits
        $prefix = date('ymd');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $accountNo = $prefix . $random;

        // Ensure uniqueness in both customers and register tables
        while (Customer::where('account_no', $accountNo)->exists() || 
               Register::where('account_no', $accountNo)->exists()) {
            $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $accountNo = $prefix . $random;
        }

        return $accountNo;
    }
}