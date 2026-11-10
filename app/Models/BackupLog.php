<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Riwayat eksekusi backup (lokal & offsite) termasuk kegagalannya.
 * Tabel: backup_logs (tanpa updated_at — hanya jejak audit sekali tulis).
 */
class BackupLog extends Model
{
    public $timestamps = false;

    protected $table = 'backup_logs';

    protected $fillable = [
        'filename',
        'trigger',
        'local_success',
        'offsite_success',
        'offsite_disk',
        'size',
        'status',
        'message',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'local_success' => 'boolean',
        'offsite_success' => 'boolean',
        'created_at' => 'datetime',
    ];

    public const TRIGGERS = ['manual', 'schedule', 'restore', 'offsite'];

    public static function record(array $attributes): ?self
    {
        try {
            return static::create(array_merge(['created_at' => now()], $attributes));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mencatat log backup: ' . $e->getMessage());

            return null;
        }
    }

    public function creator()
    {
        return $this->belongsTo(Karyawan::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'success' => 'Berhasil',
            'partial' => 'Sebagian',
            'failed' => 'Gagal',
            default => ucfirst((string) $this->status),
        };
    }
}
