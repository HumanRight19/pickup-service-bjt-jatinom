<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetoranRequest extends Model
{
    protected $fillable = [
        'setoranable_id',
        'setoranable_type',
        'type',             // update, batal
        'jumlah_lama',
        'jumlah_baru',
        'alasan',           // opsional, alasan update/batal
        'status',           // pending, approved, rejected
        'petugas_id',
        'supervisor_id',
    ];

    public function setoranable()
    {
        return $this->morphTo();
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}