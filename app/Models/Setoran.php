<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setoran extends Model
{
    protected $fillable = [
        'nasabah_id',
        'user_id', // ini adalah petugas
        'supervisor_id', // tambahkan jika kamu simpan supervisor di setoran
        'tanggal',
        'jumlah',
        'status',
    ];

    protected $appends = ['status_setoran']; // otomatis ikut saat di-serialize ke JSON/Inertia

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // alias untuk user
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function requests()
    {
        return $this->morphMany(SetoranRequest::class, 'setoranable');
    }

    // ✅ status_setoran accessor
    public function getStatusSetoranAttribute(): string
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