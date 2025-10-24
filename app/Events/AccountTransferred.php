<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountTransferred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $account_no;
    public $old_name;
    public $new_name;
    public $performed_by;

    public function __construct($account_no, $old_name, $new_name, $performed_by = null)
    {
        $this->account_no = $account_no;
        $this->old_name = $old_name;
        $this->new_name = $new_name;
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
            'old_name' => $this->old_name,
            'new_name' => $this->new_name,
            'performed_by' => $this->performed_by,
        ];
    }
}
