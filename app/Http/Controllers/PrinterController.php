<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setoran;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrinterController extends Controller
{
    public function printStruk($setoranId)
    {
        $setoran = Setoran::with('nasabah', 'petugas')->findOrFail($setoranId);

        // Sesuaikan dengan nama printer kamu
        $connector = new WindowsPrintConnector("RP58 Printer"); // nama printer di Windows

        $printer = new Printer($connector);
        $printer->initialize();

        $printer->text("==== BUKTI SETORAN ====\n");
        $printer->text("Tanggal : " . now()->format('d-m-Y H:i') . "\n");
        $printer->text("Nasabah: " . $setoran->nasabah->nama . "\n");
        $printer->text("Blok   : " . $setoran->nasabah->blokPasar->nama_blok . "\n");
        $printer->text("Petugas: " . $setoran->petugas->name . "\n");
        $printer->text("Jumlah : Rp " . number_format($setoran->jumlah) . "\n");
        $printer->text("========================\n");
        $printer->cut();
        $printer->close();

        return back()->with('success', 'Bukti setoran berhasil dicetak.');
    }
}
