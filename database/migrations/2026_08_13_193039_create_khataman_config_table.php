<?php
// database/migrations/2026_08_13_000000_create_khataman_config_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khataman_config', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->text('value');
            $table->timestamps();
        });

        // Insert default config
        DB::table('khataman_config')->insert([
            ['key' => 'active_day', 'value' => '4', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'end_hour', 'value' => '23', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'end_minute', 'value' => '59', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('khataman_config');
    }
};