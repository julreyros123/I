<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use App\Models\BillingRecord;
use App\Models\Report;

class AdminController extends Controller
{
    public function index()
    {
        // Simple role check; assumes 'role' column on users with value 'admin'
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $stats = [
            'users' => User::count(),
            'customers' => Customer::count(),
            'billings' => BillingRecord::count(),
            'today_billings' => BillingRecord::whereDate('created_at', today())->count(),
        ];

        $recent = BillingRecord::with('customer')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent'));
    }

    public function notices()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        return view('admin.notices');
    }

    public function reports()
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        $reports = Report::with('user')->orderByDesc('created_at')->paginate(20);
        return view('admin.reports', compact('reports'));
    }
}


