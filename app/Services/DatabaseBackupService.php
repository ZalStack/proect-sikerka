<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\BackupLog;
use App\Models\Karyawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function path(): string
    {
        return (string) config('backup.path', storage_path('app/backups'));
    }

    public function ensureDirectory(): void
    {
        if (!File::isDirectory($this->path())) {
            // 0700: dump database berisi data sensitif — hanya owner yang boleh akses.
            File::makeDirectory($this->path(), 0700, true);
        }

        if (!is_writable($this->path())) {
            throw new RuntimeException('Folder backup tidak dapat ditulis. Periksa izin akses storage/app/backups.');
        }

        // Kencangkan izin folder yang sudah ada (mis. dibuat lama dengan 0755).
        @chmod($this->path(), 0700);

        $this->cleanupStaleTempFiles();
    }

    /**
     * Hapus file dump sementara (.tmp-*) yang tertinggal dari proses backup
     * yang gagal/terputus (timeout, OOM, dsb.) agar tidak menumpuk diam-diam
     * — file itu tidak pernah tampil di daftar backup karena diawali titik.
     */
    protected function cleanupStaleTempFiles(): void
    {
        try {
            $cutoff = now()->subHour()->getTimestamp();

            foreach (File::files($this->path()) as $file) {
                if (str_starts_with($file->getFilename(), '.tmp-') && $file->getMTime() < $cutoff) {
                    @unlink($file->getPathname());
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal membersihkan file backup sementara: ' . $e->getMessage());
        }
    }

    /**
     * Buat file backup database baru. Mengembalikan metadata file.
     */
    public function create(string $label = 'manual'): array
    {
        $this->ensureDirectory();

        $startedAt = microtime(true);
        $baseName = 'sikekar-' . now()->format('Ymd-His') . '-' . Str::slug($label ?: 'manual');
        $filename = $baseName . '.sql.gz';

        // Cegah tabrakan nama bila backup dibuat dua kali dalam detik yang sama
        // (double-klik / jadwal berdekatan) — file lama tidak boleh tertimpa.
        $suffix = 1;
        while (is_file($this->path() . DIRECTORY_SEPARATOR . $filename)) {
            $filename = $baseName . '-' . $suffix++ . '.sql.gz';
        }

        $finalPath = $this->path() . DIRECTORY_SEPARATOR . $filename;
        $tempPath = $this->path() . DIRECTORY_SEPARATOR . '.tmp-' . Str::random(16) . '.sql';

        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                $this->dumpSqlite($tempPath);
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                if (!$this->dumpWithMysqlDump($tempPath)) {
                    Log::warning('mysqldump tidak tersedia / gagal. Backup memakai fallback PDO.');
                    $this->dumpWithPdo($tempPath);
                }
            } else {
                throw new RuntimeException("Driver database '{$driver}' belum didukung untuk backup otomatis.");
            }

            if (!is_file($tempPath) || filesize($tempPath) === 0) {
                throw new RuntimeException('File dump database kosong. Backup dibatalkan.');
            }

            // Dump & file backup hanya boleh dibaca oleh owner (data sensitif).
            @chmod($tempPath, 0600);

            $this->compress($tempPath, $finalPath);

            if (is_file($tempPath)) {
                @unlink($tempPath);
            }

            if (!is_file($finalPath) || filesize($finalPath) === 0) {
                throw new RuntimeException('File backup gagal dibuat.');
            }

            @chmod($finalPath, 0600);

            $size = (int) filesize($finalPath);

            return [
                'filename' => $filename,
                'path' => $finalPath,
                'size' => $size,
                'size_human' => $this->formatBytes($size),
                'duration' => round(microtime(true) - $startedAt, 2),
                'created_at' => Carbon::createFromTimestamp((int) filemtime($finalPath)),
                'driver' => $driver,
            ];
        } catch (\Throwable $e) {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }

            // Buang file final parsial hasil kompresi yang gagal — nama file
            // dijamin unik sehingga tidak mungkin menghapus backup lama.
            if (is_file($finalPath)) {
                @unlink($finalPath);
            }

            throw $e;
        }
    }

    /**
     * Daftar backup terbaru, diurutkan dari yang paling baru.
     */
    public function list(): array
    {
        $this->ensureDirectory();

        $files = collect(File::files($this->path()))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.sql.gz') && !str_starts_with($file->getFilename(), '.'))
            ->map(function ($file) {
                $timestamp = (int) $file->getMTime();
                $createdAt = Carbon::createFromTimestamp($timestamp);
                $size = (int) $file->getSize();

                return [
                    'filename' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $size,
                    'size_human' => $this->formatBytes($size),
                    'created_at' => $createdAt,
                    'age' => $createdAt->diffForHumans(),
                    'timestamp' => $timestamp,
                ];
            })
            ->sortByDesc('timestamp')
            ->values()
            ->all();

        return $files;
    }

    public function stats(): array
    {
        $backups = $this->list();
        $totalSize = array_sum(array_column($backups, 'size'));

        return [
            'count' => count($backups),
            'total_size' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'last' => $backups[0] ?? null,
            'retention' => (int) config('backup.retention', 14),
            'path' => $this->path(),
            'writable' => is_writable($this->path()),
        ];
    }

    public function lastBackup(): ?array
    {
        return $this->list()[0] ?? null;
    }

    public function delete(string $filename): bool
    {
        $path = $this->resolvePath($filename);

        if (!$path || !is_file($path)) {
            return false;
        }

        return @unlink($path);
    }

    public function resolvePath(string $filename): ?string
    {
        $filename = basename($filename);

        if (!$this->isValidFilename($filename)) {
            return null;
        }

        $base = realpath($this->path());
        $path = $this->path() . DIRECTORY_SEPARATOR . $filename;

        if (!$base) {
            return null;
        }

        $real = realpath($path);

        if ($real === false || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $real;
    }

    public function isValidFilename(string $filename): bool
    {
        return (bool) preg_match('/^sikekar-\d{8}-\d{6}-[A-Za-z0-9._-]+\.sql\.gz$/', $filename);
    }

    // ==========================================================
    // RESTORE (Fitur B1)
    // ==========================================================

    /**
     * Restore database dari file backup.
     *
     * Urutan pengaman:
     *  1. Validasi nama & keberadaan file backup.
     *  2. Buat backup otomatis SEBELUM restore (pre-restore safety net).
     *  3. Decompress ke file .sql sementara (izin 0600).
     *  4. Eksekusi dump sesuai driver database.
     *  5. Bersihkan file sementara (selalu, walau gagal).
     *
     * @return array metadata restore (termasuk pre_restore_backup)
     * @throws RuntimeException bila restore gagal / driver tidak didukung
     */
    public function restore(string $filename, ?int $userId = null): array
    {
        $startedAt = microtime(true);

        $path = $this->resolvePath($filename);

        if (!$path || !is_file($path)) {
            throw new RuntimeException('File backup tidak ditemukan atau nama file tidak valid.');
        }

        if (filesize($path) === 0) {
            throw new RuntimeException('File backup kosong — restore dibatalkan.');
        }

        // 1. Backup otomatis kondisi saat ini SEBELUM menimpa apapun.
        $preRestore = $this->create('pre-restore');

        $driver = DB::connection()->getDriverName();
        $sqlPath = $this->path() . DIRECTORY_SEPARATOR . '.tmp-restore-' . Str::random(16) . '.sql';
        $errorPath = $sqlPath . '.err';

        try {
            // 2. Decompress
            $this->decompress($path, $sqlPath);

            if (!is_file($sqlPath) || filesize($sqlPath) === 0) {
                throw new RuntimeException('File backup gagal didecompress atau isinya kosong.');
            }

            @chmod($sqlPath, 0600);

            // 3. Eksekusi restore
            if ($driver === 'sqlite') {
                $this->restoreSqlite($sqlPath);
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                $this->restoreMysql($sqlPath, $errorPath);
            } else {
                throw new RuntimeException("Driver database '{$driver}' belum didukung untuk restore.");
            }

            $duration = round(microtime(true) - $startedAt, 2);

            BackupLog::record([
                'filename' => $filename,
                'trigger' => 'restore',
                'local_success' => true,
                'offsite_success' => false,
                'size' => (int) filesize($path),
                'status' => 'success',
                'message' => 'Restore berhasil dalam ' . $duration . ' detik. Backup pra-restore: ' . $preRestore['filename'],
                'created_by' => $userId,
            ]);

            // Koneksi dibuang agar struktur/schema baru terbaca pada request berikutnya.
            try {
                DB::purge();
            } catch (\Throwable $e) {
                report($e);
            }

            return [
                'filename' => $filename,
                'pre_restore_backup' => $preRestore['filename'],
                'duration' => $duration,
                'driver' => $driver,
            ];
        } catch (\Throwable $e) {
            BackupLog::record([
                'filename' => $filename,
                'trigger' => 'restore',
                'local_success' => false,
                'size' => (int) filesize($path),
                'status' => 'failed',
                'message' => 'Restore gagal: ' . $e->getMessage(),
                'created_by' => $userId,
            ]);

            $this->notifyFailure(
                'Restore database GAGAL',
                'Pengembalian database dari file ' . $filename . ' gagal: ' . $e->getMessage()
                . '. Backup pra-restore tersedia: ' . ($preRestore['filename'] ?? '-')
            );

            throw $e;
        } finally {
            foreach ([$sqlPath, $errorPath] as $tmp) {
                if (is_file($tmp)) {
                    @unlink($tmp);
                }
            }
        }
    }

    /** Decompress .sql.gz → .sql. */
    protected function decompress(string $source, string $destination): void
    {
        if (!function_exists('gzopen')) {
            throw new RuntimeException('Ekstensi zlib tidak tersedia untuk decompress backup.');
        }

        $input = gzopen($source, 'rb');
        $output = fopen($destination, 'wb');

        if ($input === false || $output === false) {
            throw new RuntimeException('Tidak dapat membuka file backup untuk restore.');
        }

        try {
            while (!gzeof($input)) {
                $chunk = gzread($input, 1024 * 1024);

                if ($chunk === false) {
                    throw new RuntimeException('Gagal membaca isi file backup (file rusak).');
                }

                if ($chunk === '') {
                    continue;
                }

                if (fwrite($output, $chunk) === false) {
                    throw new RuntimeException('Gagal menulis file sementara restore — periksa izin folder backup.');
                }
            }
        } finally {
            gzclose($input);
            fclose($output);
        }
    }

    /**
     * Restore SQLite: dump SQLite dari service ini adalah salinan file database
     * mentah, jadi restore = menyalinnya kembali (setelah backup pra-restore).
     */
    protected function restoreSqlite(string $sqlPath): void
    {
        $database = DB::connection()->getDatabaseName();

        if (!$database || $database === ':memory:') {
            throw new RuntimeException('Database SQLite in-memory tidak dapat di-restore. Gunakan file database.');
        }

        try {
            DB::purge();
        } catch (\Throwable $e) {
            report($e);
        }

        $target = $database;
        $targetDir = dirname($target);

        if (!is_dir($targetDir) || !is_writable($targetDir)) {
            throw new RuntimeException('Folder database SQLite tidak dapat ditulis.');
        }

        // Salinan file sudah diverifikasi isinya (hasil decompress).
        if (!@copy($sqlPath, $target)) {
            throw new RuntimeException('Gagal menyalin file backup ke lokasi database SQLite.');
        }

        // Buang berkas WAL/SHM lama agar tidak mencampur data pra-restore.
        foreach ([$target . '-wal', $target . '-shm'] as $side) {
            if (is_file($side)) {
                @unlink($side);
            }
        }
    }

    /** Restore MySQL/MariaDB dengan menjalankan klien `mysql`. */
    protected function restoreMysql(string $sqlPath, string $errorPath): void
    {
        $binary = (string) config('backup.mysql', 'mysql');

        if (!$this->binaryExists($binary)) {
            throw new RuntimeException(
                'Klien mysql tidak ditemukan di server. Isi MYSQL_BINARY_PATH pada .env atau restore dilakukan manual.'
            );
        }

        $config = config('database.connections.' . DB::connection()->getName(), []);

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --default-character-set=utf8mb4 %s < %s 2> %s',
            escapeshellarg($binary),
            escapeshellarg((string) ($config['host'] ?? '127.0.0.1')),
            escapeshellarg((string) ($config['port'] ?? '3306')),
            escapeshellarg((string) ($config['username'] ?? 'root')),
            escapeshellarg((string) ($config['database'] ?? '')),
            escapeshellarg($sqlPath),
            escapeshellarg($errorPath)
        );

        $process = Process::fromShellCommandline(
            $command,
            base_path(),
            ['MYSQL_PWD' => (string) ($config['password'] ?? '')],
            null,
            (float) config('backup.timeout', 300)
        );
        $process->run();

        $errorOutput = is_file($errorPath) ? trim((string) file_get_contents($errorPath)) : '';

        if (!$process->isSuccessful()) {
            throw new RuntimeException('Eksekusi restore MySQL gagal: ' . ($errorOutput ?: $process->getErrorOutput() ?: 'detail tidak tersedia'));
        }
    }

    // ==========================================================
    // BACKUP OFFSITE (Fitur B4)
    // ==========================================================

    /**
     * Salin satu file backup ke storage remote (offsite).
     *
     * @return array ['status' => success|failed|skipped|disabled, 'message' => string]
     */
    public function pushOffsite(string $filename, ?int $userId = null): array
    {
        $cfg = (array) config('backup.offsite', []);

        if (empty($cfg['enabled'])) {
            return ['status' => 'disabled', 'message' => 'Backup offsite belum diaktifkan.'];
        }

        $disk = (string) ($cfg['disk'] ?? 's3');
        $prefix = trim((string) ($cfg['prefix'] ?? 'sikekar-backups'), '/');

        $path = $this->resolvePath($filename);

        if (!$path || !is_file($path)) {
            return ['status' => 'failed', 'message' => 'File backup tidak ditemukan: ' . $filename];
        }

        try {
            $filesystem = \Illuminate\Support\Facades\Storage::disk($disk);
            $target = ($prefix !== '' ? $prefix . '/' : '') . $filename;

            $handle = fopen($path, 'rb');

            if ($handle === false) {
                throw new RuntimeException('Tidak dapat membaca file backup.');
            }

            try {
                // Stream agar file backup besar tidak membebani memori.
                $filesystem->writeStream($target, $handle);
            } finally {
                @fclose($handle);
            }

            if (!$filesystem->exists($target)) {
                throw new RuntimeException('File tidak ditemukan setelah diunggah — koneksi remote bermasalah.');
            }

            $this->pruneOffsite($filesystem, $prefix, (int) ($cfg['max_age_days'] ?? 30));

            BackupLog::record([
                'filename' => $filename,
                'trigger' => 'offsite',
                'local_success' => true,
                'offsite_success' => true,
                'offsite_disk' => $disk,
                'size' => (int) filesize($path),
                'status' => 'success',
                'message' => 'Backup offsite berhasil dikirim ke disk ' . $disk . ' (' . $target . ').',
                'created_by' => $userId,
            ]);

            return ['status' => 'success', 'message' => 'Backup offsite berhasil dikirim ke ' . $target . '.'];
        } catch (\Throwable $e) {
            $message = 'Backup offsite GAGAL: ' . $e->getMessage();

            BackupLog::record([
                'filename' => $filename,
                'trigger' => 'offsite',
                'local_success' => true,
                'offsite_success' => false,
                'offsite_disk' => $disk,
                'size' => (int) filesize($path),
                'status' => 'failed',
                'message' => $message,
                'created_by' => $userId,
            ]);

            $this->notifyFailure('Backup Offsite Gagal', $message);

            return ['status' => 'failed', 'message' => $message];
        }
    }

    /** Hapus salinan offsite yang sudah melewati masa retensi. */
    protected function pruneOffsite($filesystem, string $prefix, int $maxAgeDays): void
    {
        if ($maxAgeDays <= 0) {
            return;
        }

        try {
            foreach ($filesystem->files($prefix) as $remote) {
                $lastModified = $filesystem->lastModified($remote);

                if ($lastModified && Carbon::createFromTimestamp($lastModified)->lt(now()->subDays($maxAgeDays))) {
                    $filesystem->delete($remote);
                }
            }
        } catch (\Throwable $e) {
            // Prune bersifat konsolidasi — kegagalannya tidak menggagalkan upload.
            Log::warning('Gagal prune backup offsite: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi email kegagalan ke seluruh Super Admin aktif.
     * Best-effort: kegagalan pengiriman tidak pernah melempar exception.
     */
    protected function notifyFailure(string $subject, string $body): void
    {
        if (!config('backup.notify_failure', true)) {
            return;
        }

        try {
            $recipients = Karyawan::where('posisi', 'superadmin')
                ->where('is_resigned', false)
                ->pluck('email')
                ->filter()
                ->values();

            if ($recipients->isEmpty()) {
                return;
            }

            foreach ($recipients as $email) {
                \Illuminate\Support\Facades\Mail::raw(
                    $body . "\n\nWaktu: " . now()->toDateTimeString()
                        . "\nAplikasi: " . config('app.name')
                        . "\n\nEmail ini dikirim otomatis oleh SIKEKAR.",
                    function ($message) use ($email, $subject) {
                        $message->to($email)->subject('[SIKEKAR] ' . $subject);
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi kegagalan backup: ' . $e->getMessage());
        }
    }

    /**
     * Hapus backup lama, sisakan sejumlah file terbaru.
     */
    public function prune(?int $keep = null): int
    {
        $keep = $keep ?? (int) config('backup.retention', 14);
        $keep = max(1, $keep);

        $backups = $this->list();
        $deleted = 0;

        foreach (array_slice($backups, $keep) as $backup) {
            if ($this->delete($backup['filename'])) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return round($value, $precision) . ' ' . $units[$power];
    }

    protected function dumpSqlite(string $tempPath): void
    {
        $database = DB::connection()->getDatabaseName();

        if (!$database || !is_file($database)) {
            throw new RuntimeException('File database SQLite tidak ditemukan.');
        }

        if (!@copy($database, $tempPath)) {
            throw new RuntimeException('Gagal menyalin file database SQLite.');
        }
    }

    protected function dumpWithMysqlDump(string $tempPath): bool
    {
        try {
            $connection = DB::connection();
            $config = config('database.connections.' . $connection->getName(), []);
            $binary = (string) config('backup.mysqldump', 'mysqldump');

            if (!$this->binaryExists($binary)) {
                return false;
            }

            $errorPath = $tempPath . '.err';
            $command = sprintf(
                '%s --host=%s --port=%s --user=%s --single-transaction --quick --lock-tables=false --no-tablespaces --routines --triggers --default-character-set=utf8mb4 %s > %s 2> %s',
                escapeshellarg($binary),
                escapeshellarg((string) ($config['host'] ?? '127.0.0.1')),
                escapeshellarg((string) ($config['port'] ?? '3306')),
                escapeshellarg((string) ($config['username'] ?? 'root')),
                escapeshellarg((string) ($config['database'] ?? '')),
                escapeshellarg($tempPath),
                escapeshellarg($errorPath)
            );

            $process = Process::fromShellCommandline(
                $command,
                base_path(),
                ['MYSQL_PWD' => (string) ($config['password'] ?? '')],
                null,
                (float) config('backup.timeout', 300)
            );
            $process->run();

            $errorOutput = is_file($errorPath) ? trim((string) file_get_contents($errorPath)) : '';
            if (is_file($errorPath)) {
                @unlink($errorPath);
            }

            if (!$process->isSuccessful()) {
                Log::warning('mysqldump gagal: ' . ($errorOutput ?: $process->getErrorOutput()));

                return false;
            }

            return is_file($tempPath) && filesize($tempPath) > 0;
        } catch (\Throwable $e) {
            Log::warning('mysqldump exception: ' . $e->getMessage());

            return false;
        }
    }

    protected function dumpWithPdo(string $tempPath): void
    {
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        $database = $connection->getDatabaseName();

        if (!$database) {
            throw new RuntimeException('Nama database tidak diketahui.');
        }

        $handle = fopen($tempPath, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Tidak dapat membuat file dump sementara.');
        }

        try {
            fwrite($handle, "-- Backup database {$database}\n");
            fwrite($handle, "-- Dibuat: " . now()->toDateTimeString() . "\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($handle, "SET NAMES utf8mb4;\n\n");

            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $quotedTable = '`' . str_replace('`', '``', (string) $table) . '`';
                $createRow = $pdo->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(\PDO::FETCH_NUM);

                if (!$createRow || !isset($createRow[1])) {
                    continue;
                }

                fwrite($handle, "\n-- --------------------------------------------------------\n");
                fwrite($handle, "-- Tabel {$quotedTable}\n");
                fwrite($handle, "DROP TABLE IF EXISTS {$quotedTable};\n");
                fwrite($handle, $createRow[1] . ";\n\n");

                $statement = $pdo->query('SELECT * FROM ' . $quotedTable);

                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $columns = array_map(fn ($column) => '`' . str_replace('`', '``', (string) $column) . '`', array_keys($row));
                    $values = array_map(function ($value) use ($pdo) {
                        return $value === null ? 'NULL' : $pdo->quote((string) $value);
                    }, array_values($row));

                    fwrite(
                        $handle,
                        'INSERT INTO ' . $quotedTable . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n"
                    );
                }
            }

            fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }
    }

    protected function compress(string $source, string $destination): void
    {
        if (!function_exists('gzopen')) {
            throw new RuntimeException('Ekstensi zlib tidak tersedia untuk mengompres backup.');
        }

        $input = fopen($source, 'rb');
        $output = gzopen($destination, 'wb9');

        if ($input === false || $output === false) {
            throw new RuntimeException('Gagal membuka file untuk proses kompresi backup.');
        }

        try {
            while (!feof($input)) {
                $chunk = fread($input, 1024 * 1024);

                if ($chunk === false) {
                    throw new RuntimeException('Gagal membaca file dump saat kompresi backup.');
                }

                if ($chunk === '') {
                    continue;
                }

                $written = gzwrite($output, $chunk);

                // Deteksi kegagalan tulis (disk penuh / izin ditolak) agar backup
                // korup tidak dilaporkan sebagai sukses.
                if ($written === false || $written < strlen($chunk)) {
                    throw new RuntimeException('Gagal menulis file backup — periksa kapasitas disk dan izin folder backup.');
                }
            }
        } finally {
            fclose($input);
            gzclose($output);
        }
    }

    protected function binaryExists(string $binary): bool
    {
        try {
            if (str_contains($binary, '/') || str_contains($binary, '\\')) {
                return is_file($binary) && is_executable($binary);
            }

            $command = PHP_OS_FAMILY === 'Windows'
                ? 'where ' . escapeshellarg($binary)
                : 'command -v ' . escapeshellarg($binary);

            $process = Process::fromShellCommandline($command, base_path(), null, null, 10);
            $process->run();

            return $process->isSuccessful() && trim($process->getOutput()) !== '';
        } catch (\Throwable $e) {
            return false;
        }
    }
}
