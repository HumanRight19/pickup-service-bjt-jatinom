<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setoran;
use App\Models\PenjadwalanHarian;

class PerbaikiSupervisorIdSeeder extends Seeder
{
    public function run(): void
    {
        $setorans = Setoran::whereNull('supervisor_id')->get();

        foreach ($setorans as $setoran) {
            $jadwal = PenjadwalanHarian::where('petugas_id', $setoran->user_id)
                ->whereDate('tanggal', $setoran->tanggal)
                ->first();

            if ($jadwal && $jadwal->supervisor_id) {
                $setoran->supervisor_id = $jadwal->supervisor_id;
                $setoran->save();
            }
        }
    }
}
