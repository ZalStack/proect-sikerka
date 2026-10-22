<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hak akses menu per karyawan.
 * Kehadiran baris = menu_key DINONAKTIFKAN untuk karyawan tersebut.
 * Dikelola Superadmin lewat halaman Manajemen Akses.
 */
class KaryawanMenuDisabled extends Model
{
    protected $table = 'karyawan_menu_disabled';

    protected $fillable = [
        'karyawan_id',
        'menu_key',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
