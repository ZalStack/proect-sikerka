<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * File laporan hasil generate (riwayat unduhan) — Fitur C12.
 * Tabel: generated_reports.
 */
class GeneratedReport extends Model
{
    protected $table = 'generated_reports';

    protected $fillable = [
        'scheduled_report_id',
        'type',
        'filename',
        'format',
        'size',
        'status',
        'message',
        'generated_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function scheduledReport()
    {
        return $this->belongsTo(ScheduledReport::class, 'scheduled_report_id');
    }

    public function generator()
    {
        return $this->belongsTo(Karyawan::class, 'generated_by');
    }

    public function sizeHuman(): string
    {
        $bytes = (int) $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'success' => 'Berhasil',
            'failed' => 'Gagal',
            default => ucfirst((string) $this->status),
        };
    }
}
