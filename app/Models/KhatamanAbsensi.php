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

    // Cek apakah hari ini aktif (Senin-Jumat)
    public static function isActiveDay()
    {
        $day = date('N'); // 1=Senin ... 5=Jumat
        return $day >= 1 && $day <= 5;
    }

    // Cek apakah sudah absen hari ini
    public static function hasCheckedInToday($karyawanId)
    {
        return self::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', date('Y-m-d'))
            ->exists();
    }
}
