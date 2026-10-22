<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hak akses menu per karyawan: kehadiran baris = menu DINONAKTIFKAN
        // untuk karyawan tersebut. Saat Superadmin menyalakan kembali menu,
        // barisnya dihapus (kembali ke default: aktif).
        Schema::create('karyawan_menu_disabled', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->string('menu_key', 50); // salah satu dari SystemSetting::AVAILABLE_FEATURES
            $table->timestamps();

            $table->unique(['karyawan_id', 'menu_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan_menu_disabled');
    }
};
