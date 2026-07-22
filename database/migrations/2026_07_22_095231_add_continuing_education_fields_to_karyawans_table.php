<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->boolean('is_continuing_education')->default(false)->after('pendidikan_terakhir_new');
            $table->string('continuing_program', 20)->nullable()->after('is_continuing_education');
            $table->string('continuing_perguruan_tinggi', 100)->nullable()->after('continuing_program');
            $table->string('continuing_jurusan', 100)->nullable()->after('continuing_perguruan_tinggi');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['is_continuing_education', 'continuing_program', 'continuing_perguruan_tinggi', 'continuing_jurusan']);
        });
    }
};
