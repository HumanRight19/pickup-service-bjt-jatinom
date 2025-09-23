<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit profil user.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'auth' => ['user' => $request->user()],
            'status' => session('status'),
        ]);
    }

    /**
     * Update profil user.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Hanya update password jika ada input baru, sekaligus di-hash
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']); // jangan timpa password lama dengan null
        }

        $request->user()->update($data);

        return Redirect::route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * (Opsional) Hapus akun user.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

