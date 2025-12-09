<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantPaymentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_application_id',
        'invoice_number',
        'amount_due',
        'amount_tendered',
        'change_given',
        'fee_breakdown',
        'processed_by',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_tendered' => 'decimal:2',
        'change_given' => 'decimal:2',
        'fee_breakdown' => 'array',
    ];

    public function application()
    {
        return $this->belongsTo(CustomerApplication::class, 'customer_application_id');
    }

    public function processor()
    {
        return $this->belongsTo(\App\Models\User::class, 'processed_by');
    }
}
