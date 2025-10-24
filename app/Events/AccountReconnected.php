<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountReconnected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $account_no;
    public $performed_by;

    public function __construct($account_no, $performed_by = null)
    {
        $this->account_no = $account_no;
        $this->performed_by = $performed_by;
    }

    public function broadcastOn()
    {
        return new Channel('accounts');
    }

    public function broadcastWith()
    {
        return [
            'account_no' => $this->account_no,
            'performed_by' => $this->performed_by,
        ];
    }
}
