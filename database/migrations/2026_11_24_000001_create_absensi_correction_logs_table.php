<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi_correction_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable()->index();
            $table->string('operator_name', 100);
            $table->string('operator_role', 50)->default('superadmin');
            $table->unsignedBigInteger('karyawan_id')->nullable()->index();
            $table->string('karyawan_nama', 100);
            $table->string('karyawan_kode', 50)->nullable()->index();
            $table->date('tanggal_presensi')->index();
            $table->string('action', 50)->default('koreksi'); // koreksi, hapus, tambah
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_correction_logs');
    }
};
