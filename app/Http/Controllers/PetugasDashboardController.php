<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Setoran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PetugasDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $blok = $user->blokHariIni?->blok;

        if (!$blok) {
            return Inertia::render('Petugas/Dashboard', [
                'nasabahs' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                    'links' => [],
                ],
                'setorans' => [],
                'blok' => ['nama_blok' => 'Belum ada penugasan'],
                'petugas' => $user,
                'totalSetoranHariIni' => 0,
                'search' => null,
            ]);
        }

        // Ambil search & page
        $search = $request->input('search', null);
        $page = $request->input('page', 1);

        // Query nasabah
        $query = Nasabah::where('blok_pasar_id', $blok->id)
            ->with([
                'setorans' => fn($q) => $q
                    ->whereDate('tanggal', today())
                    ->where('user_id', $user->id)
                    ->orderByDesc('id'),
                'setorans.requests' => fn($q) => $q->latest(),
            ]);

        if ($search) {
            $query->where(
                fn($q) =>
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nama_umplung', 'like', "%{$search}%")
            );
        }

        $nasabahs = $query->paginate(10, ['*'], 'page', $page);
        $nasabahs->withPath(route('petugas.dashboard')); // URL tetap bersih

        // Mapping setoran
        $setorans = $nasabahs->mapWithKeys(function ($nasabah) use ($user) {
            $setoran = $nasabah->setorans->first();
            $latestRequest = $setoran?->requests->first();

            if (!$setoran)
                $status = 'belum_setor';
            elseif ($latestRequest?->status === 'pending' && $latestRequest->type === 'batal')
                $status = 'pengajuan_batal';
            elseif ($latestRequest?->status === 'rejected' && $latestRequest->type === 'batal')
                $status = 'pengajuan_batal_ditolak';
            elseif ($latestRequest?->status === 'approved' && $latestRequest->type === 'batal')
                $status = 'pengajuan_batal_diterima';
            else
                $status = 'sudah_setor';

            return [
                $nasabah->id => [
                    'setoran_id' => $setoran->id ?? null,
                    'nasabah_id' => $nasabah->id,
                    'jumlah' => $setoran->jumlah ?? 0,
                    'tanggal' => $setoran ? Carbon::parse($setoran->tanggal)->toDateString() : null,
                    'status' => $status,
                    'user_id' => $user->id,
                ]
            ];
        });

        // Total setoran hari ini
        $totalSetoranHariIni = Setoran::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->whereIn('status', ['sudah_setor', 'pengajuan_batal_ditolak'])
            ->sum('jumlah');

        return Inertia::render('Petugas/Dashboard', [
            'nasabahs' => $nasabahs,
            'setorans' => $setorans,
            'blok' => [
                'id' => $blok->id,
                'nama_blok' => $blok->nama_blok,
            ],
            'petugas' => $user,
            'totalSetoranHariIni' => (int) $totalSetoranHariIni,
            'search' => $search,
        ]);
    }

    // --- Endpoint untuk update page ---
    public function setPage(Request $request)
    {
        session(['dashboard_page' => $request->page]);
        return response()->json(['success' => true]);
    }

    // --- Endpoint untuk update search ---
    public function setSearch(Request $request)
    {
        session(['dashboard_search' => $request->search]);
        session(['dashboard_page' => 1]); // reset page ke 1 saat search
        return response()->json(['success' => true]);
    }
}
