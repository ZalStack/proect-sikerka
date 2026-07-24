<?php
// app/Models/FhlKode.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FhlKode extends Model
{
    use HasFactory;

    protected $table = 'fhl_kode';

    protected $fillable = [
        'tanggal',
        'kode',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(Karyawan::class, 'created_by');
    }

    /**
     * Generate kode acak (6 karakter alfanumerik)
     */
    public static function generateRandomKode(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Cek apakah ada kode untuk tanggal tertentu
     */
    public static function hasKodeForDate($tanggal): bool
    {
        return self::whereDate('tanggal', $tanggal)->exists();
    }

    /**
     * Ambil kode untuk tanggal tertentu
     */
    public static function getKodeForDate($tanggal): ?string
    {
        $record = self::whereDate('tanggal', $tanggal)->first();
        return $record ? $record->kode : null;
    }
}
