<?php
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CustomerApplication;

$apps = CustomerApplication::with('customer')
    ->whereIn('status', ['scheduled', 'installing'])
    ->whereNull('customer_id')
    ->orderBy('schedule_date')
    ->get();

echo 'Processing '.$apps->count()." scheduled applications without customer_id".PHP_EOL;

foreach ($apps as $app) {
    $customer = $app->ensureCustomerAccount();
    if ($customer) {
        echo 'Linked application '.$app->id.' to customer '.$customer->id.' ('.$customer->name.")".PHP_EOL;
    } else {
        echo 'WARN: Application '.$app->id.' has no applicant name; skipped'.PHP_EOL;
    }
}
