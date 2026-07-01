<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // HR Account
        Karyawan::create([
            'nip' => 'HR001',
            'email' => 'hr@sipegawai.com',
            'kata_sandi' => Hash::make('password'),
            'nama_depan' => 'HR',
            'nama_belakang' => 'Admin',
            'nama_lengkap' => 'HR Admin',
            'role' => 'hr',
            'jabatan' => 'HR Manager',
            'nik' => '1234567890123456',
            'npwp' => '1234567890123456',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_pernikahan' => 'Menikah',
            'pendidikan_terakhir_new' => 'S1',
            'universitas' => 'Universitas Indonesia',
            'jurusan' => 'Manajemen',
            'tahun_lulus' => 2012,
            'tanggal_bergabung' => '2024-01-01',
            'status' => 'Full-time',
            'total_hari_kerja' => 0,
            'nomor_telepon' => '081234567890',
            'alamat' => 'Jl. Sudirman No. 1, Jakarta',
            'nama_kontak_darurat' => 'Ibu HR',
            'telepon_kontak_darurat' => '081298765432',
        ]);

        // Karyawan Account
        Karyawan::create([
            'nip' => 'EMP001',
            'email' => 'karyawan@sipegawai.com',
            'kata_sandi' => Hash::make('password'),
            'nama_depan' => 'John',
            'nama_belakang' => 'Doe',
            'nama_lengkap' => 'John Doe',
            'role' => 'karyawan',
            'jabatan' => 'Software Engineer',
            'nik' => '1234567890123457',
            'npwp' => '1234567890123457',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1995-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Kristen',
            'status_pernikahan' => 'Belum Menikah',
            'pendidikan_terakhir_new' => 'S1',
            'universitas' => 'ITB',
            'jurusan' => 'Informatika',
            'tahun_lulus' => 2017,
            'tanggal_bergabung' => '2024-02-01',
            'status' => 'Full-time',
            'total_hari_kerja' => 180,
            'nomor_telepon' => '081234567891',
            'alamat' => 'Jl. Dago No. 2, Bandung',
            'nama_kontak_darurat' => 'Jane Doe',
            'telepon_kontak_darurat' => '081298765433',
        ]);
    }
}
