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

    /**
     * Tampilkan halaman penjadwalan petugas hari ini
     */
    // public function index(Request $request)
    // {
    //     $query = PenjadwalanHarian::with(['petugas', 'supervisor', 'blok']); // <--- penting!

    //     // Filter opsional
    //     if ($request->filled('tanggal')) {
    //         $query->whereDate('tanggal', $request->input('tanggal'));
    //     }

    //     if ($request->filled('petugas_id')) {
    //         $query->where('petugas_id', $request->input('petugas_id'));
    //     }

    //     if ($request->filled('blok')) {
    //         $query->where('blok_id', $request->input('blok')); // harusnya blok_id
    //     }

    //     $today = now()->toDateString();

    //     $jadwals = $query->with([
    //         'blok.nasabahs' => function ($q) use ($today) {
    //             $q->with([
    //                 'titipSetorans' => function ($sub) use ($today) {
    //                     $sub->whereDate('tanggal_titip', '<', $today);
    //                 }
    //             ]);
    //         }
    //     ])->orderByDesc('tanggal')->get();

    //     return Inertia::render('Supervisor/PenugasanIndex', [
    //         'user' => auth()->user(),
    //         'users' => User::where('role', 'petugas')->get(),
    //         'jadwals' => $jadwals,
    //         'filters' => $request->only(['tanggal', 'petugas_id', 'blok']),
    //         'bloks' => BlokPasar::select('id', 'nama_blok')->get(),
    //     ]);
    // }

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

        // setelah sukses buat penjadwalan, langsung proses titipan
        $titipan = TitipSetoran::where('blok_id', $request->blok_id)
            // ->where('is_processed', false)
            ->whereDate('tanggal_titip', '<', $request->tanggal) // <— penting
            ->get();

        // DB::transaction(function () use ($request, $titipan) {
        //     foreach ($titipan as $t) {
        //         $setoran = Setoran::where('nasabah_id', $t->nasabah_id)
        //             ->whereDate('tanggal', $request->tanggal)
        //             ->first();

        //         if ($setoran) {
        //             $setoran->increment('jumlah', $t->jumlah);
        //         } else {
        //             Setoran::create([
        //                 'nasabah_id' => $t->nasabah_id,
        //                 'tanggal' => $request->tanggal,
        //                 'jumlah' => $t->jumlah,
        //                 'user_id' => $request->petugas_id,
        //                 'supervisor_id' => $request->supervisor_id,
        //                 'status' => 'sudah',
        //             ]);
        //         }

        //         $t->update(['is_processed' => true]);
        //     }
        // });

        return redirect()->route('supervisor.penugasan')
            ->with('success', 'Penugasan berhasil ditambahkan & titipan diproses.');
    }
}
