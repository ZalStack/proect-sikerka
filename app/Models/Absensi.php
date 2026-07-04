<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'check_in',
        'check_out',
        'kantor_cabang',
        'status',
        'keterangan',
        'total_jam_kerja',
        'is_lembur',
        'jam_lembur',
        'is_telat',
        'menit_telat',
        'is_manual_checkin',
        'is_manual_checkout',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_lembur' => 'boolean',
        'is_telat' => 'boolean',
        'is_manual_checkin' => 'boolean',
        'is_manual_checkout' => 'boolean',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function scopeFilterByDate($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('tanggal', [$startDate, $endDate]);
        }
        return $query;
    }

    public function scopeFilterByMonthYear($query, $month, $year)
    {
        if ($month && $year) {
            return $query->whereMonth('tanggal', $month)
                        ->whereYear('tanggal', $year);
        }
        return $query;
    }

    public function scopeFilterByKaryawan($query, $karyawanId)
    {
        if ($karyawanId) {
            return $query->where('karyawan_id', $karyawanId);
        }
        return $query;
    }
}
