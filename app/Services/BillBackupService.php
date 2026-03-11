<?php

namespace App\Services;

use App\Models\BillingRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BillBackupService
{
    /**
     * Verify and mark a single BillingRecord as backed up.
     * The PDF is already stored to S3 by BillPdfService at creation time.
     * This service re-uploads if the file is missing, and tracks backup state.
     */
    public function uploadBill(BillingRecord $record): array
    {
        try {
            if (!$record->pdf_path) {
                // Re-generate and upload the PDF
                $pdfPath = app(BillPdfService::class)->generateAndStore($record);

                if (!$pdfPath) {
                    throw new \Exception("PDF generation failed for billing record #{$record->id}");
                }

                $record->pdf_path = $pdfPath;
            }

            // Check if the file actually exists in S3
            if (!Storage::disk('s3')->exists($record->pdf_path)) {
                // Re-generate and store
                $pdfPath = app(BillPdfService::class)->generateAndStore($record);

                if (!$pdfPath || !Storage::disk('s3')->exists($pdfPath)) {
                    throw new \Exception("Re-upload to S3 failed for billing record #{$record->id}");
                }

                $record->pdf_path = $pdfPath;
            }

            $record->update([
                'pdf_path'      => $record->pdf_path,
                'backed_up_at'  => now(),
                'backup_status' => 'success',
            ]);

            Log::info("BillingRecord #{$record->id} confirmed in S3: {$record->pdf_path}");

            return ['success' => true, 'path' => $record->pdf_path];

        } catch (\Exception $e) {
            Log::error("BillingRecord #{$record->id} S3 backup failed: " . $e->getMessage());

            $record->update(['backup_status' => 'failed']);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Batch-confirm all records that have never been backed up or previously failed.
     */
    public function uploadPending(): array
    {
        $records = BillingRecord::where(function ($q) {
            $q->whereNull('backed_up_at')
              ->orWhere('backup_status', 'failed');
        })->get();

        $results = ['processed' => $records->count(), 'success' => 0, 'failed' => 0];

        foreach ($records as $record) {
            $result = $this->uploadBill($record);
            $result['success'] ? $results['success']++ : $results['failed']++;
        }

        return $results;
    }

    /**
     * Generate a temporary signed URL for secure access to the stored PDF.
     */
    public function getSignedUrl(BillingRecord $record, int $minutes = 30): ?string
    {
        if (!$record->pdf_path) {
            return null;
        }

        return Storage::disk('s3')->temporaryUrl(
            $record->pdf_path,
            now()->addMinutes($minutes)
        );
    }
}
