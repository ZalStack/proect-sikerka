<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('karyawans')->onDelete('cascade');
            $table->string('judul', 200);
            $table->text('isi');
            $table->string('gambar', 255)->nullable();
            $table->string('target', 50)->default('semua');
            $table->boolean('is_sent_to_whatsapp')->default(false);
            $table->datetime('sent_at')->nullable();
            $table->string('whatsapp_status', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
