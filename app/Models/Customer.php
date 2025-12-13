<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\MeterAssignment;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_no',
        'name',
        'address',
        'contact_no',
        'meter_no',
        'meter_size',
        'status',
        'reconnect_requested_at',
        'reconnect_requested_by',
        'created_by',
        'previous_reading',
    ];

    protected $casts = [
        'previous_reading' => 'float',
        'reconnect_requested_at' => 'datetime',
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

    public function latestApplication(): HasOne
    {
        return $this->hasOne(\App\Models\CustomerApplication::class)->latestOfMany();
    }

    public function meterAssignments(): HasMany
    {
        return $this->hasMany(MeterAssignment::class, 'account_id');
    }

    // Helper methods
}


