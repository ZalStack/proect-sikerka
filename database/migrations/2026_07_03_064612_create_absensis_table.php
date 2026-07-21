<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('kantor_cabang', 50)->nullable();
            $table->string('status', 20)->default('Hadir');
            $table->text('keterangan')->nullable();
            $table->integer('total_jam_kerja')->default(0);

            // Kolom untuk geolokasi
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('location_accuracy', 10, 2)->nullable(); // akurasi dalam meter
            $table->boolean('is_valid_location')->default(false);
            $table->string('qr_code_token', 100)->nullable();

            // Kolom audit trail & anti-manipulasi data
            $table->string('ip_address', 45)->nullable(); // jejak IP saat check-in/out
            $table->string('user_agent', 255)->nullable(); // jejak perangkat/browser
            $table->boolean('is_suspicious')->default(false); // ditandai otomatis oleh server saat akurasi GPS jelek/tidak wajar
            $table->string('suspicious_reason', 100)->nullable(); // alasan singkat kenapa ditandai mencurigakan

            $table->timestamps();

            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
