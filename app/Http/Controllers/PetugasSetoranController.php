<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Setoran;
use App\Models\PenjadwalanHarian;
use Illuminate\Http\Request;
use App\Models\SetoranRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\BlokPasar;
use Inertia\Inertia;

class PetugasSetoranController extends Controller
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

        /**
         * Gunakan session agar URL tetap bersih.
         * Jika ada input baru, perbarui session-nya.
         */
        if ($request->isMethod('post')) {
            if ($request->has('search')) {
                session(['dashboard_search' => $request->input('search')]);
                session(['dashboard_page' => 1]); // reset ke halaman pertama saat ganti search
            }
            if ($request->has('page')) {
                session(['dashboard_page' => $request->input('page')]);
            }
        }

        // Ambil nilai search dan page dari session
        $search = session('dashboard_search', null);
        $page = session('dashboard_page', 1);

        // Query nasabah berdasarkan blok
        $query = Nasabah::where('blok_pasar_id', $blok->id)
            ->with([
                'setorans' => fn($q) => $q
                    ->whereDate('tanggal', today())
                    ->where('user_id', $user->id)
                    ->orderByDesc('id'),
                'setorans.requests' => fn($q) => $q->latest(),
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nama_umplung', 'like', "%{$search}%");
            });
        }

        $nasabahs = $query->paginate(10, ['*'], 'page', $page);
        $nasabahs->withPath(route('petugas.dashboard')); // jaga URL tetap bersih

        // Mapping setoran + status
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
        $totalSetoranHariIni = \App\Models\Setoran::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->whereIn('status', ['sudah_setor', 'pengajuan_batal_ditolak'])
            ->sum('jumlah');

        // dd($nasabahs->first()->setorans);

        return Inertia::render('Petugas/Dashboard', [
            'nasabahs' => $nasabahs,
            'setorans' => $setorans->toArray(),
            'blok' => [
                'id' => $blok->id,
                'nama_blok' => $blok->nama_blok,
            ],
            'petugas' => $user,
            'totalSetoranHariIni' => (int) $totalSetoranHariIni,
            'search' => $search,
        ]);
    }

    // Simpan setoran utama
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nasabah_id' => 'required|exists:nasabahs,id',
            'jumlah' => 'required|numeric|min:0',
            'tanggal' => 'nullable|date',
        ]);

        $user = auth()->user();
        $tanggal = $validated['tanggal'] ?? now()->toDateString();

        $nasabah = Nasabah::findOrFail($validated['nasabah_id']);
        $blokDitugaskan = $user->blokHariIni?->blok;

        if (!$blokDitugaskan || $nasabah->blok_pasar_id !== $blokDitugaskan->id) {
            abort(403, 'Tidak diizinkan mencatat setoran untuk nasabah ini.');
        }

        // Ambil setoran hari ini untuk nasabah ini
        $existingSetoran = Setoran::where('nasabah_id', $nasabah->id)
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // Cek apakah ada pengajuan batal yang masih pending
        $pendingBatal = $existingSetoran
            ? SetoranRequest::where('setoranable_type', Setoran::class)
                ->where('setoranable_id', $existingSetoran->id)
                ->where('status', 'pending')
                ->where('type', 'batal')
                ->exists()
            : false;

        // Cek apakah ada pengajuan batal yang sudah approved
        $approvedBatal = $existingSetoran
            ? SetoranRequest::where('setoranable_type', Setoran::class)
                ->where('setoranable_id', $existingSetoran->id)
                ->where('status', 'approved')
                ->where('type', 'batal')
                ->exists()
            : false;

        // Logic keputusan
        if ($existingSetoran && !$approvedBatal) {
            if ($pendingBatal) {
                return response()->json([
                    'message' => 'Pengajuan batal sedang menunggu persetujuan supervisor.'
                ], 422);
            }
            return response()->json([
                'message' => 'Setoran untuk nasabah ini hari ini sudah ada. Gunakan menu update.'
            ], 422);
        }

        // Ambil supervisor dari penjadwalan
        $penjadwalan = PenjadwalanHarian::where('petugas_id', $user->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // Buat setoran baru (baik setoran pertama atau setelah batal disetujui)
        $setoran = Setoran::create([
            'nasabah_id' => $nasabah->id,
            'user_id' => $user->id,
            'supervisor_id' => $penjadwalan?->supervisor_id,
            'jumlah' => $validated['jumlah'],
            'tanggal' => $tanggal,
        ]);

        // Hitung total setoran setelah input baru
        $totalSetoran = Setoran::where('user_id', $user->id)
            ->whereDate('tanggal', now()->toDateString())
            ->sum('jumlah');

        return response()->json([
            'setoran' => [
                'id' => $setoran->id,
                'nasabah_id' => $setoran->nasabah_id,
                'jumlah' => $setoran->jumlah,
                'tanggal' => \Carbon\Carbon::parse($setoran->tanggal)->toDateString(),
                'status' => 'sudah_setor',
                'user_id' => $setoran->user_id,
                'totalSetoran' => $totalSetoran, // tambahan ini
            ],
        ]);
    }

    // Simpan nasabah yang dipilih ke session untuk detail
    public function storeToSession(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|integer|exists:nasabahs,id',
        ]);

        session(['nasabah_detail_id' => $request->nasabah_id]);

        // redirect ke halaman detail
        return redirect()->route('petugas.nasabah.detail');
    }

    // Tampilkan detail nasabah (setoran utama)
    public function show()
    {
        $uuid = session('nasabah_detail_id');
        if (!$uuid)
            abort(404);

        $nasabah = Nasabah::with([
            'blokPasar',
            'setorans' => fn($q) => $q
                ->where('status', '!=', 'batal')
                ->orderByDesc('tanggal')
                ->with('requests'),            // 🔑 ini penting
        ])->findOrFail($uuid);

        return Inertia::render('Petugas/NasabahDetail', [
            'nasabah' => $nasabah,
            'petugas' => auth()->user(),
        ]);
    }

    // Ajukan edit nominal setoran
    public function ajukanEdit(Request $request)
    {
        $validated = $request->validate([
            'setoran_id' => 'required|exists:setorans,id',
            'nominal_baru' => 'required|numeric|min:1',
        ]);

        $setoran = Setoran::findOrFail($validated['setoran_id']);

        // Cek apakah sudah ada request pending
        if ($setoran->requests()->where('status', 'pending')->exists()) {
            return back()->withErrors(['msg' => 'Sudah ada pengajuan koreksi untuk setoran ini.']);
        }

        // Buat request koreksi
        $setoran->requests()->create([
            'petugas_id' => auth()->id(),
            'jumlah_lama' => $setoran->jumlah,
            'jumlah_baru' => $validated['nominal_baru'],
            'type' => 'update',
            'status' => 'pending',
            'alasan' => 'Update nominal otomatis pada ' . now()->format('d-m-Y H:i'),
        ]);

        return back()->with('success', 'Pengajuan edit nominal berhasil dikirim.');
    }

    // Persiapan cetak bukti setoran
    public function prepareCetak(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:nasabahs,id',
        ]);

        $user = auth()->user();
        $today = now()->toDateString();

        $setoran = Setoran::where('nasabah_id', $request->nasabah_id)
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->latest('id') // 🔑 pastikan ambil setoran terbaru
            ->first();

        if (!$setoran) {
            return response()->json([
                'success' => false,
                'message' => 'Data setoran tidak ditemukan',
            ]);
        }

        // Simpan ke session
        session([
            'cetak_id' => $setoran->id,
            'cetak_expires_at' => now()->addMinutes(10),
        ]);

        return response()->json(['success' => true]);
    }

    private function getSetoranFromSession()
    {
        $user = auth()->user();
        $setoranId = session('cetak_id');
        $expiresAt = session('cetak_expires_at');

        if (!$setoranId || now()->greaterThan($expiresAt)) {
            abort(403, 'Sesi cetak sudah kadaluarsa.');
        }

        return Setoran::where('id', $setoranId)
            ->where('user_id', $user->id)
            ->whereDate('tanggal', now()->toDateString())
            ->firstOrFail();
    }

    // Cetak bukti setoran
    public function cetakGabungan()
    {
        $user = auth()->user();
        $id = session('cetak_id');
        $expiresAt = session('cetak_expires_at');

        if (!$id || !$expiresAt || now()->greaterThan($expiresAt)) {
            abort(403, 'Sesi cetak sudah kedaluwarsa.');
        }

        $model = Setoran::with('nasabah.blokPasar')->findOrFail($id);

        return view('pdf.bukti-setoran-gabungan', [
            'model' => $model,
            'petugas' => $user,
            'tipe' => 'setoran',
        ]);
    }

    // Cek apakah session cetak masih aktif
    public function checkSessionActive()
    {
        $expiresAt = session('cetak_expires_at');
        return response()->json([
            'active' => $expiresAt && now()->lessThanOrEqualTo($expiresAt)
        ]);
    }

    // Preview untuk thermal
    public function previewThermal()
    {
        $user = auth()->user();
        $id = session('cetak_id');
        $expiresAt = session('cetak_expires_at');

        if (!$id || !$expiresAt || now()->greaterThan($expiresAt)) {
            abort(403, 'Sesi cetak sudah kedaluwarsa.');
        }

        $setoran = Setoran::with('nasabah.blokPasar')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('pdf.bukti-setoran-gabungan', [
            'setoran' => $setoran,
            'nasabah' => $setoran->nasabah,
            'petugas' => $user,
            'tipe' => 'setoran',
            'model' => $setoran,
        ]);
    }

    // Cari nasabah berdasarkan QR code
    public function findByQr($token)
    {
        // \Log::info('ENTER findByQr token:', ['token' => $token]);
        // \Log::info('App env / DB: ' . config('app.env') . ' / ' . config('database.default'));

        $petugas = auth()->user();
        // \Log::info('Auth user id:', [$petugas?->id]);

        // ambil blok tugas (safety)
        $blokDitugaskan = $petugas->blokHariIni?->blok;
        // \Log::info('blokDitugaskan raw:', [$petugas->blokHariIni, $blokDitugaskan]);

        if (!$blokDitugaskan) {
            // \Log::info('NO BLOK -> abort 403');
            return response()->json(['status' => 'error', 'message' => 'Anda tidak punya blok tugas hari ini.'], 403);
        }

        // // DEBUG COUNTS: cek apakah token ada tanpa blok, ada dengan blok, untuk uuid/qr_token
        // $countUuid = Nasabah::where('uuid', $token)->count();
        // $countQr = Nasabah::where('qr_token', $token)->count();
        // $countUuidBlk = Nasabah::where('uuid', $token)->where('blok_pasar_id', $blokDitugaskan->id)->count();
        // $countQrBlk = Nasabah::where('qr_token', $token)->where('blok_pasar_id', $blokDitugaskan->id)->count();

        // \Log::info('DEBUG counts', [
        //     'countUuid' => $countUuid,
        //     'countQr' => $countQr,
        //     'countUuidBlk' => $countUuidBlk,
        //     'countQrBlk' => $countQrBlk,
        //     'blok_id_petugas' => $blokDitugaskan->id,
        // ]);

        // actual query (sama seperti sebelumnya)
        $nasabah = Nasabah::with('blokPasar:id,nama_blok')
            ->where(function ($q) use ($token) {
                $q->where('uuid', $token)
                    ->orWhere('qr_token', $token);
            })
            ->where('blok_pasar_id', $blokDitugaskan->id)
            ->first();

        // \Log::info('Query result nasabah:', [$nasabah]);

        if (!$nasabah) {
            return response()->json(['status' => 'error', 'message' => 'Nasabah tidak ditemukan atau bukan bagian dari blok tugas Anda.'], 404);
        }

        // \Log::info("ByQR response:", [$nasabah]);

        return response()->json($nasabah);
    }

    // Batal setoran
    public function destroy(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $setoran = Setoran::where('id', $id)
            ->where('user_id', $user->id)
            ->whereDate('tanggal', now()->toDateString())
            ->firstOrFail();

        // Buat request pembatalan
        SetoranRequest::create([
            'setoranable_type' => Setoran::class,
            'setoranable_id' => $setoran->id,
            'petugas_id' => $user->id,
            'supervisor_id' => $setoran->supervisor_id,
            'jumlah_lama' => $setoran->jumlah,
            'jumlah_baru' => null,
            'alasan' => $validated['alasan'],
            'status' => 'pending',
            'type' => 'batal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan pembatalan setoran dikirim ke supervisor.'
        ]);
    }

}