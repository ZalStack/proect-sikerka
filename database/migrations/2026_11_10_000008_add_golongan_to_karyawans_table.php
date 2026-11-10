<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Golongan karyawan (opsional) — dipakai oleh kebijakan cuti
 * (cuti_policies.scope_golongan) untuk memberlakukan kuota berbeda
 * per golongan, dan oleh master data type "golongan".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawans', 'golongan')) {
                $table->string('golongan', 100)->nullable()->after('divisi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (Schema::hasColumn('karyawans', 'golongan')) {
                $table->dropColumn('golongan');
            }
        });
    }
};
