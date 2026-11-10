<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\GeneratedReport;
use App\Models\Karyawan;
use App\Models\PerjalananDinas;
use App\Models\RemoteAccessViolation;
use App\Models\ScheduledReport;
use App\Models\SunnahDaily;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

/**
 * Generator laporan terjadwal (Fitur C12).
 *
 * Menghasilkan file XLSX/CSV ke storage/app/reports, mencatat hasilnya ke
 * tabel generated_reports, dan mengirimkan email bila jadwal punya penerima.
 */
class ReportGeneratorService
{
    /** Folder tujuan file laporan (private, tidak bisa diakses lewat URL). */
    public function directory(): string
    {
        $dir = storage_path('app/reports');
        File::ensureDirectoryExists($dir, 0700, true);

        return $dir;
    }

    /**
     * Jalankan satu jadwal laporan.
     *
     * @return GeneratedReport
     */
    public function run(ScheduledReport $schedule, ?int $userId = null): GeneratedReport
    {
        $period = $this->resolvePeriod($schedule);

        try {
            [$headers, $rows] = $this->buildDataset($schedule->type, $period['from'], $period['to']);

            $baseName = sprintf(
                'laporan-%s-%s-%s',
                $schedule->type,
                $period['from']->format('Ymd'),
                $period['to']->format('Ymd')
            );

            $extension = $schedule->format === 'csv' ? 'csv' : 'xlsx';
            $filename = $baseName . '-' . now()->format('His') . '.' . $extension;
            $path = $this->directory() . DIRECTORY_SEPARATOR . $filename;

            $this->writeFile($path, $headers, $rows, $schedule->format, $schedule->name, $period);

            $size = (int) @filesize($path);

            $empty = empty($rows);
            $summary = $empty
                ? 'Tidak ada data pada periode ' . $period['from']->format('d M Y') . ' – ' . $period['to']->format('d M Y') . '.'
                : count($rows) . ' baris data, periode ' . $period['from']->format('d M Y') . ' – ' . $period['to']->format('d M Y') . '.';

            $report = GeneratedReport::create([
                'scheduled_report_id' => $schedule->id,
                'type' => $schedule->type,
                'filename' => $filename,
                'format' => $schedule->format,
                'size' => $size,
                'status' => 'success',
                'message' => $summary,
                'generated_by' => $userId,
            ]);

            $schedule->forceFill([
                'last_run_at' => now(),
                'last_status' => 'success',
                'last_message' => $summary,
                'next_run_at' => $schedule->computeNextRunAt(),
            ])->save();

            if (!$empty) {
                $this->notifyRecipients($schedule, $report, $period);
            }

            return $report;
        } catch (\Throwable $e) {
            $report = GeneratedReport::create([
                'scheduled_report_id' => $schedule->id,
                'type' => $schedule->type,
                'filename' => '-',
                'format' => $schedule->format,
                'size' => 0,
                'status' => 'failed',
                'message' => $e->getMessage(),
                'generated_by' => $userId,
            ]);

            $schedule->forceFill([
                'last_run_at' => now(),
                'last_status' => 'failed',
                'last_message' => \Illuminate\Support\Str::limit($e->getMessage(), 500),
                'next_run_at' => $schedule->computeNextRunAt(),
            ])->save();

            throw $e;
        }
    }

    /**
     * Tentukan rentang periode laporan.
     *
     * @return array{from: Carbon, to: Carbon}
     */
    private function resolvePeriod(ScheduledReport $schedule): array
    {
        // Periode eksplisit pada jadwal menang (dipakai untuk laporan sekali jalan).
        if ($schedule->period_from && $schedule->period_to) {
            return [
                'from' => Carbon::parse($schedule->period_from)->startOfDay(),
                'to' => Carbon::parse($schedule->period_to)->endOfDay(),
            ];
        }

        return match ($schedule->frequency) {
            'daily' => [
                'from' => now()->subDay()->startOfDay(),
                'to' => now()->subDay()->endOfDay(),
            ],
            'weekly' => [
                'from' => now()->subWeek()->startOfDay(),
                'to' => now()->subDay()->endOfDay(),
            ],
            default => [
                'from' => now()->subMonthsNoOverflow(1)->startOfMonth(),
                'to' => now()->subMonthsNoOverflow(1)->endOfMonth(),
            ],
        };
    }

