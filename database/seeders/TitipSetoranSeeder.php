<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\BlokPasar;
use App\Models\Nasabah;
use App\Models\PenjadwalanHarian;
use App\Models\TitipSetoran;
use Carbon\Carbon;

class TitipSetoranSeeder extends Seeder
{
    public function run(): void
    {
        $petugasList = User::where('role', 'petugas')->get();
        $supervisors = User::where('role', 'supervisor')->get();
        $bloks = BlokPasar::with('nasabahs')->get();
        $nasabahAll = Nasabah::all();

        if ($petugasList->isEmpty() || $supervisors->isEmpty() || $bloks->isEmpty() || $nasabahAll->isEmpty()) {
            $this->command->warn('⚠️ Data belum lengkap untuk titip setoran.');
            return;
        }

        $totalHari = 180;
        $startDate = Carbon::today()->subDays($totalHari - 1);

        for ($i = 0; $i < $totalHari; $i++) {
            $tanggal = $startDate->copy()->addDays($i)->toDateString();

            // Ambil jadwal hari itu
            $jadwalHariIni = PenjadwalanHarian::where('tanggal', $tanggal)->get();

            // Kalau kosong, buat jadwal dummy
            if ($jadwalHariIni->isEmpty()) {
                foreach ($petugasList as $petugas) {
                    $supervisor = $supervisors->random();
                    $blok = $bloks->random();
                    PenjadwalanHarian::updateOrCreate(
                        ['tanggal' => $tanggal, 'petugas_id' => $petugas->id],
                        [
                            'supervisor_id' => $supervisor->id,
                            'blok_id' => $blok->id,
                            'ditetapkan_oleh' => $supervisor->id
                        ]
                    );
                }
                $jadwalHariIni = PenjadwalanHarian::where('tanggal', $tanggal)->get();
            }

            foreach ($jadwalHariIni as $jadwal) {
                $petugas = $jadwal->petugas;
                $supervisor = $jadwal->supervisor;
                $blokDijadwal = $jadwal->blok;

                // Nasabah yang bisa dititipkan → bukan di blok yang dijadwal
                $nasabahEligible = $nasabahAll->whereNotIn('id', $blokDijadwal->nasabahs->pluck('id'));
                if ($nasabahEligible->isEmpty())
                    continue;

                $jumlahTitip = rand(1, min(5, $nasabahEligible->count()));
                $nasabahTitip = $nasabahEligible->random($jumlahTitip);

                foreach ($nasabahTitip as $nasabah) {
                    TitipSetoran::create([
                        'nasabah_id' => $nasabah->id,
                        'petugas_id' => $petugas->id,
                        'supervisor_id' => $supervisor->id,
                        'blok_id' => $nasabah->blok_pasar_id,
                        'jumlah' => rand(50000, 200000),
                        'tanggal_titip' => $tanggal,
                    ]);
                }

                $this->command->info("📅 [$tanggal] Petugas {$petugas->name} → titip setoran $jumlahTitip nasabah berhasil.");
            }
        }

        $this->command->info("🎉 Seeder titip setoran realistis selesai untuk 6 bulan.");
    }
}
