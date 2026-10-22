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
    /** Email resmi akun Super Admin. */
    private const SUPERADMIN_EMAIL = 'superadmin@sikekar.com';

    /** Kata sandi default akun Super Admin. */
    private const SUPERADMIN_PASSWORD = 'superadmin123';

    /** Kode pegawai awal untuk akun Super Admin yang baru dibuat. */
    private const SUPERADMIN_KODE_AWAL = 'SA-001';

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

        $this->command->info('System settings berhasil diinisialisasi (maintenance OFF, semua fitur ON).');
    }

    /**
     * Pastikan akun dengan role superadmin tersedia dan aktif.
     */
    private function seedSuperAdmin(): void
    {
        $superadmin = Karyawan::query()
            ->where(function ($query) {
                $query->where('email', self::SUPERADMIN_EMAIL)
                    ->orWhere('posisi', 'superadmin');
            })
            ->orderBy('id')
            ->first();

        if (!$superadmin) {
            $superadmin = Karyawan::create([
                'kode_pegawai' => $this->kodePegawaiUnik(),
                'email' => self::SUPERADMIN_EMAIL,
                'kata_sandi' => Hash::make(self::SUPERADMIN_PASSWORD),
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

            $this->command->info('Akun Super Admin baru dibuat: ' . self::SUPERADMIN_EMAIL . ' / ' . self::SUPERADMIN_PASSWORD);

            return;
        }

        // Akun sudah ada → pastikan role & statusnya benar tanpa mengubah data lain.
        $superadmin->posisi = 'superadmin';
        $superadmin->is_resigned = false;

        // Jika ini akun Super Admin bawaan seeder, sinkronkan kata sandinya
        // agar kredensial yang terdokumentasi selalu valid.
        if (strcasecmp($superadmin->email, self::SUPERADMIN_EMAIL) === 0) {
            $superadmin->kata_sandi = Hash::make(self::SUPERADMIN_PASSWORD);
        }

        if ($superadmin->isDirty()) {
            $superadmin->save();
        }

        $this->command->info('Akun Super Admin sudah tersedia: ' . $superadmin->email);
        if (strcasecmp($superadmin->email, self::SUPERADMIN_EMAIL) === 0) {
            $this->command->info('Kredensial: ' . self::SUPERADMIN_EMAIL . ' / ' . self::SUPERADMIN_PASSWORD);
        }
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
