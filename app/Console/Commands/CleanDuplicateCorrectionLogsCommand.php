<?php

namespace App\Console\Commands;

use App\Models\AbsensiCorrectionLog;
use Illuminate\Console\Command;

class CleanDuplicateCorrectionLogsCommand extends Command
{
    protected $signature = 'audit:clean-duplicates';

    protected $description = 'Bersihkan seluruh data duplikat pada tabel Audit Log Koreksi Presensi';

    public function handle(): int
    {
        $this->info("Memeriksa duplikasi pada catatan riwayat log koreksi presensi...");

        $allLogs = AbsensiCorrectionLog::orderBy('id')->get();
        $this->line("Total catatan saat ini: " . $allLogs->count());

        $seen = [];
        $toDelete = [];

        foreach ($allLogs as $log) {
            $tgl = $log->tanggal_presensi ? (is_string($log->tanggal_presensi) ? substr($log->tanggal_presensi, 0, 10) : $log->tanggal_presensi->format('Y-m-d')) : '';
            $karyawanId = (string) $log->karyawan_id;
            $action = (string) $log->action;
            $newIn = $log->new_data['check_in'] ?? '';
            $newOut = $log->new_data['check_out'] ?? '';
            $newStatus = $log->new_data['status'] ?? '';
            $newJam = $log->new_data['total_jam_kerja'] ?? '';

            // Unique key per kejadian perubahan presensi
            $key = "{$karyawanId}_{$tgl}_{$action}_{$newIn}_{$newOut}_{$newStatus}_{$newJam}";

            if (isset($seen[$key])) {
                $toDelete[] = $log->id;
            } else {
                $seen[$key] = $log->id;
            }
        }

        $duplicateCount = count($toDelete);

        if ($duplicateCount === 0) {
            $this->info("✓ Tidak ditemukan data duplikat. Seluruh log sudah unik.");
            return self::SUCCESS;
        }

        AbsensiCorrectionLog::whereIn('id', $toDelete)->delete();

        $remaining = AbsensiCorrectionLog::count();
        $this->info("========================================================================================");
        $this->info("BERHASIL! Sebanyak {$duplicateCount} catatan duplikat berhasil dihapus.");
        $this->info("Catatan yang tersimpan sekarang: {$remaining} catatan unik (bersih tanpa duplikat).");
        $this->info("========================================================================================");

        return self::SUCCESS;
    }
}
