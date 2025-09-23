<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class PetugasDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ambil blok hari ini dari relasi penugasan petugas
        $blok = $user->blokHariIni?->blok;

        // Jika petugas belum ada penugasan blok hari ini
        if (!$blok) {
            return Inertia::render('Petugas/Dashboard', [
                'nasabahs' => [],
                'setorans' => [],
                'blok' => [
                    'nama_blok' => 'Belum ada penugasan'
                ],
                'petugas' => $user,
            ]);
        }

        // Ambil semua nasabah di blok yang ditugaskan
        $nasabahs = Nasabah::where('blok_pasar_id', $blok->id)
            ->with([
                // Ambil semua setoran hari ini milik petugas
                'setorans' => fn($q) => $q
                    ->whereDate('tanggal', Carbon::today())
                    ->where('user_id', $user->id)
                    ->orderByDesc('id'),
                // Ambil semua request (batal & update)
                'setorans.requests' => fn($q) => $q->latest(),
            ])
            ->get();

        // Bentuk array setoran per nasabah (map ke status yang jelas)
        $setorans = $nasabahs->mapWithKeys(function ($nasabah) use ($user) {
            $setoran = $nasabah->setorans->first(); // setoran terbaru hari ini
            $latestRequest = $setoran?->requests->first(); // request terbaru

            // cek apakah ada request edit nominal pending
            $hasPendingEdit = $setoran?->requests->contains(function ($r) {
                return $r->status === 'pending' && $r->type === 'update';
            }) ?? false;

            // Tentukan status setoran untuk tombol batal
            if (!$setoran) {
                $status = 'belum_setor';
            } elseif ($latestRequest && $latestRequest->status === 'pending' && $latestRequest->type === 'batal') {
                $status = 'pengajuan_batal';
            } elseif ($latestRequest && $latestRequest->status === 'rejected' && $latestRequest->type === 'batal') {
                $status = 'pengajuan_batal_ditolak';
            } elseif ($latestRequest && $latestRequest->status === 'approved' && $latestRequest->type === 'batal') {
                $status = 'pengajuan_batal_diterima';
            } else {
                $status = 'sudah_setor';
            }

            return [
                (int) $nasabah->id => [
                    'setoran_id' => $setoran->id ?? null,
                    'nasabah_id' => $nasabah->id,
                    'jumlah' => $setoran->jumlah ?? 0,
                    'tanggal' => $setoran ? Carbon::parse($setoran->tanggal)->toDateString() : null,
                    'status' => $status,
                    'user_id' => $user->id,
                    'hasPendingEdit' => $hasPendingEdit, // <-- untuk tombol batal disable
                ],
            ];
        });

        // Kirim data ke Vue via Inertia
        return Inertia::render('Petugas/Dashboard', [
            'nasabahs' => $nasabahs,
            'setorans' => $setorans,
            'blok' => [
                'id' => $blok->id,
                'nama_blok' => $blok->nama_blok,
            ],
            'petugas' => $user,
        ]);
    }
}
