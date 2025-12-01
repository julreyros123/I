<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'period_start',
        'period_end',
        'run_date',
        'due_date',
        'status',
        'total_bills',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'run_date' => 'datetime',
        'due_date' => 'date',
        'total_bills' => 'integer',
        'total_amount' => 'float',
    ];

    public function billingRecords()
    {
        return $this->hasMany(BillingRecord::class);
    }
}
