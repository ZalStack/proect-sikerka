<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remote_access_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->foreignId('absensi_id')->nullable()->constrained('absensis')->nullOnDelete();
            $table->date('tanggal');
            $table->string('konteks', 20); // checkin / checkout
            $table->json('signals'); // bukti lengkap sinyal yang terdeteksi
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->string('risk_level', 10)->default('low'); // low | high
            $table->string('status', 20)->default('pending_review'); // pending_review | confirmed | dismissed
            $table->unsignedTinyInteger('strike_number')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('karyawans')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Satu catatan pelanggaran per karyawan per hari:
            // deteksi berulang di hari yang sama digabung, tidak menambah strike kedua kalinya.
            $table->unique(['karyawan_id', 'tanggal']);
            $table->index(['status', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remote_access_violations');
    }
};
