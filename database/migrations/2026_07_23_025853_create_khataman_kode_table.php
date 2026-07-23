<?php
// database/migrations/2026_07_24_000001_create_khataman_kode_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khataman_kode', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('kode', 20);
            $table->unsignedBigInteger('created_by')->nullable(); // tidak pakai foreign key
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khataman_kode');
    }
};
