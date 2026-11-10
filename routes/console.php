<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:database')
    ->dailyAt(config('backup.schedule', '01:00'))
    ->withoutOverlapping();

Schedule::command('activity:prune --days=' . config('backup.log_retention_days', 180))
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->withoutOverlapping();

// Laporan terjadwal (Fitur C12): tick tiap 15 menit, eksekusi jadwal yang
// jatuh tempo berdasarkan kolom next_run_at (aman dari double-run).
Schedule::command('reports:run')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();
