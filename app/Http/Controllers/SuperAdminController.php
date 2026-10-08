<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Cuti;
use App\Models\PerjalananDinas;
use App\Models\Perizinan;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    /**
     * Dashboard Utama Super Admin
     */
    public function dashboard()
    {
        $today = Carbon::today()->toDateString();

        // 1. Statistik Karyawan & Presensi Hari Ini
        $totalKaryawan = Karyawan::active()->count();
        $totalSuperadmin = Karyawan::where('posisi', 'superadmin')->count();
        $totalHr = Karyawan::where('posisi', 'hr')->count();

        $absensiHariIni = Absensi::where('tanggal', $today)->get();
        $hadirHariIni = $absensiHariIni->where('status', 'Hadir')->count();
        $izinHariIni = $absensiHariIni->where('status', 'Izin')->count();
        $sakitHariIni = $absensiHariIni->where('status', 'Sakit')->count();
        $cutiHariIni = $absensiHariIni->where('status', 'Cuti')->count();
        $dinasHariIni = $absensiHariIni->where('status', 'Perjalanan Dinas')->count();
        $alphaHariIni = max(0, $totalKaryawan - ($hadirHariIni + $izinHariIni + $sakitHariIni + $cutiHariIni + $dinasHariIni));

        $terlambatHariIni = $absensiHariIni->filter(function ($abs) {
            return $abs->is_terlambat;
        })->count();

        // 2. Status Maintenance & Fitur
        $maintenanceInfo = SystemSetting::getMaintenanceInfo();
        $features = SystemSetting::getFeatureList();
        $activeFeaturesCount = collect($features)->where('is_enabled', true)->count();
        $totalFeaturesCount = count($features);

        // 3. Presensi Terbaru yang Memerlukan Perhatian / Baru Masuk
        $recentAbsensi = Absensi::with('karyawan')
            ->orderByDesc('tanggal')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        // 4. Pengajuan Pending
        $pendingCuti = Cuti::where('status', 'pending')->count();
        $pendingDinas = PerjalananDinas::where('status', 'pending')->count();
        $pendingIzin = Perizinan::where('status', 'pending')->count();

        return view('superadmin.dashboard', compact(
            'totalKaryawan',
            'totalSuperadmin',
            'totalHr',
            'hadirHariIni',
            'izinHariIni',
            'sakitHariIni',
            'cutiHariIni',
            'dinasHariIni',
            'alphaHariIni',
            'terlambatHariIni',
            'maintenanceInfo',
            'features',
            'activeFeaturesCount',
            'totalFeaturesCount',
            'recentAbsensi',
            'pendingCuti',
            'pendingDinas',
            'pendingIzin'
        ));
    }

    /**
     * Halaman Pengaturan Fitur & Maintenance
     */
    public function featuresIndex()
    {
        $maintenanceInfo = SystemSetting::getMaintenanceInfo();
        $features = SystemSetting::getFeatureList();
        $allEnabled = collect($features)->every(fn($f) => $f['is_enabled'] === true);
        $allDisabled = collect($features)->every(fn($f) => $f['is_enabled'] === false);

        return view('superadmin.features', compact('maintenanceInfo', 'features', 'allEnabled', 'allDisabled'));
    }

    /**
     * Toggle status mode maintenance (AJAX or Form)
     */
    public function maintenanceToggle(Request $request)
    {
        $currentStatus = SystemSetting::isMaintenance();
        $newStatus = $request->has('status') ? (bool) $request->status : !$currentStatus;

        SystemSetting::setMaintenance(
            $newStatus,
            $request->input('title'),
            $request->input('message'),
            $request->input('end_time')
        );

        $statusText = $newStatus ? 'diaktifkan (Sistem Offline untuk Karyawan/HR)' : 'dinonaktifkan (Sistem Kembali Normal)';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_maintenance' => $newStatus,
                'message' => 'Mode Pemeliharaan Sistem berhasil ' . $statusText . '.',
                'info' => SystemSetting::getMaintenanceInfo(),
            ]);
        }

        return redirect()->back()->with('success', 'Mode Pemeliharaan Sistem berhasil ' . $statusText . '.');
    }

    /**
     * Update detail konfigurasi maintenance
     */
    public function maintenanceUpdate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string|max:1000',
            'end_time' => 'nullable|string|max:100',
            'is_maintenance' => 'nullable|boolean',
        ]);

        $isMaintenance = $request->has('is_maintenance') ? (bool) $request->is_maintenance : SystemSetting::isMaintenance();

        SystemSetting::setMaintenance(
            $isMaintenance,
            $request->title,
            $request->message,
            $request->end_time ?? 'Segera Kembali'
        );

        return redirect()->back()->with('success', 'Pengaturan halaman pemeliharaan sistem berhasil diperbarui.');
    }

    /**
     * Toggle satu fitur tertentu (AJAX or Form)
     */
    public function featureToggle(Request $request, $feature)
    {
        if (!array_key_exists($feature, SystemSetting::AVAILABLE_FEATURES)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Fitur tidak valid.'], 404);
            }
            return redirect()->back()->with('error', 'Fitur tidak ditemukan.');
        }

        $currentStatus = SystemSetting::isFeatureEnabled($feature);
        $newStatus = $request->has('status') ? (bool) $request->status : !$currentStatus;

        SystemSetting::setFeature($feature, $newStatus);
        $meta = SystemSetting::AVAILABLE_FEATURES[$feature];
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'feature' => $feature,
                'is_enabled' => $newStatus,
                'message' => "Fitur {$meta['name']} berhasil {$statusText}.",
            ]);
        }

        return redirect()->back()->with('success', "Fitur {$meta['name']} berhasil {$statusText}.");
    }

    /**
     * Master Switch: Aktifkan / Matikan SEMUA fitur sekaligus
     */
    public function featureToggleAll(Request $request)
    {
        $enable = $request->input('action') === 'enable_all' || $request->input('status') == '1' || $request->input('status') === true;

        SystemSetting::setAllFeatures($enable);
        $statusText = $enable ? 'seluruhnya diaktifkan' : 'seluruhnya dinonaktifkan';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_all_enabled' => $enable,
                'message' => "Semua fitur sistem berhasil {$statusText}.",
            ]);
        }

        return redirect()->back()->with('success', "Semua fitur sistem berhasil {$statusText}.");
    }

    /**
     * Halaman Kelola & Koreksi Presensi Karyawan
     */
    public function absensiIndex(Request $request)
    {
        $karyawans = Karyawan::active()->orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'kode_pegawai', 'divisi', 'jabatan']);
        $divisis = Karyawan::whereNotNull('divisi')->where('divisi', '!=', '')->distinct()->pluck('divisi');
        $kantorLocations = array_keys(Absensi::getOfficeLocations());

        // Default tanggal: bulan berjalan jika filter tanggal tidak dispesifikasikan
        $selectedMonth = $request->input('month', Carbon::now()->month);
        $selectedYear = $request->input('year', Carbon::now()->year);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $karyawanId = $request->input('karyawan_id');
        $divisi = $request->input('divisi');
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Absensi::with('karyawan');

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('tanggal', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('tanggal', '<=', $endDate);
        } else {
            $query->whereMonth('tanggal', $selectedMonth)->whereYear('tanggal', $selectedYear);
        }

        if ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($divisi) {
            $query->whereHas('karyawan', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('karyawan', function ($sub) use ($search) {
                    $sub->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('kode_pegawai', 'like', "%{$search}%");
                })->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('kantor_cabang', 'like', "%{$search}%");
            });
        }

        // Summary KPI dari query filter yang sama
        $summaryQuery = clone $query;
        $allFiltered = $summaryQuery->get();
        $totalRecords = $allFiltered->count();
        $totalJamSemua = $allFiltered->sum('total_jam_kerja');
        $avgJam = $totalRecords > 0 ? round($totalJamSemua / $totalRecords, 1) : 0;
        $totalHadir = $allFiltered->where('status', 'Hadir')->count();
        $totalTerlambat = $allFiltered->filter(fn($a) => $a->is_terlambat)->count();

        $absensis = $query->orderByDesc('tanggal')->orderByDesc('check_in')->paginate(20)->withQueryString();

        return view('superadmin.absensi', compact(
            'absensis',
            'karyawans',
            'divisis',
            'kantorLocations',
            'selectedMonth',
            'selectedYear',
            'startDate',
            'endDate',
            'karyawanId',
            'divisi',
            'status',
            'search',
            'totalRecords',
            'totalJamSemua',
            'avgJam',
            'totalHadir',
            'totalTerlambat'
        ));
    }

    /**
     * Tambah data presensi manual oleh Super Admin
     */
    public function absensiStore(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'tanggal' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpha,Perjalanan Dinas,Cuti',
            'kantor_cabang' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $existing = Absensi::where('karyawan_id', $request->karyawan_id)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Data presensi karyawan tersebut pada tanggal ' . Carbon::parse($request->tanggal)->translatedFormat('d F Y') . ' sudah ada. Silakan gunakan tombol edit.');
        }

        $tanggalStr = Carbon::parse($request->tanggal)->format('Y-m-d');
        $checkInDatetime = $request->filled('check_in') ? Carbon::parse($tanggalStr . ' ' . $request->check_in . ':00') : null;
        $checkOutDatetime = $request->filled('check_out') ? Carbon::parse($tanggalStr . ' ' . $request->check_out . ':00') : null;

        // Otomatis hitung total jam kerja
        $totalJamKerja = 0;
        if ($checkInDatetime && $checkOutDatetime) {
            $totalJamKerja = Absensi::calculateTotalJamKerja($checkInDatetime, $checkOutDatetime, $tanggalStr);
        }

        $absensi = new Absensi();
        $absensi->karyawan_id = $request->karyawan_id;
        $absensi->tanggal = $tanggalStr;
        $absensi->check_in = $checkInDatetime;
        $absensi->check_out = $checkOutDatetime;
        $absensi->status = $request->status;
        $absensi->kantor_cabang = $request->kantor_cabang ?? 'KPM LALADON';
        $absensi->keterangan = $request->keterangan ?? 'Diinput manual oleh Super Admin';
        $absensi->total_jam_kerja = $totalJamKerja;
        $absensi->is_valid_location = true;
        $absensi->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data presensi berhasil ditambahkan.',
                'data' => $absensi,
            ]);
        }

        return redirect()->back()->with('success', 'Data presensi berhasil ditambahkan beserta kalkulasi otomatis jam kerja (' . $totalJamKerja . ' Jam).');
    }

    /**
     * Update / Ubah Jam Masuk, Jam Pulang, & Data Presensi Karyawan
     * Otomatis mengkalkulasi dan memasukkan Total Jam Kerja ke database.
     */
    public function absensiUpdate(Request $request, $id)
    {
        $absensi = Absensi::findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date',
            'check_in' => 'nullable|string',
            'check_out' => 'nullable|string',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpha,Perjalanan Dinas,Cuti',
            'kantor_cabang' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $tanggalStr = Carbon::parse($request->tanggal)->format('Y-m-d');
        
        // Parse jam masuk
        $checkInDatetime = null;
        if ($request->filled('check_in')) {
            $cleanIn = trim($request->check_in);
            if (strlen($cleanIn) === 5) $cleanIn .= ':00';
            $checkInDatetime = Carbon::parse($tanggalStr . ' ' . $cleanIn);
        }

        // Parse jam pulang
        $checkOutDatetime = null;
        if ($request->filled('check_out')) {
            $cleanOut = trim($request->check_out);
            if (strlen($cleanOut) === 5) $cleanOut .= ':00';
            $checkOutDatetime = Carbon::parse($tanggalStr . ' ' . $cleanOut);
        }

        // OTOMATIS HITUNG TOTAL JAM KERJA
        $totalJamKerja = 0;
        $durasiTeks = '0 Jam';
        if ($checkInDatetime && $checkOutDatetime) {
            $totalJamKerja = Absensi::calculateTotalJamKerja($checkInDatetime, $checkOutDatetime, $tanggalStr);
            $durasiTeks = Absensi::formatDurasiKerja($checkInDatetime, $checkOutDatetime, $tanggalStr);
        }

        $absensi->tanggal = $tanggalStr;
        $absensi->check_in = $checkInDatetime;
        $absensi->check_out = $checkOutDatetime;
        $absensi->status = $request->status;
        if ($request->filled('kantor_cabang')) {
            $absensi->kantor_cabang = $request->kantor_cabang;
        }
        $absensi->keterangan = $request->keterangan;
        $absensi->total_jam_kerja = $totalJamKerja;
        $absensi->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Presensi berhasil diperbarui. Jam Masuk: " . ($checkInDatetime ? $checkInDatetime->format('H:i') : '-') . ", Jam Pulang: " . ($checkOutDatetime ? $checkOutDatetime->format('H:i') : '-') . ", Total Jam Kerja: {$totalJamKerja} Jam ({$durasiTeks}).",
                'data' => [
                    'id' => $absensi->id,
                    'check_in' => $checkInDatetime ? $checkInDatetime->format('H:i') : null,
                    'check_out' => $checkOutDatetime ? $checkOutDatetime->format('H:i') : null,
                    'total_jam_kerja' => $totalJamKerja,
                    'durasi_teks' => $durasiTeks,
                    'status' => $absensi->status,
                    'kantor_cabang' => $absensi->kantor_cabang,
                    'keterangan' => $absensi->keterangan,
                ],
            ]);
        }

        return redirect()->back()->with('success', "Presensi {$absensi->karyawan->nama_lengkap} berhasil diperbarui! Jam Masuk: " . ($checkInDatetime ? $checkInDatetime->format('H:i') : '-') . " | Jam Pulang: " . ($checkOutDatetime ? $checkOutDatetime->format('H:i') : '-') . " | Total Jam Kerja Otomatis: {$totalJamKerja} Jam ({$durasiTeks}).");
    }

    /**
     * Hapus data presensi
     */
    public function absensiDestroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $nama = $absensi->karyawan->nama_lengkap ?? 'Karyawan';
        $tgl = $absensi->tanggal ? $absensi->tanggal->translatedFormat('d M Y') : '';
        $absensi->delete();

        return redirect()->back()->with('success', "Data presensi {$nama} tanggal {$tgl} berhasil dihapus.");
    }

    /**
     * Manajemen Role Pengguna (Superadmin / HR / Karyawan)
     */
    public function karyawanIndex(Request $request)
    {
        $search = $request->input('search');
        $posisi = $request->input('posisi');
        $status = $request->input('status');

        $query = Karyawan::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('kode_pegawai', 'like', "%{$search}%")
                  ->orWhere('divisi', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if ($posisi) {
            $query->where('posisi', $posisi);
        }

        if ($status === 'active') {
            $query->where('is_resigned', false);
        } elseif ($status === 'resigned') {
            $query->where('is_resigned', true);
        }

        $karyawans = $query->orderBy('posisi')->orderBy('nama_lengkap')->paginate(20)->withQueryString();

        $stats = [
            'total' => Karyawan::count(),
            'superadmin' => Karyawan::where('posisi', 'superadmin')->count(),
            'hr' => Karyawan::where('posisi', 'hr')->count(),
            'karyawan' => Karyawan::where('posisi', 'karyawan')->count(),
        ];

        return view('superadmin.karyawan', compact('karyawans', 'stats', 'search', 'posisi', 'status'));
    }

    /**
     * Ubah Role Pengguna
     */
    public function karyawanUpdateRole(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $request->validate([
            'posisi' => 'required|in:superadmin,hr,karyawan',
        ]);

        $oldRole = $karyawan->posisi;
        $newRole = $request->posisi;

        // Cegah mencopot diri sendiri jika satu-satunya superadmin
        if ($oldRole === 'superadmin' && $newRole !== 'superadmin') {
            $superadminCount = Karyawan::where('posisi', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return redirect()->back()->with('error', 'Tidak dapat mengubah role karena ini adalah satu-satunya Super Admin di sistem.');
            }
        }

        $karyawan->posisi = $newRole;
        $karyawan->save();

        $roleLabels = [
            'superadmin' => 'Super Administrator',
            'hr' => 'HR / Administrator',
            'karyawan' => 'Karyawan',
        ];

        return redirect()->back()->with('success', "Role untuk {$karyawan->nama_lengkap} berhasil diubah menjadi {$roleLabels[$newRole]}.");
    }
}
