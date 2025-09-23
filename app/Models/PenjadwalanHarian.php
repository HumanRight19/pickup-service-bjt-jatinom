<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjadwalanHarian extends Model
{
    protected $fillable = [
        'tanggal',
        'petugas_id',
        'supervisor_id',
        'blok_id',
        'ditetapkan_oleh',
    ];

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function penentu()
    {
        return $this->belongsTo(User::class, 'ditetapkan_oleh');
    }

    public function blok()
    {
        return $this->belongsTo(BlokPasar::class, 'blok_id');
    }
}