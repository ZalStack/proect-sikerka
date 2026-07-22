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
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        // Karyawan hanya boleh mengisi/melihat checklist untuk hari ini atau kemarin
        $selectedDate = $today;
        if ($request->filled('tanggal')) {
            $requested = Carbon::parse($request->input('tanggal'))->startOfDay();
            if ($requested->isSameDay($yesterday)) {
                $selectedDate = $yesterday;
            }
        }

        $todayData = SunnahDaily::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $selectedDate)
            ->first();

        $monthlyData = SunnahDaily::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPoin = $monthlyData->sum('total_poin');
        $poinConfig = SunnahDaily::getPoinConfig();
        $sholatWajibKeys = SunnahDaily::getSholatWajibKeys();

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

        // Point milestone untuk motivasi
        $milestones = [
            40 => ['message' => 'Bangun tingkatkan lagi ibadahmu!', 'message_turun' => 'Poinmu turun di bawah 40%, ayo semangat lagi!', 'sound' => 'point-40.mp4', 'duration' => 2],
            75 => ['message' => 'Ayo sedikit lagi!', 'message_turun' => 'Poinmu turun di bawah 75%, jangan menyerah!', 'sound' => 'point-75.mp4', 'duration' => 2],
            90 => ['message' => 'Aku yakin kamu pasti bisa!', 'message_turun' => 'Poinmu turun di bawah 90%, sedikit lagi tadi!', 'sound' => 'point-90.mp4', 'duration' => 2],
            100 => ['message' => 'Wah hebat kamu mencapai suprasional!', 'message_turun' => 'Poinmu turun dari 100%, ayo kembali sempurnakan!', 'sound' => 'point-100.mp4', 'duration' => 3],
        ];

        return view('karyawan.sunnah.dashboard', compact(
            'todayData',
            'monthlyData',
            'totalPoin',
            'poinConfig',
            'sholatWajibKeys',
            'statistik',
            'last30Days',
            'month',
            'year',
            'today',
            'yesterday',
            'selectedDate',
            'milestones'
        ));
    }

    // Simpan checklist harian (langsung tersimpan tanpa modal konfirmasi)
    public function saveDaily(Request $request)
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();

            $config = SunnahDaily::getPoinConfig();
            $fields = array_keys($config);

            $request->validate([
                'field_name' => 'required|string|in:' . implode(',', $fields),
                'tanggal' => 'nullable|date',
            ]);

            // Karyawan hanya boleh mengisi untuk hari ini atau 1 hari kebelakang (kemarin)
            $tanggal = $today;
            if ($request->filled('tanggal')) {
                $parsed = Carbon::parse($request->input('tanggal'))->startOfDay();
                if ($parsed->isSameDay($yesterday)) {
                    $tanggal = $yesterday;
                } elseif ($parsed->isSameDay($today)) {
                    $tanggal = $today;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Checklist hanya dapat diisi untuk hari ini atau kemarin (H-1).',
                    ], 422);
                }
            }

            $fieldName = $request->input('field_name');
            $fieldValue = $request->boolean($fieldName);

            // Ambil atau buat record untuk tanggal terpilih
            $sunnah = SunnahDaily::firstOrNew([
                'karyawan_id' => $user->id,
                'tanggal' => $tanggal->format('Y-m-d'),
            ]);

            // Jika record baru, set default semua field false
            if (!$sunnah->exists) {
                foreach ($fields as $field) {
                    $sunnah->$field = false;
                }
                $jamaahFields = ['sholat_subuh_berjamaah', 'sholat_zuhur_berjamaah', 'sholat_asar_berjamaah', 'sholat_maghrib_berjamaah', 'sholat_isya_berjamaah'];
                foreach ($jamaahFields as $jf) {
                    $sunnah->$jf = false;
                }
            }

            // Cek jika sudah approved
            if ($sunnah->exists && $sunnah->status_approval === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tanggal ini sudah disetujui HR dan tidak dapat diubah lagi.',
                ], 403);
            }

            // Update field yang dikirim
            $sunnah->$fieldName = $fieldValue;
            $sunnah->karyawan_id = $user->id;
            $sunnah->tanggal = $tanggal->format('Y-m-d');

            // Cek apakah field ini adalah sholat wajib yang memiliki opsi berjamaah
            if (isset($config[$fieldName]) && ($config[$fieldName]['has_jamaah'] ?? false)) {
                $jamaahKey = $fieldName . '_berjamaah';
                if ($request->has($jamaahKey)) {
                    $sunnah->$jamaahKey = $request->boolean($jamaahKey);
                } elseif (!$fieldValue) {
                    // Jika checklist sholat dibatalkan, otomatis batalkan status berjamaahnya juga
                    $sunnah->$jamaahKey = false;
                }
            }

            // Hitung total poin dari semua checklist
            $currentData = [];
            foreach ($fields as $field) {
                $currentData[$field] = (bool) $sunnah->$field;
            }
            $jamaahFields = ['sholat_subuh_berjamaah', 'sholat_zuhur_berjamaah', 'sholat_asar_berjamaah', 'sholat_maghrib_berjamaah', 'sholat_isya_berjamaah'];
            foreach ($jamaahFields as $jf) {
                $currentData[$jf] = (bool) $sunnah->$jf;
            }

            $oldPoin = $sunnah->total_poin ?? 0;
            $newPoin = SunnahDaily::calculateTotalPoin($currentData);
            $sunnah->total_poin = $newPoin;

            // Jika status masih pending atau null, set ke pending
            if (!$sunnah->status_approval || $sunnah->status_approval === '') {
                $sunnah->status_approval = 'pending';
            }

            $sunnah->save();
            $sunnah->refresh();

            // Kirim response dengan data yang diperlukan untuk update UI
            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil disimpan',
                'data' => [
                    'total_poin' => $sunnah->total_poin,
                    'poin_sholat_wajib' => $sunnah->poin_sholat_wajib,
                    'jumlah_sholat_berjamaah' => $sunnah->jumlah_sholat_berjamaah,
                    'tanggal' => $sunnah->tanggal->format('Y-m-d'),
                    'status' => $sunnah->status_label,
                    'status_approval' => $sunnah->status_approval,
                    'old_poin' => $oldPoin,
                    'new_poin' => $newPoin,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ], 500);
        }
    }

    // HR View - Monitoring 7SPS
    public function index(Request $request)
    {
        $query = SunnahDaily::with('karyawan');

        $periode = $request->filled('periode') ? $request->input('periode') : null;
        $month = null;
        $year = null;

        if ($periode && array_key_exists($periode, SunnahDaily::getPeriodeOptions())) {
            $query->filterByPeriode($periode);
        } else {
            $periode = null;
            $month = $request->filled('month') ? $request->month : date('m');
            $year = $request->filled('year') ? $request->year : date('Y');
            $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        if ($request->filled('status')) {
            $query->where('status_approval', $request->status);
        }

        if ($request->filled('divisi')) {
            $divisi = $request->input('divisi');
            $query->whereHas('karyawan', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        $sunnahData = (clone $query)->orderBy('tanggal', 'desc')->get();

        $statistik = [
            'total' => $sunnahData->count(),
            'pending' => $sunnahData->where('status_approval', 'pending')->count(),
            'approved' => $sunnahData->where('status_approval', 'approved')->count(),
            'rejected' => $sunnahData->where('status_approval', 'rejected')->count(),
            'total_poin' => $sunnahData->sum('total_poin'),
        ];

        // Pengelompokan berdasarkan divisi karyawan
        $groupedData = $sunnahData
            ->groupBy(function ($item) {
                return $item->karyawan->divisi ?? 'Tanpa Divisi';
            })
            ->sortKeys();

        $karyawans = Karyawan::orderBy('nama_lengkap')->get();

        $divisiList = Karyawan::query()
            ->whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->distinct()
            ->orderBy('divisi')
            ->pluck('divisi');

        $periodeOptions = SunnahDaily::getPeriodeOptions();

        return view('hr.sunnah.index', compact(
            'groupedData',
            'karyawans',
            'statistik',
            'month',
            'year',
            'periode',
            'periodeOptions',
            'divisiList'
        ));
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
        $sholatWajibKeys = SunnahDaily::getSholatWajibKeys();
        return view('hr.sunnah.detail', compact('sunnah', 'poinConfig', 'sholatWajibKeys'));
    }

    // HR Approve/Reject (satuan)
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'catatan_hr' => 'nullable|string',
        ]);

        $sunnah = SunnahDaily::findOrFail($id);
        $sunnah->status_approval = $request->status;
        $sunnah->catatan_hr = $request->catatan_hr;
        $sunnah->save();

        $statusLabel = $request->status === 'approved' ? 'Disetujui' : ($request->status === 'rejected' ? 'Ditolak' : 'Menunggu');

        return redirect()->route('hr.sunnah.index')
            ->with('success', "Status approval berhasil diubah menjadi {$statusLabel}");
    }

    // HR Approve/Reject (bulk / massal)
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:sunnah_daily,id',
            'target_status' => 'required|in:approved,rejected',
            'catatan_hr' => 'nullable|string',
        ]);

        $ids = $request->input('ids');

        // Hanya update yang statusnya belum approved
        $jumlah = SunnahDaily::whereIn('id', $ids)
            ->where('status_approval', '!=', 'approved')
            ->update([
                'status_approval' => $request->input('target_status'),
                'catatan_hr' => $request->input('catatan_hr'),
            ]);

        $statusLabel = $request->input('target_status') === 'approved' ? 'Disetujui' : 'Ditolak';

        return redirect()->route('hr.sunnah.index', $request->only([
                'month', 'year', 'periode', 'karyawan_id', 'status', 'divisi',
            ]))
            ->with('success', "{$jumlah} data berhasil diubah menjadi {$statusLabel} secara massal");
    }
}
