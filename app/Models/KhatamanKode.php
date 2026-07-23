<?php
// app/Models/KhatamanKode.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhatamanKode extends Model
{
    use HasFactory;

    protected $table = 'khataman_kode';

    protected $fillable = [
        'tanggal',
        'kode',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public static function generateRandomKode(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 6));
    }

    public static function hasKodeForDate($tanggal): bool
    {
        return self::whereDate('tanggal', $tanggal)->exists();
    }

    public static function getKodeForDate($tanggal): ?string
    {
        $record = self::whereDate('tanggal', $tanggal)->first();
        return $record ? $record->kode : null;
    }
}
