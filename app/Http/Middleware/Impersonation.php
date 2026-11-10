<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mode impersonation (Fitur A4): Super Admin "masuk sebagai" karyawan/HR
 * untuk keperluan debugging laporan, tanpa meminta kredensial.
 *
 * Sesi menyimpan user asli sehingga impersonasi dapat dihentikan kapan pun.
 * Selama impersonasi berlangsung, user TIDAK dapat mengakses route superadmin
 * (posisi aktif adalah posisi target), dan seluruh aksi tetap tercatat dengan
 * metadata "impersonated_by".
 */
class Impersonation
{
    public const SESSION_KEY = 'impersonator_id';

    /**
     * Paksa berhenti bila user asli sudah tidak valid / sudah resign —
     * mencegah sesi impersonasi nyangkut setelah akun target dihapus.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $originalId = session(self::SESSION_KEY);

        if ($originalId !== null) {
            $original = \App\Models\Karyawan::find($originalId);

            if (!$original || $original->is_resigned) {
                Auth::logout();
                session()->forget([self::SESSION_KEY, 'impersonator_email']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Sesi impersonasi diakhiri karena akun asli tidak tersedia.');
            }
        }

        $response = $next($request);

        return $response;
    }

    // ==========================================================
    // HELPER STATIK DIPAKAI CONTROLLER
    // ==========================================================

    public static function start(\App\Models\Karyawan $original, \App\Models\Karyawan $target): void
    {
        session([
            self::SESSION_KEY => $original->id,
            'impersonator_email' => $original->email,
        ]);
    }

    public static function stop(): void
    {
        session()->forget([self::SESSION_KEY, 'impersonator_email']);
    }
}
