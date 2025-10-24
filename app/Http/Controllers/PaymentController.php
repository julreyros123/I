<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Models\BillingRecord;
use App\Models\PaymentRecord;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function index()
    {
        return view('payment.index');
    }

    public function searchCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'account_no' => ['required', 'string', 'max:50']
        ]);

        try {
            $customer = Customer::where('account_no', $request->account_no)->first();
            
            if (!$customer) {
                return response()->json([
                    'error' => 'Customer not found'
                ], 404);
            }

            // Get all unpaid bills for this customer
            $unpaidBills = BillingRecord::where('account_no', $request->account_no)
                ->whereDoesntHave('paymentRecords')
                ->orderBy('created_at', 'asc')
                ->get();

            // Calculate total outstanding amount
            $totalOutstanding = $unpaidBills->sum('total_amount');

            // Get the latest unpaid bill for detailed information
            $latestBill = $unpaidBills->first();

            // Check if customer has any overdue bills (Notice of Disconnection)
            $overdueBills = BillingRecord::where('account_no', $request->account_no)
                ->where('bill_status', 'Notice of Disconnection')
                ->get();

            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'account_no' => $customer->account_no,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'meter_no' => $customer->meter_no,
                    'meter_size' => $customer->meter_size,
                ],
                'unpaid_bills' => $unpaidBills->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'billing_date' => $bill->created_at->format('Y-m-d'),
                        'previous_reading' => $bill->previous_reading,
                        'current_reading' => $bill->current_reading,
                        'consumption' => $bill->consumption_cu_m,
                        'subtotal' => $bill->consumption_cu_m * $bill->base_rate,
                        'maintenance_charge' => $bill->maintenance_charge,
                        'advance_payment' => $bill->advance_payment,
                        'overdue_penalty' => $bill->overdue_penalty,
                        'total_amount' => $bill->total_amount,
                        'bill_status' => $bill->bill_status,
                        'formatted_total' => '₱' . number_format($bill->total_amount, 2),
                        'date_from' => $bill->date_from,
                        'date_to' => $bill->date_to,
                        'base_rate' => $bill->base_rate,
                    ];
                }),
                'latest_bill' => $latestBill ? [
                    'id' => $latestBill->id,
                    'billing_date' => $latestBill->created_at->format('Y-m-d'),
                    'previous_reading' => $latestBill->previous_reading,
                    'current_reading' => $latestBill->current_reading,
                    'consumption' => $latestBill->consumption_cu_m,
                    'subtotal' => $latestBill->consumption_cu_m * $latestBill->base_rate,
                    'maintenance_charge' => $latestBill->maintenance_charge,
                    'advance_payment' => $latestBill->advance_payment,
                    'overdue_penalty' => $latestBill->overdue_penalty,
                    'total_amount' => $latestBill->total_amount,
                    'bill_status' => $latestBill->bill_status,
                    'formatted_total' => '₱' . number_format($latestBill->total_amount, 2),
                    'date_from' => $latestBill->date_from,
                    'date_to' => $latestBill->date_to,
                    'base_rate' => $latestBill->base_rate,
                ] : null,
                'overdue_bills' => $overdueBills->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'bill_status' => $bill->bill_status,
                        'total_amount' => $bill->total_amount,
                        'formatted_total' => '₱' . number_format($bill->total_amount, 2),
                        'billing_date' => $bill->created_at->format('Y-m-d'),
                    ];
                }),
                'total_outstanding' => $totalOutstanding,
                'formatted_total_outstanding' => '₱' . number_format($totalOutstanding, 2),
                'has_overdue' => $overdueBills->isNotEmpty(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to search customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processPayment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_no' => ['required', 'string', 'max:50'],
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $paymentService = new PaymentService();
            $result = $paymentService->processCustomerPayment($data);
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'payment_record_id' => $result['payment_record_id'],
                'payment_details' => $result['payment_details'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getPaymentReceipt($paymentRecordId)
    {
        try {
            $paymentRecord = PaymentRecord::with(['customer', 'billingRecord'])
                ->findOrFail($paymentRecordId);

            return view('payment.receipt', compact('paymentRecord'));
        } catch (\Exception $e) {
            abort(404, 'Payment receipt not found');
        }
    }

    public function printReceipt($paymentRecordId)
    {
        try {
            $paymentRecord = PaymentRecord::with(['customer', 'billingRecord'])
                ->findOrFail($paymentRecordId);

            return view('payment.print-receipt', compact('paymentRecord'));
        } catch (\Exception $e) {
            abort(404, 'Payment receipt not found');
        }
    }
}