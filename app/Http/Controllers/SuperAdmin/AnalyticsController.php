<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Karyawan;
use App\Models\LoginHistory;
use App\Models\PerjalananDinas;
use App\Models\Perizinan;
use App\Models\RemoteAccessViolation;
use App\Models\SunnahDaily;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Analitik HR & Kehadiran (Fitur C10) — grafik Chart.js:
 *  - Tren kehadiran harian
 *  - Distribusi status absensi per divisi
 *  - Pengajuan cuti / izin / dinas per bulan
 *  - Kualitas kehadiran (terlambat, alpha, pelanggaran)
 *  - Riwayat login karyawan terbaru
 */
class AnalyticsController extends Controller
{
    /** Daftar status absensi yang dihitung dalam grafik distribusi. */
    private const STATUS_COLORS = [
        'Hadir' => '#2E7D3E',
        'Terlambat' => '#d49b06',
        'Izin' => '#9333ea',
        'Sakit' => '#F97316',
        'Cuti' => '#00a2e9',
        'Perjalanan Dinas' => '#2563eb',
        'Alpha' => '#dc2626',
    ];

    public function index(Request $request)
    {
        $period = (int) $request->input('period', 30);

        if (!in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }

        $cacheKey = 'superadmin_analytics_' . $period;

        $data = Cache::remember($cacheKey, 300, fn () => $this->buildData($period));

        $data['period'] = $period;
        $data['generatedAt'] = now();

        return view('superadmin.analytics', $data);
    }

    private function buildData(int $period): array
    {
        $start = now()->subDays($period - 1)->startOfDay();
        $end = now()->endOfDay();
        $driver = DB::connection()->getDriverName();

        $activeEmployees = Karyawan::active()->count();

        // ----------------------------------------------------------
        // 1. Tren kehadiran harian (Hadir vs Alpha vs Izin/Sakit)
        // ----------------------------------------------------------
        $trend = $this->attendanceTrend($start, $end, $driver);

        // ----------------------------------------------------------
        // 2. Distribusi status absensi pada periode
        // ----------------------------------------------------------
        $statusRows = Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusDistribution = [
            'labels' => [],
            'data' => [],
            'colors' => [],
        ];

        foreach (Absensi::STATUSES as $status) {
            if (!$statusRows->has($status)) {
                continue;
            }

            $statusDistribution['labels'][] = $status;
            $statusDistribution['data'][] = (int) $statusRows[$status];
            $statusDistribution['colors'][] = self::STATUS_COLORS[$status] ?? '#64748b';
        }

        // ----------------------------------------------------------
        // 3. Kehadiran per divisi (persentase hadir vs total catatan)
        // ----------------------------------------------------------
        $perDivisi = $this->attendancePerDivision($start, $end);

        // ----------------------------------------------------------
        // 4. Pengajuan per bulan (cuti, izin, perjalanan dinas)
        // ----------------------------------------------------------
        $requestsPerMonth = $this->requestsPerMonth($start, $end, $driver);

        // ----------------------------------------------------------
        // 5. Kualitas kehadiran: terlambat & alpha
        // ----------------------------------------------------------
        $quality = $this->attendanceQuality($start, $end);

        // ----------------------------------------------------------
        // 6. Sunnah 7SPS per divisi (jumlah catatan)
        // ----------------------------------------------------------
        $sunnahPerDivisi = $this->sunnahPerDivision($start, $end);

        // ----------------------------------------------------------
        // 7. Login terakhir (keamanan akun)
        // ----------------------------------------------------------
        $recentLogins = $this->recentLogins();

        // ----------------------------------------------------------
        // 8. Pelanggaran presensi menunggu review
        // ----------------------------------------------------------
        $pendingViolations = Schema::hasTable((new RemoteAccessViolation)->getTable())
            ? RemoteAccessViolation::where('status', RemoteAccessViolation::STATUS_PENDING)->count()
            : 0;

        return [
            'activeEmployees' => $activeEmployees,
            'trend' => $trend,
            'statusDistribution' => $statusDistribution,
            'perDivisi' => $perDivisi,
            'requestsPerMonth' => $requestsPerMonth,
            'quality' => $quality,
            'sunnahPerDivisi' => $sunnahPerDivisi,
            'recentLogins' => $recentLogins,
            'pendingViolations' => $pendingViolations,
            'kpi' => $this->kpi($start, $end, $activeEmployees),
        ];
    }

