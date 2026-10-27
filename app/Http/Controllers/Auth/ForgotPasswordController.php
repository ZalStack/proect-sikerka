<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return $this->invalidLinkResponse();
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
            return $this->invalidLinkResponse();
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $karyawan->kata_sandi = Hash::make($request->password);
        $karyawan->save();

        // Token sekali pakai — habiskan semua token untuk akun ini
        DB::table('password_resets')->where('email', $karyawan->email)->delete();

        // Sesi aktif diakhiri (mis. Super Admin yang sedang mengetes link)
        // supaya pengguna diarahkan ke halaman login dan langsung
        // melihat pesan sukses, bukan ter-bounce ke dashboard.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }

    /**
     * Respons untuk link yang tidak valid / kedaluwarsa.
     * Dikirim ke halaman lupa password karena banner error di sana
     * selalu terlihat oleh pengguna.
     */
    private function invalidLinkResponse(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('password.request')->with(
            'error',
            'Link reset password tidak valid atau sudah kedaluwarsa (berlaku ' . self::RESET_LINK_TTL_MINUTES . ' menit). Silakan minta link baru melalui halaman ini.'
        );
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
