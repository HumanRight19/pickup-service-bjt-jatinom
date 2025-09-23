<?php

// app/Events/SetoranBaru.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SetoranBaru implements ShouldBroadcast
{
    use SerializesModels;

    public $nama_nasabah;

    public function __construct($nama_nasabah)
    {
        $this->nama_nasabah = $nama_nasabah;
    }

    public function broadcastOn()
    {
        return new Channel('setoran');
    }
}
