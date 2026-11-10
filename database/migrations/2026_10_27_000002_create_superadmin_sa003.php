<?php

use App\Models\Karyawan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Migrasi ini TIDAK lagi membuat akun dengan kata sandi hardcode.
     *
     * Akun superadmin dibuat oleh SuperAdminSettingsSeeder (yang membaca
     * kredensial dari environment SUPERADMIN_EMAIL / SUPERADMIN_PASSWORD).
     * Migrasi ini hanya memastikan akun SA-003 bila operator memberikan
     * kredensial eksplisit lewat environment — tanpa env, tidak ada akun
     * yang dibuat dan tidak ada kredensial yang diketahui publik.
     */
    public function up(): void
    {
        $email = env('SUPERADMIN3_EMAIL');
        $password = env('SUPERADMIN3_PASSWORD');

        if ($email === null || $email === '' || $password === null || $password === '') {
            Log::info('Migrasi create_superadmin_sa003 dilewati: SUPERADMIN3_EMAIL/SUPERADMIN3_PASSWORD tidak di-set. '
                . 'Gunakan php artisan db:seed --class=SuperAdminSettingsSeeder untuk membuat akun superadmin.');

            return;
        }

        $existing = Karyawan::where('kode_pegawai', 'SA-003')
            ->orWhere('email', $email)
            ->first();

        $payload = [
            'kode_pegawai' => 'SA-003',
            'email' => $email,
            'kata_sandi' => Hash::make($password),
            'nama_lengkap' => 'Super Administrator',
            'posisi' => 'superadmin',
            'jabatan' => 'Super Administrator Sistem',
            'divisi' => 'IT',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => '2026-10-27',
            'nama_bank' => 'BSI',
            'is_resigned' => false,
            'violation_count' => 0,
            'is_banned_permanent' => false,
        ];

        if (!$existing) {
            Karyawan::create($payload);
        } else {
            $existing->update($payload);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Karyawan::where('kode_pegawai', 'SA-003')->delete();
    }
};
