<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Nasabah;
use App\Http\Controllers\NasabahAdminController;
use Illuminate\Support\Facades\Storage;

class RegenerateMissingQRCodes extends Command
{
    protected $signature = 'nasabah:regenerate-missing-qrcodes';
    protected $description = 'Generate ulang QR code untuk nasabah yang file PNG-nya hilang';

    public function handle()
    {
        $controller = app(NasabahAdminController::class);
        $count = 0;

        $this->info('🔍 Memeriksa nasabah...');

        Nasabah::chunk(100, function ($nasabahList) use ($controller, &$count) {
            foreach ($nasabahList as $nasabah) {
                $path = "qrcodes/{$nasabah->qr_token}.png";

                if (!Storage::disk('local')->exists($path)) {
                    $controller->generateQrWithText($nasabah);
                    $count++;
                    $this->line("✅ QR regenerated untuk: {$nasabah->nama}");
                }
            }
        });

        $this->info("🎯 Selesai! Total QR regenerated: {$count}");
    }
}
