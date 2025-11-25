<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_id','action','changed_by','from_json','to_json','reason'
    ];

    protected $casts = [
        'from_json' => 'array',
        'to_json' => 'array',
    ];

    public function meter(): BelongsTo { return $this->belongsTo(Meter::class); }
}
