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
     * Cek apakah hari ini adalah hari aktif Khataman (default: Kamis).
     * Menggunakan Carbon::now() (bukan date()) supaya selalu konsisten
     * dengan timezone aplikasi (config/app.php) dan dengan waktu yang
     * dipakai di KhatamanController saat validasi & pencatatan check-in.
     */
    public static function isActiveDay()
    {
        return Carbon::now()->dayOfWeekIso == self::ACTIVE_DAY;
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
}
