<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $this->ensureIsNotRateLimited($request);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), 60); // catat percobaan gagal

            throw ValidationException::withMessages([
                'email' => ['Email dan password tidak sesuai'],
                'retry_after' => 60,
            ]);
        }

        RateLimiter::clear($this->throttleKey($request)); // reset kalau berhasil login

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function ensureIsNotRateLimited(Request $request)
    {
        $key = $this->throttleKey($request);
        $maxAttempts = 3;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            // sisa detik konsisten 60
            $seconds = 60;
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again later.'],
                'retry_after' => $seconds,
            ])->status(429);
        }
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}