    /** Tren harian kehadiran pada rentang tanggal. */
    private function attendanceTrend(Carbon $start, Carbon $end, string $driver): array
    {
        $dateExpression = match ($driver) {
            'pgsql' => 'CAST(tanggal AS DATE)',
            default => 'DATE(tanggal)',
        };

        $rows = Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
            ->selectRaw($dateExpression . ' as day, status, COUNT(*) as total')
            ->groupBy('day', 'status')
            ->get();

        $byDay = [];

        foreach ($rows as $row) {
            $key = Carbon::parse($row->day)->toDateString();
            $byDay[$key][$row->status] = (int) $row->total;
        }

        $labels = [];
        $hadir = [];
        $terlambat = [];
        $alpha = [];
        $lainnya = [];

        $days = $start->diffInDays($end);

        for ($i = 0; $i <= $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->toDateString();
            $row = $byDay[$key] ?? [];

            $labels[] = $day->translatedFormat('d M');
            $hadir[] = (int) ($row['Hadir'] ?? 0);
            $terlambat[] = (int) ($row['Izin'] ?? 0) + (int) ($row['Sakit'] ?? 0);
            $alpha[] = (int) ($row['Alpha'] ?? 0);
            $lainnya[] = (int) ($row['Cuti'] ?? 0) + (int) ($row['Perjalanan Dinas'] ?? 0);
        }

        return [
            'labels' => $labels,
            'hadir' => $hadir,
            'izinSakit' => $terlambat,
            'alpha' => $alpha,
            'cutiDinas' => $lainnya,
        ];
    }

    /** Persentase kehadiran per divisi pada periode. */
    private function attendancePerDivision(Carbon $start, Carbon $end): array
    {
        $rows = Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
            ->join('karyawans', 'karyawans.id', '=', 'absensis.karyawan_id')
            ->selectRaw(
                'COALESCE(NULLIF(TRIM(karyawans.divisi), \'\'), \'Tanpa Divisi\') as divisi, '
                . 'COUNT(*) as total, '
                . "SUM(CASE WHEN absensis.status = 'Hadir' THEN 1 ELSE 0 END) as hadir"
            )
            ->groupBy('divisi')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $labels = [];
        $percent = [];
        $counts = [];

        foreach ($rows as $row) {
            $total = (int) $row->total;
            $labels[] = $row->divisi;
            $percent[] = $total > 0 ? round(((int) $row->hadir / $total) * 100, 1) : 0;
            $counts[] = $total;
        }

        return [
            'labels' => $labels,
            'percent' => $percent,
            'counts' => $counts,
        ];
    }

    /** Jumlah pengajuan cuti / izin / dinas per bulan (6 bulan terakhir). */
    private function requestsPerMonth(Carbon $start, Carbon $end, string $driver): array
    {
        $labels = [];
        $cuti = [];
        $izins = [];
        $dinas = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i)->startOfMonth();
            $from = $month->toDateString();
            $to = $month->copy()->endOfMonth()->toDateString();

            $labels[] = $month->translatedFormat('M Y');

