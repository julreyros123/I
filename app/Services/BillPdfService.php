<?php

namespace App\Services;

use App\Models\BillingRecord;
use Illuminate\Support\Facades\Storage;

class BillPdfService
{
    public function generateAndStore(BillingRecord $billingRecord): ?string
    {
        if (!class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            return null;
        }

        $billingRecord->loadMissing('customer');

        $usageSeries = $this->getLastFiveMonthsUsage((string) $billingRecord->account_no);

        $invoiceNumber = $billingRecord->invoice_number ?? ('INV-' . str_pad((string) $billingRecord->id, 4, '0', STR_PAD_LEFT));
        $safeInvoice = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) $invoiceNumber);
        $fileName = $safeInvoice . '.pdf';
        $relativePath = 'bills/' . $fileName;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('records.bill-print', [
            'billingRecord' => $billingRecord,
            'usageSeries' => $usageSeries,
            'disableAutoPrint' => true,
        ]);

        $pdf->setPaper('a5', 'portrait');

        $content = $pdf->output();
        Storage::disk('public')->put($relativePath, $content);

        return $relativePath;
    }

    private function getLastFiveMonthsUsage(string $accountNo): array
    {
        return BillingRecord::query()
            ->where('account_no', $accountNo)
            ->orderByDesc('created_at')
            ->take(5)
            ->get(['created_at', 'consumption_cu_m'])
            ->map(function ($r) {
                return [
                    'label' => optional($r->created_at)->format('M Y'),
                    'value' => (float) ($r->consumption_cu_m ?? 0),
                ];
            })
            ->reverse()
            ->values()
            ->all();
    }
}
