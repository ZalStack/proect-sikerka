<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function __construct(private DatabaseBackupService $backups)
    {
    }

    public function index()
    {
        $backups = $this->backups->list();
        $stats = $this->backups->stats();

        return view('superadmin.it.backup', compact('backups', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:40',
        ]);

        try {
            $result = $this->backups->create($request->input('label', 'manual'));
            $pruned = $this->backups->prune();

            ActivityLog::record(
                'backup.create',
                'Membuat backup database ' . $result['filename'] . ' (' . $result['size_human'] . ').',
                [
                    'module' => 'backup',
                    'metadata' => [
                        'filename' => $result['filename'],
                        'size' => $result['size'],
                        'size_human' => $result['size_human'],
                        'duration' => $result['duration'],
                        'driver' => $result['driver'],
                        'pruned' => $pruned,
                    ],
                ]
            );

            return redirect()->back()->with(
                'success',
                'Backup database berhasil dibuat: ' . $result['filename'] . ' (' . $result['size_human'] . ', ' . $result['duration'] . ' detik).'
                . ($pruned > 0 ? " {$pruned} backup lama dihapus otomatis." : '')
            );
        } catch (\Throwable $e) {
            Log::error('Gagal membuat backup database: ' . $e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with('error', 'Backup gagal dibuat: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $path = $this->backups->resolvePath($filename);

        if (!$path || !is_file($path)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        ActivityLog::record(
            'backup.download',
            'Mengunduh backup database ' . basename($path) . '.',
            [
                'module' => 'backup',
                'metadata' => [
                    'filename' => basename($path),
                    'size' => filesize($path),
                ],
            ]
        );

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/gzip',
        ]);
    }

    public function destroy(string $filename)
    {
        $path = $this->backups->resolvePath($filename);

        if (!$path || !is_file($path)) {
            return redirect()->back()->with('error', 'File backup tidak ditemukan atau sudah dihapus.');
        }

        $size = filesize($path);
        $deleted = $this->backups->delete($filename);

        if (!$deleted) {
            return redirect()->back()->with('error', 'File backup gagal dihapus. Periksa izin akses folder backup.');
        }

        ActivityLog::record(
            'backup.delete',
            'Menghapus backup database ' . basename($path) . '.',
            [
                'module' => 'backup',
                'metadata' => [
                    'filename' => basename($path),
                    'size' => $size,
                ],
            ]
        );

        return redirect()->back()->with('success', 'File backup ' . basename($path) . ' berhasil dihapus.');
    }
}
