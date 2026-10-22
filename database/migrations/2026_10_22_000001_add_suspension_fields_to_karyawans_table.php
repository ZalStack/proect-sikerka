<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            // Rentang tanggal penangguhan akun (suspend) oleh Superadmin.
            // Selama today berada di rentang ini, akun TIDAK BISA LOGIN.
            // (Terpisah dari ban presensi anti remote access: banned_until / is_banned_permanent.)
            $table->date('suspend_start')->nullable()->after('tanggal_resign');
            $table->date('suspend_end')->nullable()->after('suspend_start');
            $table->text('suspend_reason')->nullable()->after('suspend_end');
            // Karyawan (Superadmin) yang memberlakukan suspend ini.
            $table->unsignedBigInteger('suspended_by')->nullable()->after('suspend_reason');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn([
                'suspend_start',
                'suspend_end',
                'suspend_reason',
                'suspended_by',
            ]);
        });
    }
};