            $cuti[] = Cuti::whereBetween('tanggal_pengajuan', [$from, $to])->count();
            $izins[] = Perizinan::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->count();
            $dinas[] = PerjalananDinas::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->count();
        }

        return [
            'labels' => $labels,
            'cuti' => $cuti,
            'izin' => $izins,
            'dinas' => $dinas,
        ];
    }

    /** Angka kualitas kehadiran pada periode. */
    private function attendanceQuality(Carbon $start, Carbon $end): array
    {
        $total = Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())->count();
        $hadir = Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
            ->where('status', 'Hadir')->count();
        $alpha = Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
            ->where('status', 'Alpha')->count();

        $lateExpression = match ($driver = DB::connection()->getDriverName()) {
            'sqlite' => "substr(check_in, 12, 8) > '07:45:00'",
            'pgsql' => "CAST(check_in AS TIME) > TIME '07:45:00'",
            default => "TIME(check_in) > '07:45:00'",
        };

        $late = Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
            ->where('status', 'Hadir')
            ->whereNotNull('check_in')
            ->whereRaw($lateExpression)
            ->count();

        $attendanceRate = $total > 0 ? round(($hadir / $total) * 100, 1) : 0.0;
        $lateRate = $hadir > 0 ? round(($late / $hadir) * 100, 1) : 0.0;

        return [
            'total_records' => $total,
            'hadir' => $hadir,
            'alpha' => $alpha,
            'late' => $late,
            'attendance_rate' => $attendanceRate,
            'late_rate' => $lateRate,
        ];
    }

    /** Jumlah catatan 7SPS per divisi pada periode. */
    private function sunnahPerDivision(Carbon $start, Carbon $end): array
    {
        if (!Schema::hasTable((new SunnahDaily)->getTable())) {
            return ['labels' => [], 'data' => []];
        }

        try {
            $rows = SunnahDaily::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
                ->join('karyawans', 'karyawans.id', '=', 'sunnah_daily.karyawan_id')
                ->selectRaw(
                    'COALESCE(NULLIF(TRIM(karyawans.divisi), \'\'), \'Tanpa Divisi\') as divisi, COUNT(*) as total'
                )
                ->groupBy('divisi')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            report($e);

            return ['labels' => [], 'data' => []];
        }

        return [
            'labels' => $rows->pluck('divisi')->values()->all(),
            'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
        ];
    }

    /** 8 login terakhir yang tercatat. */
    private function recentLogins(): array
    {
        if (!Schema::hasTable('login_histories')) {
            return [];
        }

        return LoginHistory::with('karyawan')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn (LoginHistory $row) => [
                'email' => $row->email,
                'name' => $row->karyawan?->nama_lengkap ?? '-',
                'ip' => $row->ip_address,
                'success' => (bool) $row->success,
                'reason' => $row->failure_reason,
                'at' => $row->created_at,
            ])
            ->all();
    }

    /** KPI ringkas untuk kartu di bagian atas halaman. */
    private function kpi(Carbon $start, Carbon $end, int $activeEmployees): array
    {
        $absensiQ = fn () => Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString());

        $totalRecords = $absensiQ()->count();
        $hadir = $absensiQ()->where('status', 'Hadir')->count();
        $alpha = $absensiQ()->where('status', 'Alpha')->count();

        $pendingCuti = Cuti::where('status', 'pending')->count();
        $pendingIzin = Perizinan::where('status', 'pending')->count();
        $pendingDinas = PerjalananDinas::where('status', 'pending')->count();

        $uniqueAttendees = $totalRecords > 0
            ? Absensi::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
                ->where('status', 'Hadir')
                ->distinct()
                ->count('karyawan_id')
            : 0;

        return [
            'total_records' => $totalRecords,
            'hadir' => $hadir,
            'alpha' => $alpha,
            'attendance_rate' => $totalRecords > 0 ? round(($hadir / $totalRecords) * 100, 1) : 0.0,
            'unique_attendees' => $uniqueAttendees,
            'active_employees' => $activeEmployees,
            'pending_requests' => $pendingCuti + $pendingIzin + $pendingDinas,
            'pending_cuti' => $pendingCuti,
            'pending_izin' => $pendingIzin,
            'pending_dinas' => $pendingDinas,
        ];
    }
}
