<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HrMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->posisi === 'hr') {
            return $next($request);
        }

        if (Auth::check() && Auth::user()->posisi === 'karyawan') {
            return redirect('/karyawan/dashboard');
        }

        return redirect('/login');
    }
}
