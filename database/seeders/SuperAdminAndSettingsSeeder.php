<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Hash;

class SuperAdminAndSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Inisialisasi System Settings
        SystemSetting::setMaintenance(
            false,
            'Sistem Sedang Dalam Pemeliharaan',
            'Mohon maaf atas ketidaknyamanannya. Saat ini kami sedang melakukan pemeliharaan dan pembaruan sistem untuk meningkatkan performa dan layanan. Silakan coba kembali beberapa saat lagi.',
            'Segera Kembali'
        );

        // Aktifkan semua fitur secara default
        SystemSetting::setAllFeatures(true);

        // 2. Pastikan akun Super Admin ada
        $superadmin = Karyawan::where('email', 'superadmin@sikekar.com')
            ->orWhere('posisi', 'superadmin')
            ->first();

        if (!$superadmin) {
            Karyawan::create([
                'kode_pegawai' => 'SA-001',
                'email' => 'superadmin@sikekar.com',
                'kata_sandi' => Hash::make('superadmin123'),
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
            $this->command->info('Akun Super Admin berhasil dibuat: superadmin@sikekar.com / superadmin123');
        } else {
            // Pastikan posisinya superadmin
            $superadmin->posisi = 'superadmin';
            $superadmin->save();
            $this->command->info('Akun Super Admin sudah tersedia: ' . $superadmin->email);
        }
    }
}
