<?php
// app/Models/FhlAbsensi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FhlAbsensi extends Model
{
    use HasFactory;

    protected $table = 'fhl_absensi';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'check_in',
        'foto_bukti',
        'kode_input',
        'status',
        'keterangan',
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

    // Scope untuk filter bulan dan tahun
    public function scopeFilterByMonthYear($query, $month, $year)
    {
        if ($month && $year) {
            return $query->whereMonth('tanggal', $month)
                        ->whereYear('tanggal', $year);
        }
        return $query;
    }

    // Scope untuk filter karyawan
    public function scopeFilterByKaryawan($query, $karyawanId)
    {
        if ($karyawanId) {
            return $query->where('karyawan_id', $karyawanId);
        }
        return $query;
    }

    // Cek apakah hari ini Jumat
    public static function isFriday()
    {
        return date('N') == 5; // 5 = Jumat
    }

    // Cek apakah sudah absen hari ini
    public static function hasCheckedInToday($karyawanId)
    {
        return self::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', date('Y-m-d'))
            ->exists();
    }
}
