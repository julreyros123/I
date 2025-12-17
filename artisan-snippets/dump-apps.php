<?php
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CustomerApplication;

$apps = CustomerApplication::with('customer')
    ->orderByDesc('created_at')
    ->limit(20)
    ->get();

foreach ($apps as $app) {
    $cust = $app->customer;
    echo 'id='.$app->id
        .' status='.$app->status
        .' schedule='.$app->schedule_date
        .' customer_id='.$app->customer_id
        .' has_customer='.($cust ? 'yes' : 'no')
        .' customer_status='.($cust ? $cust->status : 'NULL')
        .' applicant='.$app->applicant_name
        .PHP_EOL;
}
