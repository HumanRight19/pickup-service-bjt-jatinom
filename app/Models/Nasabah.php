<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Nasabah extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($nasabah) {
            if (empty($nasabah->uuid)) {
                $uuid = (string) Str::uuid();
                $nasabah->uuid = $uuid;
                $nasabah->qr_token = $uuid; // sama persis
            }
        });

        // Hapus QR code saat model dihapus
        static::deleting(function ($nasabah) {
            // Pastikan qr_path ada sebelum hapus file
            if (!empty($nasabah->qr_path) && Storage::disk('local')->exists($nasabah->qr_path)) {
                Storage::disk('local')->delete($nasabah->qr_path);
            }
        });
    }

    // Pakai UUID untuk route model binding
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $fillable = [
        'nama',
        'nama_umplung',
        'alamat',
        'nomor_hp', // ✅ ini harus ada
        'nomor_rekening',
        'blok_pasar_id',
        'uuid',
        'qr_token',
    ];

    public function blokPasar()
    {
        return $this->belongsTo(BlokPasar::class);
    }

    public function setorans()
    {
        return $this->hasMany(Setoran::class);
    }

    public function titipSetorans()
    {
        return $this->hasMany(TitipSetoran::class, 'nasabah_id');
    }

    // 🔹 Relasi khusus hari ini
    public function setoranHariIni()
    {
        return $this->hasOne(Setoran::class)
            ->whereDate('tanggal', today());
    }

    public function titipSetoranHariIni()
    {
        return $this->hasOne(TitipSetoran::class, 'nasabah_id')
            ->whereDate('tanggal_titip', today());
    }

    // 🔹 Atribut status_hari_ini
    protected $appends = ['status_hari_ini'];

    public function getStatusHariIniAttribute()
    {
        if ($this->setoranHariIni) {
            return $this->setoranHariIni->status_setoran;
        }

        if ($this->titipSetoranHariIni) {
            return $this->titipSetoranHariIni->status_setoran;
        }

        return 'belum_setor';
    }
}
