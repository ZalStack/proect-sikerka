<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            // Nullable: percobaan login gagal dengan email yang belum terdaftar
            // tidak punya pemilik — tetap dicatat untuk audit keamanan.
            $table->unsignedBigInteger('karyawan_id')->nullable()->index();
            $table->string('email', 100)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('success')->default(false)->index();
            $table->string('failure_reason', 60)->nullable();
            $table->string('guard', 20)->default('web');
            $table->timestamp('created_at')->nullable()->index();

            $table->index(['karyawan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
