<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Nasabah;
use App\Models\PenjadwalanHarian;
use App\Models\Setoran;
use App\Models\TitipSetoran;
use App\Models\BlokPasar;
use Carbon\Carbon;
use Inertia\Inertia;

class SupervisorDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // ===================== STATISTIK =====================
        $jumlahPetugas = User::where('role', 'petugas')->count();
        $jumlahNasabah = Nasabah::count();

        // Gabungkan setoran utama + titip setoran (hanya valid / sudah_setor)
        $totalSetoranHariIni = Setoran::where('status', 'sudah_setor')
            ->whereDate('tanggal', $today)
            ->sum('jumlah')
            + TitipSetoran::where('status', 'sudah_setor')
                ->whereDate('tanggal_titip', $today)
                ->sum('jumlah');

        $totalSetoranBulanIni = Setoran::where('status', 'sudah_setor')
            ->whereBetween('tanggal', [$now->copy()->startOfMonth(), $now])
            ->sum('jumlah')
            + TitipSetoran::where('status', 'sudah_setor')
                ->whereBetween('tanggal_titip', [$now->copy()->startOfMonth(), $now])
                ->sum('jumlah');

        $totalSetoranTahunIni = Setoran::where('status', 'sudah_setor')
            ->whereBetween('tanggal', [$now->copy()->startOfYear(), $now])
            ->sum('jumlah')
            + TitipSetoran::where('status', 'sudah_setor')
                ->whereBetween('tanggal_titip', [$now->copy()->startOfYear(), $now])
                ->sum('jumlah');

        // ===================== PETUGAS HARI INI =====================
        $penugasanHariIni = PenjadwalanHarian::with(['petugas', 'blok'])
            ->whereDate('tanggal', $today)
            ->get();

        $petugasHariIni = $penugasanHariIni->map(fn($p) => [
            'id' => $p->petugas->id ?? null,
            'name' => $p->petugas->name ?? '-',
            'blok' => $p->blok->nama_blok ?? '-',
        ])->filter(fn($p) => $p['id'])->values();

        // ===================== GRAFIK HARIAN =====================
        $grafikHarianData = Setoran::selectRaw('DATE(tanggal) as tgl, SUM(jumlah) as total')
            ->where('status', 'sudah_setor')
            ->whereDate('tanggal', '>=', $now->copy()->subDays(6))
            ->groupBy('tgl');

        $grafikHarianTitip = TitipSetoran::selectRaw('DATE(tanggal_titip) as tgl, SUM(jumlah) as total')
            ->where('status', 'sudah_setor')
            ->whereDate('tanggal_titip', '>=', $now->copy()->subDays(6))
            ->groupBy('tgl');

        $grafikHarian = $grafikHarianData->unionAll($grafikHarianTitip)
            ->get()
            ->groupBy('tgl')
            ->map(fn($g) => [
                'tanggal' => Carbon::parse($g->first()->tgl)->format('d M'),
                'jumlah' => $g->sum('total'),
            ])->values();

        // ===================== GRAFIK BULANAN =====================
        $grafikBulananData = Setoran::selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan, SUM(jumlah) as total')
            ->where('status', 'sudah_setor')
            ->whereDate('tanggal', '>=', $now->copy()->subMonths(5)->startOfMonth())
            ->groupBy('bulan');

        $grafikBulananTitip = TitipSetoran::selectRaw('DATE_FORMAT(tanggal_titip, "%Y-%m") as bulan, SUM(jumlah) as total')
            ->where('status', 'sudah_setor')
            ->whereDate('tanggal_titip', '>=', $now->copy()->subMonths(5)->startOfMonth())
            ->groupBy('bulan');

        $grafikBulanan = $grafikBulananData->unionAll($grafikBulananTitip)
            ->get()
            ->groupBy('bulan')
            ->map(fn($g) => [
                'bulan' => Carbon::parse($g->first()->bulan . '-01')->format('M Y'),
                'jumlah' => $g->sum('total'),
            ])->values();

        // ===================== GRAFIK TAHUNAN =====================
        $grafikTahunanData = Setoran::selectRaw('MONTH(tanggal) as bulan, SUM(jumlah) as total')
            ->where('status', 'sudah_setor')
            ->whereYear('tanggal', $now->year)
            ->groupBy('bulan');

        $grafikTahunanTitip = TitipSetoran::selectRaw('MONTH(tanggal_titip) as bulan, SUM(jumlah) as total')
            ->where('status', 'sudah_setor')
            ->whereYear('tanggal_titip', $now->year)
            ->groupBy('bulan');

        $grafikTahunan = $grafikTahunanData->unionAll($grafikTahunanTitip)
            ->get()
            ->groupBy('bulan')
            ->map(fn($g) => [
                'bulan' => Carbon::createFromDate(null, $g->first()->bulan, 1)->format('M'),
                'jumlah' => $g->sum('total'),
            ])->values();

        // ===================== PERGERAKAN SETORAN PER BLOK =====================
        $startThisWeek = $now->copy()->startOfWeek();
        $startLastWeek = $now->copy()->subWeek()->startOfWeek();
        $endLastWeek = $now->copy()->subWeek()->endOfWeek();

        $setoranMingguIni = Setoran::with('nasabah.blokPasar')
            ->where('status', 'sudah_setor')
            ->whereBetween('tanggal', [$startThisWeek, $now])
            ->get();

        $titipMingguIni = TitipSetoran::with('nasabah.blokPasar')
            ->where('status', 'sudah_setor')
            ->whereBetween('tanggal_titip', [$startThisWeek, $now])
            ->get();

        $totalMingguIni = $setoranMingguIni->merge($titipMingguIni)
            ->groupBy(fn($s) => $s->nasabah->blokPasar->nama_blok ?? 'Tanpa Blok')
            ->map(fn($g) => $g->sum('jumlah'));

        $setoranMingguLalu = Setoran::with('nasabah.blokPasar')
            ->where('status', 'sudah_setor')
            ->whereBetween('tanggal', [$startLastWeek, $endLastWeek])
            ->get();

        $titipMingguLalu = TitipSetoran::with('nasabah.blokPasar')
            ->where('status', 'sudah_setor')
            ->whereBetween('tanggal_titip', [$startLastWeek, $endLastWeek])
            ->get();

        $totalMingguLalu = $setoranMingguLalu->merge($titipMingguLalu)
            ->groupBy(fn($s) => $s->nasabah->blokPasar->nama_blok ?? 'Tanpa Blok')
            ->map(fn($g) => $g->sum('jumlah'));

        $blokList = $totalMingguIni->keys()->merge($totalMingguLalu->keys())->unique();

        $pergerakanSetoranPerBlok = $blokList->map(fn($blok) => [
            'nama_blok' => $blok,
            'minggu_ini' => $totalMingguIni[$blok] ?? 0,
            'minggu_lalu' => $totalMingguLalu[$blok] ?? 0,
        ])->values();

        // ===================== RETURN =====================
        return Inertia::render('Supervisor/Dashboard', [
            'user' => auth()->user(),
            'jumlahPetugas' => $jumlahPetugas,
            'jumlahNasabah' => $jumlahNasabah,
            'totalSetoranHariIni' => $totalSetoranHariIni,
            'totalSetoranBulanIni' => $totalSetoranBulanIni,
            'totalSetoranTahunIni' => $totalSetoranTahunIni,
            'petugasHariIni' => $petugasHariIni,
            'grafikHarian' => $grafikHarian,
            'grafikBulanan' => $grafikBulanan,
            'grafikTahunan' => $grafikTahunan,
            'pergerakanSetoranPerBlok' => $pergerakanSetoranPerBlok,
        ]);
    }
}
