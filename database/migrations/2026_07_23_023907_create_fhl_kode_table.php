<?php
// database/migrations/2026_07_23_100000_create_fhl_kode_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fhl_kode', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('kode', 20);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fhl_kode');
    }
};
