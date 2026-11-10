<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            // Soft delete: riwayat presensi/cuti/sunnah tidak lagi ikut terhapus
            // permanen lewat FK cascade saat karyawan diarsipkan.
            $table->softDeletes()->after('tanggal_resign');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
