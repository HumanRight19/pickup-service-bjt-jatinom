<?php
/*
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nasabah;
use App\Models\BlokPasar;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NasabahSeeder extends Seeder
{
    public function run(): void
    {
        $blokIds = BlokPasar::pluck('id')->toArray();

        $qrcodeDir = storage_path('app/qrcodes');
        if (!file_exists($qrcodeDir)) {
            mkdir($qrcodeDir, 0755, true);
        }

        for ($i = 1; $i <= 500; $i++) { // 🔥 500 nasabah
            $token = (string) Str::uuid();
            $namaUmplung = "Nasabah Umplung $i";

            $nasabah = Nasabah::create([
                'uuid' => $token,
                'nama' => "Nasabah $i",
                'nama_umplung' => $namaUmplung,
                'nomor_rekening' => str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'alamat' => "Jl. Pasar No. $i",
                'nomor_hp' => str_pad((string) random_int(0, 9999999999), 8, '0', STR_PAD_LEFT),
                'blok_pasar_id' => $blokIds ? $blokIds[array_rand($blokIds)] : null,
                'qr_token' => $token,
            ]);

            $filePath = $qrcodeDir . '/' . $token . '.png';

            QrCode::format('png')
                ->size(300)
                ->margin(1)
                ->generate($token, $filePath);

            $this->addTextBelowQr($filePath, $namaUmplung, $filePath);
        }
    }

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

        imagefilledrectangle($img, 0, 0, $w, $h, $white);
        imagecopy($img, $qrImg, 0, 0, 0, 0, $qrW, $qrH);

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = max(0, (int) (($w - $textWidth) / 2));
        $y = (int) ($qrH + ($bottomSpace - $textHeight) / 2);
        imagestring($img, $font, $x, $y, $text, $black);

        imagepng($img, $outputPath);

        imagedestroy($img);
        imagedestroy($qrImg);
    }
}
*/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nasabah;
use App\Models\BlokPasar;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NasabahSeeder extends Seeder
{
    public function run(): void
    {
        $blokIds = BlokPasar::pluck('id')->toArray();

        $qrcodeDir = storage_path('app/qrcodes');
        if (!file_exists($qrcodeDir)) {
            mkdir($qrcodeDir, 0755, true);
        }

        for ($i = 1; $i <= 10; $i++) { // 🔥 10 nasabah
            $token = (string) Str::uuid();
            $namaUmplung = "Nasabah Umplung $i";

            $nasabah = Nasabah::create([
                'uuid' => $token,
                'nama' => "Nasabah $i",
                'nama_umplung' => $namaUmplung,
                'nomor_rekening' => str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'alamat' => "Jl. Pasar No. $i",
                'nomor_hp' => str_pad((string) random_int(0, 9999999999), 8, '0', STR_PAD_LEFT),
                'blok_pasar_id' => $blokIds ? $blokIds[array_rand($blokIds)] : null,
                'qr_token' => $token,
            ]);

            $filePath = $qrcodeDir . '/' . $token . '.png';

            QrCode::format('png')
                ->size(300)
                ->margin(1)
                ->generate($token, $filePath);

            $this->addTextBelowQr($filePath, $namaUmplung, $filePath);
        }
    }

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

        imagefilledrectangle($img, 0, 0, $w, $h, $white);
        imagecopy($img, $qrImg, 0, 0, 0, 0, $qrW, $qrH);

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = max(0, (int) (($w - $textWidth) / 2));
        $y = (int) ($qrH + ($bottomSpace - $textHeight) / 2);
        imagestring($img, $font, $x, $y, $text, $black);

        imagepng($img, $outputPath);

        imagedestroy($img);
        imagedestroy($qrImg);
    }
}


