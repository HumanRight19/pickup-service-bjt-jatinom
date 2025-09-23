<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdleTimeout
{
    // dalam menit
    protected $timeout = 15;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = session('lastActivityTime');
            $now = now()->timestamp;

            if ($lastActivity && ($now - $lastActivity) > ($this->timeout * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Sesi kamu berakhir karena tidak ada aktivitas.',
                ]);
            }

            // update waktu aktivitas terakhir
            session(['lastActivityTime' => $now]);
        }

        return $next($request);
    }
}
