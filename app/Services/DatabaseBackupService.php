<?php

namespace App\Services;

use Carbon\Carbon;
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
            File::makeDirectory($this->path(), 0755, true);
        }

        if (!is_writable($this->path())) {
            throw new RuntimeException('Folder backup tidak dapat ditulis. Periksa izin akses storage/app/backups.');
        }
    }

    /**
     * Buat file backup database baru. Mengembalikan metadata file.
     */
    public function create(string $label = 'manual'): array
    {
        $this->ensureDirectory();

        $startedAt = microtime(true);
        $filename = 'sikekar-' . now()->format('Ymd-His') . '-' . Str::slug($label ?: 'manual') . '.sql.gz';
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

            $this->compress($tempPath, $finalPath);

            if (is_file($tempPath)) {
                @unlink($tempPath);
            }

            if (!is_file($finalPath) || filesize($finalPath) === 0) {
                throw new RuntimeException('File backup gagal dibuat.');
            }

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
            if (is_file($finalPath) && filesize($finalPath) === 0) {
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
                    break;
                }
                gzwrite($output, $chunk);
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
