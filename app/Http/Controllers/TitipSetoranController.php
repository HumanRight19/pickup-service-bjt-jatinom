<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\PenjadwalanHarian;
use App\Models\SetoranRequest;
use App\Models\TitipSetoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

class TitipSetoranController extends Controller
{
    /**
     * Baru - Pastikan petugas sedang dijadwalkan hari ini
     */
    private function ensureAssigned()
    {
        $user = Auth::user();
        $penjadwalan = PenjadwalanHarian::where('petugas_id', $user->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        if (!$penjadwalan) {
            abort(403, 'Anda tidak dijadwalkan hari ini.');
        }

        return $penjadwalan;
    }

    /**
     * Index - tampilkan grid nasabah untuk titip setoran
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $penjadwalan = $this->ensureAssigned();

        // Ambil semua nasabah yang TIDAK termasuk blok terjadwal
        $nasabahsBlokTerjadwal = Nasabah::where('blok_pasar_id', $penjadwalan->blok_id)->pluck('id');

        /**
         * Gunakan session agar URL tetap bersih.
         * Jika ada input baru, perbarui session-nya.
         */
        if ($request->isMethod('post')) {
            if ($request->has('search')) {
                session(['titip_search' => $request->input('search')]);
                session(['titip_page' => 1]); // reset ke halaman pertama saat ganti search
            }
            if ($request->has('page')) {
                session(['titip_page' => $request->input('page')]);
            }
        }

        $search = session('titip_search', null);
        $page = session('titip_page', 1);

        // Query nasabah
        $query = Nasabah::whereNotIn('id', $nasabahsBlokTerjadwal)
            ->with([
                'titipSetorans' => fn($q) => $q
                    ->whereDate('tanggal_titip', today())
                    ->where('petugas_id', $user->id)
                    ->orderByDesc('id'),
                'titipSetorans.requests' => fn($q) => $q->latest(),
                'blokPasar'
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nama_umplung', 'like', "%{$search}%");
            });
        }

        $nasabahs = $query->paginate(10, ['*'], 'page', $page);
        $nasabahs->withPath(route('petugas.titipsetoran.index')); // agar URL tetap bersih

        // Mapping titip setoran + status
        $titipSetorans = $nasabahs->mapWithKeys(function ($nasabah) use ($user) {
            $titip = $nasabah->titipSetorans->first();
            $latestRequest = $titip?->requests->first();

            $hasPendingEdit = $titip?->requests->contains(function ($r) {
                return $r->status === 'pending' && $r->type === 'update';
            }) ?? false;

            if (!$titip) {
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
                $nasabah->id => [
                    'titip_setoran_id' => $titip->id ?? null,
                    'nasabah_id' => $nasabah->id,
                    'jumlah' => $titip->jumlah ?? 0,
                    'tanggal' => $titip ? Carbon::parse($titip->tanggal_titip)->toDateString() : null,
                    'status' => $status,
                    'user_id' => $user->id,
                    'hasPendingEdit' => $hasPendingEdit,
                ]
            ];
        });

        // Total titip setoran hari ini
        $totalTitipSetoranHariIni = TitipSetoran::where('petugas_id', $user->id)
            ->whereDate('tanggal_titip', today())
            ->whereIn('status', ['sudah_setor', 'pengajuan_batal_ditolak'])
            ->sum('jumlah');

