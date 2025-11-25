<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id','staff_id','type','note'
    ];
}
