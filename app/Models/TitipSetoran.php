<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitipSetoran extends Model
{
    protected $fillable = [
        'nasabah_id',
        'blok_id',
        'jumlah',
        'tanggal_titip',
        'petugas_id',
        'supervisor_id',
        'status',
    ];

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id');
    }

    public function blok(): BelongsTo
    {
        return $this->belongsTo(BlokPasar::class, 'blok_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function requests()
    {
        return $this->morphMany(SetoranRequest::class, 'setoranable');
    }

    // ✅ status_titip_setoran accessor (opsional, tapi disarankan kalau alurnya sama dengan Setoran)
    protected $appends = ['status_titip_setoran'];

    public function getStatusTitipSetoranAttribute(): string
    {
        $latestBatal = $this->requests()
            ->where('type', 'batal')
            ->latest()
            ->first();

        if ($latestBatal) {
            return match ($latestBatal->status) {
                'pending' => 'pengajuan_batal_pending',
                'approved' => 'batal',
                'rejected' => 'pengajuan_batal_ditolak',
                default => $this->status === 'batal' ? 'batal' : 'sudah_setor',
            };
        }

        return $this->status === 'batal' ? 'batal' : 'sudah_setor';
    }

}