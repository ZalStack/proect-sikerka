<?php
// app/Models/KhatamanAbsensi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhatamanAbsensi extends Model
{
    use HasFactory;

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
     * Cek apakah hari ini adalah hari aktif (Kamis)
     * Day of week: 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu
     */
    public static function isActiveDay()
    {
        return date('N') == 4; // Kamis
    }

    /**
     * Cek apakah sudah absen hari ini
     */
    public static function hasCheckedInToday($karyawanId)
    {
        return self::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', date('Y-m-d'))
            ->exists();
    }
}
