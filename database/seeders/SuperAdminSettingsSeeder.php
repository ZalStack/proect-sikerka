<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder role Super Admin + inisialisasi System Settings.
 *
 * Bersifat IDEMPOTENT (aman dijalankan berkali-kali):
 * - Data settings selalu di-upsert (updateOrCreate), tidak pernah duplikat.
 * - Akun Super Admin dibuat hanya bila belum ada; bila sudah ada hanya
 *   dipastikan role-nya 'superadmin' dan akunnya aktif (tidak resign).
 *
 * Jalankan dengan:
 *   php artisan db:seed --class=SuperAdminSettingsSeeder
 */
class SuperAdminSettingsSeeder extends Seeder
{
    /** Email default akun Super Admin (dapat di-override via SUPERADMIN_EMAIL). */
    private const SUPERADMIN_EMAIL = 'superadmin@sikekar.com';

    /** Kode pegawai awal untuk akun Super Admin yang baru dibuat. */
    private const SUPERADMIN_KODE_AWAL = 'SA-001';

    /**
     * Ambil kata sandi superadmin dari environment SUPERADMIN_PASSWORD.
     * Bila tidak di-set, dibuat acak dan dicatat ke log server (bukan
     * kredensial publik) — operator mengambilnya dari log untuk login
     * pertama kali, lalu menggantinya lewat menu profil.
     */
    private function superadminPassword(): string
    {
        $password = (string) env('SUPERADMIN_PASSWORD', '');

        if ($password !== '') {
            return $password;
        }

        $generated = bin2hex(random_bytes(12));

        // $this->command bisa null bila dipanggil langsung dari migrasi.
        $this->command?->warn(
            'SUPERADMIN_PASSWORD tidak di-set — kata sandi acak dibuat dan dicatat ke storage/logs/laravel.log.'
        );
        \Illuminate\Support\Facades\Log::warning(
            'Kata sandi akun superadmin dibuat acak oleh seeder: ' . $generated
            . ' (email: ' . ($this->superadminEmail()) . ')'
        );

        return $generated;
    }

    private function superadminEmail(): string
    {
        $email = (string) env('SUPERADMIN_EMAIL', self::SUPERADMIN_EMAIL);

        return $email !== '' ? $email : self::SUPERADMIN_EMAIL;
    }

    public function run(): void
    {
        $this->seedSystemSettings();
        $this->seedSuperAdmin();
    }

    /**
     * Inisialisasi System Settings: mode maintenance nonaktif + semua fitur aktif.
     */
    private function seedSystemSettings(): void
    {
        SystemSetting::setMaintenance(
            false,
            'Sistem Sedang Dalam Pemeliharaan',
            'Mohon maaf atas ketidaknyamanannya. Saat ini kami sedang melakukan pemeliharaan dan pembaruan sistem untuk meningkatkan performa dan layanan. Silakan coba kembali beberapa saat lagi.',
            'Segera Kembali'
        );

        // Aktifkan semua fitur secara default (idempotent).
        SystemSetting::setAllFeatures(true);

        // Nomor WhatsApp Admin untuk fitur Lupa Password
        // (HR/Karyawan meminta link reset password ke nomor ini).
        SystemSetting::setValue(
            'admin_whatsapp',
            '082123439604',
            'general',
            'Nomor WhatsApp Admin SIKEKAR - tujuan permintaan link reset password'
        );

        $this->command->info('System settings berhasil diinisialisasi (maintenance OFF, semua fitur ON).');
    }

    /**
     * Pastikan akun dengan role superadmin tersedia dan aktif.
     */
    private function seedSuperAdmin(): void
    {
        $email = $this->superadminEmail();

        $superadmin = Karyawan::query()
            ->where(function ($query) use ($email) {
                $query->where('email', $email)
                    ->orWhere('posisi', 'superadmin');
            })
            ->orderBy('id')
            ->first();

        if (!$superadmin) {
            // Akun baru → kata sandi diambil dari env (atau dibuat acak).
            $superadmin = Karyawan::create([
                'kode_pegawai' => $this->kodePegawaiUnik(),
                'email' => $email,
                'kata_sandi' => Hash::make($this->superadminPassword()),
                'nama_lengkap' => 'Super Administrator',
                'posisi' => 'superadmin',
                'jabatan' => 'Super Administrator Sistem',
                'divisi' => 'IT & Systems',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => now()->toDateString(),
                'nomor_telepon' => '081234567890',
                'no_wa' => '081234567890',
                'alamat' => 'Kantor Pusat KPM',
                'is_resigned' => false,
            ]);

            $this->command?->info('Akun Super Admin baru dibuat: ' . $email);

            return;
        }

        // Akun sudah ada → pastikan role & statusnya benar tanpa mengubah data lain.
        $superadmin->posisi = 'superadmin';
        $superadmin->is_resigned = false;

        // Kata sandi TIDAK pernah di-reset saat seeder dijalankan ulang, agar
        // kredensial yang sudah diganti operator tetap berlaku (idempoten).
        if ($superadmin->isDirty()) {
            $superadmin->save();
        }

        $this->command?->info('Akun Super Admin sudah tersedia: ' . $superadmin->email);
    }

    /**
     * Cari kode pegawai unik untuk Super Admin baru (menjaga kolom unique).
     */
    private function kodePegawaiUnik(): string
    {
        $nomor = (int) str_replace('SA-', '', self::SUPERADMIN_KODE_AWAL);

        while (Karyawan::where('kode_pegawai', 'SA-' . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT))->exists()) {
            $nomor++;
        }

        return 'SA-' . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
    }
}
