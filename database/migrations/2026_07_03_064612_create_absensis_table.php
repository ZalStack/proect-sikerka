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
            $table->string('kantor_cabang', 50);
            $table->string('status', 20)->default('Hadir');
            $table->text('keterangan')->nullable();
            $table->integer('total_jam_kerja')->default(0);
            $table->boolean('is_lembur')->default(false);
            $table->integer('jam_lembur')->default(0);
            $table->boolean('is_telat')->default(false);
            $table->integer('menit_telat')->default(0);
            $table->boolean('is_manual_checkin')->default(false);
            $table->boolean('is_manual_checkout')->default(false);
            $table->timestamps();

            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