    /**
     * Susun header + baris data sesuai jenis laporan.
     *
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>}
     */
    private function buildDataset(string $type, Carbon $from, Carbon $to): array
    {
        return match ($type) {
            'absensi' => $this->absensiDataset($from, $to),
            'cuti' => $this->cutiDataset($from, $to),
            'sunnah' => $this->sunnahDataset($from, $to),
            'pelanggaran' => $this->pelanggaranDataset($from, $to),
            'kehadiran_divisi' => $this->kehadiranDivisiDataset($from, $to),
            default => throw new RuntimeException('Jenis laporan "' . $type . '" tidak dikenal.'),
        };
    }

    private function absensiDataset(Carbon $from, Carbon $to): array
    {
        $headers = ['No', 'Tanggal', 'Kode', 'Nama', 'Divisi', 'Check In', 'Check Out', 'Status', 'Terlambat (menit)', 'Jam Kerja (menit)', 'Keterangan'];

        $rows = Absensi::with('karyawan')
            ->whereDate('tanggal', '>=', $from->toDateString())
            ->whereDate('tanggal', '<=', $to->toDateString())
            ->orderBy('tanggal')
            ->orderBy('karyawan_id')
            ->get()
            ->values()
            ->map(fn (Absensi $a, int $i) => [
                $i + 1,
                $a->tanggal?->format('d/m/Y'),
                $a->karyawan?->kode_pegawai,
                $a->karyawan?->nama_lengkap,
                $a->karyawan?->divisi,
                $a->check_in?->format('H:i'),
                $a->check_out?->format('H:i'),
                $a->status,
                $a->terlambat_menit,
                $a->total_jam_kerja,
                $a->keterangan,
            ])
            ->all();

        return [$headers, $rows];
    }

