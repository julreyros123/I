<?php

namespace App\Events;

use App\Models\BillingRecord;
use Illuminate\Foundation\Events\Dispatchable;

class BillingRecordCreated
{
    use Dispatchable;

    public function __construct(public BillingRecord $record) {}
}
