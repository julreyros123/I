<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_id','account_id','assigned_at','unassigned_at','reason','notes','assigned_by','unassigned_by'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    public function meter(): BelongsTo { return $this->belongsTo(Meter::class); }
    public function account(): BelongsTo { return $this->belongsTo(Customer::class, 'account_id'); }
}
