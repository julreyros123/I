<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_no',
        'name',
        'address',
        'meter_no',
        'meter_size',
        'status',
        'previous_reading',
        'credit_balance',
    ];

    protected $casts = [
        'previous_reading' => 'float',
        'credit_balance' => 'float',
    ];

    // Scope for active customers
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    // Scope for searching customers
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('account_no', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%");
        });
    }

    // Relationships
    public function billingRecords()
    {
        return $this->hasMany(BillingRecord::class);
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class);
    }

    // Helper methods
    public function hasCredit()
    {
        return $this->credit_balance > 0;
    }

    public function getFormattedCreditBalance()
    {
        return '₱' . number_format($this->credit_balance, 2);
    }
}


