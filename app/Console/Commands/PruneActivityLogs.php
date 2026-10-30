<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature = 'activity:prune {--days=180 : Simpan log selama N hari terakhir}';

    protected $description = 'Menghapus log aktivitas yang lebih lama dari periode retensi';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = ActivityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("{$deleted} log aktivitas lebih lama dari {$days} hari berhasil dihapus.");

        return self::SUCCESS;
    }
}
