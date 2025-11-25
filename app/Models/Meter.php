<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial','qr_code','type','size','manufacturer','seal_no','status','install_date',
        'location_address','barangay','lat','lng','last_reading_value','last_reading_at',
        'current_account_id','notes','created_by','updated_by'
    ];

    protected $casts = [
        'install_date' => 'date',
        'last_reading_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'last_reading_value' => 'decimal:2',
    ];

    public function assignments(): HasMany { return $this->hasMany(MeterAssignment::class); }
    public function audits(): HasMany { return $this->hasMany(MeterAudit::class); }
    public function currentCustomer(): BelongsTo { return $this->belongsTo(Customer::class, 'current_account_id'); }
}
