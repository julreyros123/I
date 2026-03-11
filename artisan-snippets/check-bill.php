<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$r = App\Models\BillingRecord::where('invoice_number', 'INV-20260311-3728')->first();
if (!$r) {
    echo "NOT FOUND\n";
    exit;
}
echo "DB record:\n";
echo json_encode([
    'id'            => $r->id,
    'pdf_path'      => $r->pdf_path,
    'backup_status' => $r->backup_status,
    'backed_up_at'  => (string) $r->backed_up_at,
    'is_generated'  => $r->is_generated,
], JSON_PRETTY_PRINT) . "\n\n";

// Check if a file actually exists in S3
$disk = Illuminate\Support\Facades\Storage::disk('s3');
$exists = $r->pdf_path ? $disk->exists($r->pdf_path) : false;
echo "S3 file exists: " . ($exists ? "YES" : "NO") . "\n";
if ($exists) {
    $url = $disk->temporaryUrl($r->pdf_path, now()->addMinutes(10));
    echo "Temp URL: $url\n";
}
