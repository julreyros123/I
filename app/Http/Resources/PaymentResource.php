<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Whitelist display-safe payment fields for JSON responses.
     * Excludes billing_record_id, customer_id (internal foreign keys),
     * bill_amount, overpayment, credit_applied (internal financial fields),
     * and notes.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_no' => $this->account_no,
            'amount_paid' => $this->amount_paid,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'created_at' => $this->created_at,
        ];
    }
}
