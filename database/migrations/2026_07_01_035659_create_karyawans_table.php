<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pegawai', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('kata_sandi');
            $table->string('nama_lengkap', 100);
            $table->string('posisi', 20)->default('karyawan');
            $table->string('jabatan', 100);
            $table->string('divisi', 50)->nullable();
            $table->string('golongan_darah', 5)->nullable();
            $table->string('no_kk', 100)->nullable();
            $table->string('sedang_melanjutkan_pendidikan', 100)->nullable();
            $table->integer('jumlah_anak')->default(0);
            $table->string('foto_profil', 255)->nullable();
            $table->string('nomor_telepon', 20)->nullable();
            $table->string('no_wa', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_bergabung');
            $table->date('end_date')->nullable();
            $table->string('status', 50)->default('Karyawan Tetap');
            $table->string('nik', 100)->nullable();
            $table->string('npwp', 100)->nullable();
            $table->string('tempat_lahir', 50)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('agama', 20)->nullable();
            $table->string('status_pernikahan', 20)->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('pendidikan_terakhir_new', 20)->nullable();
            $table->string('perguruan_tinggi', 100)->nullable();
            $table->string('jurusan', 100)->nullable();
            $table->integer('tahun_lulus')->nullable();
            $table->string('nama_ibu_kandung', 100)->nullable();
            $table->string('nama_kontak_darurat', 100)->nullable();
            $table->string('telepon_kontak_darurat', 20)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
