<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use App\Models\SystemSetting;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Pusat pengaturan keamanan Super Admin (Fase 1):
 *  - Two-Factor Authentication (setup / challenge / disable)
 *  - Whitelist IP akses panel Super Admin
 *  - Manajemen sesi aktif (lihat & paksa logout)
 *  - Sakelar kewajiban 2FA untuk seluruh Super Admin
 */
class SecurityController extends Controller
{
    public function __construct(private TwoFactorService $totp)
    {
    }

    /** Halaman pusat keamanan. */
    public function index(Request $request)
    {
        $user = Auth::user();
        $sessions = $this->activeSessions();

        return view('superadmin.security.index', [
            'user' => $user,
            'twoFactorEnabled' => !empty($user->two_factor_confirmed_at),
            'twoFactorRequired' => SystemSetting::isTwoFactorRequired(),
            'whitelist' => SystemSetting::superadminIpWhitelist(),
            'currentIp' => (string) $request->ip(),
            'sessions' => $sessions,
        ]);
    }

    // ==========================================================
    // TWO-FACTOR AUTHENTICATION
    // ==========================================================

    /** Halaman setup 2FA: tampilkan secret + QR provisioning URI. */
    public function twoFactorShow()
    {
        $user = Auth::user();

        if (!empty($user->two_factor_confirmed_at)) {
            return redirect()->route('superadmin.security.index')
                ->with('success', 'Two-Factor Authentication sudah aktif.');
        }

        // Secret ditahan di session sampai kode pertama divalidasi,
        // sehingga secret tidak pernah tersimpan sebelum diverifikasi.
        $secret = session('two_factor_pending_secret');

        if (!$secret) {
            $secret = $this->totp->generateSecret();
            session(['two_factor_pending_secret' => $secret]);
        }

        $uri = $this->totp->provisioningUri($secret, $user->email);

        return view('superadmin.security.two-factor-setup', [
            'secret' => $secret,
            'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($uri),
            'uri' => $uri,
        ]);
    }

    /** Aktifkan 2FA setelah kode pertama berhasil diverifikasi. */
    public function twoFactorEnable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $secret = session('two_factor_pending_secret');

        if (!$secret) {
            return redirect()->route('superadmin.security.two-factor')
                ->with('error', 'Sesi setup kedaluwarsa. Silakan mulai ulang.');
        }

        if (!$this->totp->verify($secret, $request->code)) {
            return back()->with('error', 'Kode verifikasi tidak cocok. Periksa waktu perangkat lalu coba lagi.');
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes();

        $user = Auth::user();
        $user->forceFill([
            'two_factor_secret' => $this->totp->encryptSecret($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($this->totp->hashRecoveryCodes($recoveryCodes)),
        ])->save();

        session()->forget('two_factor_pending_secret');
        // Konfirmasi sesi berlaku 10 menit agar aksi kritis bisa langsung dijalankan.
        session(['twofactor_verified_at' => time()]);

        ActivityLog::record(
            'security.2fa_enabled',
            Auth::user()->nama_lengkap . ' mengaktifkan Two-Factor Authentication.',
            ['module' => 'security']
        );

        return redirect()->route('superadmin.security.index')
            ->with('success', 'Two-Factor Authentication berhasil diaktifkan.')
            ->with('recovery_codes', $recoveryCodes);
    }

    /** Nonaktifkan 2FA (perlu kode TOTP yang masih berlaku). */
    public function twoFactorDisable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $secret = $this->totp->decryptSecret($user->two_factor_secret);

        $valid = false;

        if ($secret !== null && $this->totp->verify($secret, $request->code)) {
            $valid = true;
        } else {
            $codes = json_decode((string) $user->two_factor_recovery_codes, true) ?: [];
            $remaining = $this->totp->consumeRecoveryCode($codes, $request->code);

            if ($remaining !== null) {
                $valid = true;
                $user->two_factor_recovery_codes = json_encode($remaining);
            }
        }

        if (!$valid) {
            return back()->with('error', 'Kode verifikasi tidak valid.');
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        session()->forget(['twofactor_verified_at', 'two_factor_pending_secret']);

        ActivityLog::record(
            'security.2fa_disabled',
            Auth::user()->nama_lengkap . ' menonaktifkan Two-Factor Authentication.',
            ['module' => 'security']
        );

        return redirect()->route('superadmin.security.index')
            ->with('success', 'Two-Factor Authentication dinonaktifkan.');
    }

    /** Halaman challenge 2FA untuk aksi kritis. */
    public function twoFactorChallenge()
    {
        $user = Auth::user();

        if (empty($user->two_factor_confirmed_at)) {
            return redirect()->route('superadmin.security.two-factor');
        }

        return view('superadmin.security.two-factor-challenge', [
            'redirect' => request()->query('redirect'),
        ]);
    }

    /** Verifikasi challenge 2FA. */
    public function twoFactorChallengeStore(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $secret = $this->totp->decryptSecret($user->two_factor_secret);
        $consumedRecovery = false;
        $valid = false;

        if ($secret !== null && $this->totp->verify($secret, $request->code)) {
            $valid = true;
        } else {
            $codes = json_decode((string) $user->two_factor_recovery_codes, true) ?: [];
            $remaining = $this->totp->consumeRecoveryCode($codes, $request->code);

            if ($remaining !== null) {
                $valid = true;
                $consumedRecovery = true;
                $user->two_factor_recovery_codes = json_encode($remaining);
            }
        }

        if (!$valid) {
            return back()->with('error', 'Kode verifikasi tidak valid.');
        }

        if ($consumedRecovery) {
            $user->save();
        }

        $ttl = (int) SystemSetting::getValue('twofactor_challenge_ttl', 600);
        session(['twofactor_verified_at' => time(), 'twofactor_challenge_ttl' => $ttl]);

        ActivityLog::record(
            'security.2fa_challenge_passed',
            Auth::user()->nama_lengkap . ' lolos challenge Two-Factor Authentication.',
            ['module' => 'security']
        );

        $redirect = $request->input('redirect');

        if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, url('/'))) {
            return redirect()->to($redirect)->with('success', 'Identitas terverifikasi.');
        }

