<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerIssueReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_no',
        'customer_name',
        'contact_number',
        'issue_type',
        'severity',
        'channel',
        'subject',
        'summary',
        'details',
        'status',
        'is_priority',
        'documented_by',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'is_priority' => 'boolean',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function documentedBy()
    {
        return $this->belongsTo(User::class, 'documented_by');
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
