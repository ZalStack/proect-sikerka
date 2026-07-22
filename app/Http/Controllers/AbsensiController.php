<?php

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
     * Check-in
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0.1|max:5000',
        ]);

        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);
        $now = Carbon::now($this->officeTimezone);

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
                'is_suspicious' => false,
                'suspicious_reason' => null,
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
        ]);

        $user = Auth::user();
        $today = Carbon::today($this->officeTimezone);
        $now = Carbon::now($this->officeTimezone);

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

    public function index(Request $request)
    {
        $query = Absensi::with('karyawan');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)->whereYear('tanggal', $request->year);
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
        $query = Absensi::with('karyawan');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)->whereYear('tanggal', $request->year);
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
            fprintf($file, chr(0xef) . chr(0xbb) . chr(0xbf));

            fputcsv($file, ['No', 'Nama Karyawan', 'Kode Pegawai', 'Tanggal', 'Check In', 'Check Out', 'Kantor Cabang', 'Status', 'Total Jam Kerja', 'Latitude', 'Longitude', 'Valid Lokasi', 'Jarak Terdekat (meter)', 'Keterangan']);

            $no = 1;
            foreach ($absensis as $absen) {
                $distance = '-';
                if ($absen->latitude && $absen->longitude) {
                    $locations = $this->getOfficeLocations();
                    $minDist = PHP_FLOAT_MAX;
                    foreach ($locations as $coords) {
                        $d = $this->haversineDistance($absen->latitude, $absen->longitude, $coords['latitude'], $coords['longitude']);
                        if ($d < $minDist) {
                            $minDist = $d;
                        }
                    }
                    $distance = $minDist < PHP_FLOAT_MAX ? round($minDist, 2) : '-';
                }

                fputcsv($file, [$no++, $absen->karyawan->nama_lengkap, $absen->karyawan->kode_pegawai, $absen->tanggal->format('d-m-Y'), $absen->check_in ? Carbon::parse($absen->check_in)->format('H:i') : '-', $absen->check_out ? Carbon::parse($absen->check_out)->format('H:i') : '-', $absen->kantor_cabang, $absen->status, $absen->total_jam_kerja . ' jam', $absen->latitude ?? '-', $absen->longitude ?? '-', $absen->is_valid_location ? 'Ya' : 'Tidak', $distance, $absen->keterangan ?? '-']);
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN LAPORAN']);
            fputcsv($file, ['Total Absensi', $absensis->count()]);
            fputcsv($file, ['Total Hadir', $absensis->where('status', 'Hadir')->count()]);
            fputcsv($file, ['Total Izin', $absensis->where('status', 'Izin')->count()]);
            fputcsv($file, ['Total Sakit', $absensis->where('status', 'Sakit')->count()]);
            fputcsv($file, ['Total Alpha', $absensis->where('status', 'Alpha')->count()]);
            fputcsv($file, ['Total Perjalanan Dinas', $absensis->where('status', 'Perjalanan Dinas')->count()]);
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
            $query->whereMonth('tanggal', $request->month)->whereYear('tanggal', $request->year);
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

        return view('hr.absensi.detail', compact('absensi', 'distances'));
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
}
