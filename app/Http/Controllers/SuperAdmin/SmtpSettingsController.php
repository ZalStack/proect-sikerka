<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Pengaturan SMTP aplikasi (Fitur B9).
 *
 * Nilai disimpan di tabel system_settings (bukan .env) sehingga bisa
 * diubah tanpa restart server, lalu diterapkan ke konfigurasi runtime
 * melalui SystemSetting::applyMailConfig().
 */
class SmtpSettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::smtpSettings();
        $configured = SystemSetting::isSmtpConfigured();

        return view('superadmin.smtp-settings', compact('settings', 'configured'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mail_mailer' => ['required', 'in:smtp,log'],
            'smtp_host' => ['required_unless:mail_mailer,log', 'nullable', 'string', 'max:190'],
            'smtp_port' => ['required_unless:mail_mailer,log', 'nullable', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string', 'max:190'],
            'smtp_password' => ['nullable', 'string', 'max:190'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
            'smtp_from_address' => ['required', 'email', 'max:190'],
            'smtp_from_name' => ['required', 'string', 'max:120'],
        ], [
            'smtp_host.required_unless' => 'Host SMTP wajib diisi untuk mailer smtp.',
            'smtp_port.required_unless' => 'Port SMTP wajib diisi untuk mailer smtp.',
            'smtp_from_address.email' => 'Alamat email pengirim tidak valid.',
        ]);

        $data['smtp_encryption'] = $data['smtp_encryption'] ?? 'tls';

        // Password tidak pernah dikirim ulang dari browser: field kosong
        // = pertahankan password yang sudah tersimpan.
        if (trim((string) ($data['smtp_password'] ?? '')) === '') {
            $data['smtp_password'] = SystemSetting::smtpSettings()['password'];
        }

        SystemSetting::setSmtpSettings($data);
        SystemSetting::applyMailConfig();

        ActivityLog::record(
            'settings.smtp_update',
            'Pengaturan SMTP diperbarui (mailer: ' . $data['mail_mailer'] . ').',
            ['module' => 'system']
        );

        return redirect()->route('superadmin.smtp.index')
            ->with('success', 'Pengaturan SMTP berhasil disimpan dan langsung diterapkan.');
    }

    /** Kirim email uji ke alamat yang ditentukan. */
    public function test(Request $request)
    {
        $request->validate([
            'test_email' => ['required', 'email', 'max:190'],
        ], [
            'test_email.required' => 'Masukkan alamat email tujuan uji kirim.',
            'test_email.email' => 'Alamat email tujuan uji kirim tidak valid.',
        ]);

        if (!SystemSetting::isSmtpConfigured() && config('mail.default') === 'array') {
            return redirect()->route('superadmin.smtp.index')
                ->with('error', 'SMTP belum dikonfigurasi. Simpan pengaturan terlebih dahulu.');
        }

        SystemSetting::applyMailConfig();

        try {
            $target = $request->input('test_email');

            Mail::to($target)->send(new \App\Mail\SmtpTestMail(
                sentAt: now()->format('d M Y H:i:s'),
                usedMailer: (string) config('mail.default'),
                usedHost: (string) config('mail.mailers.smtp.host', ''),
            ));

            ActivityLog::record(
                'settings.smtp_test',
                'Uji kirim email SMTP dikirim ke ' . $target . '.',
                ['module' => 'system', 'metadata' => ['to' => $target]]
            );

            return redirect()->route('superadmin.smtp.index')
                ->with('success', 'Email uji berhasil dikirim ke ' . $target . '. Periksa kotak masuk (dan folder spam).');
        } catch (\Throwable $e) {
            report($e);

            ActivityLog::record(
                'settings.smtp_test',
                'Uji kirim email SMTP GAGAL: ' . $e->getMessage(),
                ['module' => 'system']
            );

            return redirect()->route('superadmin.smtp.index')
                ->with('error', 'Email uji gagal dikirim: ' . $e->getMessage());
        }
    }
}
