<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            // Anti Remote Access: hitungan pelanggaran presensi (strike) seumur hidup akun.
            // Tangga: ke-1 → ban 1 hari, ke-2 → ban 2 hari, ke-3 → ban PERMANEN.
            $table->unsignedInteger('violation_count')->default(0)->after('is_resigned');
            // Batas waktu blokir sementara (null = tidak diblokir sementara).
            $table->timestamp('banned_until')->nullable()->after('violation_count');
            // Blokir permanen — hanya bisa dilepas kembali oleh Superadmin.
            $table->boolean('is_banned_permanent')->default(false)->after('banned_until');
            // Pesan/alert dari Superadmin yang tampil otomatis di dashboard karyawan terblokir.
            $table->text('ban_message')->nullable()->after('is_banned_permanent');
        });

        Schema::table('absensis', function (Blueprint $table) {
            // Hash fingerprint perangkat: mendeteksi perubahan perangkat
            // antara check-in & check-out, dan pemakaian satu perangkat untuk banyak akun.
            $table->string('device_fingerprint_hash', 64)->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn('device_fingerprint_hash');
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['violation_count', 'banned_until', 'is_banned_permanent', 'ban_message']);
        });
    }
};
