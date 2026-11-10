<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indeks yang menopang query riwayat/laporan yang sudah berjalan tanpa
     * penunjuk indeks (full table scan pada data yang terus bertambah).
     */
    private array $indexes = [
        'absensis' => ['tanggal', 'is_suspicious'],
        'cuti' => ['status', 'tanggal_mulai'],
        'pengumuman' => ['updated_at'],
        'sunnah_daily' => ['status_approval'],
        'karyawans' => ['posisi', 'is_resigned'],
        'password_resets' => ['token'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($tableName, $column)) {
                    continue;
                }

                try {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName, $column) {
                        $table->index($column, $this->indexName($tableName, $column));
                    });
                } catch (\Throwable $e) {
                    // Indeks sudah ada (mis. dijalankan dua kali) — lewati tanpa
                    // menggagalkan seluruh migrasi.
                    report($e);
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($tableName, $column)) {
                    continue;
                }

                try {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName, $column) {
                        $table->dropIndex($this->indexName($tableName, $column));
                    });
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    private function indexName(string $table, string $column): string
    {
        return $table . '_' . $column . '_index';
    }
};
