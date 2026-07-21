<?php
// app/Models/Absensi.php

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
        'latitude',
        'longitude',
        'location_accuracy',
        'is_valid_location',
        'qr_code_token',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_valid_location' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'location_accuracy' => 'float',
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

    /**
     * Hitung jarak antara dua koordinat dalam meter (Haversine formula)
     */
    public static function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Konfigurasi titik koordinat kantor KPM
     */
    public static function getOfficeLocations()
    {
        return [
            'KPM LALADON' => [
                'latitude' => -6.586886661039424,
                'longitude' => 106.75890044642712,
            ],
            'KPM SEMPLAK' => [
                'latitude' => -6.553776866673678,
                'longitude' => 106.76227926589081,
            ],
            'KPM RAWAMANGUN' => [
                'latitude' => -6.197799964780801,
                'longitude' => 106.88646119657936,
            ],
            'KPM CIRATA' => [
                'latitude' => -6.587336147929745,
                'longitude' => 106.75705888792925,
            ],
            'KPM PAGELARAN' => [
                'latitude' => -6.592773750035168,
                'longitude' => 106.76223439877839,
            ],
        ];
    }

    /**
     * Cek apakah lokasi berada dalam radius 50 meter dari salah satu kantor
     */
    public static function isValidLocation($latitude, $longitude, $radius = 50)
    {
        $locations = self::getOfficeLocations();
        $nearestLocation = null;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($locations as $name => $coords) {
            $distance = self::haversineDistance(
                $latitude,
                $longitude,
                $coords['latitude'],
                $coords['longitude']
            );

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestLocation = $name;
            }

            if ($distance <= $radius) {
                return [
                    'valid' => true,
                    'distance' => round($distance, 2),
                    'location_name' => $name,
                    'nearest' => $name,
                    'nearest_distance' => round($distance, 2),
                ];
            }
        }

        return [
            'valid' => false,
            'distance' => round($nearestDistance, 2),
            'location_name' => null,
            'nearest' => $nearestLocation,
            'nearest_distance' => round($nearestDistance, 2),
        ];
    }
}
