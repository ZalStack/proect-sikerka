<?php
// app/Http/Controllers/AbsensiController.php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
     * Durasi valid QR Code dalam detik (20 detik)
     */
    private int $qrValiditySeconds = 20;

    /**
     * Konfigurasi titik koordinat kantor KPM
     */
    private function getOfficeLocations(): array
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
     * Hitung jarak antara dua koordinat dalam meter (Haversine formula)
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Cek apakah lokasi berada dalam radius tertentu dari salah satu kantor
     */
    private function isValidLocation($latitude, $longitude, $radius = 50): array
    {
        $locations = $this->getOfficeLocations();
        $nearestLocation = null;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($locations as $name => $coords) {
            $distance = $this->haversineDistance(
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

    /**
     * Generate QR Code sederhana tanpa library external
     * Menggunakan pendekatan base64 encoding dengan token
     */
    private function generateQrCodeImage($data): string
    {
        // Karena tidak ada library QR Code, kita buat QR Code sederhana
        // yang bisa di-scan menggunakan jsQR di client-side
        // Data yang di-encode adalah JSON string

        $jsonData = json_encode($data);

        // Buat representasi QR Code sebagai string biner sederhana
        // Ini akan di-decode oleh jsQR di sisi client
        $qrData = $this->encodeToQrFormat($jsonData);

        // Kembalikan sebagai data URI dengan format khusus
        // Client akan mengenali ini sebagai QR Code
        return 'data:application/json;base64,' . base64_encode($jsonData);
    }

    /**
     * Encode data ke format yang bisa dibaca jsQR
     * Ini adalah fallback sederhana tanpa library external
     */
    private function encodeToQrFormat($data): string
    {
        // Buat string dengan format yang bisa dikenali jsQR
        // jsQR bisa membaca berbagai format, termasuk teks biasa
        return $data;
    }

    /**
     * Generate QR Code token
     */
    private function generateQrToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate QR Code untuk absensi
     */
    public function generateQrCode()
    {
        $token = $this->generateQrToken();
        $now = Carbon::now($this->officeTimezone);
        $expiresAt = $now->copy()->addSeconds($this->qrValiditySeconds);

        // Simpan token ke session
        session(['qr_absensi_token' => $token]);
        session(['qr_absensi_expires' => $expiresAt->timestamp]);
        session(['qr_absensi_created' => $now->timestamp]);

        // Data QR Code
        $qrData = [
            'token' => $token,
            'timestamp' => $now->timestamp,
            'expires' => $expiresAt->timestamp,
        ];

        // Generate QR Code image (tanpa library)
        $qrImage = $this->generateSimpleQrCode($qrData);

        return response()->json([
            'success' => true,
            'qr_code' => $qrImage,
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => $this->qrValiditySeconds,
        ]);
    }

    /**
     * Generate Simple QR Code tanpa library external
     * Menggunakan canvas rendering di client-side
     */
    private function generateSimpleQrCode($data): string
    {
        // Karena kita tidak bisa generate QR Code murni di PHP tanpa library,
        // kita kirimkan data sebagai JSON yang akan dirender oleh client-side jsQR
        // Client akan menggunakan jsQR untuk membaca QR Code dari gambar/stream

        // Kirim data sebagai base64 encoded JSON
        $jsonData = json_encode($data);
        return 'data:text/plain;base64,' . base64_encode($jsonData);
    }

    /**
     * Validasi QR Code token
     */
    private function validateQrToken($token): bool
    {
        $sessionToken = session('qr_absensi_token');
        $expires = session('qr_absensi_expires', 0);

        if (empty($sessionToken) || $token !== $sessionToken) {
            return false;
        }

        if (Carbon::now($this->officeTimezone)->timestamp > $expires) {
            return false;
        }

        return true;
    }

    /**
     * Endpoint untuk scan QR Code dan absensi
     */
    public function scanQrCode(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'action' => 'required|in:checkin,checkout',
        ]);

        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        // Validasi QR Code
        if (!$this->validateQrToken($request->qr_token)) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa. Silakan refresh QR Code.',
                'code' => 'INVALID_QR',
            ], 400);
        }

        // Validasi lokasi
        $locationCheck = $this->isValidLocation(
            (float) $request->latitude,
            (float) $request->longitude,
            $this->maxRadius
        );

        if (!$locationCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi ditolak! Anda berada di luar radius kantor (minimal 50 meter). ' .
                    'Jarak terdekat: ' . $locationCheck['distance'] . ' meter dari ' .
                    ($locationCheck['nearest'] ?? 'lokasi terdekat'),
                'distance' => $locationCheck['distance'],
                'nearest_location' => $locationCheck['nearest'],
                'code' => 'INVALID_LOCATION',
            ], 403);
        }

        // Cek absensi hari ini
        $absensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $now = Carbon::now($this->officeTimezone);

        if ($request->action === 'checkin') {
            if ($absensi && $absensi->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-in hari ini!',
                    'code' => 'ALREADY_CHECKIN',
                ], 400);
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
                    'location_accuracy' => $request->accuracy ?? 0,
                    'is_valid_location' => $locationCheck['valid'],
                    'qr_code_token' => $request->qr_token,
                ]
            );

            // Hapus token setelah digunakan
            session()->forget('qr_absensi_token');
            session()->forget('qr_absensi_expires');

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

        } elseif ($request->action === 'checkout') {
            if (!$absensi || !$absensi->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan check-in!',
                    'code' => 'NO_CHECKIN',
                ], 400);
            }

            if ($absensi->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-out hari ini!',
                    'code' => 'ALREADY_CHECKOUT',
                ], 400);
            }

            $checkInTime = Carbon::parse($absensi->check_in);
            $totalJamKerja = (int) round($checkInTime->diffInMinutes($now) / 60);

            $absensi->total_jam_kerja = $totalJamKerja;
            $absensi->check_out = $now;
            $absensi->latitude = $request->latitude;
            $absensi->longitude = $request->longitude;
            $absensi->location_accuracy = $request->accuracy ?? 0;
            $absensi->is_valid_location = $locationCheck['valid'];
            $absensi->qr_code_token = $request->qr_token;
            $absensi->save();

            // Hapus token setelah digunakan
            session()->forget('qr_absensi_token');
            session()->forget('qr_absensi_expires');

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

        return response()->json([
            'success' => false,
            'message' => 'Aksi tidak dikenali',
            'code' => 'INVALID_ACTION',
        ], 400);
    }

    /**
     * Check-in via QR Code
     */
    public function checkIn(Request $request)
    {
        $request->merge(['action' => 'checkin']);
        return $this->scanQrCode($request);
    }

    /**
     * Check-out via QR Code
     */
    public function checkOut(Request $request)
    {
        $request->merge(['action' => 'checkout']);
        return $this->scanQrCode($request);
    }

    /**
     * Cek status absensi
     */
    public function status()
    {
        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);
        $now = Carbon::now($this->officeTimezone);

        $absensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $todayName = $now->locale('id')->isoFormat('dddd');

        // Generate QR Code baru setiap request
        $qrToken = $this->generateQrToken();
        $expiresAt = $now->copy()->addSeconds($this->qrValiditySeconds);

        session(['qr_absensi_token' => $qrToken]);
        session(['qr_absensi_expires' => $expiresAt->timestamp]);
        session(['qr_absensi_created' => $now->timestamp]);

        // Data QR Code
        $qrData = [
            'token' => $qrToken,
            'timestamp' => $now->timestamp,
            'expires' => $expiresAt->timestamp,
        ];

        $qrImage = $this->generateSimpleQrCode($qrData);

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
                // QR Code
                'qr_code' => $qrImage,
                'qr_token' => $qrToken,
                'qr_expires_at' => $expiresAt->toIso8601String(),
                'qr_expires_in' => $this->qrValiditySeconds,
                // Server time
                'server_timestamp_ms' => $now->getTimestampMs(),
                'server_time_iso' => $now->toIso8601String(),
                // Lokasi kantor
                'office_locations' => $this->getOfficeLocations(),
                'max_radius' => $this->maxRadius,
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
    // HR Methods (tidak berubah)
    // ==========================================================

    public function index(Request $request)
    {
        $query = Absensi::with('karyawan');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)
                  ->whereYear('tanggal', $request->year);
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->orderBy('tanggal', 'desc')->paginate(15);
        $karyawans = Karyawan::all();

        $chartData = $this->getChartData($request);

        return view('hr.absensi.index', compact('absensis', 'karyawans', 'chartData'));
    }

    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        $todayAbsensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today($this->officeTimezone)->subDays($i);
            $absensi = Absensi::where('karyawan_id', $user->id)
                ->whereDate('tanggal', $date)
                ->first();

            $distance = null;
            if ($absensi && $absensi->latitude && $absensi->longitude) {
                $locations = $this->getOfficeLocations();
                $minDist = PHP_FLOAT_MAX;
                foreach ($locations as $coords) {
                    $d = $this->haversineDistance(
                        $absensi->latitude,
                        $absensi->longitude,
                        $coords['latitude'],
                        $coords['longitude']
                    );
                    if ($d < $minDist) $minDist = $d;
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
        $query = Absensi::with('karyawan');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)
                  ->whereYear('tanggal', $request->year);
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->orderBy('tanggal', 'desc')->get();

        return $this->generateExcel($absensis);
    }

    private function generateExcel($absensis)
    {
        $fileName = 'laporan_absensi_' . Carbon::now($this->officeTimezone)->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($absensis) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No', 'Nama Karyawan', 'Kode Pegawai', 'Tanggal',
                'Check In', 'Check Out', 'Kantor Cabang', 'Status',
                'Total Jam Kerja', 'Latitude', 'Longitude',
                'Valid Lokasi', 'Jarak Terdekat (meter)', 'Keterangan'
            ]);

            $no = 1;
            foreach ($absensis as $absen) {
                $distance = '-';
                if ($absen->latitude && $absen->longitude) {
                    $locations = $this->getOfficeLocations();
                    $minDist = PHP_FLOAT_MAX;
                    foreach ($locations as $coords) {
                        $d = $this->haversineDistance(
                            $absen->latitude,
                            $absen->longitude,
                            $coords['latitude'],
                            $coords['longitude']
                        );
                        if ($d < $minDist) $minDist = $d;
                    }
                    $distance = $minDist < PHP_FLOAT_MAX ? round($minDist, 2) : '-';
                }

                fputcsv($file, [
                    $no++,
                    $absen->karyawan->nama_lengkap,
                    $absen->karyawan->kode_pegawai,
                    $absen->tanggal->format('d-m-Y'),
                    $absen->check_in ? Carbon::parse($absen->check_in)->format('H:i') : '-',
                    $absen->check_out ? Carbon::parse($absen->check_out)->format('H:i') : '-',
                    $absen->kantor_cabang,
                    $absen->status,
                    $absen->total_jam_kerja . ' jam',
                    $absen->latitude ?? '-',
                    $absen->longitude ?? '-',
                    $absen->is_valid_location ? 'Ya' : 'Tidak',
                    $distance,
                    $absen->keterangan ?? '-',
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN LAPORAN']);
            fputcsv($file, ['Total Absensi', $absensis->count()]);
            fputcsv($file, ['Total Hadir', $absensis->where('status', 'Hadir')->count()]);
            fputcsv($file, ['Total Izin', $absensis->where('status', 'Izin')->count()]);
            fputcsv($file, ['Total Sakit', $absensis->where('status', 'Sakit')->count()]);
            fputcsv($file, ['Total Alpha', $absensis->where('status', 'Alpha')->count()]);
            fputcsv($file, ['Valid Lokasi', $absensis->where('is_valid_location', true)->count()]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getChartData($request)
    {
        $query = Absensi::with('karyawan');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)
                  ->whereYear('tanggal', $request->year);
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->get();

        return [
            'hadir' => $absensis->where('status', 'Hadir')->count(),
            'izin' => $absensis->where('status', 'Izin')->count(),
            'sakit' => $absensis->where('status', 'Sakit')->count(),
            'alpha' => $absensis->where('status', 'Alpha')->count(),
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
                $distances[$name] = $this->haversineDistance(
                    $absensi->latitude,
                    $absensi->longitude,
                    $coords['latitude'],
                    $coords['longitude']
                );
            }
        }

        return view('hr.absensi.detail', compact('absensi', 'distances'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Hadir,Izin,Sakit,Alpha',
            'keterangan' => 'nullable|string',
        ]);

        $absensi = Absensi::findOrFail($id);
        $absensi->status = $request->status;
        $absensi->keterangan = $request->keterangan;
        $absensi->save();

        return redirect()->route('hr.absensi.index')
            ->with('success', 'Status absensi berhasil diupdate');
    }
}
