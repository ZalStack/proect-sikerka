<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Generate QR Code token untuk absensi
     */
    private function generateQrToken(): string
    {
        return bin2hex(random_bytes(32)) . '_' . time();
    }

    /**
     * Validasi lokasi kantor
     */
    private function validateLocation($latitude, $longitude): array
    {
        $result = Absensi::isValidLocation($latitude, $longitude, $this->maxRadius);

        return [
            'valid' => $result['valid'],
            'distance' => $result['distance'] ?? 0,
            'nearest' => $result['nearest'] ?? null,
            'nearest_distance' => $result['nearest_distance'] ?? 0,
            'location_name' => $result['location_name'] ?? null,
        ];
    }

    /**
     * Generate QR Code untuk absensi (endpoint public)
     */
    public function generateQrCode()
    {
        $token = $this->generateQrToken();

        // Simpan token ke session untuk validasi
        session(['qr_absensi_token' => $token]);
        session(['qr_absensi_expires' => Carbon::now($this->officeTimezone)->addMinutes(5)->timestamp]);

        // Buat QR Code sebagai gambar base64
        $qrData = [
            'token' => $token,
            'timestamp' => Carbon::now($this->officeTimezone)->timestamp,
        ];

        $qrJson = json_encode($qrData);

        // Generate QR Code menggunakan Simple QR Code (tanpa library external)
        $qrImage = $this->generateSimpleQrCode($qrJson);

        return response()->json([
            'success' => true,
            'qr_code' => $qrImage,
            'token' => $token,
            'expires_at' => Carbon::now($this->officeTimezone)->addMinutes(5)->toIso8601String(),
        ]);
    }

    /**
     * Generate QR Code sederhana tanpa library external
     */
    private function generateSimpleQrCode($data)
    {
        // Karena kita tidak bisa membuat QR Code murni tanpa library,
        // kita akan menggunakan API fallback atau library bawaan.
        // Untuk production, sebaiknya install: composer require simplesoftwareio/simple-qrcode

        try {
            // Jika library tersedia, gunakan itu
            if (class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(300)
                    ->errorCorrection('H')
                    ->generate($data);

                return 'data:image/png;base64,' . base64_encode($qr);
            }

            // Fallback: tampilkan data sebagai teks yang bisa dipindai
            // Dalam production, install library QR Code
            return 'data:text/plain;base64,' . base64_encode($data);

        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            return 'data:text/plain;base64,' . base64_encode($data);
        }
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
     * Endpoint untuk memindai QR Code dan melakukan absensi
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
            ], 400);
        }

        // Validasi lokasi
        $locationCheck = $this->validateLocation(
            (float) $request->latitude,
            (float) $request->longitude
        );

        if (!$locationCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi ditolak! Anda berada di luar radius kantor (minimal 50 meter). ' .
                    'Jarak terdekat: ' . $locationCheck['distance'] . ' meter dari ' .
                    ($locationCheck['nearest'] ?? 'lokasi terdekat'),
                'distance' => $locationCheck['distance'],
                'nearest_location' => $locationCheck['nearest'],
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
                ], 400);
            }

            if ($absensi->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-out hari ini!',
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
        ], 400);
    }

    /**
     * Check-in via QR Code (alias untuk scanQrCode dengan action checkin)
     */
    public function checkIn(Request $request)
    {
        $request->merge(['action' => 'checkin']);
        return $this->scanQrCode($request);
    }

    /**
     * Check-out via QR Code (alias untuk scanQrCode dengan action checkout)
     */
    public function checkOut(Request $request)
    {
        $request->merge(['action' => 'checkout']);
        return $this->scanQrCode($request);
    }

    /**
     * Cek status absensi dan dapatkan QR Code terbaru
     */
    public function status()
    {
        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        $absensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $now = Carbon::now($this->officeTimezone);
        $todayName = $now->locale('id')->isoFormat('dddd');

        // Generate QR Code token baru jika belum ada atau sudah expired
        $qrToken = session('qr_absensi_token');
        $qrExpires = session('qr_absensi_expires', 0);

        if (empty($qrToken) || $now->timestamp > $qrExpires) {
            $qrToken = $this->generateQrToken();
            session(['qr_absensi_token' => $qrToken]);
            session(['qr_absensi_expires' => $now->addMinutes(5)->timestamp]);
        }

        // Generate QR Code image
        $qrData = json_encode([
            'token' => $qrToken,
            'timestamp' => $now->timestamp,
        ]);
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
                // Data untuk QR Code
                'qr_code' => $qrImage,
                'qr_token' => $qrToken,
                'qr_expires_at' => Carbon::createFromTimestamp(session('qr_absensi_expires'))->toIso8601String(),
                // Data untuk sinkronisasi jam
                'server_timestamp_ms' => $now->getTimestampMs(),
                'server_time_iso' => $now->toIso8601String(),
                'office_locations' => Absensi::getOfficeLocations(),
                'max_radius' => $this->maxRadius,
            ],
        ]);
    }

    /**
     * Server time endpoint
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

    /**
     * HR View Absensi dengan Filter
     */
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

        // Data untuk grafik
        $chartData = $this->getChartData($request);

        return view('hr.absensi.index', compact('absensis', 'karyawans', 'chartData'));
    }

    /**
     * Dashboard Karyawan
     */
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        // Absensi hari ini
        $todayAbsensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Data 7 hari terakhir
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today($this->officeTimezone)->subDays($i);
            $absensi = Absensi::where('karyawan_id', $user->id)
                ->whereDate('tanggal', $date)
                ->first();

            $last7Days[] = [
                'tanggal' => $date->format('d/m'),
                'check_in' => $absensi && $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i') : '-',
                'check_out' => $absensi && $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i') : '-',
                'status' => $absensi ? $absensi->status : 'Alpha',
                'total_jam' => $absensi ? $absensi->total_jam_kerja : 0,
                'is_valid' => $absensi ? $absensi->is_valid_location : false,
                'distance' => $absensi && $absensi->latitude ?
                    Absensi::haversineDistance(
                        $absensi->latitude,
                        $absensi->longitude,
                        -6.586886661039424,
                        106.75890044642712
                    ) : null,
            ];
        }

        // Lokasi kantor untuk peta
        $officeLocations = Absensi::getOfficeLocations();

        return view('karyawan.absensi', compact('todayAbsensi', 'last7Days', 'officeLocations'));
    }

    /**
     * Generate Laporan Excel
     */
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
                'No',
                'Nama Karyawan',
                'Kode Pegawai',
                'Tanggal',
                'Check In',
                'Check Out',
                'Kantor Cabang',
                'Status',
                'Total Jam Kerja',
                'Latitude',
                'Longitude',
                'Valid Lokasi',
                'Jarak (meter)',
                'Keterangan',
            ]);

            $no = 1;
            foreach ($absensis as $absen) {
                // Hitung jarak dari kantor terdekat
                $distance = '-';
                if ($absen->latitude && $absen->longitude) {
                    $locations = Absensi::getOfficeLocations();
                    $minDistance = PHP_FLOAT_MAX;
                    foreach ($locations as $coords) {
                        $d = Absensi::haversineDistance(
                            $absen->latitude,
                            $absen->longitude,
                            $coords['latitude'],
                            $coords['longitude']
                        );
                        if ($d < $minDistance) {
                            $minDistance = $d;
                        }
                    }
                    $distance = $minDistance < PHP_FLOAT_MAX ? round($minDistance, 2) : '-';
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

        // Hitung jarak dari semua kantor
        $distances = [];
        if ($absensi->latitude && $absensi->longitude) {
            foreach (Absensi::getOfficeLocations() as $name => $coords) {
                $distances[$name] = Absensi::haversineDistance(
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
