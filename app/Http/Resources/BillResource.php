<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    /**
     * Whitelist display-safe billing fields for JSON responses.
     * Excludes pdf_path (internal S3 key), prepared_by, notes, and
     * internal financial adjustment fields.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_no' => $this->account_no,
            'invoice_number' => $this->invoice_number,
            'bill_status' => $this->bill_status,
            'consumption_cu_m' => $this->consumption_cu_m,
            'base_rate' => $this->base_rate,
            'maintenance_charge' => $this->maintenance_charge,
            'total_amount' => $this->total_amount,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'due_date' => $this->due_date,
            'issued_at' => $this->issued_at,
            'is_generated' => $this->is_generated,
            'created_at' => $this->created_at,
        ];
    }
}
