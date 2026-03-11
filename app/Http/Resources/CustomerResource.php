<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Whitelist safe customer fields for JSON responses.
     * Excludes address (PII), contact_no (already model-hidden),
     * and internal columns (reconnect_requested_*, created_by).
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_no' => $this->account_no,
            'name' => $this->name,
            'status' => $this->status,
            'classification' => $this->classification,
            'meter_no' => $this->meter_no,
            'meter_size' => $this->meter_size,
            'previous_reading' => $this->previous_reading,
            'created_at' => $this->created_at,
        ];
    }
}
