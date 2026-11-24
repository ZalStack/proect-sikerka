<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RootAdminOnlyMiddleware
{
    /**
     * Membatasi akses rute hanya untuk akun Root Super Admin (.env in-memory).
     * Superadmin biasa (database) akan ditolak dengan error 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (Auth::check() && $user && method_exists($user, 'isVirtual') && $user->isVirtual()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Halaman ini hanya tersedia untuk Root Administrator.',
            ], 403);
        }

        abort(403, 'Akses ditolak. Halaman ini hanya tersedia untuk Root Administrator.');
    }
}
