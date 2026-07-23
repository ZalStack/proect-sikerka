<?php
// database/migrations/2026_07_09_023858_create_fhl_absensi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fhl_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('check_in')->nullable();
            $table->string('foto_bukti', 255)->nullable();
            $table->string('kode_input', 20)->nullable(); // kode yang diinput karyawan
            $table->string('status', 20)->default('Hadir');
            $table->text('keterangan')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->timestamps();

            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fhl_absensi');
    }
};
