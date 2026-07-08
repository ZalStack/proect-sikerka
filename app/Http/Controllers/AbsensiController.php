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
    /**
     * Konfigurasi jaringan WiFi Kantor.
     * Berdasarkan data jaringan yang diberikan:
     * - IPv4 Address perangkat referensi : 192.168.1.35
     * - Default Route / Gateway          : 192.168.1.1
     * - Hardware Address (BSSID) Router  : 36:A3:AE:BD:71:7D
     *
     * Semua perangkat yang terhubung ke WiFi kantor akan mendapat IP
     * pada subnet 192.168.1.0/24, sehingga validasi utama dilakukan
     * dengan mengecek apakah IP client berada pada subnet tersebut.
     */
    private string $officeNetworkCidr = '192.168.1.0/24';
    private string $officeGatewayIp   = '192.168.1.1';
    private string $officeBssid       = '36:A3:AE:BD:71:7D';
    private string $officeName        = 'KPM Pusat Laladon';
    private string $officeSsid        = 'KPM';

    /**
     * Timezone resmi yang dipakai untuk SEMUA perhitungan jam absensi.
     * Di-set eksplisit (bukan mengandalkan config('app.timezone')) supaya
     * jam check-in/check-out tidak meleset walau server/hosting berjalan
     * dengan timezone default PHP (UTC).
     */
    private string $officeTimezone = 'Asia/Jakarta';

    /**
     * Cek apakah sebuah IP berada dalam range CIDR tertentu.
     * Contoh: ipInRange('192.168.1.35', '192.168.1.0/24') => true
     */
    private function ipInRange(string $ip, string $cidr): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Saat ini validasi jaringan kantor hanya mendukung IPv4.
            return false;
        }

        if (strpos($cidr, '/') === false) {
            return $ip === $cidr;
        }

        [$subnet, $bits] = explode('/', $cidr);
        $bits = (int) $bits;

        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = $bits === 0 ? 0 : (-1 << (32 - $bits));

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    /**
     * Validasi utama koneksi WiFi Kantor.
     * - IP address WAJIB berada pada subnet kantor (192.168.1.0/24) atau gateway kantor.
     * - Jika BSSID/MAC dikirim oleh device, BSSID tersebut WAJIB sama dengan BSSID router kantor.
     * - Jika BSSID tidak dikirim (browser umumnya tidak bisa membaca BSSID), validasi
     *   tetap berlaku HANYA berdasarkan IP, sehingga tetap ketat: WiFi selain kantor
     *   (yang tentu memiliki subnet IP berbeda) otomatis akan ditolak.
     */
    private function validateOfficeConnection(Request $request): array
    {
        $clientIp = $request->ip();
        $bssid    = strtoupper(trim((string) $request->input('bssid', '')));

        // TIDAK ADA whitelist/bypass apapun (termasuk untuk localhost/127.0.0.1).
        // IP client WAJIB benar-benar berada pada subnet WiFi kantor.
        $isIpValid = $this->ipInRange($clientIp, $this->officeNetworkCidr)
            || $clientIp === $this->officeGatewayIp;

        $isBssidValid = true;
        if (!empty($bssid)) {
            $isBssidValid = $bssid === strtoupper($this->officeBssid);
        }

        $isValid = $isIpValid && $isBssidValid;

        return [
            'valid'         => $isValid,
            'ip'            => $clientIp,
            'bssid'         => $bssid,
            'is_ip_valid'   => $isIpValid,
            'is_bssid_valid'=> $isBssidValid,
        ];
    }

    // Cek koneksi WiFi via IP/BSSID
    public function checkWifi(Request $request)
    {
        $result = $this->validateOfficeConnection($request);

        return response()->json([
            'success'      => $result['valid'],
            'message'      => $result['valid']
                ? 'Terhubung ke WiFi Kantor'
                : 'Tidak terhubung ke WiFi Kantor. Pastikan perangkat Anda terhubung ke WiFi "' . $this->officeSsid . '" di kantor.',
            'ip'           => $result['ip'],
            'is_valid_ip'  => $result['is_ip_valid'],
            'is_valid_mac' => $result['is_bssid_valid'],
            'bssid'        => $result['bssid'],
            'ssid'         => $this->officeSsid,
            'kantor'       => $this->officeName,
            'server_time'  => Carbon::now($this->officeTimezone)->toIso8601String(),
            'server_timestamp_ms' => Carbon::now($this->officeTimezone)->getTimestampMs(),
        ]);
    }

    // Endpoint ringan untuk sinkronisasi jam realtime di sisi client
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

    // Karyawan Check-in
    public function checkIn(Request $request)
    {
        $request->validate([
            'ssid'  => 'nullable|string',
            'bssid' => 'nullable|string',
        ]);

        $user  = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        // Cek apakah sudah check-in hari ini
        $existingAbsen = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existingAbsen && $existingAbsen->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-in hari ini!',
            ], 400);
        }

        // Validasi koneksi WiFi kantor — WAJIB terhubung ke WiFi kantor
        $wifi = $this->validateOfficeConnection($request);

        if (!$wifi['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi ditolak! Anda tidak terhubung ke WiFi kantor "' . $this->officeSsid . '". '
                    . 'Silakan hubungkan perangkat Anda ke WiFi kantor terlebih dahulu, lalu coba lagi.',
                'ip' => $wifi['ip'],
            ], 403);
        }

        // Waktu SELALU diambil dari jam server (bukan jam perangkat karyawan)
        // agar tidak bisa dimanipulasi dengan mengubah jam HP/laptop.
        $now = Carbon::now($this->officeTimezone);
        $checkInTime = $now->format('H:i:s');

        $absensi = Absensi::updateOrCreate(
            [
                'karyawan_id' => $user->id,
                'tanggal'     => $today,
            ],
            [
                'check_in'      => $now,
                'kantor_cabang' => $this->officeName,
                'status'        => 'Hadir',
                'ip_address'    => $wifi['ip'],
                'mac_address'   => $wifi['bssid'],
                'is_valid_wifi' => $wifi['valid'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil!',
            'data' => [
                'waktu'  => $checkInTime,
                'tanggal'=> $now->format('d-m-Y'),
                'kantor' => $this->officeName,
                'status' => 'Hadir',
                'ip'     => $wifi['ip'],
                'is_valid_wifi' => $wifi['valid'],
                'server_timestamp_ms' => $now->getTimestampMs(),
            ],
        ]);
    }

    // Karyawan Check-out
    public function checkOut(Request $request)
    {
        $request->validate([
            'ssid'  => 'nullable|string',
            'bssid' => 'nullable|string',
        ]);

        $user  = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        $absensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

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

        // Validasi koneksi WiFi kantor — WAJIB terhubung ke WiFi kantor
        $wifi = $this->validateOfficeConnection($request);

        if (!$wifi['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi ditolak! Anda tidak terhubung ke WiFi kantor "' . $this->officeSsid . '". '
                    . 'Silakan hubungkan perangkat Anda ke WiFi kantor terlebih dahulu, lalu coba lagi.',
                'ip' => $wifi['ip'],
            ], 403);
        }

        // Waktu SELALU diambil dari jam server agar realtime & tidak bisa dimanipulasi
        $now = Carbon::now($this->officeTimezone);
        $checkInTime = Carbon::parse($absensi->check_in);

        // Hitung total jam kerja (dibulatkan ke jam terdekat, kolom bertipe integer)
        $totalJamKerja = (int) round($checkInTime->diffInMinutes($now) / 60);

        $absensi->total_jam_kerja = $totalJamKerja;
        $absensi->check_out       = $now;
        $absensi->ip_address      = $wifi['ip'];
        $absensi->mac_address     = $wifi['bssid'] ?: $absensi->mac_address;
        $absensi->is_valid_wifi   = $wifi['valid'];
        $absensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil!',
            'data' => [
                'waktu'    => $now->format('H:i:s'),
                'tanggal'  => $now->format('d-m-Y'),
                'total_jam'=> $totalJamKerja,
                'kantor'   => $absensi->kantor_cabang,
                'ip'       => $wifi['ip'],
                'is_valid_wifi' => $wifi['valid'],
                'server_timestamp_ms' => $now->getTimestampMs(),
            ],
        ]);
    }

    // Cek Status Absensi Hari Ini (juga dipakai untuk sinkronisasi jam realtime)
    public function status()
    {
        $user  = Auth::user();
        $today = Carbon::today($this->officeTimezone);

        $absensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $now = Carbon::now($this->officeTimezone);
        $todayName = $now->locale('id')->isoFormat('dddd');

        return response()->json([
            'success' => true,
            'data' => [
                'check_in'    => $absensi && $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i:s') : null,
                'check_out'   => $absensi && $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i:s') : null,
                'status'      => $absensi ? $absensi->status : 'Belum Absen',
                'kantor'      => $absensi ? $absensi->kantor_cabang : null,
                'total_jam'   => $absensi ? $absensi->total_jam_kerja : 0,
                'tanggal'     => $today->format('d-m-Y'),
                'hari'        => $todayName,
                'is_valid_wifi' => $absensi ? $absensi->is_valid_wifi : false,
                'ip_address'  => $absensi ? $absensi->ip_address : null,
                // Data untuk sinkronisasi jam realtime di sisi client
                'server_timestamp_ms' => $now->getTimestampMs(),
                'server_time_iso'     => $now->toIso8601String(),
            ],
        ]);
    }

    // HR View Absensi dengan Filter
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

    // Dashboard Karyawan
    public function dashboard()
    {
        $user  = Auth::user();
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
                'tanggal'   => $date->format('d/m'),
                'check_in'  => $absensi && $absensi->check_in ? Carbon::parse($absensi->check_in)->format('H:i') : '-',
                'check_out' => $absensi && $absensi->check_out ? Carbon::parse($absensi->check_out)->format('H:i') : '-',
                'status'    => $absensi ? $absensi->status : 'Alpha',
                'total_jam' => $absensi ? $absensi->total_jam_kerja : 0,
            ];
        }

        return view('karyawan.absensi', compact('todayAbsensi', 'last7Days'));
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
                'IP Address',
                'Valid WiFi',
                'Keterangan',
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
                    $absen->is_valid_wifi ? 'Ya' : 'Tidak',
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

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            'izin'  => $absensis->where('status', 'Izin')->count(),
            'sakit' => $absensis->where('status', 'Sakit')->count(),
            'alpha' => $absensis->where('status', 'Alpha')->count(),
            'total' => $absensis->count(),
        ];
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
            'status'     => 'required|in:Hadir,Izin,Sakit,Alpha',
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
