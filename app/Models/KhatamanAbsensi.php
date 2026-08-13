<?php
// app/Models/KhatamanAbsensi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KhatamanAbsensi extends Model
{
    use HasFactory;

    const ACTIVE_DAY = 4; // Kamis (default)
    const DEFAULT_END_HOUR = 23;
    const DEFAULT_END_MINUTE = 59;

    protected $table = 'khataman_absensi';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'check_in',
        'kode_input',
        'status',
        'ip_address',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Get active day from config
     */
    public static function getActiveDay()
    {
        return (int) KhatamanConfig::getValue('active_day', self::ACTIVE_DAY);
    }

    /**
     * Get end time from config
     */
    public static function getEndTime()
    {
        return [
            'hour' => (int) KhatamanConfig::getValue('end_hour', self::DEFAULT_END_HOUR),
            'minute' => (int) KhatamanConfig::getValue('end_minute', self::DEFAULT_END_MINUTE),
        ];
    }

    /**
     * Get active day name
     */
    public static function getActiveDayName()
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        return $days[self::getActiveDay()] ?? 'Kamis';
    }

    /**
     * Check if today is active day
     */
    public static function isActiveDay()
    {
        return Carbon::now()->dayOfWeekIso == self::getActiveDay();
    }

    /**
     * Check if specific date is active day
     */
    public static function isActiveDayForDate($date)
    {
        return Carbon::parse($date)->dayOfWeekIso == self::getActiveDay();
    }

    /**
     * Check if current time is within absensi time
     */
    public static function isWithinAbsensiTime()
    {
        $now = Carbon::now();
        $endTime = self::getEndTime();
        $endOfDay = Carbon::today()->setHour($endTime['hour'])->setMinute($endTime['minute']);
        
        return $now->lte($endOfDay);
    }

    /**
     * Check if already checked in today
     */
    public static function hasCheckedInToday($karyawanId)
    {
        return self::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', Carbon::today())
            ->exists();
    }

    /**
     * Check if already checked in on specific date
     */
    public static function hasCheckedInOnDate($karyawanId, $date)
    {
        return self::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', $date)
            ->exists();
    }

    /**
     * Get active days in month
     */
    public static function getActiveDaysInMonth($month, $year)
    {
        $days = [];
        $activeDay = self::getActiveDay();
        $date = Carbon::create($year, $month, 1);
        
        while ($date->month == $month) {
            if ($date->dayOfWeekIso == $activeDay) {
                $days[] = $date->copy();
            }
            $date->addDay();
        }
        return $days;
    }

    /**
     * Count active days in month
     */
    public static function countActiveDaysInMonth($month, $year)
    {
        return count(self::getActiveDaysInMonth($month, $year));
    }
}