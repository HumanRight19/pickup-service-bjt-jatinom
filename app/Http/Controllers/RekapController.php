<?php

namespace App\Http\Controllers;

use App\Models\Setoran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'harian');
        $today = now();

        $query = Setoran::with(['nasabah', 'petugas']);

        switch ($filter) {
            case 'harian':
                $query->whereDate('tanggal_setor', $today);
                break;
            case 'mingguan':
                $query->whereBetween('tanggal_setor', [
                    $today->copy()->startOfWeek(),
                    $today->copy()->endOfWeek(),
                ]);
                break;
            case 'bulanan':
                $query->whereMonth('tanggal_setor', $today->month)
                    ->whereYear('tanggal_setor', $today->year);
                break;
            case 'tahunan':
                $query->whereYear('tanggal_setor', $today->year);
                break;
        }

        $setorans = $query->orderByDesc('tanggal_setor')->get();

        $total = $setorans->sum('jumlah');

        return Inertia::render('Supervisor/RekapIndex', [
            'setorans' => $setorans,
            'total' => $total,
            'filter' => $filter,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $filter = $request->input('filter', 'harian');
        $today = now();

        $query = Setoran::with(['nasabah', 'petugas']);

        switch ($filter) {
            case 'harian':
                $query->whereDate('tanggal_setor', $today);
                break;
            case 'mingguan':
                $query->whereBetween('tanggal_setor', [
                    $today->copy()->startOfWeek(),
                    $today->copy()->endOfWeek(),
                ]);
                break;
            case 'bulanan':
                $query->whereMonth('tanggal_setor', $today->month)
                    ->whereYear('tanggal_setor', $today->year);
                break;
            case 'tahunan':
                $query->whereYear('tanggal_setor', $today->year);
                break;
        }

        $setorans = $query->orderByDesc('tanggal_setor')->get();
        $total = $setorans->sum('jumlah');

        $pdf = Pdf::loadView('pdf.rekap', [
            'setorans' => $setorans,
            'total' => $total,
            'filter' => ucfirst($filter),
        ])->setPaper('A4', 'portrait');

        return $pdf->download("rekap-{$filter}.pdf");
    }
}

