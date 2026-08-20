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

    /**
     * ==========================================================
     * PERIZINAN (KARYAWAN) — PENGAJUAN IZIN / SAKIT
     * ==========================================================
     * Karyawan mengajukan izin/sakit lewat tabel `perizinans` (status awal
     * selalu 'pending'). Pengajuan BELUM masuk ke rekap absensi sebelum
     * disetujui HRD. Begitu HRD approve, baris absensi (Izin/Sakit) dibuat
     * otomatis lewat Perizinan::syncToAbsensi() -- lihat method
     * perizinanApprove() di bawah -- sehingga langsung tercermin di rekap
     * absensi karyawan (halaman karyawan sendiri maupun halaman HR).
     */

    /**
     * Halaman riwayat + form pengajuan izin/sakit milik karyawan yang sedang login.
     */
    public function perizinan(Request $request)
    {
        $user = Auth::user();

        $query = \App\Models\Perizinan::where('karyawan_id', $user->id);

        if ($request->filled('jenis') && in_array($request->jenis, \App\Models\Perizinan::JENIS, true)) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status') && in_array($request->status, \App\Models\Perizinan::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_mulai', [$request->start_date, $request->end_date]);
        }

        $perizinan = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $selectedJenis = $request->input('jenis', 'semua');
        $selectedStatus = $request->input('status', 'semua');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return view('karyawan.perizinan', compact('perizinan', 'selectedJenis', 'selectedStatus', 'startDate', 'endDate'));
    }

    /**
     * [KARYAWAN] Ajukan izin/sakit baru. Status awal selalu 'pending'
     * (menunggu review HRD) -- TIDAK langsung tercatat di absensi.
     */
    public function perizinanStore(Request $request)
    {
        $request->validate(
            [
                'jenis' => 'required|in:Izin,Sakit',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'keterangan' => 'required|string|min:5|max:1000',
            ],
            [
                'jenis.required' => 'Silakan pilih jenis pengajuan (Izin/Sakit).',
                'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
                'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
                'keterangan.required' => 'Keterangan/alasan wajib diisi.',
                'keterangan.min' => 'Keterangan minimal 5 karakter, mohon dijelaskan lebih lengkap.',
            ],
        );

        $user = Auth::user();

        if (\App\Models\Perizinan::hasOverlap($user->id, $request->tanggal_mulai, $request->tanggal_selesai)) {
            return back()
                ->withInput()
                ->with('error', 'Anda sudah punya pengajuan izin/sakit (menunggu atau disetujui) yang tanggalnya bertabrakan dengan rentang ini.');
        }

        \App\Models\Perizinan::create([
            'karyawan_id' => $user->id,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => trim($request->keterangan),
            'status' => 'pending',
        ]);

        return redirect()
            ->route('karyawan.absensi.perizinan')
            ->with('success', 'Pengajuan ' . $request->jenis . ' berhasil dikirim dan menunggu persetujuan HRD.');
    }

    /**
     * [KARYAWAN] Batalkan pengajuan izin/sakit milik sendiri -- hanya boleh
     * selagi masih 'pending' (belum diproses HRD).
     */
    public function perizinanCancel($id)
    {
        $perizinan = \App\Models\Perizinan::findOrFail($id);

        if ($perizinan->karyawan_id !== Auth::id()) {
            return back()->with('error', 'Akses ditolak!');
        }

        if ($perizinan->status !== 'pending') {
            return back()->with('error', 'Pengajuan yang sudah diproses HRD tidak bisa dibatalkan.');
        }

        $perizinan->delete();

        return back()->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    /**
     * ==========================================================
     * PERIZINAN (HRD) — REVIEW & APPROVAL
     * ==========================================================
     */

    /**
     * [HR] Daftar seluruh pengajuan izin/sakit untuk direview, dengan filter status/jenis/karyawan.
     */
    public function perizinanIndex(Request $request)
    {
        $query = \App\Models\Perizinan::with(['karyawan', 'approver'])->orderBy('created_at', 'desc');

        $status = $request->input('status', 'pending');
        if (in_array($status, \App\Models\Perizinan::STATUSES, true)) {
            $query->where('status', $status);
        }

        if ($request->filled('jenis') && in_array($request->jenis, \App\Models\Perizinan::JENIS, true)) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_mulai', [$request->start_date, $request->end_date]);
        }

        $perizinan = $query->paginate(10)->withQueryString();

        $counts = [
            'pending' => \App\Models\Perizinan::where('status', 'pending')->count(),
            'approved' => \App\Models\Perizinan::where('status', 'approved')->count(),
            'rejected' => \App\Models\Perizinan::where('status', 'rejected')->count(),
        ];

        $karyawans = Karyawan::orderBy('nama_lengkap')->get();

        return view('hr.perizinan.index', compact('perizinan', 'counts', 'status', 'karyawans'));
    }

    /**
     * [HR] Setujui pengajuan -> otomatis tercatat sebagai Izin/Sakit di
     * tabel absensi karyawan yang bersangkutan untuk seluruh rentang tanggalnya.
     */
    public function perizinanApprove(Request $request, $id)
    {
        $request->validate([
            'catatan_hr' => 'nullable|string|max:1000',
        ]);

        $perizinan = \App\Models\Perizinan::with('karyawan')->findOrFail($id);

        if ($perizinan->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah pernah diproses sebelumnya.');
        }

        $perizinan->update([
            'status' => 'approved',
            'catatan_hr' => $request->catatan_hr,
            'approved_by' => Auth::id(),
            'approved_at' => now($this->officeTimezone),
        ]);

        // Terapkan ke tabel absensi karyawan (lihat Perizinan::syncToAbsensi())
        $perizinan->syncToAbsensi();

        return back()->with(
            'success',
            'Pengajuan ' . $perizinan->jenis . ' atas nama ' . ($perizinan->karyawan->nama_lengkap ?? 'karyawan') . ' berhasil disetujui dan tercatat di absensi.',
        );
    }

    /**
     * [HR] Tolak pengajuan -- WAJIB disertai catatan/alasan penolakan untuk karyawan.
     */
    public function perizinanReject(Request $request, $id)
    {
        $request->validate(
            [
                'catatan_hr' => 'required|string|min:3|max:1000',
            ],
            [
                'catatan_hr.required' => 'Mohon isi alasan penolakan supaya karyawan tahu penyebabnya.',
            ],
        );

        $perizinan = \App\Models\Perizinan::with('karyawan')->findOrFail($id);

        if ($perizinan->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah pernah diproses sebelumnya.');
        }

        $perizinan->update([
            'status' => 'rejected',
            'catatan_hr' => $request->catatan_hr,
            'approved_by' => Auth::id(),
            'approved_at' => now($this->officeTimezone),
        ]);

        return back()->with('success', 'Pengajuan ' . $perizinan->jenis . ' atas nama ' . ($perizinan->karyawan->nama_lengkap ?? 'karyawan') . ' berhasil ditolak.');
    }

    /**
     * [HR] Kembalikan pengajuan yang sudah diproses (approved/rejected) ke
     * status 'pending' lagi -- dipakai untuk membatalkan keputusan yang keliru.
     * Kalau sebelumnya 'approved', baris absensi yang sempat dibuat otomatis
     * juga akan dikembalikan (lihat Perizinan::revertAbsensi()).
     */
    public function perizinanReset($id)
    {
        $perizinan = \App\Models\Perizinan::findOrFail($id);

        if ($perizinan->status === 'approved') {
            $perizinan->revertAbsensi();
        }

        $perizinan->update([
            'status' => 'pending',
            'catatan_hr' => null,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', 'Status pengajuan dikembalikan ke "Menunggu Persetujuan".');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->applyAbsensiFilters(Absensi::with('karyawan'), $request);

        $absensis = $query->orderBy('tanggal', 'asc')->get();

        // ==========================================================
        // Ambil juga data Perjalanan Dinas & Cuti pada periode/karyawan
        // yang sama dengan filter absensi, supaya laporan Excel lengkap.
        // ==========================================================
        [$rangeStart, $rangeEnd] = $this->resolveExportDateRange($request);

        $perjalananDinasQuery = \App\Models\PerjalananDinas::with('karyawan');
        $cutiQuery = \App\Models\Cuti::pengajuan()->with('karyawan');

        if ($rangeStart && $rangeEnd) {
            $overlapFilter = function ($q) use ($rangeStart, $rangeEnd) {
                $q->whereBetween('tanggal_mulai', [$rangeStart, $rangeEnd])
                    ->orWhereBetween('tanggal_selesai', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($q2) use ($rangeStart, $rangeEnd) {
                        $q2->where('tanggal_mulai', '<=', $rangeStart)->where('tanggal_selesai', '>=', $rangeEnd);
                    });
            };
            $perjalananDinasQuery->where($overlapFilter);
            $cutiQuery->where($overlapFilter);
        }

        if ($request->filled('karyawan_id')) {
            $perjalananDinasQuery->where('karyawan_id', $request->karyawan_id);
            $cutiQuery->where('karyawan_id', $request->karyawan_id);
        }

        $perjalananDinasList = $perjalananDinasQuery->orderBy('tanggal_mulai', 'asc')->get();
        $cutiList = $cutiQuery->orderBy('tanggal_mulai', 'asc')->get();

        return $this->generateExcel($absensis, $perjalananDinasList, $cutiList);
    }

    /**
     * Tentukan rentang tanggal (start, end) dari filter request yang sama
     * dipakai untuk absensi, supaya sheet Perjalanan Dinas & Cuti konsisten
     * dengan periode yang sedang diexport.
     */
    private function resolveExportDateRange(Request $request): array
    {
        if ($request->filled('start_date') && $request->filled('end_date')) {
            return [Carbon::parse($request->start_date, $this->officeTimezone)->startOfDay(), Carbon::parse($request->end_date, $this->officeTimezone)->endOfDay()];
        }

        if ($request->filled('month') && $request->filled('year')) {
            $start = Carbon::create((int) $request->year, (int) $request->month, 1, 0, 0, 0, $this->officeTimezone)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            return [$start, $end];
        }

        return [null, null];
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
    private function generateExcel($absensis, $perjalananDinasList = null, $cutiList = null)
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
        $columns = ['No', 'Tanggal', 'Hari', 'Check In', 'Check Out', 'Status', 'Terlambat (menit)', 'Total Jam Kerja (menit)', 'Lembur (menit)', 'Pulang Lebih Awal (menit)', 'Keterangan'];
        $lastCol = 'K';
        $colWidths = ['A' => 5, 'B' => 13, 'C' => 12, 'D' => 12, 'E' => 12, 'F' => 17, 'G' => 16, 'H' => 18, 'I' => 16, 'J' => 20, 'K' => 34];

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
                $colJ = "J{$row}";

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

                // K: Keterangan
                $sheet->setCellValue("K{$row}", $absen->keterangan ?? '-');

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
                    $formulaTerlambat = '=0';
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
                // RUMUS 4: PULANG LEBIH AWAL (Kolom J) - dalam MENIT
                // ==========================================================
                /**
                 * Logika:
                 * - Untuk hari libur (Minggu/Senin): tidak dihitung pulang lebih awal
                 * - Untuk hari biasa: check-out < 16:00 dihitung selisih menit (16:00 - jam pulang)
                 */
                if ($isHariLibur) {
                    $formulaPulangAwal = '=0';
                } else {
                    $formulaPulangAwal = "=IF(AND({$colE}<>\"\", (HOUR({$colE})*60+MINUTE({$colE}))<{$endMinute}), {$endMinute}-(HOUR({$colE})*60+MINUTE({$colE})), 0)";
                }
                $sheet->setCellValue($colJ, $formulaPulangAwal);

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
                $sheet
                    ->getStyle("A{$row}:E{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet
                    ->getStyle("G{$row}:J{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format angka untuk kolom G, H, I, J (tanpa desimal)
                $sheet
                    ->getStyle("G{$row}:J{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('0');

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

                // Rumus SUM untuk kolom G, H, I, J
                $sheet->setCellValue("G{$row}", "=SUM(G{$dataRowStart}:G{$dataRowEnd})");
                $sheet->setCellValue("H{$row}", "=SUM(H{$dataRowStart}:H{$dataRowEnd})");
                $sheet->setCellValue("I{$row}", "=SUM(I{$dataRowStart}:I{$dataRowEnd})");
                $sheet->setCellValue("J{$row}", "=SUM(J{$dataRowStart}:J{$dataRowEnd})");
                $sheet->setCellValue("K{$row}", '');

                // Style baris total
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6E6E6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
                ]);
                $sheet
                    ->getStyle("A{$row}:F{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet
                    ->getStyle("G{$row}:J{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet
                    ->getStyle("G{$row}:J{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('0');

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

            // 3. Total Lembur (menit) - dari kolom I
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

            // 5b. Total Pulang Lebih Awal (menit) - dari kolom J
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->mergeCells("G{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", 'Total Pulang Lebih Awal (menit)');
            $sheet->setCellValue("G{$row}", "=SUM(J{$dataRowStart}:J{$dataRowEnd})");
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
        $grandPairs = [['Jumlah Karyawan', "=SUMPRODUCT(1/COUNTIF(A{$grandTotalStartRow}:A{$grandTotalEndRow}, A{$grandTotalStartRow}:A{$grandTotalEndRow}))"], ['Total Hari Kerja', "=SUM(F{$grandTotalStartRow}:F{$grandTotalEndRow})"], ['Total Jam Kerja (menit)', "=SUM(H{$grandTotalStartRow}:H{$grandTotalEndRow})"], ['Total Lembur (menit)', "=SUM(I{$grandTotalStartRow}:I{$grandTotalEndRow})"], ['Total Keterlambatan (menit)', "=SUM(G{$grandTotalStartRow}:G{$grandTotalEndRow})"], ['Total Pulang Lebih Awal (menit)', "=SUM(J{$grandTotalStartRow}:J{$grandTotalEndRow})"]];

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
            $sheet
                ->getStyle("G{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $sheet->freezePane('A4');
        $sheet->setSelectedCell('A1');

        // ==========================================================
        // SHEET TAMBAHAN: PERJALANAN DINAS & CUTI
        // ==========================================================
        $this->addPerjalananDinasSheet($spreadsheet, $perjalananDinasList ?? collect());
        $this->addCutiSheet($spreadsheet, $cutiList ?? collect());
        $spreadsheet->setActiveSheetIndex(0);

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

    /**
     * Tambahkan sheet "Perjalanan Dinas" berisi seluruh pengajuan perjalanan
     * dinas pada periode/filter yang sama dengan laporan absensi.
     */
    private function addPerjalananDinasSheet(Spreadsheet $spreadsheet, $items): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Perjalanan Dinas');

        $colorPrimary = '1F4E78';
        $colorHeader = '4472C4';
        $colorBandA = 'FFFFFF';
        $colorBandB = 'DCE6F1';
        $colorBorder = 'B7B7B7';

        $statusLabel = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];

        $columns = ['No', 'Nama Karyawan', 'Kode Pegawai', 'Judul', 'Agenda', 'Tanggal Mulai', 'Tanggal Selesai', 'Durasi (hari)', 'Status', 'Tanggal Pengajuan', 'Catatan HR'];
        $lastCol = 'K';
        $colWidths = ['A' => 5, 'B' => 24, 'C' => 14, 'D' => 26, 'E' => 30, 'F' => 13, 'G' => 13, 'H' => 12, 'I' => 13, 'J' => 15, 'K' => 32];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $row = 1;
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'LAPORAN PERJALANAN DINAS');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorPrimary]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(32);
        $row += 2;

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

        $no = 1;
        foreach ($items as $item) {
            $durasi = $item->tanggal_mulai && $item->tanggal_selesai ? $item->tanggal_mulai->diffInDays($item->tanggal_selesai) + 1 : 0;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $item->karyawan->nama_lengkap ?? '-');
            $sheet->setCellValue("C{$row}", $item->karyawan->kode_pegawai ?? '-');
            $sheet->setCellValue("D{$row}", $item->judul ?? '-');
            $sheet->setCellValue("E{$row}", $item->agenda ?? '-');
            $sheet->setCellValue("F{$row}", $item->tanggal_mulai ? $item->tanggal_mulai->format('d-m-Y') : '-');
            $sheet->setCellValue("G{$row}", $item->tanggal_selesai ? $item->tanggal_selesai->format('d-m-Y') : '-');
            $sheet->setCellValue("H{$row}", $durasi);
            $sheet->setCellValue("I{$row}", $statusLabel[$item->status] ?? $item->status);
            $sheet->setCellValue("J{$row}", $item->tanggal_pengajuan ? $item->tanggal_pengajuan->format('d-m-Y') : '-');
            $sheet->setCellValue("K{$row}", $item->catatan_hr ?? '-');

            $bandColor = $no % 2 === 0 ? $colorBandB : $colorBandA;
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bandColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'font' => ['size' => 10],
            ]);
            $sheet
                ->getStyle("A{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet
                ->getStyle("F{$row}:I{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $no++;
            $row++;
        }

        if ($no === 1) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada data perjalanan dinas pada periode ini.');
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '808080']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        $sheet->freezePane('A4');
        $sheet->setSelectedCell('A1');
    }

    /**
     * Tambahkan sheet "Cuti" berisi seluruh pengajuan cuti pada periode/filter
     * yang sama dengan laporan absensi.
     */
    private function addCutiSheet(Spreadsheet $spreadsheet, $items): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Cuti');

        $colorPrimary = '1F4E78';
        $colorHeader = '4472C4';
        $colorBandA = 'FFFFFF';
        $colorBandB = 'DCE6F1';
        $colorBorder = 'B7B7B7';

        $statusLabel = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];

        $columns = ['No', 'Nama Karyawan', 'Kode Pegawai', 'Jenis Cuti', 'Tanggal Mulai', 'Tanggal Selesai', 'Durasi (hari)', 'Status', 'Tanggal Pengajuan', 'Keterangan', 'Catatan HR'];
        $lastCol = 'K';
        $colWidths = ['A' => 5, 'B' => 24, 'C' => 14, 'D' => 18, 'E' => 13, 'F' => 13, 'G' => 12, 'H' => 13, 'I' => 15, 'J' => 28, 'K' => 28];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $row = 1;
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'LAPORAN CUTI');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorPrimary]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(32);
        $row += 2;

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

        $no = 1;
        foreach ($items as $item) {
            $durasi = $item->tanggal_mulai && $item->tanggal_selesai ? $item->tanggal_mulai->diffInDays($item->tanggal_selesai) + 1 : 0;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $item->karyawan->nama_lengkap ?? '-');
            $sheet->setCellValue("C{$row}", $item->karyawan->kode_pegawai ?? '-');
            $sheet->setCellValue("D{$row}", $item->jenis_cuti ?? '-');
            $sheet->setCellValue("E{$row}", $item->tanggal_mulai ? $item->tanggal_mulai->format('d-m-Y') : '-');
            $sheet->setCellValue("F{$row}", $item->tanggal_selesai ? $item->tanggal_selesai->format('d-m-Y') : '-');
            $sheet->setCellValue("G{$row}", $durasi);
            $sheet->setCellValue("H{$row}", $statusLabel[$item->status] ?? $item->status);
            $sheet->setCellValue("I{$row}", $item->tanggal_pengajuan ? $item->tanggal_pengajuan->format('d-m-Y') : '-');
            $sheet->setCellValue("J{$row}", $item->keterangan ?? '-');
            $sheet->setCellValue("K{$row}", $item->catatan_hr ?? '-');

            $bandColor = $no % 2 === 0 ? $colorBandB : $colorBandA;
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bandColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'font' => ['size' => 10],
            ]);
            $sheet
                ->getStyle("A{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet
                ->getStyle("E{$row}:H{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $no++;
            $row++;
        }

        if ($no === 1) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada data cuti pada periode ini.');
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '808080']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        $sheet->freezePane('A4');
        $sheet->setSelectedCell('A1');
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

    /**
     * ==========================================================
     * VERIFIKASI ABSEN - HALAMAN HR
     * ==========================================================
     * HR dapat melihat seluruh data absensi karyawan hari ini dan
     * melakukan verifikasi check-in / check-out secara manual.
     * Lokasi diambil dari device HR (latitude & longitude).
     */

    /**
     * Halaman verifikasi absen - menampilkan semua karyawan dengan status absensi hari ini.
     */
    public function verifikasiIndex(Request $request)
    {
        $today = Carbon::today($this->officeTimezone);
        $now = Carbon::now($this->officeTimezone);

        $selectedDate = $request->filled('tanggal') ? Carbon::parse($request->tanggal, $this->officeTimezone)->startOfDay() : $today;

        $karyawans = Karyawan::orderBy('nama_lengkap')->get();

        $absensiToday = Absensi::whereDate('tanggal', $selectedDate)->get()->keyBy('karyawan_id');

        $allEmployees = $karyawans->map(function ($karyawan) use ($absensiToday) {
            $absensi = $absensiToday->get($karyawan->id);
            return [
                'id' => $karyawan->id,
                'nama' => $karyawan->nama_lengkap ?? '-',
                'kode_pegawai' => $karyawan->kode_pegawai ?? '-',
                'jabatan' => $karyawan->jabatan ?? '-',
                'divisi' => $karyawan->divisi ?? '-',
                'absensi_id' => $absensi ? $absensi->id : null,
                'check_in' => $absensi && $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i') : null,
                'check_out' => $absensi && $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i') : null,
                'status' => $absensi ? $absensi->status : 'Alpha',
                'kantor_cabang' => $absensi ? ($absensi->kantor_cabang ?? '-') : '-',
            ];
        });

        $stats = [
            'total' => $karyawans->count(),
            'sudah_checkin' => $allEmployees->where('check_in', '!=', null)->count(),
            'sudah_checkout' => $allEmployees->where('check_out', '!=', null)->count(),
            'belum_absen' => $allEmployees->where('check_in', null)->count(),
        ];

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $allEmployees->count();
        $employeesData = $allEmployees->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator($employeesData, $total, $perPage, $page, [
            'path' => route('hr.absensi.verifikasi'),
            'query' => $request->query(),
        ]);

        return view('hr.absensi.verifikasi', compact('paginator', 'selectedDate', 'stats'));
    }

    /**
     * Simpan verifikasi absen manual dari halaman verifikasi.
     * HR input jam check-in dan/atau jam check-out SECARA MANUAL (bukan jam server).
     * GPS diambil dari device HR.
     * Menerima JSON request via AJAX.
     */
    public function verifikasiStore(Request $request)
    {
        $request->validate([
            'karyawan_id'  => 'required|exists:karyawans,id',
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'tanggal'      => 'required|date',
            'jam_masuk'    => 'nullable|date_format:H:i',
            'jam_keluar'   => 'nullable|date_format:H:i',
        ], [
            'jam_masuk.date_format'  => 'Format jam masuk harus HH:mm (contoh: 07:30).',
            'jam_keluar.date_format' => 'Format jam keluar harus HH:mm (contoh: 16:00).',
        ]);

        if (!$request->filled('jam_masuk') && !$request->filled('jam_keluar')) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal isi salah satu jam (Jam Masuk atau Jam Keluar).',
            ], 422);
        }

        $karyawanId = $request->karyawan_id;
        $lat        = (float) $request->latitude;
        $lng        = (float) $request->longitude;
        $selectedDate = Carbon::parse($request->tanggal, $this->officeTimezone)->startOfDay();
        $now          = Carbon::now($this->officeTimezone);
        $hrName       = Auth::user()->nama_lengkap ?? 'HR';

        $absensi = Absensi::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', $selectedDate)
            ->first();

        $doCheckin  = $request->filled('jam_masuk');
        $doCheckout = $request->filled('jam_keluar');

        // ---------- CHECK-IN ----------
        if ($doCheckin) {
            if ($absensi && $absensi->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan ini sudah memiliki data check-in pada tanggal tersebut.',
                ], 400);
            }

            $jamMasuk = Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $request->jam_masuk, $this->officeTimezone);
            $locationCheck = $this->isValidLocation($lat, $lng, $this->maxRadius, null, $karyawanId);

            if ($absensi) {
                $absensi->check_in        = $jamMasuk;
                $absensi->kantor_cabang   = $locationCheck['location_name'] ?? $locationCheck['nearest'];
                $absensi->latitude        = $lat;
                $absensi->longitude       = $lng;
                $absensi->is_valid_location = true;
                if (!$absensi->status || $absensi->status === 'Alpha') {
                    $absensi->status = 'Hadir';
                }
            } else {
                $absensi = Absensi::create([
                    'karyawan_id'       => $karyawanId,
                    'tanggal'           => $selectedDate,
                    'check_in'          => $jamMasuk,
                    'kantor_cabang'     => $locationCheck['location_name'] ?? $locationCheck['nearest'],
                    'latitude'          => $lat,
                    'longitude'         => $lng,
                    'is_valid_location' => true,
                    'status'            => 'Hadir',
                ]);
            }

            $catatanVerifikasi = 'Check-in jam ' . $request->jam_masuk . ' diverifikasi manual oleh HR (' . $hrName . ') pada ' . $now->format('d-m-Y H:i');
            $absensi->keterangan = $absensi->keterangan
                ? $absensi->keterangan . ' | ' . $catatanVerifikasi
                : $catatanVerifikasi;
            $absensi->save();
        }

        // ---------- CHECK-OUT ----------
        if ($doCheckout) {
            $absensi = Absensi::where('karyawan_id', $karyawanId)
                ->whereDate('tanggal', $selectedDate)
                ->first();

            if (!$absensi || !$absensi->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan ini belum check-in pada tanggal tersebut. Check-in harus dilakukan terlebih dahulu.',
                ], 400);
            }

            if ($absensi->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan ini sudah memiliki data check-out pada tanggal tersebut.',
                ], 400);
            }

            $jamKeluar = Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $request->jam_keluar, $this->officeTimezone);

            // Total jam kerja dihitung dari jam input masuk → jam input keluar
            $checkInTime  = Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $absensi->check_in->format('H:i'), $this->officeTimezone);
            $totalJamKerja = max(0, (int) round($checkInTime->diffInMinutes($jamKeluar) / 60));

            $absensi->check_out     = $jamKeluar;
            $absensi->total_jam_kerja = $totalJamKerja;

            if (!$absensi->kantor_cabang) {
                $locationCheck = $this->isValidLocation($lat, $lng, $this->maxRadius, null, $karyawanId);
                $absensi->kantor_cabang = $locationCheck['location_name'] ?? $locationCheck['nearest'];
            }

            $catatanVerifikasi = 'Check-out jam ' . $request->jam_keluar . ' diverifikasi manual oleh HR (' . $hrName . ') pada ' . $now->format('d-m-Y H:i');
            $absensi->keterangan = $absensi->keterangan
                ? $absensi->keterangan . ' | ' . $catatanVerifikasi
                : $catatanVerifikasi;
            $absensi->save();
        }

        // ---------- RESPONSE ----------
        $absensi->refresh();
        $checkInFormatted  = $absensi->check_in  ? $absensi->check_in->format('H:i') : '-';
        $checkOutFormatted = $absensi->check_out ? $absensi->check_out->format('H:i') : '-';

        $messages = [];
        if ($doCheckin) {
            $messages[] = 'Check-in jam ' . $request->jam_masuk;
        }
        if ($doCheckout) {
            $messages[] = 'Check-out jam ' . $request->jam_keluar;
        }

        return response()->json([
            'success' => true,
            'message' => implode(' & ', $messages) . ' berhasil diverifikasi untuk ' . ($absensi->karyawan->nama_lengkap ?? 'karyawan') . '.',
            'data' => [
                'check_in'       => $checkInFormatted,
                'check_out'      => $checkOutFormatted,
                'total_jam_kerja' => $absensi->total_jam_kerja ?? 0,
                'kantor_cabang'  => $absensi->kantor_cabang ?? '-',
            ],
        ]);
    }
}
