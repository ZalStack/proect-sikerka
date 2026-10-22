<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menendang sesi akun yang SEDANG aktif (sudah login) ketika akunnya
 * ditangguhkan (suspend) oleh Superadmin — misal suspend diberlakukan
 * saat karyawan tersebut sedang login.
 *
 * Lapisan pertama ada di AuthenticatedSessionController (memblokir
 * upaya login baru), middleware ini adalah lapisan pengaman kedua.
 */
class CheckAccountSuspension
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !method_exists($user, 'isSuspended') || !$user->isSuspended()) {
            return $next($request);
        }

        // Superadmin tidak pernah bisa disuspend (guard juga ada di controller),
        // tetap bypass demi keamanan akses sistem.
        if ($user->posisi === 'superadmin') {
            return $next($request);
        }

        $sampai = $user->suspend_end ? $user->suspend_end->isoFormat('D MMM Y') : '-';
        $alasan = method_exists($user, 'suspensionReasonLabel') ? $user->suspensionReasonLabel() : '-';
        $message = "Sesi Anda diakhiri. Akun ditangguhkan (suspend) hingga {$sampai}. Alasan: {$alasan} Hubungi Superadmin untuk mengaktifkan kembali akun Anda.";

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'code' => 'ACCOUNT_SUSPENDED',
                'message' => $message,
                'suspend_until' => $user->suspend_end ? $user->suspend_end->toDateString() : null,
            ], 403);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', $message);
    }
}
