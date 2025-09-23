<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        Log::info('Role Middleware Triggered', [
            'user' => $request->user()?->role,
            'expected' => $role
        ]);

        if (!$request->user() || $request->user()->role !== $role) {
            abort(403);
        }

        return $next($request);
    }
}
