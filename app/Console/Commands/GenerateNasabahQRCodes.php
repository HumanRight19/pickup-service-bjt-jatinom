<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Nasabah;
use App\Http\Controllers\NasabahAdminController;

class GenerateNasabahQRCodes extends Command
{
    protected $signature = 'nasabah:generate-qrcodes';
    protected $description = 'Generate QR code PNG untuk nasabah yang belum punya QR';

    public function handle()
    {
        $this->info('🔍 Mengecek nasabah tanpa file QR...');

        $nasabahs = Nasabah::all()->filter(function ($n) {
            $path = storage_path("app/qrcodes/{$n->qr_token}.png");
            return !file_exists($path);
        });

        if ($nasabahs->isEmpty()) {
            $this->info('✅ Semua nasabah sudah memiliki QR.');
            return;
        }

        $this->info("🧾 Ditemukan {$nasabahs->count()} nasabah tanpa QR. Membuat...");

        foreach ($nasabahs as $nasabah) {
            try {
                app(NasabahAdminController::class)
                    ->generateQrWithText($nasabah);
                $this->info("✅ QR untuk {$nasabah->nama} berhasil dibuat.");
            } catch (\Throwable $e) {
                $this->error("❌ Gagal untuk {$nasabah->nama}: " . $e->getMessage());
            }
        }

        $this->info('🎉 Semua QR berhasil digenerate.');
    }

}
