<?php

// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\User;
// use App\Models\BlokPasar;
// use App\Models\Nasabah;
// use App\Models\PenjadwalanHarian;
// use App\Models\Setoran;
// use App\Models\TitipSetoran;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\DB;

// class SeederGabunganSetoranTitipan extends Seeder
// {
//     public function run(): void
//     {
//         $supervisors = User::where('role', 'supervisor')->get();
//         $petugasList = User::where('role', 'petugas')->get();
//         $bloks = BlokPasar::with('nasabahs')->get();

//         if ($supervisors->isEmpty() || $petugasList->isEmpty() || $bloks->isEmpty()) {
//             $this->command->warn('⚠️ Data supervisor, petugas, atau blok belum ada.');
//             return;
//         }

//         $totalHari = 100; // jumlah hari history
//         $today = Carbon::today();
//         $allNasabah = Nasabah::all();

//         for ($i = 0; $i < $totalHari; $i++) {
//             $tanggal = $today->copy()->subDays($totalHari - $i - 1)->toDateString();
//             $supervisor = $supervisors[$i % $supervisors->count()];
//             $petugas = $petugasList[$i % $petugasList->count()];
//             $blok = $bloks[$i % $bloks->count()];

//             // Jadwal harian
//             PenjadwalanHarian::updateOrCreate(
//                 ['tanggal' => $tanggal, 'petugas_id' => $petugas->id],
//                 [
//                     'supervisor_id' => $supervisor->id,
//                     'blok_id' => $blok->id,
//                     'ditetapkan_oleh' => $supervisor->id,
//                 ]
//             );

//             foreach ($allNasabah as $nasabah) {

//                 if ($nasabah->blok_pasar_id == $blok->id) {
//                     // Setoran utama selalu dibuat besar
//                     $base = rand(50000, 100000); // jumlah setoran utama besar
//                     DB::transaction(function () use ($nasabah, $petugas, $supervisor, $tanggal, $base) {
//                         Setoran::create([
//                             'nasabah_id' => $nasabah->id,
//                             'user_id' => $petugas->id,
//                             'supervisor_id' => $supervisor->id,
//                             'tanggal' => $tanggal,
//                             'jumlah' => $base,
//                             'status' => 'sudah',
//                         ]);
//                     });
//                 } else {
//                     // Titip setoran: kecil dan acak, 60% probabilitas
//                     if (rand(1, 100) <= 60) {
//                         TitipSetoran::create([
//                             'nasabah_id' => $nasabah->id,
//                             'blok_id' => $nasabah->blok_pasar_id,
//                             'petugas_id' => $petugas->id,
//                             'tanggal_titip' => $tanggal,
//                             'jumlah' => rand(5000, 20000), // titipan lebih kecil
//                             'is_processed' => false,
//                             'setoran_id' => null,
//                         ]);
//                     }
//                 }
//             }

//             $this->command->info("📅 [$tanggal] Petugas {$petugas->name} ambil setoran blok {$blok->nama_blok}");
//         }

//         $this->command->info("🎉 Seeder gabungan setoran & titipan selesai untuk $totalHari hari.");
//     }
// }
