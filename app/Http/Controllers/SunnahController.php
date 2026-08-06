<?php
// app/Http/Controllers/SunnahController.php

namespace App\Http\Controllers;

use App\Models\SunnahDaily;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SunnahController extends Controller
{
    // Dashboard Karyawan - 7SPS
    public function dashboard(Request $request)
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $month = Carbon::now()->month;
            $year = Carbon::now()->year;

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
                'selectedDate'
            ));
        } catch (\Exception $e) {
            Log::error('Error in SunnahController@dashboard: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Simpan checklist harian
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

            $sunnah = SunnahDaily::firstOrNew([
                'karyawan_id' => $user->id,
                'tanggal' => $tanggal->format('Y-m-d'),
            ]);

            if (!$sunnah->exists) {
                foreach ($fields as $field) {
                    $sunnah->$field = false;
                }
                $jamaahFields = ['sholat_subuh_berjamaah', 'sholat_zuhur_berjamaah', 'sholat_asar_berjamaah', 'sholat_maghrib_berjamaah', 'sholat_isya_berjamaah'];
                foreach ($jamaahFields as $jf) {
                    $sunnah->$jf = false;
                }
            }

            if ($sunnah->exists && $sunnah->status_approval === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tanggal ini sudah disetujui HR dan tidak dapat diubah lagi.',
                ], 403);
            }

            $sunnah->$fieldName = $fieldValue;
            $sunnah->karyawan_id = $user->id;
            $sunnah->tanggal = $tanggal->format('Y-m-d');

            if (isset($config[$fieldName]) && ($config[$fieldName]['has_jamaah'] ?? false)) {
                $jamaahKey = $fieldName . '_berjamaah';
                if ($request->has($jamaahKey)) {
                    $sunnah->$jamaahKey = $request->boolean($jamaahKey);
                } elseif (!$fieldValue) {
                    $sunnah->$jamaahKey = false;
                }
            }

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

            if (!$sunnah->status_approval || $sunnah->status_approval === '') {
                $sunnah->status_approval = 'pending';
            }

            $sunnah->save();
            $sunnah->refresh();

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
            Log::error('Error in SunnahController@saveDaily: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ], 500);
        }
    }

    // HR View - Monitoring 7SPS
    public function index(Request $request)
    {
        try {
            $query = SunnahDaily::with('karyawan');

            // Filter berdasarkan rentang tanggal
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

            if ($startDate && $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('tanggal', '<=', $endDate);
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

            $allSunnahData = (clone $query)->orderBy('tanggal', 'desc')->get();

            $statistik = [
                'total' => $allSunnahData->count(),
                'pending' => $allSunnahData->where('status_approval', 'pending')->count(),
                'approved' => $allSunnahData->where('status_approval', 'approved')->count(),
                'rejected' => $allSunnahData->where('status_approval', 'rejected')->count(),
                'total_poin' => $allSunnahData->sum('total_poin'),
            ];

            $sunnahData = (clone $query)
                ->orderBy('tanggal', 'desc')
                ->paginate(10)
                ->withQueryString();

            $groupedData = collect($sunnahData->items())
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

            $defaultStartDate = $request->filled('start_date') ? $request->start_date : Carbon::today()->subDays(6)->format('Y-m-d');
            $defaultEndDate = $request->filled('end_date') ? $request->end_date : Carbon::today()->format('Y-m-d');

            return view('hr.sunnah.index', compact(
                'groupedData',
                'karyawans',
                'statistik',
                'divisiList',
                'sunnahData',
                'defaultStartDate',
                'defaultEndDate'
            ));
        } catch (\Exception $e) {
            Log::error('Error in SunnahController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // HR View - Rekap Divisi
    public function rekapDivisi(Request $request)
    {
        try {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

            $query = SunnahDaily::with('karyawan');

            if ($startDate && $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('tanggal', '<=', $endDate);
            }

            // Total poin per karyawan
            $poinPerKaryawan = $query->selectRaw('karyawan_id, SUM(total_poin) as total_poin')
                ->groupBy('karyawan_id')
                ->pluck('total_poin', 'karyawan_id');

            $karyawans = Karyawan::whereNotNull('divisi')
                ->where('divisi', '!=', '')
                ->where('is_resigned', false)
                ->get();

            $divisiRanking = $karyawans
                ->groupBy('divisi')
                ->map(function ($anggota, $divisi) use ($poinPerKaryawan) {
                    $jumlahAnggota = $anggota->count();
                    $totalPoinDivisi = $anggota->sum(function ($k) use ($poinPerKaryawan) {
                        return $poinPerKaryawan->get($k->id, 0);
                    });

                    return [
                        'divisi' => $divisi,
                        'jumlah_anggota' => $jumlahAnggota,
                        'total_poin' => $totalPoinDivisi,
                        'rata_rata_poin' => $jumlahAnggota > 0 ? round($totalPoinDivisi / $jumlahAnggota, 1) : 0,
                    ];
                })
                ->sortByDesc('rata_rata_poin')
                ->values();

            $defaultStartDate = $request->filled('start_date') ? $request->start_date : Carbon::today()->subDays(29)->format('Y-m-d');
            $defaultEndDate = $request->filled('end_date') ? $request->end_date : Carbon::today()->format('Y-m-d');

            return view('hr.sunnah.rekap-divisi', compact(
                'divisiRanking',
                'defaultStartDate',
                'defaultEndDate'
            ));
        } catch (\Exception $e) {
            Log::error('Error in SunnahController@rekapDivisi: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // HR View - Rekap Bulanan per Karyawan
    public function rekapBulanan(Request $request)
    {
        try {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

            $query = SunnahDaily::with('karyawan');

            if ($startDate && $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('tanggal', '<=', $endDate);
            }

            $allData = $query->get();

            $rekapData = $allData->groupBy('karyawan_id')->map(function ($items) {
                $karyawan = $items->first()->karyawan;
                $totalHari = $items->count();
                $totalPoin = $items->sum('total_poin');

                return [
                    'karyawan_id' => $karyawan->id,
                    'nama_lengkap' => $karyawan->nama_lengkap,
                    'kode_pegawai' => $karyawan->kode_pegawai ?? '-',
                    'divisi' => $karyawan->divisi ?? '-',
                    'total_hari' => $totalHari,
                    'total_poin' => $totalPoin,
                    'rata_rata' => $totalHari > 0 ? round($totalPoin / $totalHari, 1) : 0,
                    'approved' => $items->where('status_approval', 'approved')->count(),
                    'pending' => $items->where('status_approval', 'pending')->count(),
                    'rejected' => $items->where('status_approval', 'rejected')->count(),
                ];
            })->sortByDesc('total_poin')->values();

            // Filter: hanya yang sudah mengisi
            if ($request->filled('has_filled') && $request->has_filled == '1') {
                $rekapData = $rekapData->filter(function ($item) {
                    return $item['total_hari'] > 0;
                })->values();
            }

            $perPage = 10;
            $currentPage = LengthAwarePaginator::resolveCurrentPage() ?: 1;
            $items = $rekapData->forPage($currentPage, $perPage)->values();

            $rekap = new LengthAwarePaginator(
                $items,
                $rekapData->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            $totalPoinBulanan = $rekapData->sum('total_poin');
            $totalKaryawanAktif = $rekapData->where('total_hari', '>', 0)->count();

            $defaultStartDate = $request->filled('start_date') ? $request->start_date : Carbon::today()->subDays(29)->format('Y-m-d');
            $defaultEndDate = $request->filled('end_date') ? $request->end_date : Carbon::today()->format('Y-m-d');

            return view('hr.sunnah.rekap', compact(
                'rekap',
                'totalPoinBulanan',
                'totalKaryawanAktif',
                'defaultStartDate',
                'defaultEndDate'
            ));
        } catch (\Exception $e) {
            Log::error('Error in SunnahController@rekapBulanan: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // HR Detail
    public function detail($id)
    {
        try {
            $sunnah = SunnahDaily::with('karyawan')->findOrFail($id);
            $poinConfig = SunnahDaily::getPoinConfig();
            $sholatWajibKeys = SunnahDaily::getSholatWajibKeys();
            return view('hr.sunnah.detail', compact('sunnah', 'poinConfig', 'sholatWajibKeys'));
        } catch (\Exception $e) {
            Log::error('Error in SunnahController@detail: ' . $e->getMessage());
            return redirect()->route('hr.sunnah.index')->with('error', 'Data tidak ditemukan');
        }
    }

    // HR Approve/Reject
    public function approve(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected,pending',
                'catatan_hr' => 'nullable|string',
            ]);

            $sunnah = SunnahDaily::findOrFail($id);

            $tanggalData = Carbon::parse($sunnah->tanggal);
            $batasWaktu = Carbon::today()->subDays(6)->startOfDay();

            if ($tanggalData->lessThan($batasWaktu)) {
                return redirect()->route('hr.sunnah.index')
                    ->with('error', 'Approval hanya dapat dilakukan untuk data 1 minggu terakhir!');
            }

            $sunnah->status_approval = $request->status;
            $sunnah->catatan_hr = $request->catatan_hr;
            $sunnah->save();

            $statusLabel = $request->status === 'approved' ? 'Disetujui' : ($request->status === 'rejected' ? 'Ditolak' : 'Menunggu');

            return redirect()->route('hr.sunnah.index')
                ->with('success', "Status approval berhasil diubah menjadi {$statusLabel}");
        } catch (\Exception $e) {
            Log::error('Error in SunnahController@approve: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // HR Approve/Reject (bulk)
    public function bulkApprove(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:sunnah_daily,id',
                'target_status' => 'required|in:approved,rejected,pending',
                'catatan_hr' => 'nullable|string',
            ]);

            $ids = $request->input('ids');

            $dataToUpdate = SunnahDaily::whereIn('id', $ids)->get();
            $batasWaktu = Carbon::today()->subDays(6)->startOfDay();

            $validIds = [];
            $expiredTanggal = [];

            foreach ($dataToUpdate as $data) {
                $tanggalData = Carbon::parse($data->tanggal);
                if ($tanggalData->greaterThanOrEqualTo($batasWaktu)) {
                    $validIds[] = $data->id;
                } else {
                    $expiredTanggal[] = $tanggalData->format('d-m-Y');
                }
            }

            if (empty($validIds)) {
                return redirect()->route('hr.sunnah.index')
                    ->with('error', 'Tidak ada data yang dapat di-approve karena semua data sudah melewati batas waktu approval (1 minggu terakhir).');
            }

            $warningMessage = '';
            if (!empty($expiredTanggal)) {
                $warningMessage = ' ' . count($expiredTanggal) . ' data tidak dapat di-approve karena sudah melewati batas waktu.';
            }

            $jumlah = SunnahDaily::whereIn('id', $validIds)
                ->update([
                    'status_approval' => $request->input('target_status'),
                    'catatan_hr' => $request->input('catatan_hr'),
                ]);

            $statusLabelMap = [
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'pending' => 'Menunggu',
            ];
            $statusLabel = $statusLabelMap[$request->input('target_status')];

            return redirect()->route('hr.sunnah.index')
                ->with('success', "{$jumlah} data berhasil diubah menjadi {$statusLabel} secara massal." . $warningMessage);
        } catch (\Exception $e) {
            Log::error('Error in SunnahController@bulkApprove: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
