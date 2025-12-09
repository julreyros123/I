<?php

namespace App\Http\Controllers;

use App\Mail\ReportSubmitted;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'category' => 'nullable|string|in:UI bug,Delay issue,Billing problem,Water leakage,No water,Other',
            'other_problem' => 'nullable|string|max:255',
        ]);

        $category = $request->input('category');
        $other = $request->input('other_problem');
        if ($category !== 'Other') {
            $other = null; // only store other_problem when category is Other
        }

        $report = Report::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'category' => $category,
            'other_problem' => $other,
            'status' => 'open',
            'is_priority' => false,
        ]);

        Mail::to('admin@example.com')->send(new ReportSubmitted($report));

        return back()->with('success', 'Your issue has been reported. Thank you!');
    }
}
