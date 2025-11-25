<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_no','cycle_id','usage','subtotal','penalties','advances','total','status','generated_at','delivered_at','staff_id'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany(BillEvent::class);
    }
}
