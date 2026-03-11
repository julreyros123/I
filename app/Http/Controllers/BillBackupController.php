<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillResource;
use App\Models\BillingRecord;
use App\Services\BillBackupService;
use Illuminate\Http\JsonResponse;

class BillBackupController extends Controller
{
    public function __construct(protected BillBackupService $service) {}

    /**
     * Backup a single billing record to S3.
     */
    public function backup(BillingRecord $billingRecord): JsonResponse
    {
        $result = $this->service->uploadBill($billingRecord);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Backup all pending/failed billing records to S3.
     */
    public function backupAll(): JsonResponse
    {
        $results = $this->service->uploadPending();

        return response()->json([
            'message' => 'Backup complete.',
            'results' => $results,
        ]);
    }

    /**
     * Return a temporary signed URL to download a bill PDF from S3.
     */
    public function download(BillingRecord $billingRecord): JsonResponse
    {
        $url = $this->service->getSignedUrl($billingRecord);

        if (!$url) {
            return response()->json(['message' => 'File not backed up yet.'], 404);
        }

        return response()->json(['url' => $url]);
    }
}
