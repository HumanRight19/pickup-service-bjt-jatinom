<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;

// class RekapSetoran extends Model
// {
//     protected $fillable = [
//         'nasabah_id',
//         'setoran_id',
//         'setoran_utama',
//         'total_titip',
//         'jumlah',
//         'tanggal',
//     ];

//     /*
//      * Relasi ke Nasabah
//      */
//     public function nasabah(): BelongsTo
//     {
//         return $this->belongsTo(Nasabah::class, 'nasabah_id');
//     }

//     /*
//      * Relasi ke Setoran utama
//      */
//     public function setoran(): BelongsTo
//     {
//         return $this->belongsTo(Setoran::class, 'setoran_id');
//     }

//     /*
//      * Helper: cek apakah setoran utama dikosongkan (alias hanya pakai titip)
//      */
//     public function isSetoranKosong(): bool
//     {
//         return $this->setoran_utama == 0 && $this->total_titip > 0;
//     }

//     /*
//      * Helper: total keseluruhan (fallback kalau kolom jumlah belum diisi)
//      */
//     public function getTotalAttribute(): int
//     {
//         return $this->jumlah ?? ($this->setoran_utama + $this->total_titip);
//     }

//     /*
//      * Helper: cek apakah setoran utama valid untuk dikosongkan
//      */
//     public function bolehSetoranKosong(): bool
//     {
//         // Cek apakah nasabah punya minimal 1 riwayat titip setoran
//         $punyaRiwayatTitip = $this->nasabah
//             ->titipSetorans()
//             ->where('is_processed', true) // misal hanya hitung titip yang sudah diproses
//             ->exists();

//         return $this->setoran_utama == 0 && $punyaRiwayatTitip;
//     }
// }
