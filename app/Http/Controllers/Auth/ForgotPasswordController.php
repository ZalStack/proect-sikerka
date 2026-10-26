<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    /**
     * Masa berlaku link reset password (dalam menit) sejak Super Admin membuatnya.
     */
    public const RESET_LINK_TTL_MINUTES = 60;

    /**
     * Halaman lupa password.
     * Karena link reset hanya diterbitkan Super Admin, pengguna (HR / Karyawan)
     * diarahkan untuk meminta link melalui WhatsApp Admin SIKEKAR.
     */
    public function showForgotForm()
    {
        $adminWhatsapp = SystemSetting::adminWhatsapp();

        return view('auth.forgot-password', compact('adminWhatsapp'));
    }

    /**
     * Tampilkan form buat password baru dari link/token yang diterbitkan Super Admin.
     */
    public function showResetForm(string $token)
    {
        $karyawan = $this->karyawanFromToken($token);

        if (!$karyawan) {
            return redirect()->route('login')->with(
                'error',
                'Link reset password tidak valid atau sudah kedaluwarsa (berlaku ' . self::RESET_LINK_TTL_MINUTES . ' menit). Silakan minta link baru kepada Super Admin.'
            );
        }

        return view('auth.reset-password', compact('token', 'karyawan'));
    }

    /**
     * Simpan password baru dari link/token (token sekali pakai).
     */
    public function resetPassword(Request $request, string $token)
    {
        $karyawan = $this->karyawanFromToken($token);

        if (!$karyawan) {
            return redirect()->route('login')->with(
                'error',
                'Link reset password tidak valid atau sudah kedaluwarsa (berlaku ' . self::RESET_LINK_TTL_MINUTES . ' menit). Silakan minta link baru kepada Super Admin.'
            );
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $karyawan->kata_sandi = Hash::make($request->password);
        $karyawan->save();

        // Token sekali pakai — habiskan semua token untuk akun ini
        DB::table('password_resets')->where('email', $karyawan->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }

    /**
     * Cari karyawan pemilik token yang masih berlaku.
     * Token kedaluwarsa otomatis dihapus dari tabel password_resets.
     */
    private function karyawanFromToken(string $token): ?Karyawan
    {
        if ($token === '') {
            return null;
        }

        $row = DB::table('password_resets')
            ->where('token', $token)
            ->orderByDesc('created_at')
            ->first();

        if (!$row) {
            return null;
        }

        $expiresAt = Carbon::parse($row->created_at)->addMinutes(self::RESET_LINK_TTL_MINUTES);

        if ($expiresAt->isPast()) {
            DB::table('password_resets')->where('token', $token)->delete();

            return null;
        }

        return Karyawan::where('email', $row->email)->first();
    }
}
