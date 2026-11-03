<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SystemHealthController extends Controller
{
    public function index(DatabaseBackupService $backups)
    {
        $dbOk = true;
        $dbError = null;
        try {
            DB::select('SELECT 1');
        } catch (\Throwable $e) {
            $dbOk = false;
            $dbError = $e->getMessage();
        }

        $cacheOk = true;
        try {
            Cache::put('sikekar_health_check', 'ok', 10);
            $cacheOk = Cache::get('sikekar_health_check') === 'ok';
        } catch (\Throwable $e) {
            $cacheOk = false;
        }

        $storagePath = storage_path('app');
        $backupPath = (string) config('backup.path', storage_path('app/backups'));
        $storageOk = is_dir($storagePath) && is_writable($storagePath);
        $backupWritable = is_dir($backupPath) && is_writable($backupPath);

        // Semua query di bawah membutuhkan koneksi database. Jika koneksi sudah
        // gagal di atas, jangan dicoba lagi — cukup laporkan status kritis.
        // (Begitu pula bila query berikutnya ikut gagal → tandai DB bermasalah.)
        $failedJobsCount = 0;
        $pendingJobsCount = 0;
        $failedJobs = collect();

        if ($dbOk) {
            try {
                $failedJobsCount = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
                $pendingJobsCount = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
                $failedJobs = Schema::hasTable('failed_jobs')
                    ? DB::table('failed_jobs')->orderByDesc('failed_at')->limit(10)->get()
                    : collect();
            } catch (\Throwable $e) {
                $dbOk = false;
                $dbError = $dbError ?: $e->getMessage();
                $failedJobsCount = 0;
                $pendingJobsCount = 0;
                $failedJobs = collect();
            }
        }

        // stats() melempar RuntimeException bila folder backup tidak dapat
        // ditulis — jangan sampai halaman monitoring ikut gagal 500.
        $backupStats = [
            'count' => 0,
            'total_size' => 0,
            'total_size_human' => '0 B',
            'last' => null,
            'retention' => (int) config('backup.retention', 14),
            'path' => (string) config('backup.path', storage_path('app/backups')),
            'writable' => false,
        ];

        try {
            $backupStats = $backups->stats();
        } catch (\Throwable $e) {
            Log::warning('Gagal membaca statistik backup: ' . $e->getMessage());
        }

        $lastBackup = $backupStats['last'] ?? null;
        $lastBackupAgeDays = $lastBackup
            ? now()->diffInDays($lastBackup['created_at'])
            : null;

        $logPath = storage_path('logs/laravel.log');
        $logSize = is_file($logPath) ? (int) filesize($logPath) : 0;
        $logModified = is_file($logPath) ? \Carbon\Carbon::createFromTimestamp(filemtime($logPath)) : null;
        $logPreview = $this->tailFile($logPath, 120);

        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskFree = @disk_free_space(base_path()) ?: 0;
        $diskUsed = max(0, $diskTotal - $diskFree);
        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

        $storageSize = $this->directorySize(storage_path('app'));
        $logsSize = $this->directorySize(storage_path('logs'));

        $checks = [
            [
                'key' => 'database',
                'label' => 'Koneksi Database',
                'ok' => $dbOk,
                'value' => $dbOk ? 'Terhubung' : 'Gagal',
                'detail' => $dbOk ? 'Database dapat diakses normal.' : ($dbError ?: 'Tidak dapat terhubung ke database.'),
                'icon' => 'fa-database',
            ],
            [
                'key' => 'cache',
                'label' => 'Cache & Session',
                'ok' => $cacheOk,
                'value' => $cacheOk ? 'Berfungsi' : 'Bermasalah',
                'detail' => $cacheOk ? 'Cache store dapat membaca dan menulis.' : 'Cache store tidak dapat menulis data.',
                'icon' => 'fa-bolt',
            ],
            [
                'key' => 'storage',
                'label' => 'Penyimpanan Aplikasi',
                'ok' => $storageOk,
                'value' => $storageOk ? 'Writable' : 'Read-only',
                'detail' => $storageOk ? 'Folder storage/app dapat ditulis.' : 'Folder storage/app tidak dapat ditulis.',
                'icon' => 'fa-folder-open',
            ],
            [
                'key' => 'backup',
                'label' => 'Backup Database',
                'ok' => $backupWritable && $lastBackup !== null,
                'value' => !$backupWritable ? 'Folder bermasalah' : ($lastBackup ? 'Tersedia' : 'Belum ada'),
                'detail' => !$backupWritable
                    ? 'Folder backup tidak dapat ditulis.'
                    : ($lastBackup ? 'Backup terakhir ' . $lastBackup['age'] . '.' : 'Belum ada backup yang dibuat.'),
                'icon' => 'fa-cloud-arrow-up',
            ],
            [
                'key' => 'queue',
                'label' => 'Queue & Job',
                'ok' => $failedJobsCount === 0,
                'value' => $failedJobsCount === 0 ? 'Normal' : $failedJobsCount . ' gagal',
                'detail' => $failedJobsCount === 0
                    ? 'Tidak ada job yang gagal.'
                    : 'Ada job gagal yang perlu ditinjau.',
                'icon' => 'fa-list-check',
            ],
        ];

        $critical = !$dbOk || !$cacheOk || !$storageOk;
        $warning = !$critical && (!$backupWritable || $failedJobsCount > 0 || $lastBackupAgeDays === null || $lastBackupAgeDays > 7);
        $overallStatus = $critical ? 'critical' : ($warning ? 'warning' : 'healthy');
        $overallLabel = $critical ? 'Perlu Perhatian Serius' : ($warning ? 'Perlu Perhatian' : 'Sistem Sehat');
        $overallMessage = $critical
            ? 'Ada komponen inti yang bermasalah. Segera periksa detail di bawah.'
            : ($warning ? 'Sistem berjalan, tetapi ada beberapa hal yang sebaiknya segera ditinjau.'
                : 'Semua komponen utama berjalan normal. Tidak ada tindakan mendesak.');
        $overallColor = $critical ? 'rose' : ($warning ? 'amber' : 'emerald');

        $pendingMigrations = $this->pendingMigrations();

        $appInfo = [
            ['label' => 'Versi Laravel', 'value' => app()->version(), 'icon' => 'fa-laravel'],
            ['label' => 'Versi PHP', 'value' => PHP_VERSION, 'icon' => 'fa-code'],
            ['label' => 'Environment', 'value' => config('app.env'), 'icon' => 'fa-server'],
            ['label' => 'Debug Mode', 'value' => config('app.debug') ? 'Aktif' : 'Nonaktif', 'icon' => 'fa-bug'],
            ['label' => 'Timezone', 'value' => config('app.timezone'), 'icon' => 'fa-clock'],
            ['label' => 'Database', 'value' => strtoupper((string) DB::connection()->getDriverName()) . ' / ' . (DB::connection()->getDatabaseName() ?: '-'), 'icon' => 'fa-database'],
            ['label' => 'Cache Driver', 'value' => config('cache.default'), 'icon' => 'fa-bolt'],
            ['label' => 'Queue Driver', 'value' => config('queue.default'), 'icon' => 'fa-list'],
            ['label' => 'Session Driver', 'value' => config('session.driver'), 'icon' => 'fa-user-clock'],
            ['label' => 'Ukuran Database', 'value' => $this->databaseSize(), 'icon' => 'fa-hard-drive'],
            ['label' => 'Jumlah Tabel', 'value' => $this->tableCount(), 'icon' => 'fa-table'],
            ['label' => 'PHP SAPI', 'value' => PHP_SAPI, 'icon' => 'fa-terminal'],
        ];

        return view('superadmin.it.system-health', compact(
            'checks',
            'overallStatus',
            'overallLabel',
            'overallMessage',
            'overallColor',
            'failedJobsCount',
            'pendingJobsCount',
            'failedJobs',
            'backupStats',
            'lastBackup',
            'lastBackupAgeDays',
            'logPath',
            'logSize',
            'logModified',
            'logPreview',
            'diskTotal',
            'diskFree',
            'diskUsed',
            'diskPercent',
            'storageSize',
            'logsSize',
            'appInfo',
            'pendingMigrations'
        ));
    }

    public function clearCache(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:optimize,config,route,view,all',
        ]);

        $action = $validated['action'];
        $commands = match ($action) {
            'optimize' => ['optimize:clear'],
            'config' => ['config:clear'],
            'route' => ['route:clear'],
            'view' => ['view:clear'],
            // 'all' juga membersihkan cache store aplikasi (proyek ini memakai
            // CACHE_STORE=database) — 'optimize:clear' tidak menyentuhnya.
            'all' => ['optimize:clear', 'config:clear', 'route:clear', 'view:clear', 'cache:clear'],
        };

        try {
            foreach ($commands as $command) {
                Artisan::call($command);
            }

            ActivityLog::record(
                'system.cache_clear',
                'Menjalankan pembersihan cache: ' . ucfirst($action) . '.',
                [
                    'module' => 'system',
                    'metadata' => ['action' => $action, 'commands' => $commands],
                ]
            );

            return redirect()->back()->with('success', 'Cache berhasil dibersihkan: ' . ucfirst($action) . '.');
        } catch (\Throwable $e) {
            Log::error('Gagal membersihkan cache: ' . $e->getMessage(), ['exception' => $e]);

            $message = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Terjadi kesalahan saat membersihkan cache. Detail lengkap tersimpan di log sistem.';

            return redirect()->back()->with('error', 'Gagal membersihkan cache: ' . $message);
        }
    }

    public function runTool(Request $request)
    {
        $validated = $request->validate([
            'tool' => 'required|in:storage_link,retry_failed,flush_failed',
        ]);

        $tool = $validated['tool'];

        try {
            $output = match ($tool) {
                'storage_link' => Artisan::call('storage:link') === 0
                    ? 'Storage symlink berhasil dibuat / diperbarui.'
                    : 'Storage symlink selesai dengan peringatan.',
                'retry_failed' => Artisan::call('queue:retry', ['id' => ['all']]) === 0
                    ? 'Semua failed job berhasil dimasukkan kembali ke antrean.'
                    : 'Retry failed job selesai dengan peringatan.',
                'flush_failed' => Artisan::call('queue:flush') === 0
                    ? 'Semua failed job berhasil dihapus.'
                    : 'Flush failed job selesai dengan peringatan.',
            };

            ActivityLog::record(
                'system.tool',
                'Menjalankan tool sistem: ' . $tool . '.',
                [
                    'module' => 'system',
                    'metadata' => ['tool' => $tool, 'output' => $output],
                ]
            );

            return redirect()->back()->with('success', $output);
        } catch (\Throwable $e) {
            Log::error('Tool sistem gagal dijalankan: ' . $e->getMessage(), ['exception' => $e]);

            $message = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Terjadi kesalahan saat menjalankan tool sistem. Detail lengkap tersimpan di log sistem.';

            return redirect()->back()->with('error', 'Tool sistem gagal dijalankan: ' . $message);
        }
    }

    protected function tailFile(string $path, int $lines = 120): array
    {
        if (!is_file($path)) {
            return [];
        }

        try {
            $filesize = (int) filesize($path);

            if ($filesize === 0) {
                return [];
            }

            $handle = fopen($path, 'rb');

            if ($handle === false) {
                return [];
            }

            // Baca dari AKHIR file ke belakang (chunk 8 KB) agar tetap cepat
            // untuk file log berukuran ratusan MB — tidak perlu seek O(n).
            $buffer = '';
            $pos = $filesize;
            $chunkSize = 8192;

            while ($pos > 0 && substr_count($buffer, "\n") <= $lines) {
                $read = min($chunkSize, $pos);
                $pos -= $read;
                fseek($handle, $pos);
                $buffer = (string) fread($handle, $read) . $buffer;
            }

            fclose($handle);

            $output = explode("\n", $buffer);

            // File yang diakhiri newline menghasilkan elemen kosong terakhir.
            if (end($output) === '') {
                array_pop($output);
            }

            return array_map(fn ($line) => rtrim($line, "\r"), array_slice($output, -$lines));
        } catch (\Throwable $e) {
            return ['Gagal membaca file log: ' . $e->getMessage()];
        }
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

    protected function databaseSize(): string
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                $database = DB::connection()->getDatabaseName();
                $bytes = $database && is_file($database) ? (int) filesize($database) : 0;

                return app(DatabaseBackupService::class)->formatBytes($bytes);
            }

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $result = DB::selectOne(
                    'SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ?',
                    [DB::connection()->getDatabaseName()]
                );
                $bytes = (int) ($result->size ?? 0);

                return app(DatabaseBackupService::class)->formatBytes($bytes);
            }
        } catch (\Throwable $e) {
            // Abaikan; tampilkan tanda tidak tersedia.
        }

        return 'Tidak tersedia';
    }

    protected function tableCount(): int
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                return (int) (DB::selectOne("SELECT COUNT(*) AS total FROM sqlite_master WHERE type = 'table'")->total ?? 0);
            }

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                return (int) (DB::selectOne(
                    'SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE table_schema = ?',
                    [DB::connection()->getDatabaseName()]
                )->total ?? 0);
            }
        } catch (\Throwable $e) {
            // Abaikan.
        }

        return 0;
    }

    protected function pendingMigrations(): array
    {
        try {
            if (!Schema::hasTable('migrations')) {
                return [];
            }

            $ran = DB::table('migrations')->pluck('migration')->all();
            $files = collect(File::files(database_path('migrations')))
                ->map(fn ($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME))
                ->values()
                ->all();

            return array_values(array_diff($files, $ran));
        } catch (\Throwable $e) {
            return [];
        }
    }
}
