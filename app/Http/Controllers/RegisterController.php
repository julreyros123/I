<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class RegisterController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('account_no')->limit(20)->get();
        return view('register.index', compact('customers'));
    }

     public function new()
    {
        return view('register.new');
    }
}