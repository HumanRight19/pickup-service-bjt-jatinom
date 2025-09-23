<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PenjadwalanCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $penjadwalan;

    public function __construct($penjadwalan)
    {
        $this->penjadwalan = $penjadwalan;
    }
}

