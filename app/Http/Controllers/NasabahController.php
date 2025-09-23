<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\BlokPasar;
use App\Models\Setoran;
use Inertia\Inertia;
use App\Events\SetoranBaru;

class NasabahController extends Controller
{
    public function index(Request $request)
    {
        $blokId = $request->input('blok_id');
        $search = $request->input('search');

        $nasabahs = Nasabah::with('blokPasar')
            ->when($blokId, fn($q) => $q->where('blok_pasar_id', $blokId))
            ->when($search, fn($q) => $q->where('nama', 'like', "%$search%"))
            ->get();

        $blokPasars = BlokPasar::all();

        return Inertia::render('Petugas/NasabahIndex', [
            'nasabahs' => $nasabahs,
            'blokPasars' => $blokPasars,
        ]);
    }

    /**
     * Simpan setoran nasabah.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Nasabah $nasabah
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSetoran(Request $request, Nasabah $nasabah)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:100',
        ]);

        Setoran::create([
            'nasabah_id' => $nasabah->id,
            'user_id' => auth()->id(),
            'tanggal' => now()->toDateString(),
            'jumlah' => $request->input('jumlah'),
            'status' => 'sudah',
        ]);

        // Broadcast ke supervisor
        event(new SetoranBaru($nasabah->nama));

        return back()->with('success', 'Setoran berhasil disimpan.');
    }
}
