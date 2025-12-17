<?php
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;

$customers = Customer::orderByDesc('created_at')->limit(50)->get(['id','name','status','account_no']);

foreach ($customers as $customer) {
    echo 'id='.$customer->id
        .' name='.$customer->name
        .' status='.$customer->status
        .' account_no='.$customer->account_no
        .PHP_EOL;
}
