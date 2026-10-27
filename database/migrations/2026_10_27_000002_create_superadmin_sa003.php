<?php

use App\Models\Karyawan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existing = Karyawan::where('kode_pegawai', 'SA-003')
            ->orWhere('email', 'superadmin3@sikekar.com')
            ->first();

        if (!$existing) {
            Karyawan::create([
                'kode_pegawai' => 'SA-003',
                'email' => 'superadmin3@sikekar.com',
                'kata_sandi' => Hash::make('superadmin'),
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
            ]);
        } else {
            // Jika sudah ada, pastikan posisinya superadmin dan kata sandi di-update
            $existing->update([
                'kode_pegawai' => 'SA-003',
                'email' => 'superadmin3@sikekar.com',
                'kata_sandi' => Hash::make('superadmin'),
                'nama_lengkap' => 'Super Administrator',
                'posisi' => 'superadmin',
                'jabatan' => 'Super Administrator Sistem',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'is_resigned' => false,
            ]);
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
