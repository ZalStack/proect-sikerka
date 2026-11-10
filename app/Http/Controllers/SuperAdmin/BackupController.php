<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BackupLog;
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
        // Folder backup bisa saja tidak terbaca/tidak dapat ditulis — jangan
        // biarkan halaman daftar backup gagal 500 karena itu.
        try {
            $backups = $this->backups->list();
            $stats = $this->backups->stats();
        } catch (\Throwable $e) {
            Log::error('Gagal membaca daftar backup: ' . $e->getMessage(), ['exception' => $e]);

            $backups = [];
            $stats = [
                'count' => 0,
                'total_size' => 0,
                'total_size_human' => '0 B',
                'last' => null,
                'retention' => (int) config('backup.retention', 14),
                'path' => (string) config('backup.path', storage_path('app/backups')),
                'writable' => false,
            ];

            session()->now(
                'error',
                'Folder backup tidak dapat diakses. Periksa izin akses folder storage/app/backups pada server. (' . $e->getMessage() . ')'
            );
        }

        $offsite = (array) config('backup.offsite', []);
        $logs = BackupLog::orderByDesc('created_at')->limit(20)->get();

        return view('superadmin.it.backup', compact('backups', 'stats', 'offsite', 'logs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:40',
        ]);

        try {
            $result = $this->backups->create($request->input('label', 'manual'));

            // Prune otomatis agar folder tidak tumbuh tanpa batas.
            $pruned = 0;
            try {
                $pruned = $this->backups->prune();
            } catch (\Throwable $e) {
                report($e);
            }

            BackupLog::record([
                'filename' => $result['filename'],
                'trigger' => 'manual',
                'local_success' => true,
                'size' => $result['size'],
                'status' => 'success',
                'message' => 'Backup manual berhasil dibuat' . ($pruned > 0 ? " ({$pruned} file lama dihapus)" : '') . '.',
                'created_by' => auth()->id(),
            ]);

            ActivityLog::record(
                'backup.create',
                'Backup database dibuat: ' . $result['filename'] . ' (' . $result['size_human'] . ').',
                ['module' => 'backup']
            );

            // Offsite bersifat opsional & tidak menggagalkan backup lokal.
            $offsiteResult = null;
            if (!empty(config('backup.offsite.enabled'))) {
                $offsiteResult = $this->backups->pushOffsite($result['filename'], auth()->id());
            }

            $message = 'Backup berhasil dibuat: ' . $result['filename'] . ' (' . $result['size_human'] . ').';

            if ($offsiteResult !== null && $offsiteResult['status'] === 'failed') {
                $message .= ' PERHATIAN: kirim offsite gagal — ' . $offsiteResult['message'];
            }

            return redirect()->route('superadmin.backup.index')->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Backup manual gagal: ' . $e->getMessage(), ['exception' => $e]);

            ActivityLog::record('backup.create', 'Backup database GAGAL: ' . $e->getMessage(), ['module' => 'backup']);

            return redirect()->route('superadmin.backup.index')
                ->with('error', 'Backup gagal dibuat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim satu file backup ke storage remote (offsite).
     */
    public function pushOffsite(Request $request, string $filename)
    {
        if (!config('backup.offsite.enabled')) {
            return redirect()->route('superadmin.backup.index')
                ->with('error', 'Backup offsite belum diaktifkan. Aktifkan dulu di konfigurasi BACKUP_OFFSITE_ENABLED.');
        }

        $result = $this->backups->pushOffsite($filename, auth()->id());

        ActivityLog::record(
            'backup.offsite_push',
            'Kirim backup offsite ' . $filename . ': ' . $result['status'] . ' — ' . $result['message'],
            ['module' => 'backup', 'metadata' => ['filename' => $filename, 'status' => $result['status']]]
        );

        return redirect()->route('superadmin.backup.index')->with(
            $result['status'] === 'success' ? 'success' : 'error',
            $result['message']
        );
    }

    public function download(string $filename)
    {
        $path = $this->backups->resolvePath($filename);

        if (!$path || !is_file($path)) {
            return redirect()->route('superadmin.backup.index')
                ->with('error', 'File backup tidak ditemukan.');
        }

        ActivityLog::record('backup.download', 'Backup diunduh: ' . $filename, ['module' => 'backup']);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    /**
     * Restore database dari file backup (Fitur B1).
     *
     * Selalu membuat backup otomatis SEBELUM restore (pre-restore safety net)
     * dan meminta konfirmasi eksplisit dari operator.
     */
    public function restore(Request $request, string $filename)
    {
        $request->validate([
            'confirm' => ['required', 'in:RESTORE'],
        ], [
            'confirm.required' => 'Ketik RESTORE untuk konfirmasi.',
            'confirm.in' => 'Konfirmasi salah. Ketik RESTORE dengan huruf kapital.',
        ]);

        $path = $this->backups->resolvePath($filename);

        if (!$path || !is_file($path)) {
            return redirect()->route('superadmin.backup.index')
                ->with('error', 'File backup tidak ditemukan.');
        }

        try {
            $result = $this->backups->restore($filename, auth()->id());

            ActivityLog::record(
                'backup.restore',
                'Restore database dari ' . $filename . ' berhasil. Backup pra-restore: ' . $result['pre_restore_backup'] . '.',
                ['module' => 'backup', 'metadata' => $result]
            );

            return redirect()->route('superadmin.backup.index')->with(
                'success',
                'Restore berhasil dari ' . $filename . ' dalam ' . $result['duration'] . ' detik. '
                . 'Kondisi sebelum restore tersimpan pada backup: ' . $result['pre_restore_backup']
            );
        } catch (\Throwable $e) {
            Log::error('Restore backup gagal: ' . $e->getMessage(), ['file' => $filename, 'exception' => $e]);

            return redirect()->route('superadmin.backup.index')
                ->with('error', 'Restore gagal: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, string $filename)
    {
        $path = $this->backups->resolvePath($filename);

        if (!$path || !is_file($path)) {
            return redirect()->route('superadmin.backup.index')
                ->with('error', 'File backup tidak ditemukan.');
        }

        $deleted = $this->backups->delete($filename);

        if (!$deleted) {
            return redirect()->route('superadmin.backup.index')
                ->with('error', 'File backup gagal dihapus. Periksa izin folder backup.');
        }

        ActivityLog::record('backup.delete', 'Backup dihapus: ' . $filename, ['module' => 'backup']);

        return redirect()->route('superadmin.backup.index')->with('success', 'Backup berhasil dihapus.');
    }
}
