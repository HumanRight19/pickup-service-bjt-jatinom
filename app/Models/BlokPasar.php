<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlokPasar extends Model
{
    protected $fillable = ['nama_blok'];

    /**
     * Relasi ke Nasabah
     */
    public function nasabahs(): HasMany
    {
        return $this->hasMany(Nasabah::class, 'blok_pasar_id');
    }

    /**
     * Relasi ke PenjadwalanHarian (jika ingin tahu blok ini dipakai di tugas mana saja)
     */
    public function tugasHarian(): HasMany
    {
        return $this->hasMany(PenjadwalanHarian::class, 'blok_pasar_id');
    }

    public function titipSetorans()
    {
        return $this->hasMany(TitipSetoran::class, 'blok_id');
    }

}
