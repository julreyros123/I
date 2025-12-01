<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterReadingSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'account_no',
        'scheduled_date',
        'actual_reading_date',
        'status',
        'assigned_to',
        'billing_record_id',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'actual_reading_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function billingRecord()
    {
        return $this->belongsTo(BillingRecord::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
