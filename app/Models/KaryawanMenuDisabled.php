<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hak akses menu per karyawan.
 * Kehadiran baris = menu_key DINONAKTIFKAN untuk karyawan tersebut
 * pada rentang tanggal valid_from..valid_until (alasan: reason).
 * Dikelola Superadmin lewat halaman Manajemen Akses.
 * Setelah valid_until lewat, baris kedaluwarsa dibersihkan otomatis.
 */
class KaryawanMenuDisabled extends Model
{
    protected $table = 'karyawan_menu_disabled';

    protected $fillable = [
        'karyawan_id',
        'menu_key',
        'valid_from',
        'valid_until',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Apakah pembatasan ini sedang berlaku hari ini?
     */
    public function isActiveNow(): bool
    {
        $today = Carbon::today();

        // Belum mulai
        if ($this->valid_from && $this->valid_from->copy()->startOfDay()->gt($today)) {
            return false;
        }

        // Sudah berakhir
        if ($this->valid_until && $this->valid_until->copy()->startOfDay()->lt($today)) {
            return false;
        }

        return true;
    }

    /**
     * Label periode berlaku (untuk ditampilkan ke admin/karyawan).
     */
    public function periodLabel(): string
    {
        if (!$this->valid_from && !$this->valid_until) {
            return 'Tanpa batas waktu';
        }

        $mulai = $this->valid_from ? $this->valid_from->isoFormat('D MMM Y') : '...';
        $sampai = $this->valid_until ? $this->valid_until->isoFormat('D MMM Y') : '...';

        return "{$mulai} – {$sampai}";
    }
}
