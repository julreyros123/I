<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function billing()
    {
        return view('records.billing'); // Points to resources/views/records/billing.blade.php
    }

    public function payments()
    {
        return view('records.payments'); // Points to resources/views/records/payments.blade.php
    }

    public function reports()
    {
        return view('records.reports'); // Points to resources/views/records/reports.blade.php
    }

    public function history()
    {
        return view('records.history'); // Points to resources/views/records/history.blade.php
    }
}