<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kebijakan & kuota cuti per tahun, per jenis cuti (Fitur D14).
 *
 * scope_golongan:
 *  - null / ''  → berlaku untuk SEMUA golongan
 *  - "Tetap,Non-Tetap" → hanya golongan karyawan tersebut (cocok salah satu)
 *
 * Kebijakan aktif dipakai saat membuat baris kuota cuti karyawan
 * (CutiController::getOrCreateKuota) sehingga jatah tidak lagi
 * selalu 12 hari untuk semua tahun/karyawan.
 */
class CutiPolicy extends Model
{
    protected $fillable = [
        'tahun',
        'jenis_cuti',
        'kuota_hari',
        'scope_golongan',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'kuota_hari' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeForYear($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cari kebijakan yang berlaku untuk seorang karyawan pada tahun tertentu.
     * Prioritas: kebijakan dengan scope golongan yang cocok > kebijakan umum.
     * Mengembalikan null bila tidak ada kebijakan (artinya pakai default 12).
     */
    public static function applicableFor(Karyawan $karyawan, int $tahun, string $jenisCuti = 'Cuti Tahunan'): ?self
    {
        $policies = static::where('tahun', $tahun)
            ->where('jenis_cuti', $jenisCuti)
            ->where('is_active', true)
            ->get();

        if ($policies->isEmpty()) {
            return null;
        }

        $golongan = trim((string) ($karyawan->golongan ?? ''));

        // Cocokkan scope golongan (pisahkan koma) terlebih dahulu.
        $scoped = $policies->first(function (self $p) use ($golongan) {
            if ($golongan === '') {
                return false;
            }

            $list = array_filter(array_map('trim', explode(',', (string) $p->scope_golongan)));

            return in_array($golongan, $list, true);
        });

        if ($scoped) {
            return $scoped;
        }

        // Fallback: kebijakan tanpa scope (berlaku umum).
        return $policies->first(fn (self $p) => trim((string) $p->scope_golongan) === '');
    }

    /** Kuota hari yang berlaku: CutiPolicy (per tahun/scope) -> CutiType (default jenis) -> 12 hari. */
    public static function kuotaFor(Karyawan $karyawan, int $tahun, string $jenisCuti = 'Cuti Tahunan'): int
    {
        $policy = static::applicableFor($karyawan, $tahun, $jenisCuti);
        if ($policy !== null) {
            return (int) $policy->kuota_hari;
        }

        if (class_exists(\App\Models\CutiType::class)) {
            $type = \App\Models\CutiType::where('nama', $jenisCuti)->first();
            if ($type !== null) {
                return (int) $type->kuota_hari;
            }
        }

        return 12;
    }
}
