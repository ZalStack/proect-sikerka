<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'hari_liburs';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'jenis',
        'tahun',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tahun' => 'integer',
    ];

    /**
     * Clear cache setiap kali data hari libur berubah
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget('hari_liburs_dates_' . $model->tahun);
            Cache::forget('all_hari_liburs_dates');
        });

        static::deleted(function ($model) {
            Cache::forget('hari_liburs_dates_' . $model->tahun);
            Cache::forget('all_hari_liburs_dates');
        });
    }

    /**
     * Ambil array tanggal libur (format Y-m-d => keterangan)
     */
    public static function getDatesMap(?int $tahun = null): array
    {
        $cacheKey = $tahun ? ('hari_liburs_dates_' . $tahun) : 'all_hari_liburs_dates';

        return Cache::remember($cacheKey, 3600, function () use ($tahun) {
            $query = self::query();
            if ($tahun) {
                $query->where('tahun', $tahun);
            }
            return $query->pluck('keterangan', 'tanggal')
                ->mapWithKeys(function ($keterangan, $tanggal) {
                    $dateStr = Carbon::parse($tanggal)->format('Y-m-d');
                    return [$dateStr => $keterangan];
                })
                ->toArray();
        });
    }

    /**
     * Cek apakah sebuah tanggal adalah hari libur (Minggu, Senin, atau Tanggal Merah)
     */
    public static function isHariLibur($date): bool
    {
        if (!$date) {
            return false;
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // Minggu = 7, Senin = 1
        if (in_array($carbon->dayOfWeekIso, [1, 7], true)) {
            return true;
        }

        $dateStr = $carbon->format('Y-m-d');
        $datesMap = self::getDatesMap((int) $carbon->format('Y'));

        return isset($datesMap[$dateStr]);
    }

    /**
     * Ambil keterangan libur untuk tanggal tertentu
     */
    public static function getKeteranganLibur($date): ?string
    {
        if (!$date) {
            return null;
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $dateStr = $carbon->format('Y-m-d');
        $datesMap = self::getDatesMap((int) $carbon->format('Y'));

        if (isset($datesMap[$dateStr])) {
            return $datesMap[$dateStr];
        }

        if ($carbon->dayOfWeekIso === 7) {
            return 'Hari Libur Akhir Pekan (Minggu)';
        }

        if ($carbon->dayOfWeekIso === 1) {
            return 'Hari Libur Kantor (Senin)';
        }

        return null;
    }
}