        return redirect()->route('superadmin.security.index')
            ->with('success', 'Identitas terverifikasi.');
    }

    // ==========================================================
    // WHITELIST IP
    // ==========================================================

    public function updateWhitelist(Request $request)
    {
        $request->validate([
            'whitelist' => ['nullable', 'string', 'max:2000'],
            'require_2fa' => ['nullable', 'in:0,1'],
        ]);

        SystemSetting::setSuperadminIpWhitelist((string) $request->input('whitelist', ''));
        SystemSetting::setTwoFactorRequired($request->input('require_2fa') === '1');

        ActivityLog::record(
            'security.whitelist_updated',
            'Whitelist IP Super Admin diperbarui.',
            ['module' => 'security', 'metadata' => ['whitelist' => $request->input('whitelist')]]
        );

        return redirect()->route('superadmin.security.index')
            ->with('success', 'Pengaturan keamanan disimpan.');
    }

    // ==========================================================
    // SESI AKTIF
    // ==========================================================

    /** Paksa keluarkan satu sesi (perangkat lain) milik user tertentu. */
    public function sessionDestroy(Request $request, string $sessionId)
    {
        $removed = false;

        try {
            if (Schema::hasTable('sessions')) {
                $removed = DB::table('sessions')->where('id', $sessionId)->delete() > 0;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        ActivityLog::record(
            'security.session_revoked',
            'Sesi aktif dihapus: ' . $sessionId,
            ['module' => 'security', 'metadata' => ['session_id' => $sessionId, 'removed' => $removed]]
        );

        return redirect()->route('superadmin.security.index')
            ->with($removed ? 'success' : 'error',
                $removed ? 'Sesi berhasil dikeluarkan.' : 'Sesi tidak ditemukan atau sudah kedaluwarsa.');
    }

    /** Keluarkan SEMUA sesi lain (kecuali sesi aktif sekarang). */
    public function sessionRevokeOthers()
    {
        $currentId = session()->getId();
        $count = 0;

        try {
            if (Schema::hasTable('sessions')) {
                $count = DB::table('sessions')->where('id', '!=', $currentId)->delete();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        ActivityLog::record(
            'security.session_revoked_others',
            $count . ' sesi lain dikeluarkan paksa.',
            ['module' => 'security', 'metadata' => ['count' => $count]]
        );

        return redirect()->route('superadmin.security.index')
            ->with('success', $count . ' sesi lain berhasil dikeluarkan.');
    }

    /**
     * Ambil daftar sesi aktif (dari tabel sessions berbasis database).
     * Driver array/file → daftar kosong (aman, tidak error).
     */
    private function activeSessions(): array
    {
        try {
            if (!Schema::hasTable('sessions')) {
                return [];
            }

            $loginKey = 'login_' . config('auth.defaults.guard', 'web') . '_' . sha1(\Illuminate\Auth\SessionGuard::class);

            $rows = DB::table('sessions')
                ->where('last_activity', '>=', time() - (int) config('session.lifetime', 120) * 60)
                ->orderByDesc('last_activity')
                ->limit(100)
                ->get();

            $out = [];

            foreach ($rows as $row) {
                $payload = @unserialize(base64_decode((string) $row->payload)) ?: [];
                $userId = $payload[$loginKey] ?? null;

                $out[] = (object) [
                    'id' => $row->id,
                    'user_id' => $userId,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => (int) $row->last_activity,
                    'is_current' => $row->id === session()->getId(),
                ];
            }

            return $out;
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    // ==========================================================
    // AKUN ADMIN (CRUD)
    // ==========================================================
}
