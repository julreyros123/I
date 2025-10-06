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
    ];

    protected $casts = [
        'previous_reading' => 'float',
    ];
}


