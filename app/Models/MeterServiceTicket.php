<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterServiceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_id',
        'customer_id',
        'customer_application_id',
        'issue_type',
        'description',
        'status',
        'scheduled_visit_at',
        'resolved_at',
        'resolution_notes',
        'reported_by',
        'resolved_by',
    ];

    protected $casts = [
        'scheduled_visit_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(CustomerApplication::class, 'customer_application_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
