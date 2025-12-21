<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'account_no',
        'pdf_path',
        'invoice_number',
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
        'is_generated',
        'generated_at',
        'notes',
        'date_from',
        'date_to',
        'due_date',
        'prepared_by',
        'issued_at',
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
        'is_generated' => 'boolean',
        'generated_at' => 'datetime',
        'date_from' => 'date',
        'date_to' => 'date',
        'due_date' => 'date',
        'issued_at' => 'datetime',
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

    public function isOutstanding()
    {
        return $this->bill_status === 'Outstanding Payment';
    }

    public function isOverdue()
    {
        return $this->bill_status === 'Overdue';
    }

    public function getStatusBadgeClass()
    {
        return match($this->bill_status) {
            'Paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'Outstanding Payment' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'Overdue' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            'Notice of Disconnection' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'Disconnected' => 'bg-gray-200 text-gray-800 dark:bg-gray-800 dark:text-gray-100',
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

    public function isGenerated(): bool
    {
        return (bool) $this->is_generated;
    }
}


