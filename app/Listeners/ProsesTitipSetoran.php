<?php

namespace App\Listeners;

use App\Events\PenjadwalanCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProsesTitipSetoran
{
    public function handle(PenjadwalanCreated $event): void
    {
        $blokId = $event->penjadwalan->blok_id;

        $titipSetoranList = TitipSetoran::where('blok_id', $blokId)
            ->where('is_processed', false)
            ->get();

        foreach ($titipSetoranList as $titip) {
            // Insert ke tabel setorans
            Setoran::create([
                'nasabah_id' => $titip->nasabah_id,
                'blok_id' => $titip->blok_id,
                'jumlah' => $titip->jumlah,
                'tanggal' => $event->penjadwalan->tanggal,
                'istitip' => true, // opsional, kalau mau tandain
            ]);

            // Update flag biar ga diproses lagi
            $titip->update(['is_processed' => true]);
        }
    }
}

