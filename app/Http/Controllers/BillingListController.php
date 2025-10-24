<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billing; // make sure you have this model

class BillingListController extends Controller
{
    public function index()
    {
        // Example: fetch all bills or latest bills
        $bills = Billing::latest()->paginate(10);

        return view('billing-list.index', compact('bills'));
    }
}

