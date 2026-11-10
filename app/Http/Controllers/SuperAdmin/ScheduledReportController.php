<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GeneratedReport;
use App\Models\ScheduledReport;
use App\Services\ReportGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Kelola laporan terjadwal (Fitur C12) — CRUD jadwal, jalankan manual,
 * riwayat generate, dan unduh file laporan.
 */
class ScheduledReportController extends Controller
{
    public function __construct(private ReportGeneratorService $generator)
    {
    }

    public function index()
    {
        $schedules = ScheduledReport::withCount('generatedReports')
            ->with('generatedReports')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(function (ScheduledReport $s) {
                $s->latest_report = $s->generatedReports->sortByDesc('created_at')->first();

                return $s;
            });

        $types = ScheduledReport::TYPES;
        $frequencies = ScheduledReport::FREQUENCIES;
        $formats = ScheduledReport::FORMATS;
        $days = ScheduledReport::DAYS;

        $recentReports = GeneratedReport::with(['scheduledReport', 'generator'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('superadmin.scheduled-reports', compact(
            'schedules', 'types', 'frequencies', 'formats', 'days', 'recentReports'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $schedule = ScheduledReport::create(array_merge($data, [
            'is_active' => $request->boolean('is_active'),
            'next_run_at' => null,
        ]));
        $schedule->forceFill(['next_run_at' => $schedule->computeNextRunAt()])->save();

        ActivityLog::record(
            'report.create',
            'Jadwal laporan "' . $schedule->name . '" dibuat.',
            ['module' => 'reports', 'subject_type' => 'scheduled_reports', 'subject_id' => $schedule->id]
        );

        return redirect()->route('superadmin.reports.index')
            ->with('success', 'Jadwal laporan berhasil dibuat. Jalankan berikutnya: ' . ($schedule->next_run_at ?? '-'));
    }

    public function update(Request $request, int $id)
    {
        $schedule = ScheduledReport::findOrFail($id);
        $data = $this->validated($request, $schedule);

        $schedule->fill($data);
        $schedule->is_active = $request->boolean('is_active');
        $schedule->next_run_at = $schedule->computeNextRunAt();
        $schedule->save();

        ActivityLog::record(
            'report.update',
            'Jadwal laporan "' . $schedule->name . '" diperbarui.',
            ['module' => 'reports', 'subject_type' => 'scheduled_reports', 'subject_id' => $schedule->id]
        );

        return redirect()->route('superadmin.reports.index')
            ->with('success', 'Jadwal laporan berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id)
    {
        $schedule = ScheduledReport::findOrFail($id);
        $name = $schedule->name;

        $schedule->delete();

        ActivityLog::record(
            'report.delete',
            'Jadwal laporan "' . $name . '" dihapus.',
            ['module' => 'reports']
        );

        return redirect()->route('superadmin.reports.index')
            ->with('success', 'Jadwal laporan berhasil dihapus.');
    }

    /** Jalankan satu jadwal laporan secara manual. */
    public function runNow(Request $request, int $id)
    {
        $schedule = ScheduledReport::findOrFail($id);

        try {
            $report = $this->generator->run($schedule, auth()->id());

            ActivityLog::record(
                'report.run',
                'Laporan "' . $schedule->name . '" dijalankan manual: ' . $report->message,
                [
                    'module' => 'reports',
                    'subject_type' => 'generated_reports',
                    'subject_id' => $report->id,
                    'metadata' => ['filename' => $report->filename, 'status' => $report->status],
                ]
            );

            return redirect()->route('superadmin.reports.index')->with(
                $report->status === 'success' ? 'success' : 'error',
                'Laporan "' . $schedule->name . '" selesai: ' . $report->message
            );
        } catch (\Throwable $e) {
            Log::error('Jalankan laporan manual gagal: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id,
                'exception' => $e,
            ]);

            ActivityLog::record(
                'report.run',
                'Laporan "' . $schedule->name . '" GAGAL: ' . $e->getMessage(),
                ['module' => 'reports', 'subject_type' => 'scheduled_reports', 'subject_id' => $schedule->id]
            );

            return redirect()->route('superadmin.reports.index')
                ->with('error', 'Laporan gagal dibuat: ' . $e->getMessage());
        }
    }

    /** Halaman riwayat seluruh laporan yang pernah dibuat. */
    public function history()
    {
        $reports = GeneratedReport::with(['scheduledReport', 'generator'])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('superadmin.scheduled-reports-history', compact('reports'));
    }

    /** Unduh file laporan. */
    public function download(Request $request, int $id)
    {
        $report = GeneratedReport::findOrFail($id);
        $path = $this->generator->directory() . DIRECTORY_SEPARATOR . $report->filename;

        if ($report->filename === '-' || !is_file($path)) {
            return redirect()->route('superadmin.reports.history')
                ->with('error', 'File laporan tidak ditemukan di server.');
        }

        ActivityLog::record(
            'report.download',
            'Laporan "' . $report->filename . '" diunduh.',
            ['module' => 'reports', 'subject_type' => 'generated_reports', 'subject_id' => $report->id]
        );

        return response()->download($path, $report->filename, [
            'Content-Type' => $report->format === 'csv'
                ? 'text/csv; charset=UTF-8'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Validasi input jadwal laporan. */
    private function validated(Request $request, ?ScheduledReport $schedule = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:' . implode(',', array_keys(ScheduledReport::TYPES))],
            'frequency' => ['required', 'in:' . implode(',', array_keys(ScheduledReport::FREQUENCIES))],
            'day_of_week' => ['nullable', 'required_if:frequency,weekly', 'in:' . implode(',', array_keys(ScheduledReport::DAYS))],
            'day_of_month' => ['nullable', 'required_if:frequency,monthly', 'integer', 'between:1,28'],
            'run_time' => ['required', 'date_format:H:i'],
            'period_from' => ['nullable', 'date'],
            'period_to' => ['nullable', 'date', 'after_or_equal:period_from'],
            'format' => ['required', 'in:' . implode(',', array_keys(ScheduledReport::FORMATS))],
            'recipients' => ['nullable', 'string', 'max:2000'],
        ], [
            'day_of_week.required_if' => 'Pilih hari untuk jadwal mingguan.',
            'day_of_month.required_if' => 'Pilih tanggal (1-28) untuk jadwal bulanan.',
            'period_to.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        // recipients ditulis sebagai teks dipisah koma / baris baru.
        $data['recipients'] = collect(preg_split('/[\s,;]+/', (string) ($request->input('recipients') ?? '')))
            ->filter(fn ($v) => $v !== '' && filter_var($v, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();
        $data['day_of_week'] = $data['frequency'] === 'weekly' ? ($data['day_of_week'] ?? null) : null;
        $data['day_of_month'] = $data['frequency'] === 'monthly' ? (int) ($data['day_of_month'] ?? null) : null;

        return $data;
    }
}
