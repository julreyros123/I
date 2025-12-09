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

        // Latest newly added customers list for the side card
        $newCustomers = Customer::orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($c) {
                $label = '';
                if ($c->created_at) {
                    $createdAt = $c->created_at;
                    if ($createdAt->isToday()) {
                        $prefix = 'Today ';
                    } elseif ($createdAt->isYesterday()) {
                        $prefix = 'Yesterday ';
                    } else {
                        $prefix = $createdAt->format('M d ') ;
                    }
                    $label = $prefix . $createdAt->format('H:i');
                }

                return [
                    'name' => $c->name,
                    'account_no' => $c->account_no,
                    'created_at' => $label,
                ];
            })
            ->toArray();

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
        // Recent activity feed: combine recent bills, payments, and registrations
        $recentActivityFull = $this->buildRecentActivity();
        $recentActivity = array_slice($recentActivityFull, 0, 6);

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

        return view('staff-portal', compact('stats', 'recentActivity', 'activity', 'newCustomers', 'recentActivityFull'));
    }

    public function activityLog()
    {
        $activityLog = $this->buildRecentActivity(15);

        return view('staff.activity-log', compact('activityLog'));
    }

    protected function buildRecentActivity(int $perTypeLimit = 10): array
    {
        $recentActivity = [];

        $recentBills = BillingRecord::where('is_generated', true)
            ->orderBy('created_at', 'desc')
            ->take($perTypeLimit)
            ->get();

        foreach ($recentBills as $bill) {
            $createdAt = $bill->created_at;
            $recentActivity[] = [
                'type' => 'bill',
                'message' => 'Bill generated for account ' . ($bill->account_no ?? 'N/A') . ' (' . ($bill->bill_status ?? 'Status') . ')',
                'time' => $createdAt ? $createdAt->format('M d, Y H:i') : '',
                'user' => null,
                'timestamp' => $createdAt ? $createdAt->getTimestamp() : 0,
            ];
        }

        $recentPayments = PaymentRecord::orderBy('created_at', 'desc')
            ->take($perTypeLimit)
            ->get();

        foreach ($recentPayments as $payment) {
            $createdAt = $payment->created_at;
            $amount = $payment->amount_paid ?? 0;
            $recentActivity[] = [
                'type' => 'payment',
                'message' => 'Payment of ' . number_format($amount, 2) . ' received for account ' . ($payment->account_no ?? 'N/A'),
                'time' => $createdAt ? $createdAt->format('M d, Y H:i') : '',
                'user' => null,
                'timestamp' => $createdAt ? $createdAt->getTimestamp() : 0,
            ];
        }

        $recentRegistrations = Customer::orderBy('created_at', 'desc')
            ->take($perTypeLimit)
            ->get();

        foreach ($recentRegistrations as $cust) {
            $createdAt = $cust->created_at;
            $recentActivity[] = [
                'type' => 'registration',
                'message' => 'New customer registered: ' . ($cust->name ?? 'Customer') . ' (' . ($cust->account_no ?? 'N/A') . ')',
                'time' => $createdAt ? $createdAt->format('M d, Y H:i') : '',
                'user' => null,
                'timestamp' => $createdAt ? $createdAt->getTimestamp() : 0,
            ];
        }

        usort($recentActivity, function ($a, $b) {
            return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
        });

        if ($perTypeLimit > 0) {
            $recentActivity = array_slice($recentActivity, 0, $perTypeLimit * 3);
        }

        return $recentActivity;
    }
}