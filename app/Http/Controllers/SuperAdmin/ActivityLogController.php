<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /** Batas baris ekspor CSV agar tidak membanjiri memori/pengunduh. */
    public const EXPORT_MAX_ROWS = 50000;

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $logs = $this->filteredQuery($filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id') // tiebreaker: banyak log tercipta pada detik yang sama
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'unique_users' => ActivityLog::whereDate('created_at', today())
                ->whereNotNull('user_id')
                ->distinct()
                ->count('user_id'),
            'critical' => ActivityLog::whereDate('created_at', today())
                ->whereIn('action', [
                    'backup.delete',
                    'access.suspend',
                    'access.menus',
                    'violation.confirm',
                    'maintenance.toggle',
                    'karyawan.role_update',
                    'auth.failed', // selaras dengan badge "kritis" pada tabel
                ])
                ->count(),
        ];

        $users = Karyawan::orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'posisi']);
        $modules = ActivityLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('superadmin.it.activity-logs', compact('logs', 'stats', 'users', 'modules', 'actions', 'filters'));
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::EXPORT_MAX_ROWS);

        ActivityLog::record(
            'logs.export',
            'Mengekspor log aktivitas ke CSV.',
            [
                'module' => 'system',
                'metadata' => $filters,
            ]
        );

        $filename = 'activity-logs-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'User', 'Role', 'Modul', 'Aksi', 'Deskripsi', 'IP', 'User Agent', 'Metadata']);

            foreach ($query->cursor() as $log) {
                fputcsv($handle, [
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                    $this->csvSafe($log->user_name),
                    $this->csvSafe($log->user_role),
                    $this->csvSafe($log->module),
                    $this->csvSafe($log->action),
                    $this->csvSafe($log->description),
                    $this->csvSafe($log->ip_address),
                    $this->csvSafe($log->user_agent),
                    $log->metadata ? $this->csvSafe(json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : null,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Nilai yang diawali karakter formula (=, +, -, @, tab, CR) diprefiks
     * apostrof agar tidak dieksekusi sebagai formula saat file CSV dibuka
     * di Excel / Google Sheets (CSV formula injection).
     */
    protected function csvSafe($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    protected function filters(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ], [
            'from.date' => 'Filter tanggal "Dari" tidak valid.',
            'to.date' => 'Filter tanggal "Sampai" tidak valid.',
        ]);

        $from = $request->input('from') ?: null;
        $to = $request->input('to') ?: null;

        if ($from && $to && $to < $from) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'to' => 'Filter tanggal "Sampai" tidak boleh sebelum tanggal "Dari".',
            ]);
        }

        return [
            'q' => trim((string) $request->input('q', '')),
            'user_id' => $request->input('user_id'),
            'role' => $request->input('role'),
            'module' => $request->input('module'),
            'action' => $request->input('action'),
            'from' => $from,
            'to' => $to,
        ];
    }

    protected function filteredQuery(array $filters): Builder
    {
        return ActivityLog::query()->filter($filters);
    }
}
