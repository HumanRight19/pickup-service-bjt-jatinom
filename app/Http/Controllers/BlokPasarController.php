<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlokPasar;
use Inertia\Inertia;

class BlokPasarController extends Controller
{
    public function index()
    {
        $blokPasars = BlokPasar::orderBy('nama_blok')->get();

        return Inertia::render('Supervisor/BlokPasarIndex', [
            'user' => auth()->user(),
            'blokPasars' => $blokPasars,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_blok' => 'required|string|max:255',
        ]);

        BlokPasar::create([
            'nama_blok' => $request->nama_blok,
        ]);

        return back();
    }

    public function update(Request $request, BlokPasar $blok)
    {
        $request->validate([
            'nama_blok' => 'required|string|max:255',
        ]);

        $blok->update([
            'nama_blok' => $request->nama_blok,
        ]);

        return back();
    }

    public function destroy(BlokPasar $blok)
    {
        $blok->delete();

        return back();
    }
}
