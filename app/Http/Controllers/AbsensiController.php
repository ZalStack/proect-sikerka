<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // Data WiFi Kantor yang valid
    private $validWifi = [
        [
            'ssid' => 'KPM',
            'bssid' => '36:A3:AE:BD:71:7D',
            'nama_kantor' => 'KPM Pusat Laladon'
        ]
    ];

    // Cek koneksi WiFi via IP / Network
    public function checkWifi(Request $request)
    {
        $clientIp = $request->ip();
        $isLocalNetwork = $this->isLocalNetwork($clientIp);
        $wifiDetected = $this->detectWifiViaPing();

        return response()->json([
            'success' => $wifiDetected,
            'message' => $wifiDetected ? 'Terhubung ke WiFi Kantor' : 'Tidak terhubung ke WiFi Kantor',
            'ip' => $clientIp,
            'is_local' => $isLocalNetwork,
            'ssid' => 'KPM',
            'bssid' => '36:A3:AE:BD:71:7D',
            'kantor' => $wifiDetected ? 'KPM Pusat Laladon' : null
        ]);
    }

    // Karyawan Check-in
    public function checkIn(Request $request)
    {
        $request->validate([
            'ssid' => 'required|string',
            'bssid' => 'required|string',
        ]);

        $wifiValid = $this->validateWifi($request->ssid, $request->bssid);

        if (!$wifiValid) {
            $wifiValid = $this->checkNetworkConnection();
        }

        if (!$wifiValid) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terhubung ke WiFi kantor! Silakan sambungkan ke WiFi KPM atau pastikan Anda berada di area kantor.'
            ], 400);
        }

        $user = Auth::user();
        $today = Carbon::today();

        $existingAbsen = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existingAbsen && $existingAbsen->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-in hari ini!'
            ], 400);
        }

        $now = Carbon::now();
        $kantor = $this->getKantorByBSSID($request->bssid) ?? 'KPM Pusat Laladon';

        $absensi = Absensi::updateOrCreate(
            [
                'karyawan_id' => $user->id,
                'tanggal' => $today,
            ],
            [
                'check_in' => $now,
                'kantor_cabang' => $kantor,
                'status' => 'Hadir',
                'ip_address' => $request->ip(),
                'mac_address' => $request->bssid,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil!',
            'data' => [
                'waktu' => $now->format('H:i:s'),
                'tanggal' => $now->format('d-m-Y'),
                'kantor' => $kantor,
                'status' => 'Hadir'
            ]
        ]);
    }

    // Karyawan Check-out
    public function checkOut(Request $request)
    {
        $request->validate([
            'ssid' => 'required|string',
            'bssid' => 'required|string',
        ]);

        $wifiValid = $this->validateWifi($request->ssid, $request->bssid);

        if (!$wifiValid) {
            $wifiValid = $this->checkNetworkConnection();
        }

        if (!$wifiValid) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terhubung ke WiFi kantor! Silakan sambungkan ke WiFi KPM atau pastikan Anda berada di area kantor.'
            ], 400);
        }

        $user = Auth::user();
        $today = Carbon::today();

        $absensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$absensi || !$absensi->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan check-in!'
            ], 400);
        }

        if ($absensi->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-out hari ini!'
            ], 400);
        }

        $now = Carbon::now();
        $checkInTime = Carbon::parse($absensi->check_in);

        $totalJamKerja = $checkInTime->diffInHours($now);
        $absensi->total_jam_kerja = $totalJamKerja;
        $absensi->check_out = $now;
        $absensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil!',
            'data' => [
                'waktu' => $now->format('H:i:s'),
                'tanggal' => $now->format('d-m-Y'),
                'total_jam' => $totalJamKerja,
                'kantor' => $absensi->kantor_cabang
            ]
        ]);
    }

    // Cek Status Absensi Hari Ini
    public function status()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $absensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $now = Carbon::now();
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
            ]
        ]);
    }

    // HR View Absensi dengan Filter - PERBAIKAN
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

        // Data untuk grafik statistik
        $chartData = $this->getChartData($request);

        return view('hr.absensi.index', compact('absensis', 'karyawans', 'chartData'));
    }

    // Dashboard Karyawan
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $todayAbsensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $absensi = Absensi::where('karyawan_id', $user->id)
                ->whereDate('tanggal', $date)
                ->first();

            $last7Days[] = [
                'tanggal' => $date->format('d/m'),
                'check_in' => $absensi && $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i') : '-',
                'check_out' => $absensi && $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i') : '-',
                'status' => $absensi ? $absensi->status : 'Alpha',
                'total_jam' => $absensi ? $absensi->total_jam_kerja : 0,
            ];
        }

        return view('karyawan.absensi', compact('todayAbsensi', 'last7Days'));
    }

    // Get Chart Data untuk HR
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
        ];
    }

    // Generate Laporan Excel
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
        $fileName = 'laporan_absensi_' . Carbon::now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($absensis) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

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
                'IP Address',
                'MAC Address',
                'Keterangan'
            ]);

            $no = 1;
            foreach ($absensis as $absen) {
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
                    $absen->ip_address ?? '-',
                    $absen->mac_address ?? '-',
                    $absen->keterangan ?? '-'
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN LAPORAN']);
            fputcsv($file, ['Total Absensi', $absensis->count()]);
            fputcsv($file, ['Total Hadir', $absensis->where('status', 'Hadir')->count()]);
            fputcsv($file, ['Total Izin', $absensis->where('status', 'Izin')->count()]);
            fputcsv($file, ['Total Sakit', $absensis->where('status', 'Sakit')->count()]);
            fputcsv($file, ['Total Alpha', $absensis->where('status', 'Alpha')->count()]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // HR Detail Absensi Karyawan
    public function detail($id)
    {
        $absensi = Absensi::with('karyawan')->findOrFail($id);
        return view('hr.absensi.detail', compact('absensi'));
    }

    // HR Update Status Absensi
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

    // Validasi WiFi
    private function validateWifi($ssid, $bssid)
    {
        foreach ($this->validWifi as $wifi) {
            if ($wifi['ssid'] === $ssid && $wifi['bssid'] === $bssid) {
                return true;
            }
        }
        return false;
    }

    private function getKantorByBSSID($bssid)
    {
        foreach ($this->validWifi as $wifi) {
            if ($wifi['bssid'] === $bssid) {
                return $wifi['nama_kantor'];
            }
        }
        return null;
    }

    private function isLocalNetwork($ip)
    {
        return (
            strpos($ip, '192.168.') === 0 ||
            strpos($ip, '10.') === 0 ||
            (strpos($ip, '172.') === 0 &&
             intval(explode('.', $ip)[1]) >= 16 &&
             intval(explode('.', $ip)[1]) <= 31)
        );
    }

    private function checkNetworkConnection()
    {
        try {
            $connection = @fsockopen('192.168.1.1', 80, $errno, $errstr, 2);
            if ($connection) {
                fclose($connection);
                return true;
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return false;
    }

    private function detectWifiViaPing()
    {
        return $this->checkNetworkConnection();
    }
}
