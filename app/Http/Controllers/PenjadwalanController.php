<?php

namespace App\Http\Controllers;

use App\Models\PenjadwalanHarian;
use App\Models\BlokPasar; // <--- WAJIB ada ini
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\TitipSetoran;
use App\Models\Setoran;
use Illuminate\Support\Facades\DB;


class PenjadwalanController extends Controller
{
    public function __construct()
    {
        // Hanya bisa diakses oleh supervisor
        $this->middleware(['auth', 'role:supervisor']);
    }

    public function index(Request $request)
    {
        $query = PenjadwalanHarian::with(['petugas', 'supervisor', 'blok']);

        // Filter opsional
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('petugas_id')) {
            $query->where('petugas_id', $request->petugas_id);
        }

        if ($request->filled('blok')) {
            $query->where('blok_id', $request->blok); // perbaikan: sebelumnya typo "blok" vs "blok_id"
        }

        $today = now()->toDateString();

        // Pagination 10 data per halaman
        $jadwals = $query->orderByDesc('tanggal')->paginate(10)->withQueryString();

        return Inertia::render('Supervisor/PenugasanIndex', [
            'user' => auth()->user(),
            'users' => User::where('role', 'petugas')->get(),
            'jadwals' => $jadwals,
            'filters' => $request->only(['tanggal', 'petugas_id', 'blok']),
            'bloks' => BlokPasar::select('id', 'nama_blok')->get(),
        ]);
    }

    /**
     * Simpan penugasan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
            'petugas_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
            'blok_id' => ['required', Rule::exists('blok_pasars', 'id')],
        ]);

        // Cek apakah tanggal ini sudah pernah dijadwalkan
        $existing = PenjadwalanHarian::with(['petugas', 'blok'])
            ->whereDate('tanggal', $request->input('tanggal'))
            ->first();

        if ($existing) {
            $tanggal = date('d-m-Y', strtotime($request->input('tanggal')));
            $petugas = $existing->petugas->name ?? 'Petugas';
            $blok = $existing->blok->nama_blok ?? 'Blok';
            return back()->withErrors([
                'tanggal' => "Tanggal {$tanggal} sudah digunakan oleh {$petugas} untuk {$blok}.",
            ]);
        }

        PenjadwalanHarian::create([
            'tanggal' => $request->input('tanggal'),
            'petugas_id' => $request->input('petugas_id'),
            'supervisor_id' => $request->input('supervisor_id'),
            'blok_id' => $request->input('blok_id'),
            'ditetapkan_oleh' => auth()->id(),
        ]);

        return redirect()->route('supervisor.penugasan')
            ->with('success', 'Penugasan berhasil ditambahkan & titipan diproses.');
    }

    public function destroyJadwal($id)
    {
        $jadwal = PenjadwalanHarian::findOrFail($id);
        $jadwal->delete();

        return back()->with('success', 'Jadwal berhasil dibatalkan.');
    }

}
