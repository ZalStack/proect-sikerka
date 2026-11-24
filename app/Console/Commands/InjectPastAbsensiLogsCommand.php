<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\AbsensiCorrectionLog;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InjectPastAbsensiLogsCommand extends Command
{
    protected $signature = 'audit:inject-past-logs {--dry-run : Hanya simulasi tanpa menyimpan}';

    protected $description = 'Pindai dan inject seluruh jejak perubahan presensi masa lalu ke menu Audit Log Koreksi Presensi';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info("Memulai pemindaian jejak perubahan presensi masa lalu..." . ($dryRun ? " (SIMULASI)" : ""));

        $formatTime = function ($val) {
            if (empty($val) || $val === '-') return '-';
            if ($val instanceof \Carbon\CarbonInterface) {
                return $val->copy()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i');
            }
            $str = (string) $val;
            if (str_contains($str, 'T') || str_contains($str, 'Z')) {
                try {
                    return Carbon::parse($str)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i');
                } catch (\Throwable $e) {}
            }
            if (str_contains($str, ' ')) {
                try {
                    return Carbon::parse($str)->format('H:i');
                } catch (\Throwable $e) {}
            }
            if (preg_match('/^(\d{2}:\d{2})/', $str, $m)) {
                return $m[1];
            }
            return substr($str, 0, 5);
        };

        $injectedCount = 0;

        // -------------------------------------------------------------
        // SUMBER 1: ACTIVITY LOGS (Aksi Update/Store/Delete oleh Admin)
        // -------------------------------------------------------------
        $this->newLine();
        $this->warn("Memindai Sumber 1: activity_logs...");

        $activityLogs = ActivityLog::where(function ($q) {
            $q->where('description', 'like', '%absensi.update%')
              ->orWhere('description', 'like', '%absensi.store%')
              ->orWhere('description', 'like', '%absensi.destroy%')
              ->orWhere('action', 'like', '%absensi%');
        })
        ->orderBy('created_at')
        ->get();

        $this->line("Ditemukan " . $activityLogs->count() . " aktivitas presensi di activity_logs.");

        foreach ($activityLogs as $act) {
            $meta = is_array($act->metadata) ? $act->metadata : (json_decode($act->metadata, true) ?: []);
            $path = $meta['path'] ?? ($act->description ?? '');

            // Ekstrak ID Absensi dari URL path: misal superadmin/absensi/123/update
            $absensiId = null;
            if (preg_match('/absensi\/(\d+)/', $path, $matches)) {
                $absensiId = (int) $matches[1];
            }

            if (!$absensiId) {
                continue;
            }

            $absensi = Absensi::with('karyawan')->find($absensiId);
            if (!$absensi) {
                continue;
            }

            $karyawan = $absensi->karyawan ?? Karyawan::withTrashed()->find($absensi->karyawan_id);
            if (!$karyawan) {
                continue;
            }

            // Cek apakah sudah pernah diinject
            $exists = AbsensiCorrectionLog::where('karyawan_id', $karyawan->id)
                ->where('tanggal_presensi', $absensi->tanggal)
                ->where('created_at', $act->created_at)
                ->exists();

            if ($exists) {
                continue;
            }

            $action = 'koreksi';
            if (str_contains($path, 'destroy')) {
                $action = 'hapus';
            } elseif (str_contains($path, 'store')) {
                $action = 'tambah';
            }

            $jamIn = $formatTime($absensi->check_in);
            $jamOut = $formatTime($absensi->check_out);

            $newData = [
                'status' => $absensi->status ?: 'Hadir',
                'check_in' => $jamIn,
                'check_out' => $jamOut,
                'total_jam_kerja' => $absensi->total_jam_kerja ?: 0,
                'kantor_cabang' => $absensi->kantor_cabang,
            ];

            // Estimasi nilai awal sebelum diedit
            $oldData = [
                'status' => $absensi->status ?: 'Hadir',
                'check_in' => $jamIn,
                'check_out' => '-',
                'total_jam_kerja' => 0,
                'kantor_cabang' => $absensi->kantor_cabang,
            ];

            if (!$dryRun) {
                AbsensiCorrectionLog::create([
                    'operator_id' => $act->user_id,
                    'operator_name' => $act->user_name ?: 'Admin',
                    'operator_role' => $act->user_role ?: 'superadmin',
                    'karyawan_id' => $karyawan->id,
                    'karyawan_nama' => $karyawan->nama_lengkap,
                    'karyawan_kode' => $karyawan->kode_pegawai,
                    'tanggal_presensi' => $absensi->tanggal,
                    'action' => $action,
                    'old_data' => $oldData,
                    'new_data' => $newData,
                    'keterangan' => $absensi->keterangan ?: "Rekonstruksi jejak audit dari activity_logs",
                    'ip_address' => $act->ip_address,
                    'created_at' => $act->created_at,
                ]);
            }

            $injectedCount++;
            $this->line("✓ [ActivityLog] {$karyawan->nama_lengkap} (Tgl: " . ($absensi->tanggal ? $absensi->tanggal->format('d/m/Y') : '-') . ") oleh {$act->user_name}");
        }

        // -------------------------------------------------------------
        // SUMBER 2: DATA PRESENSI DENGAN UPDATED_AT BEDA HARI / KETERANGAN ADMIN
        // -------------------------------------------------------------
        $this->newLine();
        $this->warn("Memindai Sumber 2: Presensi yang tanggal editnya melompat hari (diedit belakangan)...");

        $editedAbsensis = Absensi::with('karyawan')
            ->whereNotNull('updated_at')
            ->where(function ($q) {
                $q->whereRaw('DATE(updated_at) > DATE(tanggal)')
                  ->orWhere('keterangan', 'like', '%manual%')
                  ->orWhere('keterangan', 'like', '%koreksi%')
                  ->orWhere('keterangan', 'like', '%admin%');
            })
            ->get();

        $this->line("Ditemukan " . $editedAbsensis->count() . " data presensi dengan indikasi modifikasi belakangan.");

        foreach ($editedAbsensis as $abs) {
            $karyawan = $abs->karyawan ?? Karyawan::withTrashed()->find($abs->karyawan_id);
            if (!$karyawan) {
                continue;
            }

            // Cek apakah presensi ini sudah ada di log pada tanggal_presensi yang sama
            $exists = AbsensiCorrectionLog::where('karyawan_id', $karyawan->id)
                ->where('tanggal_presensi', $abs->tanggal)
                ->exists();

            if ($exists) {
                continue;
            }

            $jamIn = $formatTime($abs->check_in);
            $jamOut = $formatTime($abs->check_out);

            $newData = [
                'status' => $abs->status ?: 'Hadir',
                'check_in' => $jamIn,
                'check_out' => $jamOut,
                'total_jam_kerja' => $abs->total_jam_kerja ?: 0,
                'kantor_cabang' => $abs->kantor_cabang,
            ];

            // Reconstruct Nilai Awal
            $oldData = [
                'status' => 'Belum Lengkap',
                'check_in' => $jamIn,
                'check_out' => '-',
                'total_jam_kerja' => 0,
                'kantor_cabang' => $abs->kantor_cabang,
            ];

            $ket = $abs->keterangan ?: ("Presensi diedit pada " . ($abs->updated_at ? $abs->updated_at->format('d/m/Y H:i') : '-'));

            if (!$dryRun) {
                AbsensiCorrectionLog::create([
                    'operator_id' => null,
                    'operator_name' => 'Super Administrator (Audit Lampau)',
                    'operator_role' => 'superadmin',
                    'karyawan_id' => $karyawan->id,
                    'karyawan_nama' => $karyawan->nama_lengkap,
                    'karyawan_kode' => $karyawan->kode_pegawai,
                    'tanggal_presensi' => $abs->tanggal,
                    'action' => 'koreksi',
                    'old_data' => $oldData,
                    'new_data' => $newData,
                    'keterangan' => $ket,
                    'ip_address' => $abs->ip_address ?: '127.0.0.1',
                    'created_at' => $abs->updated_at ?: now(),
                ]);
            }

            $injectedCount++;
            $this->line("✓ [Absensi Lampau] {$karyawan->nama_lengkap} (Tgl: " . ($abs->tanggal ? $abs->tanggal->format('d/m/Y') : '-') . ")");
        }

        $this->newLine();
        $this->info("========================================================================================");
        $this->info("SELESAI! Sebanyak {$injectedCount} jejak perubahan masa lalu berhasil di-inject ke Log Koreksi Presensi.");
        $this->info("Silakan buka/refresh menu 'Log Koreksi Presensi' di browser untuk melihat seluruh riwayatnya.");
        $this->info("========================================================================================");

        return self::SUCCESS;
    }
}
