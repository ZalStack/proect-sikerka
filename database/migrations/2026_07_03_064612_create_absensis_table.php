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

            $table->timestamps();

            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
