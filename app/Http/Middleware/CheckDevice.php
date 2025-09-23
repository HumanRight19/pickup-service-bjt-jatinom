<?php
/*
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDevice
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $deviceId = $request->header('X-DEVICE-ID'); // atau $request->input('device_id');

        if ($user->role === 'petugas') {
            if (!$user->device_id) {
                // Daftarkan device baru pertama kali
                $user->device_id = $deviceId;
                $user->save();
            } elseif ($user->device_id !== $deviceId) {
                // Device berbeda → abort atau logout
                Auth::logout();
                return redirect('/login')
                    ->with('error', 'Device belum terdaftar. Hubungi supervisor.');
            }
        }

        return $next($request);
    }
}

*/
