<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Register;
use App\Services\AccountNumberGenerator;
use App\Services\ScoringService;
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
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            // KYC
            'id_type' => 'required|string|max:50',
            'id_number' => 'required|string|max:100',
            // Optional KYC images; if provided, enforce sane dimensions and types
            'id_front' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=400,min_height=250',
            'id_back' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=400,min_height=250',
            'selfie' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=400,min_height=250',
            'consent' => 'accepted',
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'barangay.required' => 'Barangay is required.',
            'city.required' => 'City is required.',
            'province.required' => 'Province is required.',
            'id_type.required' => 'ID type is required.',
            'id_number.required' => 'ID number is required.',
            'id_front.required' => 'ID front image is required.',
            'id_back.required' => 'ID back image is required.',
            'selfie.required' => 'Selfie is required.',
            'consent.accepted' => 'Please confirm consent to proceed.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate account number (AA-XXXXXX-R)
            $generator = new AccountNumberGenerator();
            $accountNo = $generator->next();

            // Compose full address from components
            $address = trim(implode(', ', array_filter([
                trim((string)$request->barangay),
                trim((string)$request->city),
                trim((string)$request->province),
            ])));

            // Create customer record (meter fields deferred until after verification)
            $customer = Customer::create([
                'account_no' => $accountNo,
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'address' => $address,
                'status' => 'Pending',
                'previous_reading' => 0,
            ]);

            // Create register record
            Register::create([
                'account_no' => $accountNo,
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'address' => $address,
                'contact_no' => $request->contact_number ? trim($request->contact_number) : null,
                'connection_classification' => 'Residential',
                'status' => 'Pending',
            ]);

            // Store KYC documents when provided (optional uploads)
            $disk = 'public';
            $base = 'kyc';
            $frontPath = $request->file('id_front') ? $request->file('id_front')->store($base, $disk) : null;
            $backPath = $request->file('id_back') ? $request->file('id_back')->store($base, $disk) : null;
            $selfiePath = $request->file('selfie') ? $request->file('selfie')->store($base, $disk) : null;

            // Create Customer Application record (workflow starts at registered)
            $application = \App\Models\CustomerApplication::create([
                'customer_id' => $customer->id,
                'applicant_name' => trim($request->first_name . ' ' . $request->last_name),
                'address' => $address,
                'contact_no' => $request->contact_number ? trim($request->contact_number) : null,
                'status' => 'registered',
                'documents' => [
                    'id_type' => $request->string('id_type'),
                    'id_number' => $request->string('id_number'),
                    'id_front' => $frontPath,
                    'id_back' => $backPath,
                    'selfie' => $selfiePath,
                    'consent' => (bool) $request->boolean('consent'),
                ],
                'created_by' => optional($request->user())->id,
            ]);

            // Auto-score the application
            try {
                $scorer = new ScoringService();
                $res = $scorer->score($application);
                $application->score = $res['score'] ?? null;
                $application->score_breakdown = $res['breakdown'] ?? null;
                $application->risk_level = $res['risk_level'] ?? null;
                if (($res['score'] ?? 0) >= 80) {
                    $application->decision = 'approve';
                } elseif (($res['score'] ?? 0) >= 60) {
                    $application->decision = 'review';
                } else {
                    $application->decision = 'reject';
                }
                $application->save();
            } catch (\Throwable $t) {
                Log::warning('Auto-score failed: '.$t->getMessage());
            }

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

    // Account numbers now generated via AccountNumberGenerator service
}