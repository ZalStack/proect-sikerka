<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\ActivityLog;
use App\Models\Cuti;
use App\Models\FhlAbsensi;
use App\Models\Karyawan;
use App\Models\KhatamanAbsensi;
use App\Models\Pengumuman;
use App\Models\Perizinan;
use App\Models\PerjalananDinas;
use App\Models\SunnahDaily;
use App\Services\DatabaseBackupService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SystemStatisticController extends Controller
{
    public function index(Request $request)
    {
        $period = (int) $request->input('period', 30);
        if (!in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }

        $cacheKey = 'superadmin_system_stats_' . $period;

        $data = Cache::remember($cacheKey, 300, function () use ($period) {
            $start = now()->subDays($period - 1)->startOfDay();
            $end = now()->endOfDay();

            $loginQuery = ActivityLog::query()->where('action', 'auth.login');

            $totalUsers = Karyawan::count();
            $activeUsers = Karyawan::active()->count();
            $suspendedUsers = Karyawan::whereNotNull('suspend_start')
                ->whereNotNull('suspend_end')
                ->whereDate('suspend_start', '<=', now()->toDateString())
                ->whereDate('suspend_end', '>=', now()->toDateString())
                ->count();

            $roleDistribution = [
                'labels' => ['Superadmin', 'HR', 'Karyawan'],
                'data' => [
                    Karyawan::where('posisi', 'superadmin')->count(),
                    Karyawan::where('posisi', 'hr')->count(),
                    Karyawan::where('posisi', 'karyawan')->count(),
                ],
                'colors' => ['#FCC626', '#00a2e9', '#27438D'],
            ];

            $onlineUsers = 0;
            $activeSessions = 0;
            if (Schema::hasTable('sessions')) {
                $threshold = now()->subMinutes(15)->timestamp;
                $onlineUsers = DB::table('sessions')
                    ->where('last_activity', '>=', $threshold)
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->count('user_id');
                $activeSessions = DB::table('sessions')
                    ->where('last_activity', '>=', $threshold)
                    ->count();
            }

            $loginsToday = (clone $loginQuery)->whereDate('created_at', today())->count();
            $periodLogins = (clone $loginQuery)->whereBetween('created_at', [$start, $end])->count();
            $uniqueUsers = ActivityLog::query()
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('user_id')
                ->distinct()
                ->count('user_id');
            $newUsers = Karyawan::whereBetween('created_at', [$start, $end])->count();

            $trendStart = now()->subDays(13)->startOfDay();
            $trendRaw = (clone $loginQuery)
                ->where('created_at', '>=', $trendStart)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $loginTrend = ['labels' => [], 'data' => []];
            for ($i = 13; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $key = $day->toDateString();
                $loginTrend['labels'][] = $day->translatedFormat('d M');
                $loginTrend['data'][] = (int) ($trendRaw[$key] ?? 0);
            }

            $moduleUsage = [
                ['key' => 'absensi', 'name' => 'Presensi', 'icon' => 'fa-clock', 'color' => '#00a2e9', 'count' => Absensi::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])->count()],
                ['key' => 'cuti', 'name' => 'Cuti', 'icon' => 'fa-calendar-check', 'color' => '#2E7D3E', 'count' => Cuti::whereBetween('created_at', [$start, $end])->count()],
                ['key' => 'perizinan', 'name' => 'Perizinan', 'icon' => 'fa-file-signature', 'color' => '#9333ea', 'count' => Perizinan::whereBetween('created_at', [$start, $end])->count()],
                ['key' => 'perjalanan_dinas', 'name' => 'Perjalanan Dinas', 'icon' => 'fa-plane-departure', 'color' => '#d49b06', 'count' => PerjalananDinas::whereBetween('created_at', [$start, $end])->count()],
                ['key' => 'fhl', 'name' => 'FHL', 'icon' => 'fa-heart', 'color' => '#ec1d1d', 'count' => FhlAbsensi::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])->count()],
                ['key' => 'khataman', 'name' => 'Khataman', 'icon' => 'fa-book-quran', 'color' => '#2563eb', 'count' => KhatamanAbsensi::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])->count()],
                ['key' => 'sunnah', 'name' => '7SPS', 'icon' => 'fa-kaaba', 'color' => '#059669', 'count' => SunnahDaily::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])->count()],
                ['key' => 'pengumuman', 'name' => 'Pengumuman', 'icon' => 'fa-bullhorn', 'color' => '#ea580c', 'count' => Pengumuman::whereBetween('created_at', [$start, $end])->count()],
            ];

            $driver = DB::connection()->getDriverName();
            $hourExpression = match ($driver) {
                'sqlite' => "strftime('%H', created_at)",
                'pgsql' => 'EXTRACT(HOUR FROM created_at)',
                default => 'HOUR(created_at)',
            };

            $peakRaw = (clone $loginQuery)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw($hourExpression . ' as hour, COUNT(*) as total')
                ->groupBy('hour')
                ->pluck('total', 'hour');

            $peakHours = ['labels' => [], 'data' => []];
            for ($hour = 0; $hour < 24; $hour++) {
                $peakHours['labels'][] = str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00';
                $peakHours['data'][] = (int) ($peakRaw[$hour] ?? $peakRaw[(string) $hour] ?? 0);
            }

            $actionBreakdown = ActivityLog::query()
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('action, COUNT(*) as total')
                ->groupBy('action')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->map(fn ($row) => [
                    'action' => $row->action,
                    'label' => ActivityLog::ACTION_LABELS[$row->action] ?? str_replace(['.', '_'], [' ', ' '], $row->action),
                    'total' => (int) $row->total,
                ])
                ->all();

            return [
                'period' => $period,
                'startDate' => $start->toDateString(),
                'endDate' => $end->toDateString(),
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'suspendedUsers' => $suspendedUsers,
                'onlineUsers' => $onlineUsers,
                'activeSessions' => $activeSessions,
                'loginsToday' => $loginsToday,
                'periodLogins' => $periodLogins,
                'uniqueUsers' => $uniqueUsers,
                'newUsers' => $newUsers,
                'roleDistribution' => $roleDistribution,
                'loginTrend' => $loginTrend,
                'moduleUsage' => $moduleUsage,
                'totalModuleUsage' => array_sum(array_column($moduleUsage, 'count')),
                'peakHours' => $peakHours,
                'actionBreakdown' => $actionBreakdown,
            ];
        });

        // Data yang bergantung pada kondisi runtime dihitung segar setiap
        // request agar tidak ikut terserialisasi ke dalam cache.
        $data['startDate'] = Carbon::parse($data['startDate'])->startOfDay();
        $data['endDate'] = Carbon::parse($data['endDate'])->endOfDay();
        $data['generatedAt'] = now();
        $data['topUsers'] = ActivityLog::query()
            ->whereBetween('created_at', [$data['startDate'], $data['endDate']])
            ->whereNotNull('user_id')
            ->selectRaw('user_id, MAX(user_name) as user_name, MAX(user_role) as user_role, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        $data['backupStats'] = app(DatabaseBackupService::class)->stats();
        $data['storageBytes'] = $this->directorySize(storage_path('app'));
        $data['logBytes'] = $this->directorySize(storage_path('logs'));
        $data['failedJobs'] = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        $data['chartData'] = [
            'loginTrend' => $data['loginTrend'],
            'moduleUsage' => [
                'labels' => array_column($data['moduleUsage'], 'name'),
                'data' => array_column($data['moduleUsage'], 'count'),
                'colors' => array_column($data['moduleUsage'], 'color'),
            ],
            'peakHours' => $data['peakHours'],
            'roleDistribution' => $data['roleDistribution'],
        ];

        return view('superadmin.it.statistics', $data);
    }

    protected function directorySize(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $size = 0;

        try {
            foreach (File::allFiles($path) as $file) {
                $size += (int) $file->getSize();
            }
        } catch (\Throwable $e) {
            return $size;
        }

        return $size;
    }
}