    private function cutiDataset(Carbon $from, Carbon $to): array
    {
        $headers = ['No', 'Diajukan', 'Kode', 'Nama', 'Divisi', 'Jenis Cuti', 'Mulai', 'Selesai', 'Durasi', 'Status', 'Catatan HR'];

        $rows = Cuti::with('karyawan')
            ->whereBetween('tanggal_pengajuan', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('tanggal_pengajuan')
            ->get()
            ->values()
            ->map(fn (Cuti $c, int $i) => [
                $i + 1,
                $c->tanggal_pengajuan?->format('d/m/Y'),
                $c->karyawan?->kode_pegawai,
                $c->karyawan?->nama_lengkap,
                $c->karyawan?->divisi,
                $c->jenis_cuti,
                $c->tanggal_mulai?->format('d/m/Y'),
                $c->tanggal_selesai?->format('d/m/Y'),
                $c->durasi ?? null,
                $c->status,
                $c->catatan_hr,
            ])
            ->all();

        return [$headers, $rows];
    }

    private function sunnahDataset(Carbon $from, Carbon $to): array
    {
        $headers = ['No', 'Tanggal', 'Kode', 'Nama', 'Divisi', 'Total Poin', 'Status Approval', 'Catatan HR'];

        $rows = SunnahDaily::with('karyawan')
            ->whereDate('tanggal', '>=', $from->toDateString())
            ->whereDate('tanggal', '<=', $to->toDateString())
            ->orderBy('tanggal')
            ->get()
            ->values()
            ->map(fn (SunnahDaily $s, int $i) => [
                $i + 1,
                $s->tanggal?->format('d/m/Y'),
                $s->karyawan?->kode_pegawai,
                $s->karyawan?->nama_lengkap,
                $s->karyawan?->divisi,
                $s->total_poin,
                $s->status_approval,
                $s->catatan_hr,
            ])
            ->all();

        return [$headers, $rows];
    }

    private function pelanggaranDataset(Carbon $from, Carbon $to): array
    {
        if (!Schema::hasTable((new RemoteAccessViolation)->getTable())) {
            return [['No', 'Waktu', 'Kode', 'Nama', 'Divisi', 'Konteks', 'Skor Risiko', 'Level Risiko', 'Status', 'Catatan'], []];
        }

        $headers = ['No', 'Waktu', 'Kode', 'Nama', 'Divisi', 'Konteks', 'Skor Risiko', 'Level Risiko', 'Status', 'Catatan'];

        $rows = RemoteAccessViolation::with(['karyawan', 'reviewer'])
            ->whereBetween('created_at', [$from->toDateTimeString(), $to->toDateTimeString()])
            ->orderByDesc('created_at')
            ->get()
            ->values()
            ->map(fn (RemoteAccessViolation $v, int $i) => [
                $i + 1,
                $v->created_at?->format('d/m/Y H:i'),
                $v->karyawan?->kode_pegawai,
                $v->karyawan?->nama_lengkap,
                $v->karyawan?->divisi,
                $v->konteks,
                $v->risk_score,
                $v->risk_level,
                $v->status_label,
                $v->notes,
            ])
            ->all();

        return [$headers, $rows];
    }

    private function kehadiranDivisiDataset(Carbon $from, Carbon $to): array
    {
        $headers = ['No', 'Divisi', 'Karyawan Aktif', 'Catatan Absensi', 'Hadir', 'Izin', 'Sakit', 'Cuti', 'Dinas', 'Alpha', 'Tingkat Kehadiran (%)'];

        $activePerDivisi = Karyawan::active()
            ->selectRaw("COALESCE(NULLIF(TRIM(divisi), ''), 'Tanpa Divisi') as divisi, COUNT(*) as total")
            ->groupBy('divisi')
            ->pluck('total', 'divisi');

        $absensiRows = Absensi::whereDate('tanggal', '>=', $from->toDateString())->whereDate('tanggal', '<=', $to->toDateString())
            ->join('karyawans', 'karyawans.id', '=', 'absensis.karyawan_id')
            ->selectRaw(
                "COALESCE(NULLIF(TRIM(karyawans.divisi), ''), 'Tanpa Divisi') as divisi, "
                . 'COUNT(*) as total, '
                . "SUM(CASE WHEN absensis.status = 'Hadir' THEN 1 ELSE 0 END) as hadir, "
                . "SUM(CASE WHEN absensis.status = 'Izin' THEN 1 ELSE 0 END) as izin, "
                . "SUM(CASE WHEN absensis.status = 'Sakit' THEN 1 ELSE 0 END) as sakit, "
                . "SUM(CASE WHEN absensis.status = 'Cuti' THEN 1 ELSE 0 END) as cuti, "
                . "SUM(CASE WHEN absensis.status = 'Perjalanan Dinas' THEN 1 ELSE 0 END) as dinas, "
                . "SUM(CASE WHEN absensis.status = 'Alpha' THEN 1 ELSE 0 END) as alpha"
            )
            ->groupBy('divisi')
            ->get();

        $divisiNames = $activePerDivisi->keys()
            ->merge($absensiRows->pluck('divisi'))
            ->unique()
            ->sort()
            ->values();

        $rows = [];

        foreach ($divisiNames as $index => $divisi) {
            $stat = $absensiRows->firstWhere('divisi', $divisi);
            $total = (int) ($stat->total ?? 0);
            $hadir = (int) ($stat->hadir ?? 0);

            $rows[] = [
                $index + 1,
                $divisi,
                (int) ($activePerDivisi[$divisi] ?? 0),
                $total,
                $hadir,
                (int) ($stat->izin ?? 0),
                (int) ($stat->sakit ?? 0),
                (int) ($stat->cuti ?? 0),
                (int) ($stat->dinas ?? 0),
                (int) ($stat->alpha ?? 0),
                $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        }

        return [$headers, $rows];
    }

    /** Tulis dataset ke file XLSX atau CSV. */
    private function writeFile(string $path, array $headers, array $rows, string $format, string $title, array $period): void
    {
        if ($format === 'csv') {
            $handle = @fopen($path, 'wb');

            if ($handle === false) {
                throw new RuntimeException('Folder laporan tidak dapat ditulis.');
            }

            try {
                // BOM UTF-8 agar Excel membaca karakter non-ASCII dengan benar.
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, $headers);

                foreach ($rows as $row) {
                    fputcsv($handle, array_map($this->csvSafe(...), $row));
                }
            } finally {
                fclose($handle);
            }

            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(\Illuminate\Support\Str::limit(preg_replace('/[^\w\s-]/u', '', $title) ?: 'Laporan', 30));

        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $this->columnLetter(count($headers)) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1F4E78');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('A2', 'Periode: ' . $period['from']->format('d M Y') . ' – ' . $period['to']->format('d M Y')
            . ' | Dibuat: ' . now()->format('d M Y H:i'));
        $sheet->mergeCells('A2:' . $this->columnLetter(count($headers)) . '2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('666666');

        // Header tabel di baris 4.
        $headerRow = 4;

        foreach ($headers as $index => $header) {
            $coordinate = $this->columnLetter($index + 1) . $headerRow;
            $sheet->setCellValue($coordinate, $header);
            $sheet->getStyle($coordinate)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($coordinate)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($coordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($coordinate)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        }

        $rowNum = $headerRow + 1;

        foreach ($rows as $row) {
            foreach ($row as $index => $value) {
                $sheet->setCellValue($this->columnLetter($index + 1) . $rowNum, $value);
            }

            // Zebra striping ringan.
            if (($rowNum - $headerRow) % 2 === 0) {
                $sheet->getStyle('A' . $rowNum . ':' . $this->columnLetter(count($headers)) . $rowNum)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F7FB');
            }

            $rowNum++;
        }

        // Auto-width sederhana (maks 45).
        foreach (range(1, count($headers)) as $colIndex) {
            $letter = $this->columnLetter($colIndex);
            $sheet->getColumnDimension($letter)->setWidth(min(45, max(10, strlen((string) ($headers[$colIndex - 1] ?? '')) + 8)));
        }

        $sheet->freezePane('A' . ($headerRow + 1));

        if ($rowNum > $headerRow + 1) {
            $sheet->setAutoFilter('A' . $headerRow . ':' . $this->columnLetter(count($headers)) . ($rowNum - 1));
        }

        $writer = new Xlsx($spreadsheet);

        try {
            $writer->save($path);
        } catch (\Throwable $e) {
            throw new RuntimeException('Gagal menulis file laporan: ' . $e->getMessage());
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        if (!is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('File laporan kosong setelah dibuat.');
        }
    }

    /** Huruf kolom Excel (1 → A, 27 → AA, dst). */
    private function columnLetter(int $index): string
    {
        $letter = '';
        $n = $index;

        while ($n > 0) {
            $mod = ($n - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $n = intdiv($n - $mod - 1, 26);
        }

        return $letter;
    }

    /** Nilai formula-injection aman untuk CSV. */
    private function csvSafe($value)
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    /** Kirim email laporan ke penerima terjadwal (best-effort). */
    private function notifyRecipients(ScheduledReport $schedule, GeneratedReport $report, array $period): void
    {
        $recipients = array_values(array_filter((array) ($schedule->recipients ?? [])));

        if (empty($recipients)) {
            return;
        }

        $path = $this->directory() . DIRECTORY_SEPARATOR . $report->filename;

        if (!is_file($path)) {
            return;
        }

        $mailable = new \App\Mail\ScheduledReportMail(
            scheduleName: $schedule->name,
            summary: $report->message,
            periodLabel: $period['from']->format('d M Y') . ' – ' . $period['to']->format('d M Y'),
            attachmentPath: $path,
            attachmentName: $report->filename,
            attachmentMime: $report->format === 'csv'
                ? 'text/csv'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );

        foreach ($recipients as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            try {
                \Illuminate\Support\Facades\Mail::to($email)->send($mailable);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning(
                    'Gagal mengirim email laporan terjadwal ke ' . $email . ': ' . $e->getMessage()
                );
            }
        }
    }
}
