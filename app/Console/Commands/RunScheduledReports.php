<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ScheduledReport;
use App\Services\ReportGeneratorService;
use Illuminate\Console\Command;

/**
 * Jalankan seluruh jadwal laporan yang jatuh tempo pada tick scheduler.
 *
 * Dijadwalkan tiap 15 menit di routes/console.php; setiap jadwal hanya
 * dieksekusi satu kali per waktu run_time berkat kolom next_run_at.
 */
class RunScheduledReports extends Command
{
    protected $signature = 'reports:run {--id= : Jalankan hanya jadwal tertentu} {--force : Abaikan batas waktu next_run_at}';

    protected $description = 'Menjalankan laporan terjadwal yang jatuh tempo';

    public function handle(ReportGeneratorService $generator): int
    {
        $query = ScheduledReport::query()->where('is_active', true);

        if ($onlyId = $this->option('id')) {
            $query->where('id', (int) $onlyId);
        }

        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            });
        }

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            $this->info('Tidak ada laporan terjadwal yang jatuh tempo.');

            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;

        foreach ($schedules as $schedule) {
            try {
                $report = $generator->run($schedule);
                $success++;

                $this->line(sprintf(
                    '[OK] %s → %s (%s)',
                    $schedule->name,
                    $report->filename,
                    $report->message
                ));

                ActivityLog::record(
                    'report.scheduled_run',
                    'Laporan terjadwal "' . $schedule->name . '" berhasil dibuat: ' . $report->message,
                    [
                        'user_id' => null,
                        'user_name' => 'System Scheduler',
                        'user_role' => 'system',
                        'module' => 'reports',
                        'subject_type' => 'generated_reports',
                        'subject_id' => $report->id,
                        'metadata' => ['filename' => $report->filename, 'schedule_id' => $schedule->id],
                    ]
                );
            } catch (\Throwable $e) {
                $failed++;

                $this->error('[GAGAL] ' . $schedule->name . ' → ' . $e->getMessage());

                ActivityLog::record(
                    'report.scheduled_run',
                    'Laporan terjadwal "' . $schedule->name . '" GAGAL: ' . $e->getMessage(),
                    [
                        'user_id' => null,
                        'user_name' => 'System Scheduler',
                        'user_role' => 'system',
                        'module' => 'reports',
                        'subject_type' => 'scheduled_reports',
                        'subject_id' => $schedule->id,
                    ]
                );
            }
        }

        $this->info("Selesai: {$success} berhasil, {$failed} gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
