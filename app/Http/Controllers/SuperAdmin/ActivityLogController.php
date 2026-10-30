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
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $logs = $this->filteredQuery($filters)
            ->orderByDesc('created_at')
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
        $query = $this->filteredQuery($filters)->orderByDesc('created_at');

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
                    $log->user_name,
                    $log->user_role,
                    $log->module,
                    $log->action,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                    $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'user_id' => $request->input('user_id'),
            'role' => $request->input('role'),
            'module' => $request->input('module'),
            'action' => $request->input('action'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];
    }

    protected function filteredQuery(array $filters): Builder
    {
        return ActivityLog::query()->filter($filters);
    }
}
