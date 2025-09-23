<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function setorans()
    {
        return $this->hasMany(Setoran::class);
    }

    public function penugasanSebagaiPetugas()
    {
        return $this->hasMany(PenjadwalanHarian::class, 'petugas_id');
    }

    public function penugasanSebagaiSupervisor()
    {
        return $this->hasMany(PenjadwalanHarian::class, 'supervisor_id');
    }

    public function penjadwalanDitentukan()
    {
        return $this->hasMany(PenjadwalanHarian::class, 'ditetapkan_oleh');
    }

    // Ganti method blokHariIni() yang lama, dengan ini:
    public function getBlokHariIniAttribute()
    {
        return $this->penugasanSebagaiPetugas()
            ->whereDate('tanggal', Carbon::today())
            ->with('blok')
            ->first();
    }

}
