<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AccessLog;
use Illuminate\Support\Facades\Auth;

class CheckIpAccess
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Mapping role => daftar IP yang boleh
        $allowedIps = [
            'supervisor' => ['172.18.0.1'], // contoh: ip kantor + localhost
            // 'supervisor' => ['127.0.0.1'],
        ];

        $ip = $request->ip();

        // Kalau role tidak dikenal, langsung tolak
        if (!isset($allowedIps[$role])) {
            return response()->json(['message' => 'Role not configured'], 403);
        }

        // Kalau IP tidak ada di whitelist -> log + block
        if (!in_array($ip, $allowedIps[$role])) {
            AccessLog::create([
                'user_id' => Auth::id(),
                'role' => $role,
                'ip' => $ip,
                'path' => $request->path(),
                'status' => 'denied',
            ]);

            session()->forget('url.intended');

            // // Force non-Inertia
            // if ($request->header('X-Inertia')) {
            //     // Kalau request Inertia, redirect paksa ke halaman error
            //     return redirect()->to('/unauthorized-ip');
            // }

            // return response()->view('errors.unauthorized-ip', [
            //     'ip' => $ip,
            //     'role' => $role,
            // ], 403);

            // Redirect ke route yang menampilkan modal
            return redirect()->route('unauthorized-ip');
        }

        // Kalau lolos -> catat sebagai access granted (opsional)
        AccessLog::create([
            'user_id' => Auth::id(),
            'role' => $role,
            'ip' => $ip,
            'path' => $request->path(),
            'status' => 'granted',
        ]);

        return $next($request);
    }
}
