<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BillingRecord;
use App\Models\Customer;
use App\Models\PaymentRecord;
use Illuminate\Support\Facades\Schema;

class StaffPortalController extends Controller
{
    public function index()
    {
        // Dashboard KPI stats
        $pendingGenerate = 0;
        $generatedTotal = 0;
        $generatedToday = 0;
        $generatedThisMonth = 0;

        if (Schema::hasColumn('billing_records', 'is_generated')) {
            $pendingGenerate = BillingRecord::where('is_generated', false)->count();

            $generatedQuery = BillingRecord::where('is_generated', true);
            $generatedTotal = (clone $generatedQuery)->count();
            $generatedToday = (clone $generatedQuery)
                ->whereDate('created_at', now()->toDateString())
                ->count();
            $generatedThisMonth = (clone $generatedQuery)
                ->whereBetween('created_at', [now()->startOfMonth(), now()])
                ->count();
        }

        $overdue = BillingRecord::where('bill_status', 'Notice of Disconnection')->count();

        // Newly registered customers (simple: created today / this month)
        $newCustomersToday = Customer::whereDate('created_at', now()->toDateString())->count();
        $newCustomersThisMonth = Customer::whereBetween('created_at', [now()->startOfMonth(), now()])->count();

        $stats = [
            'pending_generate' => $pendingGenerate,
            // keep original key for backwards compatibility
            'generated' => $generatedTotal,
            'generated_total' => $generatedTotal,
            'generated_today' => $generatedToday,
            'generated_month' => $generatedThisMonth,
            'overdue' => $overdue,
            'new_customers_today' => $newCustomersToday,
            'new_customers_month' => $newCustomersThisMonth,
        ];
        // Placeholder: recent staff activity (login/logout)
        $recentActivity = [];

        // Last 7 days activity for staff graph (system-wide for now)
        $labels = [];
        $seriesBills = [];
        $seriesPayments = [];
        $seriesRegistrations = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->format('M d');

            $seriesBills[] = BillingRecord::whereDate('created_at', $day->toDateString())
                ->where('is_generated', true)
                ->count();

            $seriesPayments[] = PaymentRecord::whereDate('created_at', $day->toDateString())
                ->count();

            $seriesRegistrations[] = Customer::whereDate('created_at', $day->toDateString())
                ->count();
        }

        $activity = [
            'labels' => $labels,
            'bills' => $seriesBills,
            'payments' => $seriesPayments,
            'registrations' => $seriesRegistrations,
        ];

        return view('staff-portal', compact('stats', 'recentActivity', 'activity'));
    }
}