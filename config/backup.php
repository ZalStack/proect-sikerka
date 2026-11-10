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

    /*
    |--------------------------------------------------------------------------
    | Backup Offsite
    |--------------------------------------------------------------------------
    |
    | Salinan file backup ke storage remote (S3 / MinIO / Google Drive via S3
    | compatible) setelah backup lokal sukses. Bila dinonaktifkan atau disk
    | belum dikonfigurasi, backup tetap berjalan lokal dan status offsite
    | dicatat sebagai "skipped" (bukan error).
    |
    */

    'offsite' => [
        'enabled' => (bool) env('BACKUP_OFFSITE_ENABLED', false),
        'disk' => env('BACKUP_OFFSITE_DISK', 's3'),
        'prefix' => env('BACKUP_OFFSITE_PREFIX', 'sikekar-backups'),
        // Hapus salinan offsite yang lebih tua dari N hari (0 = tanpa batas).
        'max_age_days' => (int) env('BACKUP_OFFSITE_MAX_AGE_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifikasi Kegagalan
    |--------------------------------------------------------------------------
    |
    | Backup/offsite yang gagal dikirimkan notifikasi email ke seluruh akun
    | Super Admin aktif (best-effort — kegagalan kirim tidak menggagalkan
    | proses backup).
    |
    */

    'notify_failure' => (bool) env('BACKUP_NOTIFY_FAILURE', true),

];
