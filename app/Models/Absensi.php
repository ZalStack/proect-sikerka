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
        'latitude',
        'longitude',
        'location_accuracy',
        'is_valid_location',
        'qr_code_token',
        'ip_address',
        'user_agent',
        'is_suspicious',
        'suspicious_reason',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_valid_location' => 'boolean',
        'is_suspicious' => 'boolean',
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
            'KPM CABANG POLTANGAN' => [
                'latitude' => -6.297271839927471,
                'longitude' => 106.84699770957265,
            ],
        ];
    }

    /**
     * Batas akurasi GPS maksimum yang masih dianggap layak dipakai (meter).
     * Bacaan GPS yang lebih buruk dari ini (mis. dari sinyal wifi/cell tower,
     * indoor, atau aplikasi fake-GPS yang malas mensimulasikan akurasi)
     * ditolak karena tidak bisa dipercaya untuk validasi radius 50 meter.
     */
    const MAX_GPS_ACCURACY = 75; // meter

    /**
     * Cek apakah lokasi berada dalam radius tertentu dari salah satu kantor.
     *
     * Selain jarak, fungsi ini juga mempertimbangkan akurasi GPS ($accuracy)
     * agar hasil "valid/tidak valid" lebih bisa dipercaya:
     * - Jika akurasi tidak masuk akal (<= 0) atau lebih buruk dari
     *   MAX_GPS_ACCURACY meter, lokasi otomatis ditolak (reason: poor_accuracy).
     * - Jarak yang dipakai untuk keputusan tetap jarak sebenarnya ke kantor
     *   (radius TIDAK diperlonggar oleh akurasi) supaya orang tidak bisa lolos
     *   hanya dengan melaporkan akurasi yang jelek/besar.
     */
    public static function isValidLocation($latitude, $longitude, $radius = 50, $accuracy = null)
    {
        $locations = self::getOfficeLocations();
        $nearestLocation = null;
        $nearestDistance = PHP_FLOAT_MAX;

        // Validasi kualitas sinyal GPS terlebih dahulu
        $accuracyOk = true;
        $accuracyReason = null;
        if ($accuracy !== null) {
            if ($accuracy <= 0) {
                $accuracyOk = false;
                $accuracyReason = 'invalid_accuracy';
            } elseif ($accuracy > self::MAX_GPS_ACCURACY) {
                $accuracyOk = false;
                $accuracyReason = 'poor_accuracy';
            }
        }

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
        }

        $withinRadius = $nearestDistance <= $radius;

        return [
            'valid' => $withinRadius && $accuracyOk,
            'within_radius' => $withinRadius,
            'accuracy_ok' => $accuracyOk,
            'accuracy_reason' => $accuracyReason,
            'distance' => round($nearestDistance, 2),
            'location_name' => ($withinRadius && $accuracyOk) ? $nearestLocation : null,
            'nearest' => $nearestLocation,
            'nearest_distance' => round($nearestDistance, 2),
        ];
    }
}
