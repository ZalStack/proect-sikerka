<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Memblokir check-in & check-out karyawan yang sedang kena ban presensi
 * (baik ban sementara maupun ban permanen akibat pelanggaran akses remote).
 *
 * Karyawan yang diblokir tetap bisa login dan melihat dashboard
 * (termasuk pesan alert dari Superadmin), hanya fitur presensi yang dinonaktifkan.
 */
class CheckAttendanceBan
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'isBanned') && $user->isBanned()) {
            $permanent = (bool) $user->is_banned_permanent;
            $sisaHari = $user->banDaysRemaining();

            $message = $permanent
                ? 'Presensi ditolak! Akun Anda di-BLOKIR PERMANEN dari fitur check-in/check-out karena 3 pelanggaran kebijakan akses. Hubungi Superadmin untuk peninjauan.'
                : 'Presensi ditolak! Akun Anda diblokir dari fitur check-in/check-out selama ' . ($sisaHari ?? 1) . ' hari karena pelanggaran kebijakan akses (pelanggaran ke-' . $user->violation_count . '). Hubungi Superadmin/HR.';

            return response()->json(
                [
                    'success' => false,
                    'message' => $message,
                    'code' => 'ACCOUNT_BANNED',
                    'ban' => [
                        'permanent' => $permanent,
                        'sisa_hari' => $sisaHari,
                        'banned_until' => $user->banned_until ? $user->banned_until->toDateTimeString() : null,
                        'violation_count' => (int) $user->violation_count,
                        'pesan_superadmin' => $user->ban_message,
                    ],
                ],
                403,
            );
        }

        return $next($request);
    }
}
