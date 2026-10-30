<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
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

            $this->info('Backup berhasil: ' . $result['filename'] . ' (' . $result['size_human'] . ').');
            if ($pruned > 0) {
                $this->line("{$pruned} backup lama dihapus otomatis.");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
