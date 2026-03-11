<?php

namespace App\Listeners;

use App\Events\BillingRecordCreated;
use App\Services\BillBackupService;

class BackupBillToS3
{
    public function __construct(protected BillBackupService $service) {}

    public function handle(BillingRecordCreated $event): void
    {
        $this->service->uploadBill($event->record);
    }
}
