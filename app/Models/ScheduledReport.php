<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jadwal laporan berkala yang dijalankan otomatis oleh scheduler (Fitur C12).
 * Tabel: scheduled_reports.
 */
class ScheduledReport extends Model
{
    protected $table = 'scheduled_reports';

    protected $fillable = [
        'name',
        'type',
        'frequency',
        'day_of_week',
        'day_of_month',
        'run_time',
        'period_from',
        'period_to',
        'format',
        'recipients',
        'is_active',
        'last_run_at',
        'last_status',
        'last_message',
        'next_run_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'run_time' => 'string',
        'period_from' => 'date',
        'period_to' => 'date',
        'day_of_month' => 'integer',
    ];

    /** Jenis laporan yang didukung generator. */
    public const TYPES = [
        'absensi' => 'Rekap Absensi',
        'cuti' => 'Rekap Cuti',
        'sunnah' => 'Rekap 7SPS',
        'pelanggaran' => 'Pelanggaran Presensi',
        'kehadiran_divisi' => 'Kehadiran per Divisi',
    ];

    public const FREQUENCIES = [
        'daily' => 'Harian',
        'weekly' => 'Mingguan',
        'monthly' => 'Bulanan',
    ];

    public const FORMATS = ['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (.csv)'];

    /** Nama hari (0 = Senin ... 6 = Minggu, sesuai date('w') bukan; pakai label sendiri). */
    public const DAYS = [
        '1' => 'Senin',
        '2' => 'Selasa',
        '3' => 'Rabu',
        '4' => 'Kamis',
        '5' => 'Jumat',
        '6' => 'Sabtu',
        '0' => 'Minggu',
    ];

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public function frequencyLabel(): string
    {
        $base = self::FREQUENCIES[$this->frequency] ?? ucfirst((string) $this->frequency);

        if ($this->frequency === 'weekly' && $this->day_of_week !== null) {
            return $base . ' (' . (self::DAYS[(string) $this->day_of_week] ?? '-') . ')';
        }

        if ($this->frequency === 'monthly' && $this->day_of_month !== null) {
            return $base . ' (tgl ' . $this->day_of_month . ')';
        }

        return $base;
    }

    public function statusLabel(): string
    {
        return match ($this->last_status) {
            'success' => 'Berhasil',
            'failed' => 'Gagal',
            default => 'Belum pernah dijalankan',
        };
    }

    public function generatedReports()
    {
        return $this->hasMany(GeneratedReport::class, 'scheduled_report_id');
    }

    /** Hitung kapan jadwal berikutnya harus dijalankan. */
    public function computeNextRunAt(): ?string
    {
        $time = $this->run_time ?: '07:00';

        $next = match ($this->frequency) {
            'daily' => now()->setTimeFromTimeString($time),
            'weekly' => $this->nextWeeklyRun($time),
            'monthly' => $this->nextMonthlyRun($time),
            default => now()->setTimeFromTimeString($time),
        };

        // Bila waktu hari ini sudah lewat, geser ke hari berikutnya/bulan berikutnya.
        if ($next->lessThanOrEqualTo(now())) {
            $next = match ($this->frequency) {
                'daily' => $next->addDay(),
                'weekly' => $next->addWeek(),
                'monthly' => $next->addMonthNoOverflow(),
                default => $next->addDay(),
            };
        }

        return $next->toDateTimeString();
    }

    private function nextWeeklyRun(string $time): \Carbon\Carbon
    {
        $targetDay = (int) ($this->day_of_week ?? 1);
        $candidate = now()->setTimeFromTimeString($time);

        // date('w'): 0=Minggu..6=Sabtu — cocok dengan kunci DAYS.
        while ((int) $candidate->format('w') !== $targetDay) {
            $candidate->addDay();
        }

        return $candidate;
    }

    private function nextMonthlyRun(string $time): \Carbon\Carbon
    {
        $day = min(max((int) ($this->day_of_month ?? 1), 1), 28);
        $candidate = now()->setDate((int) now()->format('Y'), (int) now()->format('m'), $day)
            ->setTimeFromTimeString($time);

        if ($candidate->day !== $day) {
            // Bulan dengan hari lebih pendek — mundur ke hari terakhir bulan itu.
            $candidate->day((int) $candidate->daysInMonth);
        }

        return $candidate;
    }
}
