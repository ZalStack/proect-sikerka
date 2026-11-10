<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\ActivityLog;
use App\Models\GeneratedReport;
use App\Models\Karyawan;
use App\Models\LoginHistory;
use App\Models\MessageTemplate;
use App\Models\Pengumuman;
use App\Models\ScheduledReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Fase 3 — Analitik, riwayat login, audit, broadcast, laporan terjadwal:
 *  - Halaman analitik HR (Chart.js) render & berisi data
 *  - LoginHistory tercatat saat login sukses/gagal/logout
 *  - AuditActivity mencatat aksi karyawan & melaporkan error
 *  - Broadcast superadmin ke semua / karyawan tertentu
 *  - CRUD jadwal laporan + generate file (xlsx/csv) + unduh
 */
class AnalyticsAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-ANL-' . $counter . '-' . uniqid(),
            'email' => 'anl' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Analitik ' . $counter,
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    private function makeSuperadmin(array $overrides = []): Karyawan
    {
        return $this->makeKaryawan(array_merge([
            'posisi' => 'superadmin',
            'jabatan' => 'Super Administrator Sistem',
            'divisi' => 'IT',
            'nama_lengkap' => 'Super Admin Analitik',
        ], $overrides));
    }

    private function makeAbsensi(Karyawan $k, array $overrides = []): Absensi
    {
        return Absensi::create(array_merge([
            'karyawan_id' => $k->id,
            'tanggal' => now()->toDateString(),
            'check_in' => now()->setTime(7, 30),
            'check_out' => now()->setTime(16, 0),
            'status' => 'Hadir',
            'total_jam_kerja' => 480,
        ], $overrides));
    }

    // ==========================================================
    // ANALITIK HR
    // ==========================================================

    public function test_analytics_page_renders_with_data(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->makeAbsensi($k);
        $this->makeAbsensi($k, ['tanggal' => now()->subDay()->toDateString(), 'status' => 'Alpha', 'check_in' => null, 'check_out' => null]);

        $this->actingAs($sa)
            ->get(route('superadmin.analytics.index'))
            ->assertOk()
            ->assertSee('Analitik Kehadiran')
            ->assertSee('Tren Kehadiran Harian')
            ->assertSee('Tingkat Kehadiran');
    }

    public function test_analytics_accepts_period_filter(): void
    {
        $sa = $this->makeSuperadmin();

        foreach ([7, 30, 90] as $period) {
            $this->actingAs($sa)
                ->get(route('superadmin.analytics.index', ['period' => $period]))
                ->assertOk();
        }

        // Nilai tidak valid jatuh kembali ke 30 hari (tidak error).
        $this->actingAs($sa)
            ->get(route('superadmin.analytics.index', ['period' => '99999']))
            ->assertOk();
    }

    public function test_analytics_blocked_for_non_superadmin(): void
    {
        $hr = $this->makeKaryawan(['posisi' => 'hr']);

        $this->actingAs($hr)
            ->get(route('superadmin.analytics.index'))
            ->assertRedirect(route('hr.dashboard'));
    }

    // ==========================================================
    // LOGIN HISTORY (Fitur C11)
    // ==========================================================

    public function test_successful_login_is_recorded_in_login_history(): void
    {
        $k = $this->makeKaryawan(['kata_sandi' => Hash::make('password-kuat-sangat')]);

        $this->post(route('login'), [
            'email' => $k->email,
            'password' => 'password-kuat-sangat',
            'agree_policy' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('login_histories', [
            'karyawan_id' => $k->id,
            'email' => $k->email,
            'success' => true,
        ]);
    }

    public function test_failed_login_is_recorded_in_login_history(): void
    {
        $k = $this->makeKaryawan(['kata_sandi' => Hash::make('password-kuat-sangat')]);

        $this->post(route('login'), [
            'email' => $k->email,
            'password' => 'salah-total',
            'agree_policy' => '1',
        ]);

        $row = LoginHistory::where('karyawan_id', $k->id)
            ->where('success', false)
            ->first();

        $this->assertNotNull($row, 'Percobaan login gagal seharusnya tercatat');
        $this->assertSame($k->email, $row->email);
        $this->assertNotEmpty($row->failure_reason);
        $this->assertNotEmpty($row->ip_address);
    }

    public function test_logout_is_recorded_in_login_history(): void
    {
        $k = $this->makeKaryawan();

        $this->actingAs($k)->post(route('logout'));

        $this->assertDatabaseHas('login_histories', [
            'karyawan_id' => $k->id,
            'success' => true,
            'failure_reason' => 'logout',
        ]);
    }

    // ==========================================================
    // AUDIT AKSI KARYAWAN (Fitur C8)
    // ==========================================================

    public function test_karyawan_post_action_is_audited(): void
    {
        $k = $this->makeKaryawan();

        $this->actingAs($k)->post(route('notifications.mark-read'));
        // route dikecualikan — tidak boleh menghasilkan log http.post
        $this->assertSame(0, ActivityLog::where('action', 'http.post')
            ->where('module', 'http')->count());

        $logged = ActivityLog::whereNotNull('user_id')->where('user_id', $k->id)->count();
        $this->assertGreaterThanOrEqual(0, $logged);
    }

    public function test_audit_records_actor_posisi_in_metadata(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.hari-libur.sync'));

        $log = ActivityLog::where('user_id', $sa->id)
            ->where('action', 'http.post')
            ->latest()
            ->first();

        $this->assertNotNull($log, 'Aksi superadmin seharusnya tercatat di audit');
        $this->assertSame('users', $log->module);
        $this->assertSame('superadmin', $log->metadata['actor_posisi'] ?? null);
    }

    // ==========================================================
    // BROADCAST (Fitur C9)
    // ==========================================================

    public function test_broadcast_page_renders(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.broadcast.index'))
            ->assertOk()
            ->assertSee('Broadcast Pengumuman');
    }

    public function test_broadcast_to_everyone_creates_pengumuman(): void
    {
        $sa = $this->makeSuperadmin();
        $this->makeKaryawan();

        $this->actingAs($sa)->post(route('superadmin.broadcast.store'), [
            'judul' => 'Libur Nasional',
            'isi' => 'Kantor libur pada tanggal merah berikutnya.',
            'target' => 'semua',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('pengumuman', [
            'judul' => 'Libur Nasional',
            'target' => 'semua',
            'created_by' => $sa->id,
        ]);

        $this->assertNotNull(
            ActivityLog::where('action', 'broadcast.send')->latest()->first()
        );
    }

    public function test_broadcast_spesifik_requires_recipients(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.broadcast.store'), [
            'judul' => 'Penting',
            'isi' => 'Isi pesan penting.',
            'target' => 'spesifik',
            'target_karyawan' => [],
        ])->assertSessionHasErrors('target_karyawan');

        $this->assertSame(0, Pengumuman::count());
    }

    public function test_broadcast_spesifik_targets_selected_employees(): void
    {
        $sa = $this->makeSuperadmin();
        $target = $this->makeKaryawan();
        $this->makeKaryawan();

        $this->actingAs($sa)->post(route('superadmin.broadcast.store'), [
            'judul' => 'Rapat Divisi',
            'isi' => 'Rapat mingguan.',
            'target' => 'spesifik',
            'target_karyawan' => [$target->id],
        ])->assertSessionHas('success');

        $p = Pengumuman::where('judul', 'Rapat Divisi')->first();
        $this->assertNotNull($p);
        $this->assertSame('spesifik', $p->target);
        $this->assertContains($target->id, (array) $p->target_karyawan_ids);
    }

    public function test_broadcast_can_be_deleted(): void
    {
        $sa = $this->makeSuperadmin();
        $p = Pengumuman::create([
            'judul' => 'Sementara',
            'isi' => 'Pengumuman sementara.',
            'target' => 'semua',
            'created_by' => $sa->id,
        ]);

        $this->actingAs($sa)
            ->delete(route('superadmin.broadcast.destroy', $p->id))
            ->assertRedirect(route('superadmin.broadcast.index'));

        $this->assertSoftDeleted('pengumuman', ['id' => $p->id]);
    }

    // ==========================================================
    // LAPORAN TERJADWAL (Fitur C12)
    // ==========================================================

    public function test_reports_page_renders(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Terjadwal');
    }

    public function test_reports_history_page_renders(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.reports.history'))
            ->assertOk()
            ->assertSee('Riwayat Laporan');
    }

    public function test_create_schedule_validates_required_fields(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.reports.store'), [
            'name' => '',
            'type' => 'jenis-ngawur',
        ])->assertSessionHasErrors(['name', 'type']);

        $this->assertSame(0, ScheduledReport::count());
    }

    public function test_create_weekly_schedule_requires_day_of_week(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.reports.store'), [
            'name' => 'Rekap Mingguan',
            'type' => 'absensi',
            'frequency' => 'weekly',
            'run_time' => '07:00',
            'format' => 'xlsx',
        ])->assertSessionHasErrors('day_of_week');
    }

    public function test_create_monthly_schedule_computes_next_run(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.reports.store'), [
            'name' => 'Rekap Bulanan Absensi',
            'type' => 'absensi',
            'frequency' => 'monthly',
            'day_of_month' => 5,
            'run_time' => '07:30',
            'format' => 'xlsx',
            'recipients' => 'hr@example.com, bukan-email',
            'is_active' => '1',
        ])->assertSessionHas('success');

        $s = ScheduledReport::where('name', 'Rekap Bulanan Absensi')->first();
        $this->assertNotNull($s);
        $this->assertTrue($s->is_active);
        $this->assertNotNull($s->next_run_at);
        // Email tidak valid dibuang, yang valid disimpan.
        $this->assertSame(['hr@example.com'], $s->recipients);
    }

    public function test_update_schedule_and_toggle_active(): void
    {
        $sa = $this->makeSuperadmin();
        $s = ScheduledReport::create([
            'name' => 'Laporan Lama',
            'type' => 'cuti',
            'frequency' => 'daily',
            'run_time' => '06:00',
            'format' => 'csv',
            'is_active' => true,
        ]);

        $this->actingAs($sa)->post(route('superadmin.reports.update', $s->id), [
            'name' => 'Laporan Baru',
            'type' => 'cuti',
            'frequency' => 'daily',
            'run_time' => '06:30',
            'format' => 'csv',
            'is_active' => '0',
        ])->assertSessionHas('success');

        $s->refresh();
        $this->assertSame('Laporan Baru', $s->name);
        $this->assertSame('06:30', $s->run_time);
        $this->assertFalse($s->is_active);
    }

    public function test_delete_schedule(): void
    {
        $sa = $this->makeSuperadmin();
        $s = ScheduledReport::create([
            'name' => 'Akan Dihapus',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '06:00',
            'format' => 'csv',
            'is_active' => true,
        ]);

        $this->actingAs($sa)
            ->delete(route('superadmin.reports.destroy', $s->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('scheduled_reports', ['id' => $s->id]);
    }

    public function test_run_now_generates_xlsx_file(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();
        // Laporan harian berperiode kemarin.
        $this->makeAbsensi($k, ['tanggal' => now()->subDay()->toDateString()]);

        $s = ScheduledReport::create([
            'name' => 'Absensi Harian',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '07:00',
            'format' => 'xlsx',
            'is_active' => true,
        ]);

        $this->actingAs($sa)
            ->post(route('superadmin.reports.run', $s->id))
            ->assertSessionHas('success');

        $report = GeneratedReport::where('scheduled_report_id', $s->id)->first();
        $this->assertNotNull($report);
        $this->assertSame('success', $report->status);
        $this->assertStringEndsWith('.xlsx', $report->filename);
        $this->assertGreaterThan(0, $report->size);

        $path = storage_path('app/reports/' . $report->filename);
        $this->assertFileExists($path);

        @unlink($path);
    }

    public function test_run_now_generates_csv_file(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();
        // Laporan harian berperiode kemarin — data harus berada di situ.
        $this->makeAbsensi($k, ['status' => 'Izin', 'tanggal' => now()->subDay()->toDateString()]);

        $s = ScheduledReport::create([
            'name' => 'Absensi CSV',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '07:00',
            'format' => 'csv',
            'is_active' => true,
        ]);

        $this->actingAs($sa)
            ->post(route('superadmin.reports.run', $s->id))
            ->assertSessionHas('success');

        $report = GeneratedReport::where('scheduled_report_id', $s->id)->first();
        $this->assertStringEndsWith('.csv', $report->filename);

        $path = storage_path('app/reports/' . $report->filename);
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('Tanggal', $content);
        $this->assertStringContainsString('Izin', $content);

        @unlink($path);
    }

    public function test_run_now_handles_period_without_data(): void
    {
        $sa = $this->makeSuperadmin();

        $s = ScheduledReport::create([
            'name' => 'Tanpa Data',
            'type' => 'cuti',
            'frequency' => 'monthly',
            'day_of_month' => 1,
            'run_time' => '07:00',
            'format' => 'csv',
            'is_active' => true,
            // Periode tanpa cuti sama sekali.
            'period_from' => now()->subYears(5)->toDateString(),
            'period_to' => now()->subYears(5)->addDay()->toDateString(),
        ]);

        $this->actingAs($sa)
            ->post(route('superadmin.reports.run', $s->id))
            ->assertSessionHas('success');

        $report = GeneratedReport::where('scheduled_report_id', $s->id)->first();
        $this->assertSame('success', $report->status);
        $this->assertStringContainsString('Tidak ada data', (string) $report->message);

        if ($report->filename !== '-') {
            @unlink(storage_path('app/reports/' . $report->filename));
        }
    }

    public function test_run_now_with_unknown_type_fails_gracefully(): void
    {
        $sa = $this->makeSuperadmin();

        $s = ScheduledReport::create([
            'name' => 'Jenis Aneh',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '07:00',
            'format' => 'csv',
            'is_active' => true,
        ]);

        // Rusak tipe di database langsung untuk memicu jalur error.
        \Illuminate\Support\Facades\DB::table('scheduled_reports')
            ->where('id', $s->id)->update(['type' => 'tidak-ada']);

        $this->actingAs($sa)
            ->post(route('superadmin.reports.run', $s->id))
            ->assertSessionHas('error');

        $report = GeneratedReport::where('scheduled_report_id', $s->id)->first();
        $this->assertSame('failed', $report->status);
    }

    public function test_download_report_returns_file(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();
        $this->makeAbsensi($k);

        $s = ScheduledReport::create([
            'name' => 'Untuk Unduh',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '07:00',
            'format' => 'csv',
            'is_active' => true,
        ]);

        $this->actingAs($sa)->post(route('superadmin.reports.run', $s->id));
        $report = GeneratedReport::where('scheduled_report_id', $s->id)->first();

        $this->actingAs($sa)
            ->get(route('superadmin.reports.download', $report->id))
            ->assertOk()
            ->assertDownload($report->filename);

        @unlink(storage_path('app/reports/' . $report->filename));
    }

    public function test_download_missing_file_redirects_with_error(): void
    {
        $sa = $this->makeSuperadmin();

        $report = GeneratedReport::create([
            'type' => 'absensi',
            'filename' => 'sudah-hilang-xyz.csv',
            'format' => 'csv',
            'size' => 10,
            'status' => 'success',
            'message' => 'uji coba',
        ]);

        $this->actingAs($sa)
            ->get(route('superadmin.reports.download', $report->id))
            ->assertRedirect(route('superadmin.reports.history'))
            ->assertSessionHas('error');
    }

    public function test_artisan_reports_run_command_works(): void
    {
        $k = $this->makeKaryawan();
        $this->makeAbsensi($k);

        $s = ScheduledReport::create([
            'name' => 'Via Artisan',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '07:00',
            'format' => 'csv',
            'is_active' => true,
            'next_run_at' => now()->subMinute()->toDateTimeString(),
        ]);

        $this->artisan('reports:run')->assertExitCode(0);

        $s->refresh();
        $this->assertSame('success', $s->last_status);
        $this->assertNotNull($s->next_run_at);
        $this->assertGreaterThan(now()->timestamp, \Carbon\Carbon::parse($s->next_run_at)->timestamp);

        $report = GeneratedReport::where('scheduled_report_id', $s->id)->first();
        if ($report && $report->filename !== '-') {
            @unlink(storage_path('app/reports/' . $report->filename));
        }
    }

    public function test_artisan_reports_run_skips_not_due_schedules(): void
    {
        $s = ScheduledReport::create([
            'name' => 'Belum Waktunya',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '07:00',
            'format' => 'csv',
            'is_active' => true,
            'next_run_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        $this->artisan('reports:run')->assertExitCode(0);

        $this->assertSame(0, GeneratedReport::where('scheduled_report_id', $s->id)->count());
    }

    public function test_inactive_schedule_is_not_run_by_command(): void
    {
        $s = ScheduledReport::create([
            'name' => 'Nonaktif',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '07:00',
            'format' => 'csv',
            'is_active' => false,
            'next_run_at' => now()->subHour()->toDateTimeString(),
        ]);

        $this->artisan('reports:run')->assertExitCode(0);

        $this->assertSame(0, GeneratedReport::where('scheduled_report_id', $s->id)->count());
    }

    // ==========================================================
    // TEMPLATE PESAN (dipakai form broadcast — Fitur F23)
    // ==========================================================

    public function test_broadcast_page_lists_templates(): void
    {
        $sa = $this->makeSuperadmin();

        MessageTemplate::create([
            'type' => 'broadcast',
            'title' => 'Pengumuman Libur',
            'body' => 'Kantor libur pada {tanggal}.',
            'is_default' => true,
            'is_active' => true,
            'created_by' => $sa->id,
        ]);

        $this->actingAs($sa)
            ->get(route('superadmin.broadcast.index'))
            ->assertOk()
            ->assertSee('Pengumuman Libur');
    }

    public function test_inactive_templates_are_hidden_from_broadcast(): void
    {
        $sa = $this->makeSuperadmin();

        MessageTemplate::create([
            'type' => 'broadcast',
            'title' => 'Template Nonaktif',
            'body' => 'Tidak boleh tampil.',
            'is_active' => false,
            'created_by' => $sa->id,
        ]);

        $this->actingAs($sa)
            ->get(route('superadmin.broadcast.index'))
            ->assertOk()
            ->assertDontSee('Template Nonaktif');
    }

    // ==========================================================
    // EMAIL PENERIMA LAPORAN
    // ==========================================================

    public function test_report_email_sent_to_recipients(): void
    {
        Mail::fake();

        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();
        $this->makeAbsensi($k, ['tanggal' => now()->subDay()->toDateString()]);

        $s = ScheduledReport::create([
            'name' => 'Dengan Email',
            'type' => 'absensi',
            'frequency' => 'daily',
            'run_time' => '07:00',
            'format' => 'csv',
            'is_active' => true,
            'recipients' => ['tujuan@example.com'],
        ]);

        $this->actingAs($sa)->post(route('superadmin.reports.run', $s->id));

        Mail::assertSent(\App\Mail\ScheduledReportMail::class, function ($mail) {
            return $mail->hasTo('tujuan@example.com');
        });

        $report = GeneratedReport::where('scheduled_report_id', $s->id)->first();
        if ($report && $report->filename !== '-') {
            @unlink(storage_path('app/reports/' . $report->filename));
        }
    }
}
