<?php
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CustomerApplication;
use Illuminate\Support\Str;

$assignmentOptions = CustomerApplication::query()
    ->whereIn('status', ['scheduled', 'installing', 'Scheduled', 'Installing'])
    ->with(['customer:id,name,account_no,address,meter_no,status'])
    ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
    ->orderBy('schedule_date')
    ->limit(50)
    ->get(['id', 'customer_id', 'applicant_name', 'address', 'status', 'schedule_date'])
    ->filter(function ($app) {
        $customer = $app->customer;
        $customerId = $customer?->id ?? $app->customer_id;
        if (!$customerId) {
            return false;
        }
        if ($customer) {
            if (!empty($customer->meter_no)) {
                return false;
            }
            $hasAssignment = $customer->meterAssignments()->whereNull('unassigned_at')->exists();
            if ($hasAssignment) {
                return false;
            }
        }
        return true;
    })
    ->map(function ($app) {
        $customer = $app->customer;
        $customerId = $customer?->id ?? $app->customer_id;
        $status = Str::lower((string) ($customer?->status ?? ''));
        if (in_array($status, ['active','validated','approved'], true)) {
            return null;
        }
        return [
            'application_id' => $app->id,
            'customer_id' => $customerId,
            'customer_name' => $customer?->name ?? $app->applicant_name,
            'account_no' => $customer?->account_no,
            'address' => $customer?->address ?? $app->address,
            'scheduled_for' => optional($app->schedule_date)->format('Y-m-d'),
            'status' => $app->status,
            'customer_status' => $customer?->status,
        ];
    })
    ->filter()
    ->values();

foreach ($assignmentOptions as $option) {
    echo json_encode($option, JSON_UNESCAPED_SLASHES).PHP_EOL;
}

echo 'Total options: '.$assignmentOptions->count().PHP_EOL;
