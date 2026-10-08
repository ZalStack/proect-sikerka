<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->posisi === 'superadmin') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Halaman ini hanya dapat diakses oleh Super Admin.',
            ], 403);
        }

        if (Auth::check()) {
            if (Auth::user()->posisi === 'hr') {
                return redirect()->route('hr.dashboard')->with('error', 'Akses khusus Super Admin.');
            }
            return redirect()->route('karyawan.dashboard')->with('error', 'Akses khusus Super Admin.');
        }

        return redirect()->route('superadmin.login');
    }
}
