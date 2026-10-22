<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan_menu_disabled', function (Blueprint $table) {
            // Rentang tanggal berlakunya pembatasan menu (sama seperti form suspend).
            // Baris aktif = valid_from <= hari ini <= valid_until (null = tanpa batas).
            // Setelah valid_until lewat, menu otomatis kembali aktif (auto-expiry).
            $table->date('valid_from')->nullable()->after('menu_key');
            $table->date('valid_until')->nullable()->after('valid_from');
            $table->text('reason')->nullable()->after('valid_until');
            // Superadmin yang memberlakukan pembatasan ini.
            $table->unsignedBigInteger('created_by')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('karyawan_menu_disabled', function (Blueprint $table) {
            $table->dropColumn([
                'valid_from',
                'valid_until',
                'reason',
                'created_by',
            ]);
        });
    }
};
