<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class KaryawanMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->posisi === 'karyawan') {
            return $next($request);
        }

        if (Auth::check() && Auth::user()->posisi === 'hr') {
            return redirect('/hr/dashboard');
        }

        return redirect('/login');
    }
}
