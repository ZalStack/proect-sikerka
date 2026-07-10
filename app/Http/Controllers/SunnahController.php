<?php

namespace App\Http\Controllers;

use App\Models\SunnahDaily;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SunnahController extends Controller
{
    // Dashboard Karyawan - 7SPS
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $todayData = SunnahDaily::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $monthlyData = SunnahDaily::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPoin = $monthlyData->sum('total_poin');
        $poinConfig = SunnahDaily::getPoinConfig();

        $statistik = [
            'total_hari' => $monthlyData->count(),
            'total_poin' => $totalPoin,
            'rata_rata' => $monthlyData->count() > 0 ? round($totalPoin / $monthlyData->count(), 1) : 0,
            'tertinggi' => $monthlyData->max('total_poin') ?? 0,
        ];

        $last30Days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data = SunnahDaily::where('karyawan_id', $user->id)
                ->whereDate('tanggal', $date)
                ->first();

            $last30Days[] = [
                'iso' => $date->format('Y-m-d'),
                'tanggal' => $date->format('d/m'),
                'poin' => $data ? $data->total_poin : 0,
                'status' => $data ? $data->status_label : 'Belum',
            ];
        }

        return view('karyawan.sunnah.dashboard', compact(
            'todayData',
            'monthlyData',
            'totalPoin',
            'poinConfig',
            'statistik',
            'last30Days',
            'month',
            'year',
            'today'
        ));
    }

    // Simpan checklist harian
    public function saveDaily(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Dapatkan semua field dari konfigurasi
        $config = SunnahDaily::getPoinConfig();
        $fields = array_keys($config);

        // Field yang diizinkan untuk dikirim
        $allowedFields = array_merge($fields, [
            'sholat_subuh_berjamaah',
            'sholat_zuhur_berjamaah',
            'sholat_asar_berjamaah',
            'sholat_maghrib_berjamaah',
            'sholat_isya_berjamaah'
        ]);

        $request->validate([
            'field_name' => 'required|string|in:' . implode(',', $fields),
        ]);

        $fieldName = $request->input('field_name');
        $fieldValue = $request->boolean($fieldName);

        // Ambil atau buat record hari ini
        $sunnah = SunnahDaily::firstOrNew([
            'karyawan_id' => $user->id,
            'tanggal' => $today->format('Y-m-d'),
        ]);

        // Jika record baru, set default semua field false
        if (!$sunnah->exists) {
            foreach ($fields as $field) {
                $sunnah->$field = false;
            }
            // Set default berjamaah false
            $jamaahFields = ['sholat_subuh_berjamaah', 'sholat_zuhur_berjamaah', 'sholat_asar_berjamaah', 'sholat_maghrib_berjamaah', 'sholat_isya_berjamaah'];
            foreach ($jamaahFields as $jf) {
                $sunnah->$jf = false;
            }
        }

        // Cek jika sudah approved
        if ($sunnah->exists && $sunnah->status_approval === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Data hari ini sudah disetujui HR dan tidak dapat diubah lagi.',
            ], 403);
        }

        // Update field yang dikirim
        $sunnah->$fieldName = $fieldValue;
        $sunnah->karyawan_id = $user->id;
        $sunnah->tanggal = $today->format('Y-m-d');

        // Cek apakah field ini adalah sholat wajib yang memiliki berjamaah
        $config = SunnahDaily::getPoinConfig();
        if (isset($config[$fieldName]) && ($config[$fieldName]['has_jamaah'] ?? false)) {
            // Jika checkbox sholat dicentang, tampilkan opsi berjamaah di modal
            // Data berjamaah akan dikirim via form
            $jamaahKey = $fieldName . '_berjamaah';
            if ($request->has($jamaahKey)) {
                $sunnah->$jamaahKey = $request->boolean($jamaahKey);
            } else {
                // Jika tidak ada data berjamaah, default false
                $sunnah->$jamaahKey = false;
            }
        }

        // Hitung total poin dari semua checklist
        $currentData = [];
        foreach ($fields as $field) {
            $currentData[$field] = (bool) $sunnah->$field;
        }
        // Tambahkan data berjamaah untuk perhitungan
        $jamaahFields = ['sholat_subuh_berjamaah', 'sholat_zuhur_berjamaah', 'sholat_asar_berjamaah', 'sholat_maghrib_berjamaah', 'sholat_isya_berjamaah'];
        foreach ($jamaahFields as $jf) {
            $currentData[$jf] = (bool) $sunnah->$jf;
        }

        $sunnah->total_poin = SunnahDaily::calculateTotalPoin($currentData);
        $sunnah->status_approval = 'pending';
        $sunnah->save();

        // Refresh data untuk response
        $sunnah->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Checklist berhasil disimpan!',
            'data' => [
                'total_poin' => $sunnah->total_poin,
                'status' => $sunnah->status_label,
                'status_approval' => $sunnah->status_approval,
                'checklist' => $currentData,
            ]
        ]);
    }

    // HR View - Monitoring 7SPS
    public function index(Request $request)
    {
        $query = SunnahDaily::with('karyawan');

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)
                  ->whereYear('tanggal', $request->year);
        } else {
            $request->merge([
                'month' => date('m'),
                'year' => date('Y')
            ]);
            $query->whereMonth('tanggal', date('m'))
                  ->whereYear('tanggal', date('Y'));
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        if ($request->filled('status')) {
            $query->where('status_approval', $request->status);
        }

        $sunnahData = (clone $query)->orderBy('tanggal', 'desc')->paginate(15);
        $karyawans = Karyawan::all();

        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');

        $filteredData = (clone $query)->get();
        $statistik = [
            'total' => $filteredData->count(),
            'pending' => $filteredData->where('status_approval', 'pending')->count(),
            'approved' => $filteredData->where('status_approval', 'approved')->count(),
            'rejected' => $filteredData->where('status_approval', 'rejected')->count(),
            'total_poin' => $filteredData->sum('total_poin'),
        ];

        return view('hr.sunnah.index', compact('sunnahData', 'karyawans', 'statistik', 'month', 'year'));
    }

    // HR View - Rekap Bulanan
    public function rekapBulanan(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $rekap = SunnahDaily::rekapPerKaryawan($month, $year);
        $totalPoinKeseluruhan = $rekap->sum('total_poin');
        $totalKaryawanAktif = $rekap->where('total_hari', '>', 0)->count();

        return view('hr.sunnah.rekap', compact(
            'rekap',
            'month',
            'year',
            'totalPoinKeseluruhan',
            'totalKaryawanAktif'
        ));
    }

    // HR Detail
    public function detail($id)
    {
        $sunnah = SunnahDaily::with('karyawan')->findOrFail($id);
        $poinConfig = SunnahDaily::getPoinConfig();
        return view('hr.sunnah.detail', compact('sunnah', 'poinConfig'));
    }

    // HR Approve/Reject
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'catatan_hr' => 'nullable|string',
        ]);

        $sunnah = SunnahDaily::findOrFail($id);
        $sunnah->status_approval = $request->status;
        $sunnah->catatan_hr = $request->catatan_hr;
        $sunnah->save();

        $statusLabel = $request->status === 'approved' ? 'Disetujui' : 'Ditolak';

        return redirect()->route('hr.sunnah.index')
            ->with('success', "Status approval berhasil diubah menjadi {$statusLabel}");
    }
}
