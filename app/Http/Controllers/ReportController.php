<?php

namespace App\Http\Controllers;

use App\Mail\ReportSubmitted;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $systemCategories = [
            'UI bug',
            'Delay issue',
            'Billing problem',
            'Login issue',
            'Other (system)',
        ];

        $customerCategories = [
            'Water quality',
            'Service interruption',
            'Meter concern',
            'Billing dispute',
            'Collection or payment',
            'Other (customer)',
        ];

        $allowedReportTypes = ['system', 'customer'];
        $allowedCategories = array_merge($systemCategories, $customerCategories, ['Other']);

        $data = $request->validate([
            'report_type' => ['required', Rule::in($allowedReportTypes)],
            'message' => 'required|string|max:2000',
            'category' => ['required', 'string', Rule::in($allowedCategories)],
            'other_problem' => 'nullable|string|max:255',
            'customer_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $reportType = $data['report_type'];
        $categoryInput = $data['category'];

        $category = $categoryInput === 'Other'
            ? ($reportType === 'system' ? 'Other (system)' : 'Other (customer)')
            : $categoryInput;

        $systemAllowed = array_merge($systemCategories, ['Other (system)']);
        $customerAllowed = array_merge($customerCategories, ['Other (customer)']);

        if ($reportType === 'system') {
            if (!in_array($category, $systemAllowed, true)) {
                throw ValidationException::withMessages([
                    'category' => 'Please choose a valid system issue category.',
                ]);
            }
            $customerReference = null;
        } else {
            if (!in_array($category, $customerAllowed, true)) {
                throw ValidationException::withMessages([
                    'category' => 'Please choose a valid customer complaint category.',
                ]);
            }

            $customerReference = trim((string) ($data['customer_reference'] ?? ''));
            if ($customerReference === '') {
                throw ValidationException::withMessages([
                    'customer_reference' => 'Please provide the account number or name related to the customer complaint.',
                ]);
            }
        }

        $otherRaw = $data['other_problem'] ?? null;
        $other = in_array($category, ['Other (system)', 'Other (customer)'], true)
            ? trim((string) $otherRaw)
            : null;

        $report = Report::create([
            'user_id' => Auth::id(),
            'message' => trim($data['message']),
            'report_type' => $reportType,
            'customer_reference' => $customerReference ?? null,
            'category' => $category,
            'other_problem' => $other ?: null,
            'status' => 'open',
            'is_priority' => false,
        ]);

        Mail::to(config('mail.from.address', 'admin@example.com'))->send(new ReportSubmitted($report));

        return back()->with('success', 'Your issue has been reported. Thank you!');
    }
}
