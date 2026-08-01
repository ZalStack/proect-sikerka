<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class AbsensiController extends Controller
{
    /**
     * Timezone resmi yang dipakai untuk SEMUA perhitungan jam absensi.
     */
    private string $officeTimezone = 'Asia/Jakarta';

    /**
     * Radius maksimum absensi dalam meter (50 meter)
     */
    private int $maxRadius = 50;

    /**
     * Konfigurasi titik koordinat kantor KPM.
     */
    private function getOfficeLocations(): array
    {
        return Absensi::getOfficeLocations();
    }

    /**
     * Hitung jarak antara dua koordinat dalam meter (Haversine formula)
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        return Absensi::haversineDistance($lat1, $lon1, $lat2, $lon2);
    }

    /**
     * Cek apakah lokasi (dan akurasi GPS-nya) valid untuk absensi.
     */
    private function isValidLocation($latitude, $longitude, $radius = 50, $accuracy = null): array
    {
        return Absensi::isValidLocation($latitude, $longitude, $radius, $accuracy);
    }

    /**
     * ==========================================================
     * ANTI FAKE GPS
     * ==========================================================
     * Menggabungkan sinyal dari client (browser) dan pola historis di
     * database untuk mendeteksi kemungkinan penggunaan fake GPS / lokasi palsu.
     */
    private function checkFakeGpsAttempt(int $karyawanId, float $lat, float $lng, float $accuracy, array $clientFlags, Request $request): array
    {
        $suspicion = Absensi::detectSuspiciousAttempt($karyawanId, $lat, $lng, $accuracy, $clientFlags);

        if ($suspicion['is_suspicious']) {
            Log::warning('Percobaan absensi mencurigakan (indikasi fake GPS)', [
                'karyawan_id' => $karyawanId,
                'reasons' => $suspicion['reasons'],
                'high_confidence' => $suspicion['is_high_confidence'],
                'lat' => $lat,
                'lng' => $lng,
                'accuracy' => $accuracy,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $suspicion;
    }

    /**
     * Response standar saat absensi ditolak karena terindikasi fake GPS (high confidence).
     */
    private function rejectFakeGps(array $suspicion)
    {
        return response()->json(
            [
                'success' => false,
                'message' => 'Absensi ditolak! Sistem mendeteksi indikasi penggunaan lokasi palsu (fake GPS) pada perangkat Anda. Nonaktifkan aplikasi/mode fake GPS lalu coba lagi menggunakan lokasi GPS asli.',
                'code' => 'FAKE_GPS_DETECTED',
                'reasons' => array_map(fn($r) => Absensi::suspiciousReasonLabel($r), $suspicion['reasons']),
            ],
            403,
        );
    }

    /**
     * Ubah kumpulan kode alasan kecurigaan menjadi satu string yang mudah dibaca HR.
     */
    private function formatSuspiciousReason(array $suspicion): ?string
    {
        if (empty($suspicion['reasons'])) {
            return null;
        }

        return implode('; ', array_map(fn($r) => Absensi::suspiciousReasonLabel($r), $suspicion['reasons']));
    }

    /**
     * Check-in
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0.1|max:5000',
            'client_flags' => 'nullable|array',
            'client_flags.*' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);
        $now = Carbon::now($this->officeTimezone);

        // ==========================================================
        // ANTI FAKE GPS: cek sinyal kecurangan SEBELUM validasi lokasi
        // ==========================================================
        $suspicion = $this->checkFakeGpsAttempt($user->id, (float) $request->latitude, (float) $request->longitude, (float) $request->accuracy, (array) $request->input('client_flags', []), $request);

        if ($suspicion['is_high_confidence']) {
            return $this->rejectFakeGps($suspicion);
        }

        $locationCheck = $this->isValidLocation((float) $request->latitude, (float) $request->longitude, $this->maxRadius, (float) $request->accuracy);

        if (!$locationCheck['valid']) {
            Log::warning('Percobaan check-in ditolak', [
                'karyawan_id' => $user->id,
                'reason' => $locationCheck['accuracy_reason'] ?? 'out_of_radius',
                'distance' => $locationCheck['distance'],
                'accuracy' => $request->accuracy,
                'lat' => $request->latitude,
                'lng' => $request->longitude,
                'ip' => $request->ip(),
            ]);

            if (!$locationCheck['accuracy_ok']) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Absensi ditolak! Sinyal GPS Anda kurang akurat (± ' . $request->accuracy . ' meter). Coba pindah ke area terbuka lalu ulangi.',
                        'distance' => $locationCheck['distance'],
                        'nearest_location' => $locationCheck['nearest'],
                        'code' => 'POOR_GPS_ACCURACY',
                    ],
                    403,
                );
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Absensi ditolak! Anda berada di luar radius kantor (50 meter). ' . 'Jarak terdekat: ' . $locationCheck['distance'] . ' meter dari ' . ($locationCheck['nearest'] ?? 'lokasi terdekat'),
                    'distance' => $locationCheck['distance'],
                    'nearest_location' => $locationCheck['nearest'],
                    'code' => 'INVALID_LOCATION',
                ],
                403,
            );
        }

        $absensi = Absensi::where('karyawan_id', $user->id)->whereDate('tanggal', $today)->first();

        if ($absensi && $absensi->check_in) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-in hari ini!',
                    'code' => 'ALREADY_CHECKIN',
                ],
                400,
            );
        }

        $absensi = Absensi::updateOrCreate(
            [
                'karyawan_id' => $user->id,
                'tanggal' => $today,
            ],
            [
                'check_in' => $now,
                'kantor_cabang' => $locationCheck['location_name'] ?? $locationCheck['nearest'],
                'status' => 'Hadir',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_accuracy' => $request->accuracy,
                'is_valid_location' => $locationCheck['valid'],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'is_suspicious' => $suspicion['is_suspicious'],
                'suspicious_reason' => $this->formatSuspiciousReason($suspicion),
            ],
        );

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil!',
            'data' => [
                'waktu' => $now->format('H:i:s'),
                'tanggal' => $now->format('d-m-Y'),
                'kantor' => $locationCheck['location_name'] ?? $locationCheck['nearest'],
                'status' => 'Hadir',
                'distance' => $locationCheck['distance'],
                'location' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ],
                'server_timestamp_ms' => $now->getTimestampMs(),
            ],
        ]);
    }

    /**
     * Check-out
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0.1|max:5000',
            'client_flags' => 'nullable|array',
            'client_flags.*' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);
        $now = Carbon::now($this->officeTimezone);

        // ==========================================================
        // ANTI FAKE GPS: cek sinyal kecurangan SEBELUM validasi lokasi
        // ==========================================================
        $suspicion = $this->checkFakeGpsAttempt($user->id, (float) $request->latitude, (float) $request->longitude, (float) $request->accuracy, (array) $request->input('client_flags', []), $request);

        if ($suspicion['is_high_confidence']) {
            return $this->rejectFakeGps($suspicion);
        }

        $locationCheck = $this->isValidLocation((float) $request->latitude, (float) $request->longitude, $this->maxRadius, (float) $request->accuracy);

        if (!$locationCheck['valid']) {
            if (!$locationCheck['accuracy_ok']) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Absensi ditolak! Sinyal GPS Anda kurang akurat (± ' . $request->accuracy . ' meter). Coba pindah ke area terbuka lalu ulangi.',
                        'distance' => $locationCheck['distance'],
                        'nearest_location' => $locationCheck['nearest'],
                        'code' => 'POOR_GPS_ACCURACY',
                    ],
                    403,
                );
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Absensi ditolak! Anda berada di luar radius kantor (50 meter). ' . 'Jarak terdekat: ' . $locationCheck['distance'] . ' meter dari ' . ($locationCheck['nearest'] ?? 'lokasi terdekat'),
                    'distance' => $locationCheck['distance'],
                    'nearest_location' => $locationCheck['nearest'],
                    'code' => 'INVALID_LOCATION',
                ],
                403,
            );
        }

        $absensi = Absensi::where('karyawan_id', $user->id)->whereDate('tanggal', $today)->first();

        if (!$absensi || !$absensi->check_in) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Anda belum melakukan check-in!',
                    'code' => 'NO_CHECKIN',
                ],
                400,
            );
        }

        if ($absensi->check_out) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-out hari ini!',
                    'code' => 'ALREADY_CHECKOUT',
                ],
                400,
            );
        }

        $checkInTime = Carbon::parse($absensi->check_in);
        $totalJamKerja = max(0, (int) round($checkInTime->diffInMinutes($now) / 60));

        $absensi->total_jam_kerja = $totalJamKerja;
        $absensi->check_out = $now;
        $absensi->latitude = $request->latitude;
        $absensi->longitude = $request->longitude;
        $absensi->location_accuracy = $request->accuracy;
        $absensi->is_valid_location = $locationCheck['valid'];
        $absensi->ip_address = $request->ip();
        $absensi->user_agent = substr((string) $request->userAgent(), 0, 255);

        // Kalau check-out terindikasi mencurigakan, tandai (tanpa menghapus tanda
        // mencurigakan yang mungkin sudah ada dari saat check-in)
        if ($suspicion['is_suspicious']) {
            $absensi->is_suspicious = true;
            $existingReason = $absensi->suspicious_reason;
            $newReason = $this->formatSuspiciousReason($suspicion);
            $absensi->suspicious_reason = $existingReason && !str_contains($existingReason, $newReason) ? $existingReason . '; ' . $newReason : $existingReason ?? $newReason;
        }

        $absensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil!',
            'data' => [
                'waktu' => $now->format('H:i:s'),
                'tanggal' => $now->format('d-m-Y'),
                'total_jam' => $totalJamKerja,
                'kantor' => $absensi->kantor_cabang,
                'distance' => $locationCheck['distance'],
                'location' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ],
                'server_timestamp_ms' => $now->getTimestampMs(),
            ],
        ]);
    }

    /**
     * Cek status absensi
     */
    public function status()
    {
        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);
        $now = Carbon::now($this->officeTimezone);

        $absensi = Absensi::where('karyawan_id', $user->id)->whereDate('tanggal', $today)->first();

        $todayName = $now->locale('id')->isoFormat('dddd');

        return response()->json([
            'success' => true,
            'data' => [
                'check_in' => $absensi && $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i:s') : null,
                'check_out' => $absensi && $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i:s') : null,
                'status' => $absensi ? $absensi->status : 'Belum Absen',
                'kantor' => $absensi ? $absensi->kantor_cabang : null,
                'total_jam' => $absensi ? $absensi->total_jam_kerja : 0,
                'tanggal' => $today->format('d-m-Y'),
                'hari' => $todayName,
                'is_valid_location' => $absensi ? $absensi->is_valid_location : false,
                'latitude' => $absensi ? $absensi->latitude : null,
                'longitude' => $absensi ? $absensi->longitude : null,
                'server_timestamp_ms' => $now->getTimestampMs(),
                'server_time_iso' => $now->toIso8601String(),
                'office_locations' => $this->getOfficeLocations(),
                'max_radius' => $this->maxRadius,
                'max_gps_accuracy' => Absensi::MAX_GPS_ACCURACY,
            ],
        ]);
    }

    /**
     * Server time
     */
    public function serverTime()
    {
        $now = Carbon::now($this->officeTimezone);

        return response()->json([
            'success' => true,
            'timestamp_ms' => $now->getTimestampMs(),
            'iso' => $now->toIso8601String(),
            'tanggal' => $now->format('d-m-Y'),
            'jam' => $now->format('H:i:s'),
            'hari' => $now->locale('id')->isoFormat('dddd'),
        ]);
    }

    // ==========================================================
    // HR Methods
    // ==========================================================

    /**
     * Terapkan filter tanggal/bulan-tahun/karyawan/status secara konsisten.
     * Dipakai bareng oleh index(), getChartData(), dan exportExcel() supaya
     * hasil filter di halaman, di chart, dan di export SELALU sinkron.
     *
     * Prioritas filter tanggal: kalau start_date & end_date diisi, itu yang
     * dipakai (bulan/tahun diabaikan). Kalau tidak, baru pakai bulan/tahun.
     */
    private function applyAbsensiFilters($query, Request $request)
    {
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)->whereYear('tanggal', $request->year);
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Kalau HR belum memilih filter apapun, defaultkan ke bulan berjalan
     * supaya halaman tidak tampil kosong saat pertama dibuka. HR tetap bebas
     * mengganti/reset filter seperti biasa (reset akan kembali ke bulan ini juga).
     */
    private function withDefaultMonthFilter(Request $request): Request
    {
        $hasExplicitFilter = $request->filled('start_date')
            || $request->filled('end_date')
            || $request->filled('month')
            || $request->filled('year')
            || $request->filled('karyawan_id')
            || ($request->filled('status') && $request->status !== 'semua');

        if (!$hasExplicitFilter) {
            $request->merge([
                'month' => Carbon::now($this->officeTimezone)->month,
                'year' => Carbon::now($this->officeTimezone)->year,
            ]);
        }

        return $request;
    }

    public function index(Request $request)
    {
        $request = $this->withDefaultMonthFilter($request);

        $query = $this->applyAbsensiFilters(Absensi::with('karyawan'), $request);

        /*
    |--------------------------------------------------------------------------
    | Pagination (10 data per halaman, dengan tombol Previous/Next)
    |--------------------------------------------------------------------------
    */

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $allMatching = $query->orderBy('tanggal', 'desc')->get();
        $displayRows = Absensi::mergeConsecutivePerjalananDinas($allMatching);
        $total = $displayRows->count();

        $results = $displayRows->slice(($page - 1) * $perPage, $perPage)->values();

        $absensis = new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        $chartData = $this->getChartData($request);

        $karyawans = Karyawan::orderBy('nama_lengkap')->get();

        // Supaya form filter bulan/tahun tetap menampilkan pilihan yang sedang
        // aktif (termasuk saat default bulan-berjalan diterapkan otomatis).
        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');

        return view('hr.absensi.index', compact('absensis', 'karyawans', 'chartData', 'selectedMonth', 'selectedYear'));
    }

    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        $todayAbsensi = Absensi::where('karyawan_id', $user->id)->whereDate('tanggal', $today)->first();

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today($this->officeTimezone)->subDays($i);
            $absensi = Absensi::where('karyawan_id', $user->id)->whereDate('tanggal', $date)->first();

            $distance = null;
            if ($absensi && $absensi->latitude && $absensi->longitude) {
                $locations = $this->getOfficeLocations();
                $minDist = PHP_FLOAT_MAX;
                foreach ($locations as $coords) {
                    $d = $this->haversineDistance($absensi->latitude, $absensi->longitude, $coords['latitude'], $coords['longitude']);
                    if ($d < $minDist) {
                        $minDist = $d;
                    }
                }
                $distance = $minDist < PHP_FLOAT_MAX ? round($minDist, 1) : null;
            }

            $last7Days[] = [
                'tanggal' => $date->format('d/m'),
                'check_in' => $absensi && $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i') : '-',
                'check_out' => $absensi && $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i') : '-',
                'status' => $absensi ? $absensi->status : 'Alpha',
                'total_jam' => $absensi ? $absensi->total_jam_kerja : 0,
                'is_valid' => $absensi ? $absensi->is_valid_location : false,
                'distance' => $distance,
            ];
        }

        $officeLocations = $this->getOfficeLocations();

        return view('karyawan.absensi', compact('todayAbsensi', 'last7Days', 'officeLocations'));
    }

    public function exportExcel(Request $request)
    {
        $query = $this->applyAbsensiFilters(Absensi::with('karyawan'), $request);

        $absensis = $query->orderBy('tanggal', 'asc')->get();

        return $this->generateExcel($absensis);
    }

    /**
     * Generate laporan absensi dalam bentuk CSV, DIKELOMPOKKAN PER KARYAWAN.
     * Tiap karyawan mendapat blok tersendiri berisi:
     *   - Data harian: tanggal, hari, check-in, check-out, status, terlambat
     *     (menit), total jam kerja (menit), lembur (menit), keterangan.
     *   - Ringkasan per karyawan: jumlah hari kerja, total jam kerja, total
     *     lembur, total keterlambatan, rekap status, dan jumlah masuk di hari
     *     Minggu (hari libur kantor).
     * Di akhir file ditambahkan ringkasan total untuk seluruh karyawan.
     */
    private function generateExcel($absensis)
    {
        $fileName = 'laporan_absensi_' . Carbon::now($this->officeTimezone)->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($absensis) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xef) . chr(0xbb) . chr(0xbf));

            fputcsv($file, ['LAPORAN ABSENSI PER KARYAWAN']);
            fputcsv($file, ['Dicetak', Carbon::now($this->officeTimezone)->translatedFormat('d F Y H:i')]);
            fputcsv($file, []);

            // Kelompokkan per karyawan (bukan tercampur semua orang jadi satu tabel),
            // lalu urutkan kelompok berdasarkan nama karyawan supaya rapi dibaca.
            $groups = $absensis->groupBy('karyawan_id')->sortBy(function ($items) {
                return $items->first()->karyawan->nama_lengkap ?? '';
            });

            $grandTotal = [
                'hari_kerja' => 0,
                'menit_kerja' => 0,
                'menit_lembur' => 0,
                'menit_terlambat' => 0,
                'jumlah_karyawan' => $groups->count(),
            ];

            foreach ($groups as $items) {
                $items = $items->sortBy(fn ($item) => $item->tanggal->timestamp)->values();
                $karyawan = $items->first()->karyawan;

                fputcsv($file, ['KARYAWAN', $karyawan->nama_lengkap ?? '-']);
                fputcsv($file, ['Kode Pegawai', $karyawan->kode_pegawai ?? '-']);
                fputcsv($file, ['No', 'Tanggal', 'Hari', 'Check In', 'Check Out', 'Status', 'Terlambat (menit)', 'Total Jam Kerja (menit)', 'Lembur (menit)', 'Keterangan']);

                $summary = [
                    'hari_kerja' => 0,
                    'menit_kerja' => 0,
                    'menit_lembur' => 0,
                    'menit_terlambat' => 0,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'alpha' => 0,
                    'dinas' => 0,
                    'masuk_hari_minggu' => 0,
                ];

                $no = 1;
                foreach ($items as $absen) {
                    $totalMenit = $absen->total_menit_kerja;
                    $terlambat = $absen->terlambat_menit;
                    $lembur = $absen->lembur_menit;

                    // Tetap tampilkan jam check-in/check-out apa adanya untuk
                    // SEMUA hari, termasuk kalau karyawan masuk di hari Minggu
                    // atau Senin -- bukan cuma hari kerja "biasa".
                    fputcsv($file, [
                        $no++,
                        $absen->tanggal->format('d-m-Y'),
                        $absen->hari,
                        $absen->check_in ? $absen->check_in->format('H:i') : '-',
                        $absen->check_out ? $absen->check_out->format('H:i') : '-',
                        $absen->status,
                        $terlambat,
                        $totalMenit,
                        $lembur,
                        $absen->keterangan ?? '-',
                    ]);

                    $summary['menit_kerja'] += $totalMenit;
                    $summary['menit_lembur'] += $lembur;
                    $summary['menit_terlambat'] += $terlambat;

                    switch ($absen->status) {
                        case 'Hadir':
                            $summary['hadir']++;
                            $summary['hari_kerja']++;
                            break;
                        case 'Izin':
                            $summary['izin']++;
                            break;
                        case 'Sakit':
                            $summary['sakit']++;
                            break;
                        case 'Alpha':
                            $summary['alpha']++;
                            break;
                        case 'Perjalanan Dinas':
                            $summary['dinas']++;
                            $summary['hari_kerja']++;
                            break;
                    }

                    if ($absen->is_hari_minggu && $absen->check_in) {
                        $summary['masuk_hari_minggu']++;
                    }
                }

                fputcsv($file, []);
                fputcsv($file, ['RINGKASAN', $karyawan->nama_lengkap ?? '-']);
                fputcsv($file, ['Jumlah Hari Kerja (Hadir + Perjalanan Dinas)', $summary['hari_kerja']]);
                fputcsv($file, ['Total Jam Kerja (menit)', $summary['menit_kerja']]);
                fputcsv($file, ['Total Jam Kerja (jam:menit)', intdiv($summary['menit_kerja'], 60) . ' jam ' . str_pad($summary['menit_kerja'] % 60, 2, '0', STR_PAD_LEFT) . ' menit']);
                fputcsv($file, ['Total Lembur (menit)', $summary['menit_lembur']]);
                fputcsv($file, ['Total Keterlambatan (menit)', $summary['menit_terlambat']]);
                fputcsv($file, ['Jumlah Hadir', $summary['hadir']]);
                fputcsv($file, ['Jumlah Izin', $summary['izin']]);
                fputcsv($file, ['Jumlah Sakit', $summary['sakit']]);
                fputcsv($file, ['Jumlah Alpha', $summary['alpha']]);
                fputcsv($file, ['Jumlah Perjalanan Dinas', $summary['dinas']]);
                fputcsv($file, ['Jumlah Masuk di Hari Minggu (hari libur)', $summary['masuk_hari_minggu']]);
                fputcsv($file, []);
                fputcsv($file, []);

                $grandTotal['hari_kerja'] += $summary['hari_kerja'];
                $grandTotal['menit_kerja'] += $summary['menit_kerja'];
                $grandTotal['menit_lembur'] += $summary['menit_lembur'];
                $grandTotal['menit_terlambat'] += $summary['menit_terlambat'];
            }

            fputcsv($file, ['RINGKASAN TOTAL SEMUA KARYAWAN']);
            fputcsv($file, ['Jumlah Karyawan', $grandTotal['jumlah_karyawan']]);
            fputcsv($file, ['Total Hari Kerja', $grandTotal['hari_kerja']]);
            fputcsv($file, ['Total Jam Kerja (menit)', $grandTotal['menit_kerja']]);
            fputcsv($file, ['Total Lembur (menit)', $grandTotal['menit_lembur']]);
            fputcsv($file, ['Total Keterlambatan (menit)', $grandTotal['menit_terlambat']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getChartData($request)
    {
        $query = $this->applyAbsensiFilters(Absensi::with('karyawan'), $request);

        $absensis = $query->get();

        return [
            'hadir' => $absensis->where('status', 'Hadir')->count(),
            'izin' => $absensis->where('status', 'Izin')->count(),
            'sakit' => $absensis->where('status', 'Sakit')->count(),
            'alpha' => $absensis->where('status', 'Alpha')->count(),
            'perjalanan_dinas' => $absensis->where('status', 'Perjalanan Dinas')->count(),
            'total' => $absensis->count(),
            'valid_location' => $absensis->where('is_valid_location', true)->count(),
            'invalid_location' => $absensis->where('is_valid_location', false)->count(),
        ];
    }

    public function detail($id)
    {
        $absensi = Absensi::with('karyawan')->findOrFail($id);

        $distances = [];
        if ($absensi->latitude && $absensi->longitude) {
            foreach ($this->getOfficeLocations() as $name => $coords) {
                $distances[$name] = $this->haversineDistance($absensi->latitude, $absensi->longitude, $coords['latitude'], $coords['longitude']);
            }
        }

        // Kalau statusnya "Perjalanan Dinas", cari data perjalanan dinas terkait
        // supaya HR bisa lihat konteks periode & surat tugasnya.
        $perjalananDinas = null;
        if ($absensi->status === 'Perjalanan Dinas') {
            $perjalananDinas = \App\Models\PerjalananDinas::where('karyawan_id', $absensi->karyawan_id)->whereDate('tanggal_mulai', '<=', $absensi->tanggal)->whereDate('tanggal_selesai', '>=', $absensi->tanggal)->latest('id')->first();
        }

        // Ambil previous dan next ID
        $prevNext = $this->getPrevNextIds($id);

        return view('hr.absensi.detail', compact('absensi', 'distances', 'perjalananDinas', 'prevNext'));
    }

    private function getPrevNextIds($currentId)
    {
        $ids = Absensi::orderBy('tanggal', 'desc')->pluck('id')->toArray();
        $index = array_search($currentId, $ids);
        return [
            'prev' => $index > 0 ? $ids[$index - 1] : null,
            'next' => $index < count($ids) - 1 ? $ids[$index + 1] : null,
        ];
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Hadir,Izin,Sakit,Alpha,Perjalanan Dinas',
            'keterangan' => 'nullable|string',
        ]);

        $absensi = Absensi::findOrFail($id);
        $absensi->status = $request->status;
        $absensi->keterangan = $request->keterangan;
        $absensi->save();

        return redirect()->route('hr.absensi.index')->with('success', 'Status absensi berhasil diupdate');
    }

    /**
     * ==========================================================
     * VERIFIKASI ABSEN MANUAL OLEH HR
     * ==========================================================
     * Dipakai kalau karyawan lupa/tidak bisa check-in atau check-out sendiri.
     * Lokasi TIDAK diinput manual -- diambil otomatis dari lokasi GPS HR saat
     * menekan tombol verifikasi, lalu dicocokkan ke kantor terdekat, supaya
     * kolom Lokasi tetap konsisten dengan data absensi karyawan biasa.
     */

    /**
     * Verifikasi check-in manual oleh HR.
     */
    public function manualCheckIn(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $absensi = Absensi::findOrFail($id);

        if ($absensi->check_in) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Karyawan ini sudah punya data check-in.',
                ],
                400,
            );
        }

        $now = Carbon::now($this->officeTimezone);

        // Cocokkan lokasi HR saat ini ke kantor terdekat (tanpa batas radius/akurasi,
        // karena ini verifikasi manual oleh HR, bukan absensi mandiri karyawan).
        $locationCheck = $this->isValidLocation((float) $request->latitude, (float) $request->longitude, $this->maxRadius, null);
        $hrName = Auth::user()->nama_lengkap ?? 'HR';

        $absensi->check_in = $now;
        $absensi->kantor_cabang = $locationCheck['location_name'] ?? $locationCheck['nearest'];
        $absensi->latitude = $request->latitude;
        $absensi->longitude = $request->longitude;
        $absensi->is_valid_location = true;

        if (!$absensi->status || $absensi->status === 'Alpha') {
            $absensi->status = 'Hadir';
        }

        $catatanVerifikasi = 'Check-in diverifikasi manual oleh HR (' . $hrName . ') pada ' . $now->format('d-m-Y H:i');
        $absensi->keterangan = $absensi->keterangan ? $absensi->keterangan . ' | ' . $catatanVerifikasi : $catatanVerifikasi;

        $absensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil diverifikasi secara manual.',
            'data' => [
                'check_in' => $now->format('H:i'),
                'kantor_cabang' => $absensi->kantor_cabang,
            ],
        ]);
    }

    /**
     * Verifikasi check-out manual oleh HR.
     */
    public function manualCheckOut(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $absensi = Absensi::findOrFail($id);

        if (!$absensi->check_in) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Karyawan ini belum check-in, tidak bisa diverifikasi check-out.',
                ],
                400,
            );
        }

        if ($absensi->check_out) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Karyawan ini sudah punya data check-out.',
                ],
                400,
            );
        }

        $now = Carbon::now($this->officeTimezone);

        $locationCheck = $this->isValidLocation((float) $request->latitude, (float) $request->longitude, $this->maxRadius, null);
        $hrName = Auth::user()->nama_lengkap ?? 'HR';

        $checkInTime = Carbon::parse($absensi->check_in);
        $totalJamKerja = max(0, (int) round($checkInTime->diffInMinutes($now) / 60));

        $absensi->check_out = $now;
        $absensi->total_jam_kerja = $totalJamKerja;

        // Kalau kolom lokasi belum kebisi (mis. checkin lama tanpa lokasi), lengkapi
        // dari lokasi HR sekarang. Kalau sudah ada (dari checkin karyawan/HR), biarkan.
        if (!$absensi->kantor_cabang) {
            $absensi->kantor_cabang = $locationCheck['location_name'] ?? $locationCheck['nearest'];
        }

        $catatanVerifikasi = 'Check-out diverifikasi manual oleh HR (' . $hrName . ') pada ' . $now->format('d-m-Y H:i');
        $absensi->keterangan = $absensi->keterangan ? $absensi->keterangan . ' | ' . $catatanVerifikasi : $catatanVerifikasi;

        $absensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil diverifikasi secara manual.',
            'data' => [
                'check_out' => $now->format('H:i'),
                'total_jam_kerja' => $totalJamKerja,
            ],
        ]);
    }
}
