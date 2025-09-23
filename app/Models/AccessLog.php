<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    public $timestamps = false; // kita pakai accessed_at manual
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'accessed_at',
    ];
}
