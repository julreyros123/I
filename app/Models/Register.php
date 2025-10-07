<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;

    // Specify the correct table name
    protected $table = 'register';

    protected $fillable = [
        'account_no',
        'name',
        'address',
        'contact_no',
        'connection_classification',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];
}
