<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Karyawan::create([
            'kode_pegawai' => 'HRD001',
            'email' => 'hrd@company.com',
            'kata_sandi' => Hash::make('password123'),
            'nama_lengkap' => 'Budi Santoso',
            'posisi' => 'hr',
            'jabatan' => 'Kepala HRD',
            'divisi' => 'HRD',
            'golongan_darah' => 'A',
            'no_kk' => '1234567890123456',
            'sedang_melanjutkan_pendidikan' => 'S2 Manajemen SDM',
            'jumlah_anak' => 2,
            'foto_profil' => null,
            'nomor_telepon' => '081234567890',
            'no_wa' => '081234567890',
            'alamat' => 'Jl. Merdeka No. 1, Jakarta',
            'tanggal_bergabung' => '2020-01-01',
            'end_date' => null,
            'status' => 'Karyawan Tetap',
            'nik' => '1234567890123456',
            'npwp' => '12.345.678.9-012.345',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1985-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_pernikahan' => 'Menikah',
            'pendidikan_terakhir' => 'S2',
            'pendidikan_terakhir_new' => 'S2',
            // Kolom baru dari migrasi continuing education
            'is_continuing_education' => true,
            'continuing_program' => 'S2',
            'continuing_perguruan_tinggi' => 'Universitas Indonesia',
            'continuing_jurusan' => 'Manajemen SDM',
            'perguruan_tinggi' => 'Universitas Indonesia',
            'jurusan' => 'Manajemen SDM',
            'tahun_lulus' => 2015,
            'nama_ibu_kandung' => 'Siti Rahayu',
            'nama_kontak_darurat' => 'Ahmad Subagyo',
            'telepon_kontak_darurat' => '081234567891',
            'tanggal_pengangkatan_tetap' => '2021-01-01',
            'nomor_rekening' => '1234567890',
            'nama_bank' => 'BSI',
            'ipk_terakhir' => 3.85,
            'alamat_domisili' => 'Jl. Merdeka No. 1, Jakarta',
            'is_resigned' => false,
            'tanggal_resign' => null,
        ]);

        // ==========================================================
        // Data Karyawan (Employee)
        // ==========================================================
        $karyawanData = [
            [
                'kode_pegawai' => '1042001',
                'nama_lengkap' => 'Dr. R. Ridwan Hasan Saputra, M.Si.',
                'jabatan' => 'Dokter',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2020-04-01',
            ],
            [
                'kode_pegawai' => '2042001',
                'nama_lengkap' => 'Anis Kurniasih',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2020-04-01',
            ],
            [
                'kode_pegawai' => '1012009',
                'nama_lengkap' => 'Desi Kurnia Wati',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2009-01-01',
            ],
            [
                'kode_pegawai' => '2072009',
                'nama_lengkap' => 'Weni Wulan Sari',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2009-07-01',
            ],
            [
                'kode_pegawai' => '1052010',
                'nama_lengkap' => 'Siti Khoerunnisa',
                'jabatan' => 'Apoteker',
                'divisi' => 'Farmasi',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2010-05-01',
            ],
            [
                'kode_pegawai' => '1102012',
                'nama_lengkap' => 'Muchammad Fachri',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2012-10-01',
            ],
            [
                'kode_pegawai' => '1072013',
                'nama_lengkap' => 'Sutriyati',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2013-07-01',
            ],
            [
                'kode_pegawai' => '1072014',
                'nama_lengkap' => 'Thyeadi Tungson',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2014-07-01',
            ],
            [
                'kode_pegawai' => '1092014',
                'nama_lengkap' => 'M Rijwan',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2014-09-01',
            ],
            [
                'kode_pegawai' => '2112014',
                'nama_lengkap' => 'Tri Jumsari',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2014-11-01',
            ],
            [
                'kode_pegawai' => '1092015',
                'nama_lengkap' => 'Muhammad Ihsan',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2015-09-01',
            ],
            [
                'kode_pegawai' => '1062016',
                'nama_lengkap' => 'Ryky Tunggal Saputra Aji',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2016-06-01',
            ],
            [
                'kode_pegawai' => '1012017',
                'nama_lengkap' => 'Muhamad Seis Kusumanegara',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2017-01-01',
            ],
            [
                'kode_pegawai' => '2012017',
                'nama_lengkap' => 'Nabila Nurhakimah',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2017-01-01',
            ],
            [
                'kode_pegawai' => '1012018',
                'nama_lengkap' => 'Rheta Rezkianti',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2018-01-01',
            ],
            [
                'kode_pegawai' => '1012019',
                'nama_lengkap' => 'Lukmanul Hakim',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2019-01-01',
            ],
            [
                'kode_pegawai' => '2012019',
                'nama_lengkap' => 'Dwi Atika Permata Sari',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2019-01-01',
            ],
            [
                'kode_pegawai' => '1102019',
                'nama_lengkap' => 'Ali Zulfikar',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2019-10-01',
            ],
            [
                'kode_pegawai' => '1122019',
                'nama_lengkap' => 'Andri Imam Munandar',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2019-12-01',
            ],
            [
                'kode_pegawai' => '2032021',
                'nama_lengkap' => 'Vega Oktaviana',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-03-01',
            ],
            [
                'kode_pegawai' => '3032021',
                'nama_lengkap' => 'Ardianto',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-03-01',
            ],
            [
                'kode_pegawai' => '2092021',
                'nama_lengkap' => 'Dedi Wahyudi',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-09-01',
            ],
            [
                'kode_pegawai' => '1102021',
                'nama_lengkap' => 'Hendra Minar',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-10-01',
            ],
            [
                'kode_pegawai' => '3102021',
                'nama_lengkap' => 'Nanda Lindawati',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-10-01',
            ],
            [
                'kode_pegawai' => '4102021',
                'nama_lengkap' => 'Agus Sutisna',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-10-01',
            ],
            [
                'kode_pegawai' => '5102021',
                'nama_lengkap' => 'Fikri Fauzi',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-10-01',
            ],
            [
                'kode_pegawai' => '6122021',
                'nama_lengkap' => 'Devi Ariyanti',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-12-01',
            ],
            [
                'kode_pegawai' => '3122021',
                'nama_lengkap' => 'Muhamad Rijal Apriansyah',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2021-12-01',
            ],
            [
                'kode_pegawai' => '2022022',
                'nama_lengkap' => 'Siti Fatimah',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-02-01',
            ],
            [
                'kode_pegawai' => '2032022',
                'nama_lengkap' => 'Roy Yulio',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-03-01',
            ],
            [
                'kode_pegawai' => '1042022',
                'nama_lengkap' => 'Siti Maesaroh',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-04-01',
            ],
            [
                'kode_pegawai' => '3042022',
                'nama_lengkap' => 'Febriyana',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-04-01',
            ],
            [
                'kode_pegawai' => '4042022',
                'nama_lengkap' => 'Moh. Napis Aropi',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-04-01',
            ],
            [
                'kode_pegawai' => '3052022',
                'nama_lengkap' => 'Rully Dwiandika Yunus',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-05-01',
            ],
            [
                'kode_pegawai' => '1082022',
                'nama_lengkap' => 'Mia Nur Aprilia',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-08-01',
            ],
            [
                'kode_pegawai' => '2082022',
                'nama_lengkap' => 'Siti Alpiah',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-08-01',
            ],
            [
                'kode_pegawai' => '2092022',
                'nama_lengkap' => 'Desi Dwi Ariyanti',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-09-01',
            ],
            [
                'kode_pegawai' => '3092022',
                'nama_lengkap' => 'Ragil Agustian Alpiansyah',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2022-09-01',
            ],
            [
                'kode_pegawai' => '1032023',
                'nama_lengkap' => 'Fajar Zulham Ibrahim',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2023-03-01',
            ],
            [
                'kode_pegawai' => '1102023',
                'nama_lengkap' => 'M Zidan Mahardika',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2023-10-01',
            ],
            [
                'kode_pegawai' => '4112023',
                'nama_lengkap' => 'Moehammad Buchori',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2023-11-01',
            ],
            [
                'kode_pegawai' => '5112023',
                'nama_lengkap' => 'Cahya Aditya',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2023-11-01',
            ],
            [
                'kode_pegawai' => '1032024',
                'nama_lengkap' => 'Rangga Pradana',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2024-03-01',
            ],
            [
                'kode_pegawai' => '1072024',
                'nama_lengkap' => 'Sang Baharsyah',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2024-07-01',
            ],
            [
                'kode_pegawai' => '1092024',
                'nama_lengkap' => 'Khomsalia Denmasti Harun',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2024-09-01',
            ],
            [
                'kode_pegawai' => '2092024',
                'nama_lengkap' => 'Ramadhan Setiawan',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2024-09-01',
            ],
            [
                'kode_pegawai' => '1022025',
                'nama_lengkap' => 'Adinda Baby Cantika Dewi',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-02-01',
            ],
            [
                'kode_pegawai' => '2012025',
                'nama_lengkap' => 'Irvan Sanjaya',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-01-01',
            ],
            [
                'kode_pegawai' => '1072025',
                'nama_lengkap' => 'Deri Rahman',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-07-01',
            ],
            [
                'kode_pegawai' => '1082025',
                'nama_lengkap' => 'Isna Nur Fajriah',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-08-01',
            ],
            [
                'kode_pegawai' => '2082025',
                'nama_lengkap' => 'Arisna Dwi Hapsari',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-08-01',
            ],
            [
                'kode_pegawai' => '3082025',
                'nama_lengkap' => 'Suhardi Prayitno',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-08-01',
            ],
            [
                'kode_pegawai' => '4082025',
                'nama_lengkap' => 'Muhammad Rafiq Alfiansyah',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-08-01',
            ],
            [
                'kode_pegawai' => '5082025',
                'nama_lengkap' => 'Ayu Mandasari',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-08-01',
            ],
            [
                'kode_pegawai' => '6082025',
                'nama_lengkap' => 'Selfia Annatasya',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-08-01',
            ],
            [
                'kode_pegawai' => '1092025',
                'nama_lengkap' => 'Ferdianto',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-09-01',
            ],
            [
                'kode_pegawai' => '2092025',
                'nama_lengkap' => 'Siti Silvia Handayani',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-09-01',
            ],
            [
                'kode_pegawai' => '1112025',
                'nama_lengkap' => 'Ziban Lesmana Sutiawan',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-11-01',
            ],
            [
                'kode_pegawai' => '2112025',
                'nama_lengkap' => 'Rathri Candra Dewi',
                'jabatan' => 'Administrasi',
                'divisi' => 'Umum',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2025-11-01',
            ],
            [
                'kode_pegawai' => '1012026',
                'nama_lengkap' => 'M. Ariek Hidayat',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2026-01-01',
            ],
            [
                'kode_pegawai' => '1032026',
                'nama_lengkap' => 'M Haikal Catur Saputra',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2026-03-01',
            ],
            [
                'kode_pegawai' => '2032026',
                'nama_lengkap' => 'Peggy Nurida Asri',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2026-03-01',
            ],
            [
                'kode_pegawai' => '1062026',
                'nama_lengkap' => 'Jovita Anggraeni',
                'jabatan' => 'Perawat',
                'divisi' => 'Medis',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2026-06-01',
            ],
            [
                'kode_pegawai' => '2062026',
                'nama_lengkap' => 'Muhammad Fakhrizal Garnindyo',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2026-06-01',
            ],
            [
                'kode_pegawai' => '3062026',
                'nama_lengkap' => 'Muhammad Burhanudin',
                'jabatan' => 'Staff IT',
                'divisi' => 'IT',
                'status' => 'Karyawan Tetap',
                'tanggal_bergabung' => '2026-06-01',
            ],
        ];

        // Insert data karyawan
        foreach ($karyawanData as $data) {
            // Generate email from nama_lengkap
            $email = $this->generateEmail($data['nama_lengkap']);

            Karyawan::create([
                'kode_pegawai' => $data['kode_pegawai'],
                'email' => $email,
                'kata_sandi' => Hash::make('password123'),
                'nama_lengkap' => $data['nama_lengkap'],
                'posisi' => 'karyawan',
                'jabatan' => $data['jabatan'],
                'divisi' => $data['divisi'],
                'golongan_darah' => null,
                'no_kk' => null,
                'sedang_melanjutkan_pendidikan' => null,
                'jumlah_anak' => 0,
                'foto_profil' => null,
                'nomor_telepon' => null,
                'no_wa' => null,
                'alamat' => null,
                'tanggal_bergabung' => $data['tanggal_bergabung'],
                'end_date' => null,
                'status' => $data['status'],
                'nik' => null,
                'npwp' => null,
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'jenis_kelamin' => null,
                'agama' => null,
                'status_pernikahan' => null,
                'pendidikan_terakhir' => null,
                'pendidikan_terakhir_new' => null,
                // Kolom baru dari migrasi continuing education (default kosong untuk karyawan)
                'is_continuing_education' => false,
                'continuing_program' => null,
                'continuing_perguruan_tinggi' => null,
                'continuing_jurusan' => null,
                'perguruan_tinggi' => null,
                'jurusan' => null,
                'tahun_lulus' => null,
                'nama_ibu_kandung' => null,
                'nama_kontak_darurat' => null,
                'telepon_kontak_darurat' => null,
                'tanggal_pengangkatan_tetap' => null,
                'nomor_rekening' => null,
                'nama_bank' => 'BSI',
                'ipk_terakhir' => null,
                'alamat_domisili' => null,
                'is_resigned' => false,
                'tanggal_resign' => null,
            ]);
        }

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('👤 HR Account: hrd@company.com / password123');
        $this->command->info('👥 Total ' . count($karyawanData) . ' employees seeded.');
    }

    /**
     * Generate a gmail-style email from a full name, stripping common
     * academic titles/suffixes (Dr., M.Si., S.E., etc).
     */
    private function generateEmail($fullName): string
    {
        // Remove titles and suffixes (Dr., M.Si., S.E.,etc)
        $name = preg_replace('/^(Dr\.|dr\.|H\.|Ir\.|Dra\.)\s*/', '', $fullName);
        $name = preg_replace('/,\s*(M\.Si\.|S\.E\.|S\.Kom\.|S\.Pd\.|S\.H\.|M\.M\.|M\.Pd\.|M\.Kom\.|M\.Si\.|M\.H\.|S\.Sos\.|M\.Sos\.|A\.Md\.)\s*$/', '', $name);
        $name = preg_replace('/\s*(M\.Si\.|S\.E\.|S\.Kom\.|S\.Pd\.|S\.H\.|M\.M\.|M\.Pd\.|M\.Kom\.|M\.Si\.|M\.H\.|S\.Sos\.|M\.Sos\.|A\.Md\.)\s*$/', '', $name);

        // Split name into parts
        $parts = explode(' ', trim($name));

        if (count($parts) >= 2) {
            // Take first name and last name
            $firstName = strtolower($parts[0]);
            $lastName = strtolower(end($parts));

            // Remove special characters
            $firstName = preg_replace('/[^a-z]/', '', $firstName);
            $lastName = preg_replace('/[^a-z]/', '', $lastName);

            // Handle single character last name or special cases
            if (strlen($lastName) <= 1) {
                // Use second last name if available
                if (count($parts) >= 3) {
                    $lastName = strtolower($parts[count($parts) - 2]);
                    $lastName = preg_replace('/[^a-z]/', '', $lastName);
                } else {
                    // If only one name, use it as is
                    $firstName = strtolower($parts[0]);
                    $firstName = preg_replace('/[^a-z]/', '', $firstName);
                    return $firstName . '@gmail.com';
                }
            }

            // Handle case where last name is same as first name (duplicate)
            if ($firstName === $lastName && count($parts) >= 3) {
                $lastName = strtolower($parts[1]);
                $lastName = preg_replace('/[^a-z]/', '', $lastName);
            }

            return $firstName . '.' . $lastName . '@gmail.com';
        }

        // If only one name
        $singleName = strtolower($parts[0]);
        $singleName = preg_replace('/[^a-z]/', '', $singleName);
        return $singleName . '@gmail.com';
    }
}
