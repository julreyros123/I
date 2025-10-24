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
        'advance_payment',
        'overdue_penalty',
        'vat',
        'total_amount',
        'bill_status',
        'notes',
        'date_from',
        'date_to',
    ];

    protected $casts = [
        'previous_reading' => 'float',
        'current_reading' => 'float',
        'consumption_cu_m' => 'float',
        'base_rate' => 'float',
        'maintenance_charge' => 'float',
        'advance_payment' => 'float',
        'overdue_penalty' => 'float',
        'vat' => 'float',
        'total_amount' => 'float',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class);
    }

    // Helper methods for bill status
    public function isPaid()
    {
        return $this->bill_status === 'Paid';
    }

    public function isPending()
    {
        return $this->bill_status === 'Pending';
    }

    public function isOverdue()
    {
        return $this->bill_status === 'Notice of Disconnection';
    }

    public function getStatusBadgeClass()
    {
        return match($this->bill_status) {
            'Paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'Pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'Notice of Disconnection' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
        };
    }

    public function getBillingPeriod()
    {
        if ($this->date_from && $this->date_to) {
            return $this->date_from->format('M d') . ' - ' . $this->date_to->format('M d, Y');
        }
        return $this->created_at->format('M d, Y');
    }
}


