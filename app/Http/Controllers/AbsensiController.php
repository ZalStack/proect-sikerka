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
    // Karyawan Check-in
    public function checkIn(Request $request)
    {
        $request->validate([
            'kantor_cabang' => 'required|in:KPM Pusat Laladon,KPM Cabang Semplak,KPM Pagelaran,KPM Cabang Poltangan,KPM Cabang Rawamangun',
            'check_in_manual' => 'nullable|date_format:H:i',
        ]);

        $user = Auth::user();
        $today = Carbon::today();

        // Cek apakah sudah check-in hari ini
        $existingAbsen = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existingAbsen && $existingAbsen->check_in) {
            return back()->with('error', 'Anda sudah melakukan check-in hari ini! Data check-in tidak dapat diubah!');
        }

        // Jika input manual
        if ($request->filled('check_in_manual')) {
            $checkInTime = Carbon::createFromFormat('H:i', $request->check_in_manual);
            $isManual = true;
        } else {
            $checkInTime = Carbon::now();
            $isManual = false;
        }

        // Cek telat (07:40)
        $checkInTimeCompare = Carbon::createFromTime(7, 40, 0);
        $isTelat = $checkInTime->gt($checkInTimeCompare);
        $menitTelat = $isTelat ? $checkInTime->diffInMinutes($checkInTimeCompare) : 0;

        $absensi = Absensi::updateOrCreate(
            [
                'karyawan_id' => $user->id,
                'tanggal' => $today,
            ],
            [
                'check_in' => $checkInTime,
                'kantor_cabang' => $request->kantor_cabang,
                'status' => 'Hadir',
                'is_telat' => $isTelat,
                'menit_telat' => $menitTelat,
                'is_manual_checkin' => $isManual,
            ]
        );

        return back()->with('success', 'Check-in berhasil!');
    }

    // Karyawan Check-out
    public function checkOut(Request $request)
    {
        $request->validate([
            'check_out_manual' => 'nullable|date_format:H:i',
        ]);

        $user = Auth::user();
        $today = Carbon::today();

        $absensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$absensi || !$absensi->check_in) {
            return back()->with('error', 'Anda belum melakukan check-in!');
        }

        if ($absensi->check_out) {
            return back()->with('error', 'Anda sudah melakukan check-out hari ini! Data check-out tidak dapat diubah!');
        }

        // Jika input manual
        if ($request->filled('check_out_manual')) {
            $checkOutTime = Carbon::createFromFormat('H:i', $request->check_out_manual);
            $isManual = true;
        } else {
            $checkOutTime = Carbon::now();
            $isManual = false;
        }

        $checkInTime = Carbon::parse($absensi->check_in);

        // Hitung total jam kerja
        $totalJamKerja = $checkInTime->diffInHours($checkOutTime);
        $absensi->total_jam_kerja = $totalJamKerja;

        // Cek lembur (lebih dari 8 jam)
        if ($totalJamKerja > 8) {
            $absensi->is_lembur = true;
            $absensi->jam_lembur = $totalJamKerja - 8;
        } else {
            $absensi->is_lembur = false;
            $absensi->jam_lembur = 0;
        }

        $absensi->check_out = $checkOutTime;
        $absensi->is_manual_checkout = $isManual;
        $absensi->save();

        return back()->with('success', 'Check-out berhasil! Total jam kerja: ' . $totalJamKerja . ' jam');
    }

    // HR View Absensi dengan Filter
    public function index(Request $request)
    {
        $query = Absensi::with('karyawan');

        // Filter berdasarkan tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        // Filter berdasarkan bulan dan tahun
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)
                  ->whereYear('tanggal', $request->year);
        }

        // Filter berdasarkan karyawan
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->orderBy('tanggal', 'desc')->paginate(15);
        $karyawans = Karyawan::all();

        // Data untuk grafik
        $chartData = $this->getChartData($request);
        $waveData = $this->getWaveData($request);

        return view('hr.absensi.index', compact('absensis', 'karyawans', 'chartData', 'waveData'));
    }

    // Dashboard Karyawan
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        // Absensi hari ini
        $todayAbsensi = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Statistik bulan ini
        $statistik = Absensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->select(
                DB::raw('COUNT(*) as total_hadir'),
                DB::raw('SUM(CASE WHEN is_telat = 1 THEN 1 ELSE 0 END) as total_telat'),
                DB::raw('SUM(CASE WHEN is_lembur = 1 THEN 1 ELSE 0 END) as total_lembur'),
                DB::raw('SUM(total_jam_kerja) as total_jam_kerja')
            )
            ->first();

        // Data grafik 7 hari terakhir
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

        $kantorCabang = [
            'KPM Pusat Laladon',
            'KPM Cabang Semplak',
            'KPM Pagelaran',
            'KPM Cabang Poltangan',
            'KPM Cabang Rawamangun'
        ];

        return view('karyawan.absensi', compact('todayAbsensi', 'statistik', 'last7Days', 'kantorCabang'));
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
            'telat' => $absensis->where('is_telat', true)->count(),
            'lembur' => $absensis->where('is_lembur', true)->count(),
            'total' => $absensis->count(),
        ];
    }

    // Get Wave Data untuk Grafik Gelombang
    private function getWaveData($request)
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

        $absensis = $query->orderBy('tanggal')->get();

        // Group by tanggal
        $grouped = $absensis->groupBy(function($item) {
            return $item->tanggal->format('Y-m-d');
        });

        $labels = [];
        $hadirData = [];
        $telatData = [];
        $lemburData = [];

        foreach ($grouped as $date => $items) {
            $labels[] = Carbon::parse($date)->format('d/m');
            $hadirData[] = $items->where('status', 'Hadir')->count();
            $telatData[] = $items->where('is_telat', true)->count();
            $lemburData[] = $items->where('is_lembur', true)->count();
        }

        return [
            'labels' => $labels,
            'hadir' => $hadirData,
            'telat' => $telatData,
            'lembur' => $lemburData,
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
                'NIP',
                'Tanggal',
                'Check In',
                'Check Out',
                'Kantor Cabang',
                'Status',
                'Total Jam Kerja',
                'Telat (menit)',
                'Lembur (jam)',
                'Manual Checkin',
                'Manual Checkout',
                'Keterangan'
            ]);

            $no = 1;
            foreach ($absensis as $absen) {
                fputcsv($file, [
                    $no++,
                    $absen->karyawan->nama_lengkap,
                    $absen->karyawan->nip,
                    $absen->tanggal->format('d-m-Y'),
                    $absen->check_in ? Carbon::parse($absen->check_in)->format('H:i') : '-',
                    $absen->check_out ? Carbon::parse($absen->check_out)->format('H:i') : '-',
                    $absen->kantor_cabang,
                    $absen->status,
                    $absen->total_jam_kerja . ' jam',
                    $absen->menit_telat,
                    $absen->jam_lembur,
                    $absen->is_manual_checkin ? 'Ya' : 'Tidak',
                    $absen->is_manual_checkout ? 'Ya' : 'Tidak',
                    $absen->keterangan ?? '-'
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN LAPORAN']);
            fputcsv($file, ['Total Karyawan Absen', $absensis->count()]);
            fputcsv($file, ['Total Hadir', $absensis->where('status', 'Hadir')->count()]);
            fputcsv($file, ['Total Izin', $absensis->where('status', 'Izin')->count()]);
            fputcsv($file, ['Total Sakit', $absensis->where('status', 'Sakit')->count()]);
            fputcsv($file, ['Total Alpha', $absensis->where('status', 'Alpha')->count()]);
            fputcsv($file, ['Total Telat', $absensis->where('is_telat', true)->count()]);
            fputcsv($file, ['Total Lembur', $absensis->where('is_lembur', true)->count()]);

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
}
