<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'applicant_name',
        'address',
        'contact_no',
        'status',
        'documents',
        'score',
        'score_breakdown',
        'risk_level',
        'decision',
        'reviewed_by',
        'reviewed_at',
        'risk_flags',
        'inspection_date',
        'inspected_by',
        'inspection_notes',
        'approved_by',
        'approved_at',
        'fee_application',
        'fee_inspection',
        'fee_materials',
        'fee_labor',
        'meter_deposit',
        'fee_total',
        'payment_receipt_no',
        'paid_at',
        'paid_by',
        'schedule_date',
        'scheduled_by',
        'installed_at',
        'installed_by',
        'created_by',
    ];

    /**
     * Include computed attributes on JSON.
     */
    protected $appends = [
        'application_code',
    ];

    protected $casts = [
        'documents' => 'array',
        'score_breakdown' => 'array',
        'risk_flags' => 'array',
        'inspection_date' => 'date',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
        'schedule_date' => 'date',
        'installed_at' => 'datetime',
        'fee_application' => 'decimal:2',
        'fee_inspection' => 'decimal:2',
        'fee_materials' => 'decimal:2',
        'fee_labor' => 'decimal:2',
        'meter_deposit' => 'decimal:2',
        'fee_total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /**
     * Human-friendly application code (e.g. APP-000123).
     */
    public function getApplicationCodeAttribute(): string
    {
        $id = $this->getKey();
        if (!$id) {
            return 'APP-NEW';
        }
        return 'APP-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public function getDocumentsAttribute($value): array
    {
        $docs = $value;
        if (is_string($docs)) {
            $decoded = json_decode($docs, true);
            $docs = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($docs)) {
            $docs = [];
        }

        foreach (['id_type', 'id_number'] as $key) {
            if (array_key_exists($key, $docs)) {
                $docs[$key] = $this->normalizeDocumentValue($docs[$key]);
            }
        }

        return $docs;
    }

    public function setDocumentsAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['documents'] = null;
            return;
        }

        if (!is_array($value)) {
            $value = (array) $value;
        }

        foreach (['id_type', 'id_number'] as $key) {
            if (array_key_exists($key, $value)) {
                $value[$key] = $this->normalizeDocumentValue($value[$key]);
            }
        }

        $this->attributes['documents'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    protected function normalizeDocumentValue($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            if (empty($value)) {
                return null;
            }
            $first = reset($value);
            return $this->normalizeDocumentValue($first);
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $this->normalizeDocumentValue((string) $value);
            }
            if ($value instanceof self) {
                return null;
            }
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }
}
