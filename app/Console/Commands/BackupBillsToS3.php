<?php

namespace App\Console\Commands;

use App\Services\BillBackupService;
use Illuminate\Console\Command;

class BackupBillsToS3 extends Command
{
    protected $signature = 'bills:backup-s3';
    protected $description = 'Upload any pending or failed billing PDFs to S3';

    public function handle(BillBackupService $service): int
    {
        $this->info('Starting S3 bill backup...');

        $result = $service->uploadPending();

        $this->info("Processed: {$result['processed']}");
        $this->info("Success:   {$result['success']}");
        $this->info("Failed:    {$result['failed']}");

        return self::SUCCESS;
    }
}
