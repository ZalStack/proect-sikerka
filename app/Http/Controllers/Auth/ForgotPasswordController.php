<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    // Tampilkan form lupa password
    public function showForgotForm()
    {
        // Generate CAPTCHA sederhana
        $captcha = $this->generateCaptcha();

        return view('auth.forgot-password', compact('captcha'));
    }

    // Proses verifikasi email dan CAPTCHA
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:karyawans,email',
            'captcha_input' => 'required|string',
        ]);

        // Verifikasi CAPTCHA
        if (!$this->verifyCaptcha($request->captcha_input)) {
            return back()->withErrors(['captcha_input' => 'Kode verifikasi salah. Silakan coba lagi.'])
                ->withInput();
        }

        // Cek apakah email terdaftar
        $karyawan = Karyawan::where('email', $request->email)->first();

        if (!$karyawan) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.'])
                ->withInput();
        }

        // Simpan email di session untuk reset password
        Session::put('reset_email', $request->email);
        Session::put('reset_verified', true);

        return redirect()->route('password.reset.form')
            ->with('success', 'Email terverifikasi. Silakan buat password baru.');
    }

    // Tampilkan form reset password
    public function showResetForm()
    {
        // Cek apakah sudah terverifikasi
        if (!Session::get('reset_verified')) {
            return redirect()->route('password.request')
                ->with('error', 'Silakan verifikasi email Anda terlebih dahulu.');
        }

        $email = Session::get('reset_email');

        return view('auth.reset-password', compact('email'));
    }

    // Proses reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:karyawans,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Cek apakah email sesuai dengan session
        if ($request->email !== Session::get('reset_email')) {
            return back()->withErrors(['email' => 'Email tidak sesuai.'])
                ->withInput();
        }

        // Update password
        $karyawan = Karyawan::where('email', $request->email)->first();
        $karyawan->kata_sandi = Hash::make($request->password);
        $karyawan->save();

        // Hapus session
        Session::forget(['reset_email', 'reset_verified']);

        return redirect()->route('login')
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }

    // Generate CAPTCHA sederhana
    private function generateCaptcha()
    {
        // Buat angka random 2 digit
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $operator = ['+', '-'][rand(0, 1)];

        // Hitung hasil
        if ($operator === '+') {
            $result = $num1 + $num2;
        } else {
            // Pastikan hasil tidak negatif
            if ($num1 < $num2) {
                $temp = $num1;
                $num1 = $num2;
                $num2 = $temp;
            }
            $result = $num1 - $num2;
        }

        // Simpan di session
        Session::put('captcha_result', $result);
        Session::put('captcha_text', "$num1 $operator $num2 = ?");

        return [
            'text' => "$num1 $operator $num2 = ?",
            'result' => $result
        ];
    }

    // Verifikasi CAPTCHA
    private function verifyCaptcha($input)
    {
        $result = Session::get('captcha_result');

        // Cek apakah input berupa angka
        if (!is_numeric($input)) {
            return false;
        }

        // Bandingkan
        return (int)$input === (int)$result;
    }

    // Regenerate CAPTCHA (via AJAX)
    public function refreshCaptcha()
    {
        $captcha = $this->generateCaptcha();

        return response()->json([
            'text' => $captcha['text'],
            'result' => $captcha['result']
        ]);
    }
}
