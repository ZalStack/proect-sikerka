<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->string('jenis_cuti', 50)->default('Cuti Tahunan');
            $table->integer('jatah_cuti')->default(12);
            $table->integer('sisa_cuti')->default(12);
            $table->integer('cuti_digunakan')->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('pending');
            $table->date('tanggal_pengajuan')->nullable();
            $table->text('catatan_hr')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};
