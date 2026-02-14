<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user LOGIN dan ROLE-nya OWNER
        if (Auth::check() && Auth::user()->role === 'owner') {
            return $next($request);
        }

        // Jika bukan owner (misal: Kasir), tolak akses
        abort(403, 'Akses Ditolak. Halaman ini khusus Owner.');
    }
}