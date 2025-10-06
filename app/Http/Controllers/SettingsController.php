<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // optional, if you want auth protection
    }

    public function index()
    {
        // pass any data you need; for now show a basic view
        return view('settings.index');
    }
}
