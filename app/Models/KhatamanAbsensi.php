<?php
// app/Models/KhatamanAbsensi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KhatamanAbsensi extends Model
{
    use HasFactory;

    /**
     * Default hari aktif Khataman: Kamis.
     * ISO-8601 day of week: 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu
     */
    const ACTIVE_DAY = 4; // Kamis
    const DEFAULT_END_HOUR = 23; // Jam 23:00 (11 malam)
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

    // Tambahan properti untuk konfigurasi dinamis (tidak disimpan di DB)
    protected static $activeDay = self::ACTIVE_DAY;
    protected static $endHour = self::DEFAULT_END_HOUR;
    protected static $endMinute = self::DEFAULT_END_MINUTE;

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Set hari aktif (default: Kamis)
     */
    public static function setActiveDay($day)
    {
        self::$activeDay = $day;
    }

    /**
     * Get hari aktif
     */
    public static function getActiveDay()
    {
        return self::$activeDay;
    }

    /**
     * Set jam terakhir absensi
     */
    public static function setEndTime($hour, $minute = 0)
    {
        self::$endHour = $hour;
        self::$endMinute = $minute;
    }

    /**
     * Get jam terakhir absensi
     */
    public static function getEndTime()
    {
        return [
            'hour' => self::$endHour,
            'minute' => self::$endMinute,
        ];
    }

    /**
     * Cek apakah hari ini adalah hari aktif
     */
    public static function isActiveDay()
    {
        return Carbon::now()->dayOfWeekIso == self::$activeDay;
    }

    /**
     * Cek apakah hari tertentu adalah hari aktif
     */
    public static function isActiveDayForDate($date)
    {
        return Carbon::parse($date)->dayOfWeekIso == self::$activeDay;
    }

    /**
     * Cek apakah masih dalam jam absensi
     */
    public static function isWithinAbsensiTime()
    {
        $now = Carbon::now();
        $endTime = self::getEndTime();
        $endOfDay = Carbon::today()->setHour($endTime['hour'])->setMinute($endTime['minute']);

        return $now->lte($endOfDay);
    }

    /**
     * Cek apakah sudah absen hari ini
     */
    public static function hasCheckedInToday($karyawanId)
    {
        return self::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', Carbon::today())
            ->exists();
    }

    /**
     * Cek apakah sudah absen pada tanggal tertentu
     */
    public static function hasCheckedInOnDate($karyawanId, $date)
    {
        return self::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', $date)
            ->exists();
    }

    /**
     * Get daftar hari aktif dalam bulan
     */
    public static function getActiveDaysInMonth($month, $year)
    {
        $days = [];
        $date = Carbon::create($year, $month, 1);
        while ($date->month == $month) {
            if ($date->dayOfWeekIso == self::$activeDay) {
                $days[] = $date->copy();
            }
            $date->addDay();
        }
        return $days;
    }

    /**
     * Hitung jumlah hari aktif dalam bulan
     */
    public static function countActiveDaysInMonth($month, $year)
    {
        return count(self::getActiveDaysInMonth($month, $year));
    }

    /**
     * Get nama hari aktif
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
        return $days[self::$activeDay] ?? 'Kamis';
    }
}
