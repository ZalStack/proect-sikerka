<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AuditKaryawanCommand extends Command
{
    protected $signature = 'audit:karyawan {name=Fakhrizal : Nama lengkap atau sebagian nama karyawan}';

    protected $description = 'Audit mendalam riwayat presensi karyawan untuk mendeteksi perubahan/koreksi tidak wajar oleh admin';

    public function handle(): int
    {
        $search = (string) $this->argument('name');
        $this->info("Mencari data karyawan dengan kata kunci: '{$search}'...");

        $karyawans = Karyawan::where('nama_lengkap', 'like', "%{$search}%")
            ->orWhere('kode_pegawai', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->get();

        if ($karyawans->isEmpty()) {
            $this->error("Karyawan dengan kata kunci '{$search}' tidak ditemukan di database.");
            return self::FAILURE;
        }

        foreach ($karyawans as $karyawan) {
            $this->auditKaryawan($karyawan);
        }

        return self::SUCCESS;
    }

    private function auditKaryawan(Karyawan $karyawan): void
    {
        $this->newLine();
        $this->line("========================================================================================");
        $this->info("HASIL FORENSIK PRESENSI: " . strtoupper($karyawan->nama_lengkap));
        $this->line("========================================================================================");
        $this->line("ID Karyawan   : {$karyawan->id}");
        $this->line("Kode Pegawai  : {$karyawan->kode_pegawai}");
        $this->line("Email         : {$karyawan->email}");
        $this->line("Jabatan / Div : {$karyawan->jabatan} / {$karyawan->divisi}");
        $this->line("Status        : {$karyawan->status}");
        $this->newLine();

        $formatTime = function ($val) {
            if (empty($val)) return '-';
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

        // 1. CARI PRESENSI YANG DIUBAH DI LUAR HARI PRESENSINYA
        $this->warn("--- [1] PRESENSI YANG DIEDIT SETELAH HARI-H (Beda Hari Tanggal Presensi vs Updated_at) ---");
        $suspicious = Absensi::where('karyawan_id', $karyawan->id)
            ->whereNotNull('updated_at')
            ->whereRaw('DATE(updated_at) > DATE(tanggal)')
            ->orderByDesc('updated_at')
            ->get();

        if ($suspicious->isEmpty()) {
            $this->line("✓ Tidak ditemukan presensi yang tanggal editnya melompat ke hari lain.");
        } else {
            $this->error("Ditemukan {$suspicious->count()} presensi yang diubah setelah hari presensi!");
            $tableData = [];
            foreach ($suspicious as $s) {
                $tgl = $s->tanggal ? (is_string($s->tanggal) ? substr($s->tanggal, 0, 10) : $s->tanggal->format('d/m/Y')) : '-';
                $in = $formatTime($s->check_in);
                $out = $formatTime($s->check_out);
                $dibuat = $s->created_at ? $s->created_at->format('d/m/Y H:i') : '-';
                $diedit = $s->updated_at ? $s->updated_at->format('d/m/Y H:i') : '-';

                $tableData[] = [
                    $s->id,
                    $tgl,
                    $s->status,
                    "{$in} - {$out} ({$s->total_jam_kerja} Jam)",
                    $dibuat,
                    $diedit,
                    $s->keterangan ?: '-',
                ];
            }
            $this->table(['ID Presensi', 'Tgl Presensi', 'Status', 'Jam Kerja', 'Created (Dibuat)', 'Updated (Diedit)', 'Keterangan'], $tableData);
        }

        // 2. CEK ACTIVITY LOGS BERDASARKAN ID PRESENSI KARYAWAN
        $this->newLine();
        $this->warn("--- [2] JEJAK OPERATOR DI ACTIVITY LOGS TERKAIT PRESENSI KARYAWAN INI ---");
        $allAbsensiIds = Absensi::where('karyawan_id', $karyawan->id)->pluck('id')->toArray();
        $matchedLogs = collect();

        if (count($allAbsensiIds) > 0) {
            $matchedLogs = ActivityLog::where(function ($q) use ($allAbsensiIds) {
                foreach ($allAbsensiIds as $aid) {
                    $q->orWhere('description', 'like', "%/{$aid}%")
                      ->orWhere('description', 'like', "% {$aid}%")
                      ->orWhere('metadata', 'like', "%/{$aid}%");
                }
            })
            ->orderByDesc('created_at')
            ->get();
        }

        if ($matchedLogs->isEmpty()) {
            $this->line("✓ Tidak ditemukan URL atau aksi spesifik pada ID presensi ini di activity_logs.");
        } else {
            $logTable = [];
            foreach ($matchedLogs as $ml) {
                $waktu = $ml->created_at ? $ml->created_at->format('d/m/Y H:i:s') : '-';
                $logTable[] = [
                    $waktu,
                    $ml->user_name,
                    $ml->user_role,
                    $ml->ip_address,
                    $ml->description,
                ];
            }
            $this->table(['Waktu Log', 'Operator', 'Role', 'IP Address', 'Deskripsi Aksi'], $logTable);
        }

        // 3. DAFTAR 20 RIWAYAT PRESENSI TERBARU
        $this->newLine();
        $this->info("--- [3] 20 RIWAYAT PRESENSI TERAKHIR BESERTA WAKTU DIBUAT & DIUPDATE ---");
        $recents = Absensi::where('karyawan_id', $karyawan->id)
            ->orderByDesc('tanggal')
            ->take(20)
            ->get();

        if ($recents->isEmpty()) {
            $this->line("Belum ada data presensi.");
        } else {
            $recentTable = [];
            foreach ($recents as $r) {
                $tgl = $r->tanggal ? (is_string($r->tanggal) ? substr($r->tanggal, 0, 10) : $r->tanggal->format('d/m/Y')) : '-';
                $in = $formatTime($r->check_in);
                $out = $formatTime($r->check_out);
                $created = $r->created_at ? $r->created_at->format('d/m/Y H:i') : '-';
                $updated = $r->updated_at ? $r->updated_at->format('d/m/Y H:i') : '-';
                $isEdited = ($r->created_at && $r->updated_at && $r->created_at->diffInMinutes($r->updated_at) > 720);

                $recentTable[] = [
                    $r->id,
                    $tgl,
                    $r->status,
                    "{$in} - {$out}",
                    "{$r->total_jam_kerja} Jam",
                    $created,
                    $updated,
                    $r->keterangan ?: '-',
                ];
            }
            $this->table(['ID', 'Tanggal', 'Status', 'Jam Masuk - Pulang', 'Total', 'Created At', 'Updated At', 'Keterangan'], $recentTable);
        }

        $this->line("========================================================================================");
    }
}
