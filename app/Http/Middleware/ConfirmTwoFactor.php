<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Konfirmasi Two-Factor Auth untuk aksi kritis Super Admin (Fitur A2).
 *
 * Dipasang pada route yang dampaknya tinggi: ganti role, hapus backup,
 * restore database, ban permanen, dsb.
 *
 * Aturan:
 *  - Bila user TIDAK mengaktifkan 2FA → dilewati (fitur opsional, kecuali
 *    sistem menyalakan `require_2fa_superadmin`).
 *  - Bila aktif & sudah pernah konfirmasi di sesi ini → lanjut.
 *  - Selain itu → redirect ke halaman challenge (atau 401 untuk JSON).
 */
class ConfirmTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || $user->posisi !== 'superadmin') {
            return $next($request);
        }

        $hasTwoFactor = !empty($user->two_factor_confirmed_at);

        if (!$hasTwoFactor && !SystemSetting::isTwoFactorRequired()) {
            return $next($request);
        }

        if (!$hasTwoFactor) {
            // Sistem mewajibkan 2FA tapi superadmin belum setup.
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'two_factor_setup_required' => true,
                    'message' => 'Anda wajib mengaktifkan Two-Factor Authentication terlebih dahulu.',
                ], 403);
            }

            return redirect()->route('superadmin.security.two-factor')
                ->with('error', 'Aktifkan Two-Factor Authentication sebelum menjalankan aksi kritis.');
        }

        $verifiedAt = session('twofactor_verified_at');
        $ttl = (int) SystemSetting::getValue('twofactor_challenge_ttl', 600); // 10 menit

        if ($verifiedAt && (time() - (int) $verifiedAt) <= $ttl) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'two_factor_challenge_required' => true,
                'message' => 'Konfirmasi Two-Factor Authentication diperlukan untuk aksi ini.',
                'challenge_url' => route('superadmin.security.two-factor-challenge'),
            ], 401);
        }

        // Request GET → kembali persis ke URL tsb setelah lolos challenge.
        // Request non-GET (POST/PUT/DELETE) → kembali ke halaman asal form
        // (referer), karena GET tidak boleh diarahkan ke URL method POST
        // (berujung 405 Method Not Allowed).
        $returnTo = $request->isMethod('GET')
            ? $request->fullUrl()
            : ($request->headers->get('referer') ?: route('superadmin.security.index'));

        return redirect()->route('superadmin.security.two-factor-challenge', [
            'redirect' => $returnTo,
        ]);
    }
}
