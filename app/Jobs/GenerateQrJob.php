<?php

namespace App\Jobs;

use App\Models\Nasabah;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $nasabahId;
    public $tries = 3;
    public $timeout = 60;

    public function __construct($nasabahId)
    {
        $this->nasabahId = $nasabahId;
    }

    public function handle()
    {
        $nasabah = Nasabah::find($this->nasabahId);

        if (!$nasabah) {
            return;
        }

        $this->generateQr($nasabah);
    }

    private function generateQr(Nasabah $nasabah): void
    {
        $qrDir = storage_path('app/qrcodes');

        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0755, true);
        }

        $token = $nasabah->qr_token;
        $text = trim((string) ($nasabah->nama_umplung ?: $nasabah->nama)) ?: 'nasabah';
        $fileName = "{$token}.png";
        $filePath = $qrDir . '/' . $fileName;

        $tempFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';

        QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate(url("/nasabah/by-qr/{$token}"), $tempFile);

        $this->addTextBelowQr($tempFile, $text, $filePath);

        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        $nasabah->update(['qr_path' => "qrcodes/{$fileName}"]);
    }


    /**
     * Tambahkan teks di bawah QR dan simpan ke $outputPath
     */
    private function addTextBelowQr(string $inputPath, string $text, string $outputPath): void
    {
        $qrImg = imagecreatefrompng($inputPath);
        if (!$qrImg) {
            throw new \Exception("Gagal membaca QR image dari $inputPath");
        }

        $qrW = imagesx($qrImg);
        $qrH = imagesy($qrImg);

        $bottomSpace = 60;
        $w = $qrW;
        $h = $qrH + $bottomSpace;

        $img = imagecreatetruecolor($w, $h);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        // Background putih
        imagefilledrectangle($img, 0, 0, $w, $h, $white);

        // Tempel QR di atas
        imagecopy($img, $qrImg, 0, 0, 0, 0, $qrW, $qrH);

        // Tulis teks di bawah
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = max(0, (int) (($w - $textWidth) / 2));
        $y = (int) ($qrH + ($bottomSpace - $textHeight) / 2);
        imagestring($img, $font, $x, $y, $text, $black);

        // Simpan ke file output permanen
        imagepng($img, $outputPath);

        imagedestroy($img);
        imagedestroy($qrImg);
    }
}