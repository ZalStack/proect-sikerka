<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\BackupLog;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep= : Jumlah backup terbaru yang disimpan}';

    protected $description = 'Membuat backup database dan membersihkan backup lama';

    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $result = $backups->create('scheduled');
            $keep = $this->option('keep');
            $pruned = $backups->prune($keep !== null ? (int) $keep : null);

            ActivityLog::record(
                'backup.create',
                'Backup terjadwal berhasil dibuat: ' . $result['filename'] . '.',
                [
                    'user_id' => null,
                    'user_name' => 'System Scheduler',
                    'user_role' => 'system',
                    'module' => 'backup',
                    'metadata' => [
                        'filename' => $result['filename'],
                        'size' => $result['size'],
                        'size_human' => $result['size_human'],
                        'duration' => $result['duration'],
                        'driver' => $result['driver'],
                        'pruned' => $pruned,
                        'scheduled' => true,
                    ],
                ]
            );

            $offsite = null;
            if (!empty(config('backup.offsite.enabled'))) {
                $offsite = $backups->pushOffsite($result['filename']);
            }

            BackupLog::record([
                'filename' => $result['filename'],
                'trigger' => 'schedule',
                'local_success' => true,
                'offsite_success' => $offsite !== null && $offsite['status'] === 'success',
                'offsite_disk' => $offsite !== null ? (string) config('backup.offsite.disk') : null,
                'size' => $result['size'],
                'status' => $offsite !== null && $offsite['status'] === 'failed' ? 'partial' : 'success',
                'message' => 'Backup terjadwal berhasil'
                    . ($pruned > 0 ? " ({$pruned} file lama dihapus)" : '')
                    . ($offsite !== null ? '. Offsite: ' . $offsite['message'] : '.'),
            ]);

            $this->info('Backup berhasil: ' . $result['filename'] . ' (' . $result['size_human'] . ').');
            if ($pruned > 0) {
                $this->line("{$pruned} backup lama dihapus otomatis.");
            }
            if ($offsite !== null) {
                $this->line('Offsite: ' . $offsite['message']);
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup gagal: ' . $e->getMessage());

            BackupLog::record([
                'trigger' => 'schedule',
                'local_success' => false,
                'status' => 'failed',
                'message' => 'Backup terjadwal gagal: ' . $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
