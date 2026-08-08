<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Revisi tabel `pengumuman` (BUKAN tabel baru):
 * - Hapus kolom-kolom terkait fitur "kirim ke WhatsApp" karena fitur tsb sudah dihapus.
 * - Tambah kolom `deleted_at` (soft delete) supaya saat pengumuman dihapus,
 *   datanya tetap ada di tabel dan masih bisa dipakai sebagai sumber
 *   notifikasi "Pengumuman Dihapus" tanpa perlu tabel log terpisah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->dropColumn(['is_sent_to_whatsapp', 'sent_at', 'whatsapp_status']);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->boolean('is_sent_to_whatsapp')->default(false);
            $table->datetime('sent_at')->nullable();
            $table->string('whatsapp_status', 50)->nullable();
            $table->dropSoftDeletes();
        });
    }
};
