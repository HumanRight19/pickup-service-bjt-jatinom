<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use App\Models\PenjadwalanHarian;
use App\Models\BlokPasar;

class ManajemenUserController extends Controller
{
    public function index()
    {
        return Inertia::render('Supervisor/PenugasanIndex', [
            'user' => auth()->user(), // 🔥 ini wajib, biar props.user ada
            'users' => User::where('role', 'petugas')->get(),
            'jadwals' => PenjadwalanHarian::with(['petugas', 'supervisor', 'blok'])->latest()->get(),
            'bloks' => BlokPasar::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'petugas',
        ]);

        return redirect()->route('supervisor.penugasan')
            ->with('success', 'Petugas berhasil ditambahkan');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->filled('password') ? bcrypt($request->password) : $user->password,
        ]);

        return redirect()->route('supervisor.penugasan')
            ->with('success', 'Petugas diperbarui');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('supervisor.penugasan')
            ->with('success', 'Petugas dihapus');
    }

}
