<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'account_no',
        'previous_reading',
        'current_reading',
        'consumption_cu_m',
        'base_rate',
        'maintenance_charge',
        'service_fee',
        'vat',
        'total_amount',
        'date_from',
        'date_to',
    ];

    protected $casts = [
        'previous_reading' => 'float',
        'current_reading' => 'float',
        'consumption_cu_m' => 'float',
        'base_rate' => 'float',
        'maintenance_charge' => 'float',
        'service_fee' => 'float',
        'vat' => 'float',
        'total_amount' => 'float',
        'date_from' => 'date',
        'date_to' => 'date',
    ];
}


