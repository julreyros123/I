<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'billing_record_id',
        'account_no',
        'bill_amount',
        'amount_paid',
        'overpayment',
        'credit_applied',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'bill_amount' => 'float',
        'amount_paid' => 'float',
        'overpayment' => 'float',
        'credit_applied' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function billingRecord()
    {
        return $this->belongsTo(BillingRecord::class);
    }

    // Scope for overpaid transactions
    public function scopeOverpaid($query)
    {
        return $query->where('payment_status', 'overpaid');
    }

    // Scope for payments with credits applied
    public function scopeWithCredits($query)
    {
        return $query->where('credit_applied', '>', 0);
    }
}