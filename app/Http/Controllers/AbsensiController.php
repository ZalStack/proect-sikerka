<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
    private function isValidLocation($latitude, $longitude, $radius = 50, $accuracy = null, $karyawanId = null): array
    {
        return Absensi::isValidLocation($latitude, $longitude, $radius, $accuracy, $karyawanId);
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

        $locationCheck = $this->isValidLocation((float) $request->latitude, (float) $request->longitude, $this->maxRadius, (float) $request->accuracy, $user->id);

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

        $locationCheck = $this->isValidLocation((float) $request->latitude, (float) $request->longitude, $this->maxRadius, (float) $request->accuracy, $user->id);

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
        $hasExplicitFilter = $request->filled('start_date') || $request->filled('end_date') || $request->filled('month') || $request->filled('year') || $request->filled('karyawan_id') || ($request->filled('status') && $request->status !== 'semua');

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

    /**
     * Get riwayat absensi karyawan dengan filter dan pagination
     * Untuk halaman dashboard karyawan
     */
    public function getRiwayat(Request $request)
    {
        $user = Auth::user();

        $query = Absensi::where('karyawan_id', $user->id);

        // Filter tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        // Filter status
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        // Pagination 7 data per halaman
        $perPage = 7;
        $riwayat = $query->orderBy('tanggal', 'desc')->paginate($perPage);

        // Format data untuk ditampilkan
        $riwayatData = $riwayat->map(function ($absensi) {
            $locations = $this->getOfficeLocations();
            $minDist = null;
            if ($absensi->latitude && $absensi->longitude) {
                $minDist = PHP_FLOAT_MAX;
                foreach ($locations as $coords) {
                    $d = $this->haversineDistance($absensi->latitude, $absensi->longitude, $coords['latitude'], $coords['longitude']);
                    if ($d < $minDist) {
                        $minDist = $d;
                    }
                }
                $minDist = $minDist < PHP_FLOAT_MAX ? round($minDist, 1) : null;
            }

            return [
                'id' => $absensi->id,
                'tanggal' => $absensi->tanggal->format('d/m/Y'),
                'check_in' => $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i') : '-',
                'check_out' => $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i') : '-',
                'status' => $absensi->status ?? 'Alpha',
                'total_jam' => $absensi->total_jam_kerja ?? 0,
                'is_valid' => $absensi->is_valid_location ?? false,
                'distance' => $minDist,
                'kantor' => $absensi->kantor_cabang ?? '-',
                'terlambat_menit' => $absensi->terlambat_menit ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $riwayatData,
            'pagination' => [
                'total' => $riwayat->total(),
                'per_page' => $riwayat->perPage(),
                'current_page' => $riwayat->currentPage(),
                'last_page' => $riwayat->lastPage(),
                'from' => $riwayat->firstItem(),
                'to' => $riwayat->lastItem(),
                'has_previous' => $riwayat->previousPageUrl() !== null,
                'has_next' => $riwayat->nextPageUrl() !== null,
                'previous_page_url' => $riwayat->previousPageUrl(),
                'next_page_url' => $riwayat->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Dashboard karyawan - revisi dengan filter dan pagination
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        $todayAbsensi = Absensi::where('karyawan_id', $user->id)->whereDate('tanggal', $today)->first();

        // Ambil riwayat dengan filter dan pagination
        $query = Absensi::where('karyawan_id', $user->id);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $riwayat = $query->orderBy('tanggal', 'desc')->paginate(7);

        // Format riwayat
        $formattedRiwayat = $riwayat->map(function ($absensi) {
            $locations = $this->getOfficeLocations();
            $minDist = null;
            if ($absensi->latitude && $absensi->longitude) {
                $minDist = PHP_FLOAT_MAX;
                foreach ($locations as $coords) {
                    $d = $this->haversineDistance($absensi->latitude, $absensi->longitude, $coords['latitude'], $coords['longitude']);
                    if ($d < $minDist) {
                        $minDist = $d;
                    }
                }
                $minDist = $minDist < PHP_FLOAT_MAX ? round($minDist, 1) : null;
            }

            return [
                'id' => $absensi->id,
                'tanggal' => $absensi->tanggal->format('d/m/Y'),
                'tanggal_raw' => $absensi->tanggal,
                'check_in' => $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i') : '-',
                'check_out' => $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i') : '-',
                'status' => $absensi->status ?? 'Alpha',
                'total_jam' => $absensi->total_jam_kerja ?? 0,
                'is_valid' => $absensi->is_valid_location ?? false,
                'distance' => $minDist,
                'kantor' => $absensi->kantor_cabang ?? '-',
                'terlambat_menit' => $absensi->terlambat_menit ?? 0,
                'is_terlambat' => $absensi->is_terlambat ?? false,
            ];
        });

        $officeLocations = $this->getOfficeLocations();

        // Data untuk filter
        $statuses = ['semua', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Perjalanan Dinas', 'Cuti'];
        $selectedStatus = $request->input('status', 'semua');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return view('karyawan.absensi', compact('todayAbsensi', 'formattedRiwayat', 'riwayat', 'officeLocations', 'statuses', 'selectedStatus', 'startDate', 'endDate'));
    }

    public function exportExcel(Request $request)
    {
        $query = $this->applyAbsensiFilters(Absensi::with('karyawan'), $request);

        $absensis = $query->orderBy('tanggal', 'asc')->get();

        return $this->generateExcel($absensis);
    }

   /**
 * Generate laporan absensi dalam bentuk EXCEL (.xlsx) yang rapi & profesional,
 * DIKELOMPOKKAN PER KARYAWAN. Tiap karyawan mendapat blok tersendiri berisi:
 *   - Data harian: tanggal, hari, check-in, check-out, status, terlambat
 *     (menit), total jam kerja (menit), lembur (menit), keterangan.
 *   - Ringkasan per karyawan: jumlah hari kerja, total jam kerja, total
 *     lembur, total keterlambatan, rekap status, dan jumlah masuk di hari
 *     Minggu & Senin (hari libur).
 * Di akhir file ditambahkan ringkasan total untuk seluruh karyawan.
 *
 * SEMUA RINGKASAN MENGGUNAKAN RUMUS SUM/COUNT DARI BARIS DATA,
 * sehingga jika user mengedit data di Excel, ringkasan otomatis menyesuaikan.
 */
private function generateExcel($absensis)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Laporan Absensi');

    // ==========================================================
    // PALET WARNA
    // ==========================================================
    $colorPrimary = '1F4E78';
    $colorSecondary = '2E75B6';
    $colorHeader = '4472C4';
    $colorBandA = 'FFFFFF';
    $colorBandB = 'DCE6F1';
    $colorBorder = 'B7B7B7';
    $colorSummaryBg = 'F2F2F2';
    $colorGrandBg = 'BDD7EE';
    $colorHariLibur = 'FFCC00';

    $statusFill = [
        'Hadir' => 'C6EFCE',
        'Izin' => 'FFEB9C',
        'Sakit' => 'FCE4D6',
        'Alpha' => 'F8CBAD',
        'Perjalanan Dinas' => 'D9D2E9',
        'Cuti' => 'BDD7EE',
    ];
    $statusFont = [
        'Hadir' => '1E7B34',
        'Izin' => '9C6500',
        'Sakit' => 'C55A11',
        'Alpha' => 'C00000',
        'Perjalanan Dinas' => '5B2C87',
        'Cuti' => '1F4E78',
    ];

    // ==========================================================
    // KOLOM
    // ==========================================================
    $columns = ['No', 'Tanggal', 'Hari', 'Check In', 'Check Out', 'Status', 'Terlambat (menit)', 'Total Jam Kerja (menit)', 'Lembur (menit)', 'Keterangan'];
    $lastCol = 'J';
    $colWidths = ['A' => 5, 'B' => 13, 'C' => 12, 'D' => 12, 'E' => 12, 'F' => 17, 'G' => 16, 'H' => 18, 'I' => 16, 'J' => 34];

    foreach ($colWidths as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    $thinBorder = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]],
        ],
    ];

    $row = 1;

    // ==========================================================
    // JUDUL LAPORAN
    // ==========================================================
    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
    $sheet->setCellValue("A{$row}", 'LAPORAN ABSENSI KARYAWAN');
    $sheet->getStyle("A{$row}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorPrimary]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension($row)->setRowHeight(32);
    $row++;

    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
    $sheet->setCellValue("A{$row}", 'Dicetak pada ' . Carbon::now($this->officeTimezone)->translatedFormat('d F Y, H:i') . ' WIB');
    $sheet->getStyle("A{$row}")->applyFromArray([
        'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorPrimary]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getRowDimension($row)->setRowHeight(18);
    $row += 2;

    // Kelompokkan per karyawan
    $groups = $absensis->groupBy('karyawan_id')->sortBy(function ($items) {
        return $items->first()->karyawan->nama_lengkap ?? '';
    });

    $grandTotalStartRow = null;
    $grandTotalEndRow = null;

    foreach ($groups as $items) {
        $items = $items->sortBy(fn($item) => $item->tanggal->timestamp)->values();
        $karyawan = $items->first()->karyawan;

        // Simpan posisi baris data untuk ringkasan
        $dataStartRow = $row + 1;

        // ==========================================================
        // NAMA & KODE KARYAWAN
        // ==========================================================
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", strtoupper($karyawan->nama_lengkap ?? '-') . '   |   Kode Pegawai: ' . ($karyawan->kode_pegawai ?? '-'));
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSecondary]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(24);
        $row++;

        // ==========================================================
        // HEADER TABEL
        // ==========================================================
        foreach ($columns as $i => $colName) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}{$row}", $colName);
        }
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        // ==========================================================
        // DATA BARIS DENGAN RUMUS EXCEL
        // ==========================================================
        $no = 1;
        $dataRowStart = $row;

        foreach ($items as $absen) {
            $colD = "D{$row}";
            $colE = "E{$row}";
            $colG = "G{$row}";
            $colH = "H{$row}";
            $colI = "I{$row}";

            // ==========================================================
            // DATA MENTAH (hardcoded values)
            // ==========================================================
            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $absen->tanggal->format('d-m-Y'));
            $sheet->setCellValue("C{$row}", $absen->hari);

            // D: Check In (format HH:MM)
            $checkIn = $absen->check_in ? $absen->check_in->format('H:i') : '';
            $sheet->setCellValue($colD, $checkIn);

            // E: Check Out (format HH:MM)
            $checkOut = $absen->check_out ? $absen->check_out->format('H:i') : '';
            $sheet->setCellValue($colE, $checkOut);

            // F: Status
            $sheet->setCellValue("F{$row}", $absen->status);

            // J: Keterangan
            $sheet->setCellValue("J{$row}", $absen->keterangan ?? '-');

            // ==========================================================
            // RUMUS EXCEL - PERHITUNGAN DALAM MENIT
            // ==========================================================

            /**
             * KONSTANTA WAKTU (dalam menit dari jam 00:00)
             * - 07:45 = 7*60 + 45 = 465 menit
             * - 16:00 = 16*60 = 960 menit
             */
            $startMinute = 465; // 07:45
            $endMinute = 960; // 16:00

            /**
             * CEK APAKAH HARI MINGGU ATAU SENIN (hari libur)
             * Hari Minggu = 7, Senin = 1 (menggunakan dayOfWeekIso)
             * 
             * Untuk hari Minggu & Senin, semua jam kerja dianggap LEMBUR
             * karena masuk di hari libur
             */
            $isHariLibur = in_array($absen->hari, ['Minggu', 'Senin']);

            // ==========================================================
            // RUMUS 1: TERLAMBAT (Kolom G) - dalam MENIT
            // ==========================================================
            /**
             * Logika:
             * - Untuk hari libur (Minggu/Senin): tidak ada terlambat
             * - Untuk hari biasa: check-in > 07:45 dihitung selisih menit
             */
            if ($isHariLibur) {
                // Hari libur: tidak ada keterlambatan
                $formulaTerlambat = "=0";
            } else {
                $formulaTerlambat = "=IF(AND({$colD}<>\"\", (HOUR({$colD})*60+MINUTE({$colD}))>{$startMinute}), (HOUR({$colD})*60+MINUTE({$colD}))-{$startMinute}, 0)";
            }
            $sheet->setCellValue($colG, $formulaTerlambat);

            // ==========================================================
            // RUMUS 2: TOTAL JAM KERJA (Kolom H) - dalam MENIT
            // ==========================================================
            $formulaTotalJam = "=IF(AND({$colD}<>\"\", {$colE}<>\"\"), (HOUR({$colE})*60+MINUTE({$colE})) - (HOUR({$colD})*60+MINUTE({$colD})), 0)";
            $sheet->setCellValue($colH, $formulaTotalJam);

            // ==========================================================
            // RUMUS 3: LEMBUR (Kolom I) - dalam MENIT
            // ==========================================================
            /**
             * Logika:
             * 1. Jika hari Minggu atau Senin (hari libur):
             *    - SEMUA jam kerja dihitung sebagai lembur
             *    - Rumus: Total Jam Kerja (col H)
             * 
             * 2. Jika hari biasa (Selasa - Sabtu):
             *    - Check-out > 16:00 dihitung selisih menit
             */
            if ($isHariLibur) {
                // Hari libur: semua jam kerja = lembur
                $formulaLembur = "=IF({$colH}>0, {$colH}, 0)";
            } else {
                // Hari biasa: lembur hanya jika check-out > 16:00
                $formulaLembur = "=IF(AND({$colE}<>\"\", (HOUR({$colE})*60+MINUTE({$colE}))>{$endMinute}), (HOUR({$colE})*60+MINUTE({$colE}))-{$endMinute}, 0)";
            }
            $sheet->setCellValue($colI, $formulaLembur);

            // ==========================================================
            // STYLING
            // ==========================================================
            $bandColor = $no % 2 === 0 ? $colorBandB : $colorBandA;
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bandColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'font' => ['size' => 10],
            ]);

            // Alignment center untuk kolom tertentu
            $sheet->getStyle("A{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Format angka untuk kolom G, H, I (tanpa desimal)
            $sheet->getStyle("G{$row}:I{$row}")->getNumberFormat()->setFormatCode('0');

            // Tandai hari Minggu & Senin (hari libur) dengan warna khusus
            if ($isHariLibur) {
                $sheet->getStyle("C{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'C00000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorHariLibur]],
                ]);
            }

            // Warna badge status
            $fillColor = $statusFill[$absen->status] ?? 'FFFFFF';
            $fontColor = $statusFont[$absen->status] ?? '000000';
            $sheet->getStyle("F{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $fontColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $no++;
            $row++;
        }

        $dataRowEnd = $row - 1;

        // ==========================================================
        // BARIS TOTAL PER KARYAWAN (MENGGUNAKAN RUMUS SUM)
        // ==========================================================
        if ($dataRowStart <= $dataRowEnd) {
            $sheet->setCellValue("A{$row}", '');
            $sheet->setCellValue("B{$row}", '');
            $sheet->setCellValue("C{$row}", '');
            $sheet->setCellValue("D{$row}", '');
            $sheet->setCellValue("E{$row}", '');
            $sheet->setCellValue("F{$row}", 'TOTAL');

            // Rumus SUM untuk kolom G, H, I
            $sheet->setCellValue("G{$row}", "=SUM(G{$dataRowStart}:G{$dataRowEnd})");
            $sheet->setCellValue("H{$row}", "=SUM(H{$dataRowStart}:H{$dataRowEnd})");
            $sheet->setCellValue("I{$row}", "=SUM(I{$dataRowStart}:I{$dataRowEnd})");
            $sheet->setCellValue("J{$row}", '');

            // Style baris total
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6E6E6']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
            ]);
            $sheet->getStyle("A{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:I{$row}")->getNumberFormat()->setFormatCode('0');

            // Simpan posisi baris total untuk ringkasan
            $totalRow = $row;
            $row++;
        }

        $row++; // spasi

        // ==========================================================
        // RINGKASAN PER KARYAWAN (MENGGUNAKAN RUMUS DARI BARIS DATA)
        // ==========================================================
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'RINGKASAN — ' . strtoupper($karyawan->nama_lengkap ?? '-'));
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '808080']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // ==========================================================
        // RINGKASAN MENGGUNAKAN RUMUS DARI BARIS DATA
        // ==========================================================

        // 1. Jumlah Hari Kerja (menghitung baris dengan status Hadir atau Perjalanan Dinas)
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Jumlah Hari Kerja (Hadir + Perjalanan Dinas)');
        $sheet->setCellValue("G{$row}", "=COUNTIF(F{$dataRowStart}:F{$dataRowEnd},\"Hadir\")+COUNTIF(F{$dataRowStart}:F{$dataRowEnd},\"Perjalanan Dinas\")");
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSummaryBg]],
            'font' => ['size' => 10],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
        ]);
        $sheet->getStyle("G{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        // 2. Total Jam Kerja (menit) - dari kolom H
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Total Jam Kerja (menit)');
        $sheet->setCellValue("G{$row}", "=SUM(H{$dataRowStart}:H{$dataRowEnd})");
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSummaryBg]],
            'font' => ['size' => 10],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
        ]);
        $sheet->getStyle("G{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        // Simpan row untuk referensi di rumus berikutnya
        $totalJamRow = $row;
        $row++;

        // 3. Total Jam Kerja (jam:menit) - konversi dari rumus
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Total Jam Kerja (jam:menit)');
        // Gunakan referensi ke baris sebelumnya dengan cara yang aman
        $sheet->setCellValue("G{$row}", "=INT(G{$totalJamRow}/60)&\" jam \"&TEXT(MOD(G{$totalJamRow},60),\"00\")&\" menit\"");
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSummaryBg]],
            'font' => ['size' => 10],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
        ]);
        $sheet->getStyle("G{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        // 4. Total Lembur (menit) - dari kolom I
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Total Lembur (menit)');
        $sheet->setCellValue("G{$row}", "=SUM(I{$dataRowStart}:I{$dataRowEnd})");
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSummaryBg]],
            'font' => ['size' => 10],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
        ]);
        $sheet->getStyle("G{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        // 5. Total Keterlambatan (menit) - dari kolom G
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Total Keterlambatan (menit)');
        $sheet->setCellValue("G{$row}", "=SUM(G{$dataRowStart}:G{$dataRowEnd})");
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSummaryBg]],
            'font' => ['size' => 10],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
        ]);
        $sheet->getStyle("G{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        // 6-11. Rekap Status (COUNTIF)
        $statuses = ['Hadir', 'Izin', 'Sakit', 'Alpha', 'Perjalanan Dinas', 'Cuti'];
        $statusLabels = ['Jumlah Hadir', 'Jumlah Izin', 'Jumlah Sakit', 'Jumlah Alpha', 'Jumlah Perjalanan Dinas', 'Jumlah Cuti'];

        foreach ($statuses as $idx => $status) {
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", $statusLabels[$idx]);
            $sheet->setCellValue("G{$row}", "=COUNTIF(F{$dataRowStart}:F{$dataRowEnd},\"{$status}\")");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSummaryBg]],
                'font' => ['size' => 10],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
            ]);
            $sheet->getStyle("G{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }

        // 12. Jumlah Masuk di Hari Minggu & Senin (hari libur)
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Jumlah Masuk di Hari Libur (Minggu & Senin)');
        $sheet->setCellValue("G{$row}", "=COUNTIF(C{$dataRowStart}:C{$dataRowEnd},\"Minggu\")+COUNTIF(C{$dataRowStart}:C{$dataRowEnd},\"Senin\")");
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSummaryBg]],
            'font' => ['size' => 10],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
        ]);
        $sheet->getStyle("G{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        $row += 2;

        // ==========================================================
        // SIMPAN POSISI DATA UNTUK GRAND TOTAL
        // ==========================================================
        if ($grandTotalStartRow === null) {
            $grandTotalStartRow = $dataRowStart;
        }
        $grandTotalEndRow = $dataRowEnd;
    }

    // ==========================================================
    // RINGKASAN TOTAL SELURUH KARYAWAN (RUMUS DARI SEMUA DATA)
    // ==========================================================
    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
    $sheet->setCellValue("A{$row}", 'RINGKASAN TOTAL SELURUH KARYAWAN');
    $sheet->getStyle("A{$row}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorPrimary]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension($row)->setRowHeight(26);
    $row++;

    // Grand Total menggunakan SUM dari semua baris data
    $grandPairs = [
        ['Jumlah Karyawan', "=SUMPRODUCT(1/COUNTIF(A{$grandTotalStartRow}:A{$grandTotalEndRow}, A{$grandTotalStartRow}:A{$grandTotalEndRow}))"],
        ['Total Hari Kerja', "=SUM(F{$grandTotalStartRow}:F{$grandTotalEndRow})"],
        ['Total Jam Kerja (menit)', "=SUM(H{$grandTotalStartRow}:H{$grandTotalEndRow})"],
        ['Total Lembur (menit)', "=SUM(I{$grandTotalStartRow}:I{$grandTotalEndRow})"],
        ['Total Keterlambatan (menit)', "=SUM(G{$grandTotalStartRow}:G{$grandTotalEndRow})"],
    ];

    foreach ($grandPairs as [$label, $formula]) {
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->setCellValue("G{$row}", $formula);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorGrandBg]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $colorPrimary]]],
        ]);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
    }

    $sheet->freezePane('A4');
    $sheet->setSelectedCell('A1');

    $fileName = 'Laporan_Absensi_' . Carbon::now($this->officeTimezone)->format('Ymd_His') . '.xlsx';
    $writer = new Xlsx($spreadsheet);

    return response()->streamDownload(
        function () use ($writer) {
            $writer->save('php://output');
        },
        $fileName,
        [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ],
    );
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
            'cuti' => $absensis->where('status', 'Cuti')->count(),
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

        // Kalau statusnya "Cuti", cari pengajuan cuti terkait (pending atau approved)
        // supaya HR bisa lihat konteks periode & keterangan pengajuannya.
        $cutiInfo = null;
        if ($absensi->status === 'Cuti') {
            $cutiInfo = \App\Models\Cuti::pengajuan()
                ->where('karyawan_id', $absensi->karyawan_id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('tanggal_mulai', '<=', $absensi->tanggal)
                ->whereDate('tanggal_selesai', '>=', $absensi->tanggal)
                ->latest('id')
                ->first();
        }

        // Ambil previous dan next ID
        $prevNext = $this->getPrevNextIds($id);

        return view('hr.absensi.detail', compact('absensi', 'distances', 'perjalananDinas', 'cutiInfo', 'prevNext'));
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
            'status' => 'required|in:Hadir,Izin,Sakit,Alpha,Perjalanan Dinas,Cuti',
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
        $locationCheck = $this->isValidLocation((float) $request->latitude, (float) $request->longitude, $this->maxRadius, null, $absensi->karyawan_id);
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

        $locationCheck = $this->isValidLocation((float) $request->latitude, (float) $request->longitude, $this->maxRadius, null, $absensi->karyawan_id);
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