        return Inertia::render('Petugas/TitipSetoran', [
            'nasabahs' => $nasabahs,
            'titipSetorans' => $titipSetorans->toArray(),
            'petugas' => $user,
            'totalTitipSetoranHariIni' => (int) $totalTitipSetoranHariIni,
            'search' => $search,
        ]);
    }

    /**
     * Store titip setoran baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nasabah_id' => 'required|exists:nasabahs,id',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $penjadwalan = $this->ensureAssigned();
        $tanggal = now()->toDateString();

        // Ambil nasabah
        $nasabah = Nasabah::findOrFail($validated['nasabah_id']);

        // Cek titip setoran hari ini
        $existingTitip = TitipSetoran::where('nasabah_id', $nasabah->id)
            ->where('petugas_id', $user->id)
            ->whereDate('tanggal_titip', $tanggal)
            ->first();

        $pendingBatal = $existingTitip
            ? $existingTitip->requests()->where('status', 'pending')->where('type', 'batal')->exists()
            : false;

        $approvedBatal = $existingTitip
            ? $existingTitip->requests()->where('status', 'approved')->where('type', 'batal')->exists()
            : false;

        if ($existingTitip && !$approvedBatal) {
            if ($pendingBatal) {
                return response()->json([
                    'message' => 'Pengajuan batal sedang menunggu persetujuan supervisor.'
                ], 422);
            }
            return response()->json([
                'message' => 'Titip setoran hari ini sudah ada. Gunakan menu update.'
            ], 422);
        }

        // Buat titip setoran baru
        $titip = TitipSetoran::create([
            'nasabah_id' => $nasabah->id,
            'blok_id' => $nasabah->blok_pasar_id,
            'petugas_id' => $user->id,
            'supervisor_id' => $penjadwalan->supervisor_id,
            'jumlah' => $validated['jumlah'],
            'tanggal_titip' => $tanggal,
            'status' => 'sudah_setor',
        ]);

        return response()->json([
            'titip_setoran' => [
                'id' => $titip->id,
                'nasabah_id' => $titip->nasabah_id,
                'jumlah' => $titip->jumlah,
                'tanggal' => $titip->tanggal_titip,
                'status' => 'sudah_setor',
                'user_id' => $titip->petugas_id,
            ],
        ]);
    }

    // Simpan nasabah yang dipilih ke session (pakai uuid)
    public function storeToSession(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|integer|exists:nasabahs,id',
        ]);

        // Simpan ID ke session
        session(['nasabah_detail_id' => $request->nasabah_id]);

        return redirect()->route('petugas.titipsetoran.detail');
    }

    // Tampilkan detail nasabah (titip setoran)
    public function show()
    {
        $id = session('nasabah_detail_id');
        if (!$id) {
            abort(404);
        }

        $user = Auth::user();

        // Ambil nasabah dengan relasi yang dibutuhkan
        $nasabah = Nasabah::with([
            'blokPasar',
            'setoranHariIni',
            // pastikan eager load blok di titipSetoranHariIni
            'titipSetoranHariIni' => fn($q) => $q->with('blok'),
        ])->findOrFail($id);

        // Ambil titip setoran hari ini langsung (biar konsisten dengan petugas yang login)
        $titipHariIni = TitipSetoran::where('nasabah_id', $nasabah->id)
            ->where('petugas_id', $user->id)
            ->whereDate('tanggal_titip', today())
            ->with(['requests', 'blok'])
            ->latest('id')
            ->first();

        return Inertia::render('Petugas/NasabahDetailTitip', [
            'nasabah' => $nasabah,
            'petugas' => $user,
            'titipHariIni' => $titipHariIni,
        ]);
    }

    /**
     * Ajukan edit nominal titip setoran
     */
    public function ajukanEdit(Request $request)
    {
        $validated = $request->validate([
            'titip_setoran_id' => 'required|exists:titip_setorans,id',
            'nominal_baru' => 'required|numeric|min:1',
        ]);

        $titip = TitipSetoran::findOrFail($validated['titip_setoran_id']);

        if ($titip->requests()->where('status', 'pending')->exists()) {
            return back()->withErrors(['msg' => 'Sudah ada pengajuan koreksi untuk titip setoran ini.']);
        }

        $titip->requests()->create([
            'petugas_id' => Auth::id(),
            'jumlah_lama' => $titip->jumlah,
            'jumlah_baru' => $validated['nominal_baru'],
            'type' => 'update',
            'status' => 'pending',
            'alasan' => 'Update nominal otomatis pada ' . now()->format('d-m-Y H:i'),
        ]);

        return back()->with('success', 'Pengajuan edit nominal berhasil dikirim.');
    }

    /**
     * Batal titip setoran
     */
    public function destroy(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $titip = TitipSetoran::where('id', $id)
            ->where('petugas_id', $user->id)
            ->whereDate('tanggal_titip', now()->toDateString())
            ->firstOrFail();

        SetoranRequest::create([
            'setoranable_type' => TitipSetoran::class,
            'setoranable_id' => $titip->id,
            'petugas_id' => $user->id,
            'supervisor_id' => $titip->supervisor_id,
            'jumlah_lama' => $titip->jumlah,
            'jumlah_baru' => null,
            'alasan' => $validated['alasan'],
            'status' => 'pending',
            'type' => 'batal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan pembatalan titip setoran dikirim ke supervisor.'
        ]);
    }

    /**
     * Prepare cetak bukti titip setoran
     */
    public function prepareCetak(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:nasabahs,id',
        ]);

        $user = Auth::user();
        $today = now()->toDateString();

        $titip = TitipSetoran::where('nasabah_id', $request->nasabah_id)
            ->where('petugas_id', $user->id)
            ->whereDate('tanggal_titip', $today)
            ->latest('id')
            ->first();

        if (!$titip) {
            return response()->json([
                'success' => false,
                'message' => 'Data titip setoran tidak ditemukan',
            ]);
        }

        session([
            'cetak_id' => $titip->id,
            'cetak_expires_at' => now()->addMinutes(10),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Ambil titip setoran dari session
     */
    private function getTitipSetoranFromSession()
    {
        $user = Auth::user();
        $titipId = session('cetak_id');
        $expiresAt = session('cetak_expires_at');

        if (!$titipId || now()->greaterThan($expiresAt)) {
            abort(403, 'Sesi cetak sudah kadaluarsa.');
        }

        return TitipSetoran::where('id', $titipId)
            ->where('petugas_id', $user->id)
            ->whereDate('tanggal_titip', now()->toDateString())
            ->firstOrFail();
    }

    /**
     * Cetak bukti titip setoran
     */
    public function cetakGabungan()
    {
        $model = $this->getTitipSetoranFromSession();
        $user = Auth::user();

        return view('pdf.bukti-setoran-gabungan', [
            'model' => $model,
            'petugas' => $user,
            'tipe' => 'titip_setoran',
        ]);
    }

    /**
     * Preview untuk thermal
     */
    public function previewThermal()
    {
        $titip = $this->getTitipSetoranFromSession();
        $user = Auth::user();

        return view('pdf.bukti-setoran-gabungan', [
            'model' => $titip,
            'nasabah' => $titip->nasabah,
            'petugas' => $user,
            'tipe' => 'titip_setoran',
        ]);
    }

    /**
     * Cek apakah session cetak masih aktif
     */
    public function checkSessionActive()
    {
        $expiresAt = session('cetak_expires_at');
        return response()->json([
            'active' => $expiresAt && now()->lessThanOrEqualTo($expiresAt)
        ]);
    }

    /**
     * Cari nasabah berdasarkan QR
     */
    public function findByQr($token)
    {
        $user = Auth::user();
        $penjadwalan = $this->ensureAssigned();

        // Ambil semua ID nasabah di blok terjadwal
        $nasabahsBlokTerjadwal = Nasabah::where('blok_pasar_id', $penjadwalan->blok_id)
            ->pluck('id')
            ->toArray();

        // Cari nasabah berdasarkan uuid
        $nasabah = Nasabah::with('blokPasar:id,nama_blok')
            ->where('uuid', $token)
            ->first();

        if (!$nasabah) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR tidak valid / nasabah tidak ditemukan'
            ], 404);
        }

        // Cek apakah dia bagian blok supervisor hari ini
        if (in_array($nasabah->id, $nasabahsBlokTerjadwal)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nasabah ini termasuk blok tugas Anda, bukan titipan.'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $nasabah
        ]);
    }
}
