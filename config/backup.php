<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Database
    |--------------------------------------------------------------------------
    |
    | Semua file backup disimpan di storage/app/backups (private). File tidak
    | pernah diletakkan di public agar tidak bisa diakses langsung lewat URL.
    |
    */

    'path' => env('BACKUP_PATH', storage_path('app/backups')),

    'retention' => (int) env('BACKUP_RETENTION', 14),

    'mysqldump' => env('MYSQLDUMP_PATH', 'mysqldump'),

    'mysql' => env('MYSQL_BINARY_PATH', 'mysql'),

    'timeout' => (int) env('BACKUP_TIMEOUT', 300),

    'schedule' => env('BACKUP_SCHEDULE', '01:00'),

    'log_retention_days' => (int) env('ACTIVITY_LOG_RETENTION_DAYS', 180),

];
