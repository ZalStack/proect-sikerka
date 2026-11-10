<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Data master divisi / jabatan / golongan (Fitur D13).
 *
 * Menggantikan input free-text pada form karyawan yang rawan tidak
 * konsisten ("HRD" vs "Hrd" vs "HR Dept") dan memudahkan penyaringan
 * laporan. Tabel: master_data.
 */
class MasterData extends Model
{
    protected $table = 'master_data';

    protected $fillable = [
        'type',
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Tipe yang didukung. */
    public const TYPES = [
        'divisi' => 'Divisi',
        'jabatan' => 'Jabatan',
        'golongan' => 'Golongan',
    ];

    public static function typeLabel(string $type): string
    {
        return self::TYPES[$type] ?? $type;
    }

    /** Daftar nama aktif untuk satu tipe (dipakai dropdown form karyawan). */
    public static function options(string $type): array
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
