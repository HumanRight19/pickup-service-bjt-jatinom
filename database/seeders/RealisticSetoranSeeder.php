<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\BlokPasar;
use App\Models\PenjadwalanHarian;
use App\Models\Setoran;
use Carbon\Carbon;

class RealisticSetoranSeeder extends Seeder
{
    public function run(): void
    {
        $supervisors = User::where('role', 'supervisor')->get();
        $petugasList = User::where('role', 'petugas')->get();
        $bloks = BlokPasar::with('nasabahs')->get();

        if ($supervisors->isEmpty() || $petugasList->isEmpty() || $bloks->isEmpty()) {
            $this->command->warn('⚠️ Data supervisor, petugas, atau blok belum ada.');
            return;
        }

        $totalHari = 180;
        $startDate = Carbon::today()->subDays($totalHari - 1);

        for ($i = 0; $i < $totalHari; $i++) {
            $tanggal = $startDate->copy()->addDays($i)->toDateString();

            // Pilih supervisor, petugas, blok secara rotasi/random
            $supervisor = $supervisors[$i % $supervisors->count()];
            $petugas = $petugasList[$i % $petugasList->count()];
            $blok = $bloks[$i % $bloks->count()];

            // Jadwal harian
            PenjadwalanHarian::updateOrCreate(
                ['tanggal' => $tanggal, 'petugas_id' => $petugas->id],
                [
                    'supervisor_id' => $supervisor->id,
                    'blok_id' => $blok->id,
                    'ditetapkan_oleh' => $supervisor->id,
                ]
            );

            // Setoran utama → semua nasabah di blok
            foreach ($blok->nasabahs as $nasabah) {
                Setoran::create([
                    'nasabah_id' => $nasabah->id,
                    'tanggal' => $tanggal,
                    'jumlah' => rand(5000, 20000),
                    'user_id' => $petugas->id,
                    'supervisor_id' => $supervisor->id,
                ]);
            }

            $this->command->info("📅 [$tanggal] Petugas {$petugas->name} di blok {$blok->nama_blok} → setoran utama berhasil.");
        }

        $this->command->info("🎉 Seeder setoran utama selesai untuk 6 bulan.");
    }
}
