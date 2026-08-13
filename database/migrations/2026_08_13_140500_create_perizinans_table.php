<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perizinans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');

            // Jenis pengajuan: Izin atau Sakit
            $table->string('jenis', 20);

            // Rentang tanggal pengajuan (mendukung izin/sakit lebih dari 1 hari)
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            // Alasan/keterangan dari karyawan saat mengajukan
            $table->text('keterangan');

            // pending  = baru diajukan, menunggu review HRD
            // approved = disetujui HRD -> otomatis tercatat di tabel absensi karyawan
            // rejected = ditolak HRD
            $table->string('status', 20)->default('pending');

            // Catatan/alasan dari HRD saat approve/reject (khusus reject: wajib diisi)
            $table->text('catatan_hr')->nullable();

            // HRD yang memproses pengajuan ini
            $table->foreignId('approved_by')->nullable()->constrained('karyawans')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['karyawan_id', 'status']);
            $table->index(['status', 'tanggal_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perizinans');
    }
};
