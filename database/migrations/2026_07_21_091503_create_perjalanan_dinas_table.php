<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perjalanan_dinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->string('judul', 200);
            $table->text('agenda');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('surat_tugas', 255)->nullable();
            $table->string('status', 20)->default('pending'); 
            $table->text('catatan_hr')->nullable();
            $table->date('tanggal_pengajuan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('karyawans')->onDelete('set null');
            $table->timestamps();

            $table->index(['karyawan_id', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perjalanan_dinas');
    }
};
