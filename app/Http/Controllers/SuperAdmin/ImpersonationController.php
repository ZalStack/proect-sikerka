<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Impersonation;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Impersonation (Fitur A4): Super Admin masuk sebagai karyawan/HR
 * untuk keperluan verifikasi laporan, tanpa meminta kredensial.
 */
class ImpersonationController extends Controller
{
    public function start(Request $request, int $id)
    {
        // Tidak boleh berpura-pura menjadi diri sendiri.
        if ((int) auth()->id() === $id) {
            return back()->with('error', 'Anda sudah berada pada akun tersebut.');
        }

        // Tidak boleh menggandakan sesi impersonasi lain (mencegah kehilangan
        // akun asli bila superadmin memilih target berbeda saat sedang impersonasi).
        if (session(Impersonation::SESSION_KEY) !== null) {
            return back()->with('error', 'Akhiri impersonasi aktif sebelum memulai yang baru.');
        }

        $target = Karyawan::findOrFail($id);

        if ($target->is_resigned) {
            return back()->with('error', 'Akun target sudah resign/nonaktif.');
        }

        if ($target->posisi === 'superadmin') {
            return back()->with('error', 'Impersonasi antar Super Admin tidak diizinkan.');
        }

        $original = Auth::user();

        Impersonation::start($original, $target);

        // Regenerasi sesi setelah login ulang mencegah sesi fixation.
        Auth::login($target, true);
        $request->session()->regenerate();

        // Regenerate() membuat sesi baru → set ulang kunci impersonasi.
        session([
            Impersonation::SESSION_KEY => $original->id,
            'impersonator_email' => $original->email,
        ]);

        ActivityLog::record(
            'access.impersonate_start',
            'Superadmin ' . $original->email . ' masuk sebagai ' . $target->email . '.',
            [
                'user' => $original,
                'module' => 'access',
                'subject_type' => 'karyawans',
                'subject_id' => $target->id,
                'metadata' => ['target_email' => $target->email, 'target_posisi' => $target->posisi],
            ]
        );

        $redirectTo = $target->posisi === 'hr' ? 'hr.dashboard' : 'karyawan.dashboard';

        return redirect()->route($redirectTo)
            ->with('success', 'Impersonasi aktif. Anda sedang masuk sebagai ' . $target->nama_lengkap . '.');
    }

    public function stop(Request $request)
    {
        $originalId = session(Impersonation::SESSION_KEY);

        if ($originalId === null) {
            return redirect()->route('superadmin.dashboard');
        }

        $original = Karyawan::find($originalId);
        $targetEmail = Auth::user()?->email;

        if (!$original) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun asli tidak ditemukan. Silakan login ulang.');
        }

        Auth::login($original, true);
        $request->session()->regenerate();
        session()->forget([Impersonation::SESSION_KEY, 'impersonator_email']);

        ActivityLog::record(
            'access.impersonate_stop',
            'Impersonasi diakhiri. Kembali ke akun ' . $original->email . ' (dari ' . $targetEmail . ').',
            ['module' => 'access', 'subject_type' => 'karyawans', 'subject_id' => $original->id]
        );

        return redirect()->route('superadmin.dashboard')
            ->with('success', 'Impersonasi diakhiri. Anda kembali ke akun ' . $original->nama_lengkap . '.');
    }
}
