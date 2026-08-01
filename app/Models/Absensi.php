<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Absensi extends Model
{
    use HasFactory;

    // Daftar status yang diperbolehkan
    const STATUSES = ['Hadir', 'Izin', 'Sakit', 'Alpha', 'Perjalanan Dinas'];

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

    /**
     * ==========================================================
     * JAM KERJA STANDAR & PERHITUNGAN TERLAMBAT / LEMBUR
     * ==========================================================
     */

    // Batas jam check-in maksimum supaya TIDAK dianggap terlambat.
    // Check-in setelah jam ini akan dihitung terlambat (dalam menit).
    const WORK_START_TIME = '07:45:00';

    // Jam pulang standar kantor, dipakai sebagai basis perhitungan lembur.
    // Check-out setelah jam ini akan dihitung sebagai lembur (dalam menit).
    const WORK_END_TIME = '16:00:00';

    /**
     * Nama hari (Bahasa Indonesia) dari tanggal absensi. Contoh: Senin, Selasa, ... Minggu.
     * Dipakai supaya tiap baris laporan/export jelas jatuh di hari apa,
     * termasuk kalau karyawan masuk di hari Minggu atau Senin.
     */
    public function getHariAttribute(): ?string
    {
        return $this->tanggal ? $this->tanggal->copy()->locale('id')->isoFormat('dddd') : null;
    }

    /**
     * True kalau tanggal absensi jatuh di hari Minggu (hari libur kantor).
     * Dipakai untuk menandai kalau ada karyawan yang masuk kerja di hari Minggu.
     */
    public function getIsHariMingguAttribute(): bool
    {
        return $this->tanggal ? $this->tanggal->dayOfWeekIso === 7 : false;
    }

    /**
     * Jumlah menit keterlambatan check-in, dihitung dari batas WORK_START_TIME (07:45).
     * Kalau check-in dilakukan tepat atau sebelum jam 07:45, atau belum check-in
     * sama sekali, hasilnya 0 (tidak dianggap terlambat).
     */
    public function getTerlambatMenitAttribute(): int
    {
        if (!$this->check_in || !$this->tanggal) {
            return 0;
        }

        $checkIn = $this->check_in->copy();
        $batas = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . self::WORK_START_TIME, $checkIn->getTimezone());

        return $checkIn->greaterThan($batas) ? $batas->diffInMinutes($checkIn) : 0;
    }

    /**
     * Helper boolean: apakah absensi ini terlambat (check-in melebihi jam 07:45)?
     */
    public function getIsTerlambatAttribute(): bool
    {
        return $this->terlambat_menit > 0;
    }

    /**
     * Jumlah menit lembur, dihitung dari selisih check-out terhadap jam pulang
     * standar (WORK_END_TIME / 16:00). Kalau belum check-out, atau pulang
     * lebih awal/tepat waktu, hasilnya 0.
     */
    public function getLemburMenitAttribute(): int
    {
        if (!$this->check_out || !$this->tanggal) {
            return 0;
        }

        $checkOut = $this->check_out->copy();
        $batas = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . self::WORK_END_TIME, $checkOut->getTimezone());

        return $checkOut->greaterThan($batas) ? $batas->diffInMinutes($checkOut) : 0;
    }

    /**
     * Total menit kerja aktual, dihitung presisi per menit dari selisih
     * check-in ke check-out. Berbeda dari kolom total_jam_kerja yang
     * disimpan dalam satuan jam (dibulatkan), field ini dipakai untuk
     * laporan/export yang butuh akurasi per menit.
     */
    public function getTotalMenitKerjaAttribute(): int
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        return $this->check_in->diffInMinutes($this->check_out);
    }

    /**
     * Gabungkan baris-baris absensi "Perjalanan Dinas" yang berturut-turut (per karyawan,
     * per keterangan) menjadi SATU baris tampilan berbentuk periode (tanggal_mulai s/d
     * tanggal_selesai), sementara baris absensi harian biasa tetap tampil apa adanya.
     *
     * Ini HANYA mengubah tampilan/urutan koleksi, TIDAK mengubah data di database.
     * Setiap baris hasil gabungan akan punya atribut tambahan:
     *   - tanggal_mulai_display, tanggal_selesai_display (Carbon)
     *   - is_periode (bool)  -> true kalau baris ini gabungan lebih dari 1 hari
     *   - jumlah_hari        -> jumlah hari yang tergabung
     *
     * @param  \Illuminate\Support\Collection $absensis  Koleksi model Absensi (boleh dari banyak karyawan)
     * @return \Illuminate\Support\Collection
     */
    public static function mergeConsecutivePerjalananDinas($absensis)
    {
        $displayRows = collect();

        foreach ($absensis->groupBy('karyawan_id') as $items) {
            $items = $items->sortBy(fn ($item) => $item->tanggal->timestamp)->values();
            $buffer = null;

            foreach ($items as $item) {
                $isPD = $item->status === 'Perjalanan Dinas';

                if ($isPD && $buffer && $buffer->keterangan === $item->keterangan && $item->tanggal->isSameDay($buffer->tanggal_selesai_display->copy()->addDay())) {
                    // Perpanjang periode yang sedang berjalan
                    $buffer->tanggal_selesai_display = $item->tanggal;
                    $buffer->jumlah_hari += 1;
                    $buffer->is_periode = true;
                    $buffer->total_jam_kerja += (int) $item->total_jam_kerja;
                    continue;
                }

                if ($buffer) {
                    $displayRows->push($buffer);
                }

                $item->tanggal_mulai_display = $item->tanggal->copy();
                $item->tanggal_selesai_display = $item->tanggal->copy();
                $item->jumlah_hari = 1;
                $item->is_periode = false;

                $buffer = $isPD ? $item : null;

                if (!$isPD) {
                    $displayRows->push($item);
                }
            }

            if ($buffer) {
                $displayRows->push($buffer);
            }
        }

        return $displayRows->sortByDesc(fn ($item) => $item->tanggal_selesai_display->timestamp)->values();
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
            'KPM LALADON 2' => [
                'latitude' => -6.586988011637659,
                'longitude' => 106.75881657975602,
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
            'SMP AL AZHAR SYIFA BUDI CIBINONG' => [
                'latitude' => -6.5046491241629525,
                'longitude' => 106.8071259,
            ],
            'YAPI AL AZHAR RAWAMANGUN' => [
                'latitude' => -6.19530173446925,
                'longitude' => 106.88405384978994,
            ],
        ];
    }

    /**
     * Batas akurasi GPS maksimum (meter)
     */
    const MAX_GPS_ACCURACY = 75;

    /**
     * ==========================================================
     * ANTI FAKE GPS / ANTI KECURANGAN ABSENSI
     * ==========================================================
     */

    // Berapa kali koordinat/akurasi yang IDENTIK PERSIS boleh berulang
    // sebelum dianggap mencurigakan (indikasi lokasi di-hardcode / fake GPS statis)
    const SUSPICIOUS_REPEAT_THRESHOLD = 3;

    // Jendela waktu (hari) untuk mengecek pengulangan koordinat/akurasi
    const SUSPICIOUS_REPEAT_WINDOW_DAYS = 45;

    // Kecepatan perpindahan lokasi yang dianggap tidak wajar (indikasi GPS "melompat")
    const MAX_REALISTIC_SPEED_KMH = 200;

    /**
     * Flag dari client (browser) yang dianggap BUKTI KUAT fake GPS / manipulasi.
     * Kalau salah satu flag ini muncul, absensi langsung ditolak (bukan cuma ditandai).
     */
    const HIGH_CONFIDENCE_FLAGS = ['automasi_browser_terdeteksi', 'geolocation_api_dimodifikasi', 'lokasi_melompat_tidak_wajar'];

    /**
     * Deteksi pola mencurigakan pada percobaan absensi (check-in/out) seorang karyawan.
     * Menggabungkan sinyal dari client (browser) + pola historis di database.
     *
     * @param  int    $karyawanId
     * @param  float  $lat
     * @param  float  $lng
     * @param  float|null $accuracy
     * @param  array  $clientFlags  Flag kecurigaan yang dikirim dari JS (lihat karyawan/absensi.blade.php)
     * @return array{is_suspicious: bool, is_high_confidence: bool, reasons: array}
     */
    public static function detectSuspiciousAttempt(int $karyawanId, float $lat, float $lng, ?float $accuracy, array $clientFlags = []): array
    {
        $reasons = [];

        // 1) Sinyal langsung dari browser (webdriver/automasi, Geolocation API dimodifikasi,
        //    beberapa sample lokasi berturut-turut identik 100% / tidak ada jitter sama sekali,
        //    atau lokasi "melompat" jauh dalam waktu singkat)
        foreach ($clientFlags as $flag) {
            $flag = is_string($flag) ? trim($flag) : '';
            if ($flag !== '') {
                $reasons[] = $flag;
            }
        }

        // 2) Koordinat identik persis berulang kali. GPS asli selalu punya sedikit
        //    variasi (drift) walau di titik yang sama; fake GPS statis biasanya
        //    mengirim titik yang PERSIS sama terus-menerus.
        $latRounded = round($lat, 6);
        $lngRounded = round($lng, 6);

        $coordinateRepeatCount = self::where('karyawan_id', $karyawanId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw('ROUND(latitude, 6) = ?', [$latRounded])
            ->whereRaw('ROUND(longitude, 6) = ?', [$lngRounded])
            ->where('tanggal', '>=', now()->subDays(self::SUSPICIOUS_REPEAT_WINDOW_DAYS)->toDateString())
            ->count();

        if ($coordinateRepeatCount >= self::SUSPICIOUS_REPEAT_THRESHOLD) {
            $reasons[] = 'koordinat_identik_berulang';
        }

        // 3) Akurasi GPS identik persis berulang kali. Akurasi GPS asli berfluktuasi
        //    tergantung cuaca/sinyal/posisi satelit; kalau selalu sama persis, itu
        //    indikasi nilai yang di-hardcode oleh aplikasi fake GPS.
        if ($accuracy !== null) {
            $accuracyRepeatCount = self::where('karyawan_id', $karyawanId)
                ->where('location_accuracy', $accuracy)
                ->where('tanggal', '>=', now()->subDays(self::SUSPICIOUS_REPEAT_WINDOW_DAYS)->toDateString())
                ->count();

            if ($accuracyRepeatCount >= self::SUSPICIOUS_REPEAT_THRESHOLD) {
                $reasons[] = 'akurasi_identik_berulang';
            }
        }

        $reasons = array_values(array_unique($reasons));

        $isHighConfidence = count(array_intersect($reasons, self::HIGH_CONFIDENCE_FLAGS)) > 0;

        return [
            'is_suspicious' => count($reasons) > 0,
            'is_high_confidence' => $isHighConfidence,
            'reasons' => $reasons,
        ];
    }

    /**
     * Terjemahkan kode alasan kecurigaan menjadi teks yang mudah dibaca HR.
     */
    public static function suspiciousReasonLabel(string $reason): string
    {
        return match ($reason) {
            'automasi_browser_terdeteksi' => 'Browser otomatis/robot terdeteksi',
            'geolocation_api_dimodifikasi' => 'Geolocation API browser dimodifikasi (indikasi fake GPS)',
            'lokasi_melompat_tidak_wajar' => 'Lokasi berpindah tidak wajar (indikasi GPS palsu)',
            'lokasi_tanpa_variasi' => 'Sinyal GPS tidak wajar (tidak ada variasi sama sekali)',
            'koordinat_identik_berulang' => 'Koordinat persis sama berulang kali (indikasi lokasi statis/palsu)',
            'akurasi_identik_berulang' => 'Akurasi GPS persis sama berulang kali (indikasi nilai di-hardcode)',
            default => $reason,
        };
    }

    /**
     * Cek validitas lokasi
     */
    public static function isValidLocation($latitude, $longitude, $radius = 50, $accuracy = null)
    {
        $locations = self::getOfficeLocations();
        $nearestLocation = null;
        $nearestDistance = PHP_FLOAT_MAX;

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
