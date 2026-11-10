<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master data divisi / jabatan / golongan — menggantikan input free-text
        // yang rawan tidak konsisten pada form karyawan.
        Schema::create('master_data', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index(); // divisi | jabatan | golongan
            $table->string('name', 100);
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['type', 'name']);
        });

        // Kebijakan & kuota cuti per tahun (per jenis cuti).
        Schema::create('cuti_policies', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun')->index();
            $table->string('jenis_cuti', 50)->default('Cuti Tahunan');
            $table->integer('kuota_hari')->default(12);
            $table->string('scope_golongan', 100)->nullable(); // null = berlaku untuk semua golongan
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['tahun', 'jenis_cuti', 'scope_golongan']);
        });

        // Jadwal laporan berkala yang dijalankan otomatis oleh scheduler.
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 40)->index(); // absensi | cuti | sunnah | pelanggaran | kehadiran_divisi
            $table->string('frequency', 20)->default('monthly'); // daily | weekly | monthly
            $table->string('day_of_week', 10)->nullable();   // 0-6 (Senin..Minggu) untuk weekly
            $table->integer('day_of_month')->nullable();     // 1-28 untuk monthly
            $table->time('run_time')->default('07:00');
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->string('format', 10)->default('xlsx');   // xlsx | csv
            $table->json('recipients')->nullable();          // daftar email (opsional)
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_status', 20)->nullable();   // success | failed
            $table->text('last_message')->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->timestamps();
        });

        // File laporan hasil generate (riwayat unduhan).
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_report_id')->nullable()
                ->constrained('scheduled_reports')->nullOnDelete();
            $table->string('type', 40);
            $table->string('filename', 190);
            $table->string('format', 10)->default('xlsx');
            $table->integer('size')->default(0);
            $table->string('status', 20)->default('success');
            $table->text('message')->nullable();
            $table->foreignId('generated_by')->nullable()
                ->constrained('karyawans')->nullOnDelete();
            $table->timestamps();
        });

        // Feature flags per cakupan (role / divisi / karyawan individual).
        Schema::create('feature_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key', 60)->index();
            $table->string('scope_type', 20);   // role | divisi | karyawan
            $table->string('scope_value', 100);
            $table->boolean('enabled')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('karyawans')->nullOnDelete();
            $table->timestamps();

            $table->unique(['feature_key', 'scope_type', 'scope_value']);
            $table->index(['scope_type', 'scope_value']);
        });

        // Template pesan siap pakai (ban, pelanggaran, broadcast).
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index(); // ban | violation | broadcast
            $table->string('title', 150);
            $table->text('body');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()
                ->constrained('karyawans')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'title']);
        });

        // Riwayat eksekusi backup (termasuk upaya offsite & kegagalannya).
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 190)->nullable()->index();
            $table->string('trigger', 20)->default('manual'); // manual | schedule
            $table->boolean('local_success')->default(false);
            $table->boolean('offsite_success')->default(false);
            $table->string('offsite_disk', 30)->nullable();
            $table->integer('size')->default(0);
            $table->string('status', 20)->default('success'); // success | partial | failed
            $table->text('message')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('karyawans')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('feature_assignments');
        Schema::dropIfExists('generated_reports');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('cuti_policies');
        Schema::dropIfExists('master_data');
    }
};
