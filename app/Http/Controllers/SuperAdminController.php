<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Cuti;
use App\Models\PerjalananDinas;
use App\Models\Perizinan;
use App\Models\RemoteAccessViolation;
use App\Models\KaryawanMenuDisabled;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

    public function absensiUpdate(Request $request, $id)
    {
        $absensi = Absensi::with('karyawan')->findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date',
            'check_in' => 'nullable|string',
            'check_out' => 'nullable|string',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpha,Perjalanan Dinas,Cuti',
            'kantor_cabang' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $tanggalStr = Carbon::parse($request->tanggal)->format('Y-m-d');

        // Validasi Duplikasi: Cegah tabrakan data unik (karyawan_id + tanggal)
        $conflict = Absensi::where('karyawan_id', $absensi->karyawan_id)
            ->where('tanggal', $tanggalStr)
            ->where('id', '!=', $absensi->id)
            ->first();

        if ($conflict) {
            $formattedTgl = Carbon::parse($tanggalStr)->translatedFormat('d F Y');
            $nama = $absensi->karyawan->nama_lengkap ?? 'Karyawan';
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal update: Karyawan {$nama} sudah memiliki data presensi lain pada tanggal {$formattedTgl}.");
        }

        try {
            $cleanIn = null;
            if ($request->filled('check_in')) {
                $cleanIn = trim($request->check_in);
                if (strlen($cleanIn) === 5) $cleanIn .= ':00';
            }

            $cleanOut = null;
            if ($request->filled('check_out')) {
                $cleanOut = trim($request->check_out);
                if (strlen($cleanOut) === 5) $cleanOut .= ':00';
            }

            // OTOMATIS HITUNG TOTAL JAM KERJA
            $totalJamKerja = 0;
            $durasiTeks = '0 Jam';
            if ($cleanIn && $cleanOut) {
                $totalJamKerja = Absensi::calculateTotalJamKerja($cleanIn, $cleanOut, $tanggalStr);
                $durasiTeks = Absensi::formatDurasiKerja($cleanIn, $cleanOut, $tanggalStr);
            }

            $absensi->tanggal = $tanggalStr;
            $absensi->check_in = $cleanIn;
            $absensi->check_out = $cleanOut;
            $absensi->status = $request->status;
            if ($request->filled('kantor_cabang')) {
                $absensi->kantor_cabang = $request->kantor_cabang;
            }
            $absensi->keterangan = $request->keterangan;
            $absensi->total_jam_kerja = $totalJamKerja;
            $absensi->save();

            $namaKaryawan = $absensi->karyawan->nama_lengkap ?? 'Karyawan';
            $inDisplay = $cleanIn ? substr($cleanIn, 0, 5) : '-';
            $outDisplay = $cleanOut ? substr($cleanOut, 0, 5) : '-';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Presensi {$namaKaryawan} berhasil diperbarui. Jam Masuk: {$inDisplay}, Jam Pulang: {$outDisplay}, Total Jam Kerja: {$totalJamKerja} Jam ({$durasiTeks}).",
                    'data' => [
                        'id' => $absensi->id,
                        'check_in' => $inDisplay,
                        'check_out' => $outDisplay,
                        'total_jam_kerja' => $totalJamKerja,
                        'durasi_teks' => $durasiTeks,
                        'status' => $absensi->status,
                        'kantor_cabang' => $absensi->kantor_cabang,
                        'keterangan' => $absensi->keterangan,
                    ],
                ]);
            }

            return redirect()->back()->with('success', "Presensi {$namaKaryawan} berhasil diperbarui! Jam Masuk: {$inDisplay} | Jam Pulang: {$outDisplay} | Total Jam Kerja Otomatis: {$totalJamKerja} Jam ({$durasiTeks}).");
        } catch (\Throwable $e) {
            Log::error('Error updating absensi by superadmin: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan presensi: ' . $e->getMessage());
        }
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

    // ==========================================================
    // PELANGGARAN PRESENSI (ANTI REMOTE ACCESS)
    // ==========================================================

    /**
     * Halaman monitoring pelanggaran presensi (indikasi akses remote)
     * & daftar karyawan yang sedang diblokir.
     */
    public function violationsIndex(Request $request)
    {
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        $validStatuses = [
            RemoteAccessViolation::STATUS_PENDING,
            RemoteAccessViolation::STATUS_CONFIRMED,
            RemoteAccessViolation::STATUS_DISMISSED,
        ];

        $query = RemoteAccessViolation::with(['karyawan', 'reviewer']);

        if (in_array($status, $validStatuses, true)) {
            $query->where('status', $status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('tanggal', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('tanggal', '<=', $endDate);
        }

        if ($search) {
            $query->whereHas('karyawan', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('kode_pegawai', 'like', "%{$search}%")
                    ->orWhere('divisi', 'like', "%{$search}%");
            });
        }

        $violations = $query->orderByDesc('tanggal')->orderByDesc('created_at')->paginate(15)->withQueryString();

        $stats = [
            'pending' => RemoteAccessViolation::where('status', RemoteAccessViolation::STATUS_PENDING)->count(),
            'confirmed' => RemoteAccessViolation::where('status', RemoteAccessViolation::STATUS_CONFIRMED)->count(),
            'dismissed' => RemoteAccessViolation::where('status', RemoteAccessViolation::STATUS_DISMISSED)->count(),
            'banned_now' => Karyawan::where('is_banned_permanent', true)
                ->orWhere(function ($q) {
                    $q->whereNotNull('banned_until')->where('banned_until', '>', Carbon::now());
                })
                ->count(),
        ];

        $bannedKaryawans = Karyawan::where('is_banned_permanent', true)
            ->orWhere(function ($q) {
                $q->whereNotNull('banned_until')->where('banned_until', '>', Carbon::now());
            })
            ->orderBy('nama_lengkap')
            ->get();

        return view('superadmin.violations', compact(
            'violations',
            'stats',
            'bannedKaryawans',
            'status',
            'startDate',
            'endDate',
            'search'
        ));
    }

    /**
     * Konfirmasi pelanggaran → strike bertambah & ban diterapkan sesuai tangga
     * (ke-1: 1 hari, ke-2: 2 hari, ke-3: PERMANEN).
     */
    public function violationsConfirm(Request $request, $id)
    {
        $violation = RemoteAccessViolation::with('karyawan')->findOrFail($id);

        if ($violation->status === RemoteAccessViolation::STATUS_CONFIRMED) {
            return redirect()->back()->with('error', 'Pelanggaran ini sudah pernah dikonfirmasi dan ban sudah diterapkan.');
        }

        $request->validate([
            'pesan' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($violation, $request) {
            $karyawan = Karyawan::whereKey($violation->karyawan_id)->lockForUpdate()->first();
            $strike = $karyawan->applyBan();

            $violation->status = RemoteAccessViolation::STATUS_CONFIRMED;
            $violation->risk_level = RemoteAccessViolation::RISK_HIGH;
            $violation->strike_number = $strike;
            $violation->reviewed_by = Auth::id();
            $violation->reviewed_at = Carbon::now();
            $violation->save();

            if ($request->filled('pesan')) {
                $karyawan->ban_message = $request->pesan;
                $karyawan->save();
            }
        });

        // Antrean review berkurang → badge sidebar ter-update langsung
        Cache::forget('superadmin_pending_violations');

        $violation->refresh()->load('karyawan');

        $nama = $violation->karyawan->nama_lengkap ?? 'Karyawan';
        $message = $violation->strike_number >= 3
            ? "Pelanggaran DIKONFIRMASI. {$nama} kena BAN PERMANEN (pelanggaran ke-3, akun tidak bisa check-in/check-out selamanya)."
            : "Pelanggaran DIKONFIRMASI. {$nama} diblokir presensi {$violation->strike_number} hari (pelanggaran ke-{$violation->strike_number}).";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Tandai pelanggaran sebagai false positive. Jika sebelumnya sudah
     * dikonfirmasi Superadmin (ban sudah diterapkan), strike & blokirnya
     * dicabut kembali.
     */
    public function violationsDismiss(Request $request, $id)
    {
        $violation = RemoteAccessViolation::with('karyawan')->findOrFail($id);

        if ($violation->status === RemoteAccessViolation::STATUS_DISMISSED) {
            return redirect()->back()->with('error', 'Pelanggaran ini sudah pernah ditandai false positive.');
        }

        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        $wasConfirmed = $violation->status === RemoteAccessViolation::STATUS_CONFIRMED;

        DB::transaction(function () use ($violation, $request, $wasConfirmed) {
            $karyawan = Karyawan::whereKey($violation->karyawan_id)->lockForUpdate()->first();

            if ($wasConfirmed) {
                $karyawan->revokeStrike();
            }

            $violation->status = RemoteAccessViolation::STATUS_DISMISSED;
            $violation->strike_number = null;
            $violation->reviewed_by = Auth::id();
            $violation->reviewed_at = Carbon::now();
            $violation->notes = trim(
                ($violation->notes ? $violation->notes . "\n" : '')
                . 'Ditolak sebagai false positive oleh Superadmin.'
                . ($request->filled('catatan') ? ' Catatan: ' . $request->catatan : '')
            );
            $violation->save();
        });

        // Antrean review berkurang → badge sidebar ter-update langsung
        Cache::forget('superadmin_pending_violations');

        $suffix = $wasConfirmed ? ' — strike & blokir karyawan dicabut.' : '.';

        return redirect()->back()->with('success', 'Pelanggaran ditandai FALSE POSITIVE' . $suffix);
    }

    /**
     * Aktifkan kembali (unban) akun karyawan: lepas blokir & reset hitungan pelanggaran.
     */
    public function karyawanUnban($id)
    {
        $karyawan = Karyawan::findOrFail($id);

        if (!$karyawan->is_banned_permanent && !$karyawan->banned_until) {
            return redirect()->back()->with('error', "Akun {$karyawan->nama_lengkap} saat ini tidak dalam blokir presensi.");
        }

        $karyawan->liftBan();

        return redirect()->back()->with('success', "Blokir presensi akun {$karyawan->nama_lengkap} dilepas. Hitungan pelanggaran direset ke 0 dan akun dapat presensi kembali.");
    }

    /**
     * Kirim/update pesan alert untuk karyawan terblokir.
     * Pesan tampil otomatis di halaman dashboard & absensi karyawan tersebut.
     */
    public function karyawanBanMessage(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $request->validate([
            'ban_message' => 'required|string|max:1000',
        ]);

        $karyawan->ban_message = $request->ban_message;
        $karyawan->save();

        return redirect()->back()->with('success', "Pesan alert untuk {$karyawan->nama_lengkap} diperbarui dan akan tampil otomatis di dashboard karyawan.");
    }

    // ==========================================================
    // MANAJEMEN AKSES KARYAWAN (SUSPEND AKUN & HAK AKSES MENU)
    // ==========================================================

    /**
     * Halaman Manajemen Akses: seluruh data karyawan + aksi suspend
     * (rentang tanggal) + pengaturan hak akses menu per karyawan.
     */
    public function accessIndex(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $posisi = $request->input('posisi', '');
        $status = $request->input('status', '');
        $today = now()->toDateString();

        $query = Karyawan::with('menuDisabled')->orderBy('posisi')->orderBy('nama_lengkap');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('kode_pegawai', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if (in_array($posisi, ['superadmin', 'hr', 'karyawan'], true)) {
            $query->where('posisi', $posisi);
        }

        if ($status === 'suspended') {
            $query->whereNotNull('suspend_start')
                ->whereNotNull('suspend_end')
                ->whereDate('suspend_start', '<=', $today)
                ->whereDate('suspend_end', '>=', $today);
        } elseif ($status === 'active') {
            $query->where(function ($q) use ($today) {
                $q->whereNull('suspend_end')->orWhereDate('suspend_end', '<', $today);
            });
        }

        $karyawans = $query->paginate(20)->withQueryString();

        $suspendedCount = Karyawan::whereNotNull('suspend_start')
            ->whereNotNull('suspend_end')
            ->whereDate('suspend_start', '<=', $today)
            ->whereDate('suspend_end', '>=', $today)
            ->count();

        $totalCount = Karyawan::count();

        $stats = [
            'total' => $totalCount,
            'suspended' => $suspendedCount,
            'menu_limited' => KaryawanMenuDisabled::where(function ($q) use ($today) {
                    $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
                })
                ->distinct()
                ->count('karyawan_id'),
            'aktif' => $totalCount - $suspendedCount,
        ];

        $menuMeta = SystemSetting::AVAILABLE_FEATURES;

        return view('superadmin.access-karyawan', compact(
            'karyawans',
            'stats',
            'search',
            'posisi',
            'status',
            'menuMeta'
        ));
    }

    /**
     * Terapkan suspend akun (blokir login total) pada rentang tanggal tertentu.
     */
    public function accessSuspend(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $request->validate([
            'suspend_start' => 'required|date',
            'suspend_end' => 'required|date|after_or_equal:suspend_start',
            'suspend_reason' => 'required|string|max:500',
        ], [
            'suspend_start.required' => 'Tanggal mulai suspend wajib diisi.',
            'suspend_start.date' => 'Tanggal mulai suspend tidak valid.',
            'suspend_end.required' => 'Tanggal selesai suspend wajib diisi.',
            'suspend_end.date' => 'Tanggal selesai suspend tidak valid.',
            'suspend_end.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'suspend_reason.required' => 'Alasan suspend wajib diisi.',
            'suspend_reason.max' => 'Alasan suspend maksimal 500 karakter.',
        ]);

        // Tidak boleh menangguhkan akun sendiri
        if ((int) $karyawan->id === (int) Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menangguhkan akun Anda sendiri.');
        }

        // Superadmin: tidak boleh menyisakan 0 Super Admin aktif
        if ($karyawan->posisi === 'superadmin') {
            $today = now()->toDateString();

            $activeSuperadmins = Karyawan::where('posisi', 'superadmin')
                ->where('is_resigned', false)
                ->where(function ($q) use ($today) {
                    $q->whereNull('suspend_end')->orWhereDate('suspend_end', '<', $today);
                })
                ->count();

            if (!$karyawan->isSuspended() && $activeSuperadmins <= 1) {
                return redirect()->back()->with(
                    'error',
                    'Tidak dapat menangguhkan akun karena target adalah satu-satunya Super Admin aktif di sistem.'
                );
            }
        }

        $karyawan->forceFill([
            'suspend_start' => $request->input('suspend_start'),
            'suspend_end' => $request->input('suspend_end'),
            'suspend_reason' => trim((string) $request->input('suspend_reason')),
            'suspended_by' => Auth::id(),
        ])->save();

        $mulai = Carbon::parse($request->input('suspend_start'))->isoFormat('D MMM Y');
        $sampai = Carbon::parse($request->input('suspend_end'))->isoFormat('D MMM Y');

        return redirect()->back()->with(
            'success',
            "Akun {$karyawan->nama_lengkap} ditangguhkan (suspend) dari {$mulai} hingga {$sampai}. Akun tidak dapat login selama periode tersebut."
        );
    }

    /**
     * Angkat suspend → akun bisa login kembali.
     */
    public function accessUnsuspend($id)
    {
        $karyawan = Karyawan::findOrFail($id);

        if (!$karyawan->suspend_start && !$karyawan->suspend_end) {
            return redirect()->back()->with('error', "Akun {$karyawan->nama_lengkap} saat ini tidak dalam masa suspend.");
        }

        $karyawan->forceFill([
            'suspend_start' => null,
            'suspend_end' => null,
            'suspend_reason' => null,
            'suspended_by' => null,
        ])->save();

        return redirect()->back()->with(
            'success',
            "Masa suspend akun {$karyawan->nama_lengkap} diangkat. Akun dapat login kembali."
        );
    }

    /**
     * Simpan hak akses menu untuk satu karyawan.
     * Checkbox yang dicentang = menu AKTIF; yang tidak = dinonaktifkan
     * pada rentang tanggal tertentu (sama seperti form suspend) beserta alasan.
     * Jika semua menu dicentang → pembatasan dilepas (tanpa periode/alasan).
     */
    public function accessUpdateMenus(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $menuKeys = array_keys(SystemSetting::AVAILABLE_FEATURES);

        $request->validate([
            'menus' => 'nullable|array',
            'menus.*' => 'required|string|in:' . implode(',', $menuKeys),
        ], [
            'menus.array' => 'Format hak akses menu tidak valid.',
            'menus.*.in' => 'Menu yang dipilih tidak dikenal.',
        ]);

        // Superadmin selalu punya akses penuh — pembatasan menu tidak berlaku
        if ($karyawan->posisi === 'superadmin') {
            return redirect()->back()->with('error', 'Pembatasan menu tidak berlaku untuk akun Super Admin.');
        }

        $aktif = array_values(array_intersect($menuKeys, (array) $request->input('menus', [])));
        $nonaktif = array_values(array_diff($menuKeys, $aktif));

        // Periode & alasan wajib hanya bila ada menu yang dinonaktifkan.
        // (Jika semua dicentang → murni pelepasan pembatasan.)
        if (count($nonaktif) > 0) {
            $request->validate([
                'valid_from' => 'required|date',
                'valid_until' => 'required|date|after_or_equal:valid_from',
                'reason' => 'required|string|max:500',
            ], [
                'valid_from.required' => 'Tanggal mulai pembatasan menu wajib diisi.',
                'valid_from.date' => 'Tanggal mulai pembatasan menu tidak valid.',
                'valid_until.required' => 'Tanggal selesai pembatasan menu wajib diisi.',
                'valid_until.date' => 'Tanggal selesai pembatasan menu tidak valid.',
                'valid_until.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
                'reason.required' => 'Alasan pembatasan menu wajib diisi.',
                'reason.max' => 'Alasan pembatasan menu maksimal 500 karakter.',
            ]);
        }

        DB::transaction(function () use ($karyawan, $nonaktif, $request) {
            KaryawanMenuDisabled::where('karyawan_id', $karyawan->id)->delete();

            foreach ($nonaktif as $menuKey) {
                KaryawanMenuDisabled::create([
                    'karyawan_id' => $karyawan->id,
                    'menu_key' => $menuKey,
                    'valid_from' => $request->input('valid_from'),
                    'valid_until' => $request->input('valid_until'),
                    'reason' => trim((string) $request->input('reason')),
                    'created_by' => Auth::id(),
                ]);
            }
        });

        $karyawan->flushMenuAccessCache();

        $jumlah = count($nonaktif);

        if ($jumlah === 0) {
            $pesan = "Semua menu diaktifkan kembali untuk {$karyawan->nama_lengkap} — pembatasan dilepas.";
        } else {
            $mulai = Carbon::parse($request->input('valid_from'))->isoFormat('D MMM Y');
            $sampai = Carbon::parse($request->input('valid_until'))->isoFormat('D MMM Y');
            $pesan = "{$jumlah} menu dinonaktifkan untuk {$karyawan->nama_lengkap} pada {$mulai} – {$sampai}. Menu tetap tampil di sidebar dalam keadaan nonaktif dan kembali aktif otomatis setelah tanggal selesai.";
        }

        return redirect()->back()->with('success', $pesan);
    }
}
