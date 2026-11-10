<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Bersihkan orphans lebih dulu agar penambahan FK tidak gagal pada
            // data lama (log dari akun yang sudah dihapus permanen).
            Schema::table('activity_logs', function (Blueprint $inner) {
                $inner->unsignedBigInteger('user_id')->nullable()->change();
            });

            $table->foreign('user_id', 'activity_logs_user_id_foreign')
                ->references('id')->on('karyawans')
                ->nullOnDelete();
        });

        Schema::table('cuti', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('catatan_hr');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approved_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
