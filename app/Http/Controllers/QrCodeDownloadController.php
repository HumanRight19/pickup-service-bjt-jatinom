<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

class QrCodeDownloadController extends Controller
{
    public function downloadAll()
    {
        $zipFileName = 'qrcodes.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = glob(storage_path('app/qrcodes/*.png'));

            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }

            $zip->close();
        }

        // Kirim ZIP ke browser
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
