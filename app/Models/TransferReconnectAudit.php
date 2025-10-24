<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferReconnectAudit extends Model
{
    use HasFactory;

    protected $table = 'transfer_reconnect_audits';

    protected $fillable = [
        'account_no', 'action', 'old_value', 'new_value', 'notes', 'performed_by', 'performed_at'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];
}
