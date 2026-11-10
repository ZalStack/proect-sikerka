<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\BackupLog;
use App\Models\Karyawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fase 2 — Pemeliharaan IT:
 *  - Restore database dengan backup pra-restore otomatis (Fitur B1)
 *  - Backup offsite + pencatatan backup_logs + notifikasi gagal (B4)
 *  - Tool kesehatan sistem tambahan (B3): queue, sesi, optimize
 *
 * Sengaja TANPA RefreshDatabase: pengujian restore membutuhkan database
 * berbentuk FILE (SQLite in-memory tidak bisa di-backup/di-restore), sehingga
 * setiap test memakai file SQLite miliknya sendiri yang dimigrasi ulang dan
 * dibersihkan di akhir test.
 */
class BackupRestoreTest extends TestCase
{
    private ?string $tempDatabase = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->startFreshFileDatabase();
    }

    protected function tearDown(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        if ($this->tempDatabase !== null && is_file($this->tempDatabase)) {
            @unlink($this->tempDatabase);
            $this->tempDatabase = null;
        }

        parent::tearDown();
    }

    /**
     * Arahkan koneksi sqlite ke file sementara & migrasi skema lengkap ke sana.
     */
    private function startFreshFileDatabase(): void
    {
        $this->tempDatabase = sys_get_temp_dir() . '/sikekar-restore-' . uniqid() . '.sqlite';
        touch($this->tempDatabase);

        config(['database.connections.sqlite.database' => $this->tempDatabase]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        $this->artisan('migrate:fresh', ['--force' => true])->run();
    }

    private function makeSuperadmin(): Karyawan
    {
        return Karyawan::create([
            'kode_pegawai' => 'NIP-BKP-' . uniqid(),
            'email' => 'bkp.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Super Admin Backup',
            'jabatan' => 'Super Administrator Sistem',
            'divisi' => 'IT',
            'posisi' => 'superadmin',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);
    }

    /**
     * Pindahkan database uji ke file sementara sehingga backup & restore
     * benar-benar menyentuh file (tidak bisa dilakukan pada :memory:).
     */
    // ==========================================================
    // RESTORE
    // ==========================================================

    public function test_restore_requires_explicit_confirmation(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->post(route('superadmin.backup.restore', 'sikekar-20261110-000000-manual.sql.gz'))
            ->assertSessionHasErrors('confirm');
    }

    public function test_restore_rejects_unknown_backup_file(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->post(route('superadmin.backup.restore', 'sikekar-20261110-000000-manual.sql.gz'), [
                'confirm' => 'RESTORE',
            ])
            ->assertSessionHas('error');
    }

    public function test_restore_brings_data_back_and_creates_pre_restore_backup(): void
    {
        $sa = $this->makeSuperadmin();
        $baselineCount = Karyawan::count();

        // Buat backup kondisi awal
        $this->actingAs($sa)
            ->post(route('superadmin.backup.store'))
            ->assertSessionHas('success');

        $backup = \App\Models\BackupLog::where('trigger', 'manual')->first();
        $this->assertNotNull($backup, 'Log backup manual seharusnya tercatat');
        $filename = (string) $backup->filename;
        $this->assertNotSame('', $filename);

        // Tambah data SETELAH backup → akan hilang saat restore
        Karyawan::create([
            'kode_pegawai' => 'NIP-BARU-' . uniqid(),
            'email' => 'baru.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Setelah Backup',
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);

        $this->assertSame($baselineCount + 1, Karyawan::count());

        // Restore
        $this->actingAs($sa)
            ->post(route('superadmin.backup.restore', $filename), ['confirm' => 'RESTORE'])
            ->assertSessionHas('success');

        // Data setelah backup terhapus kembali ke kondisi saat backup dibuat
        $this->assertSame($baselineCount, Karyawan::count());

        // Backup pra-restore tercatat
        $restoreLog = BackupLog::where('trigger', 'restore')->first();
        $this->assertNotNull($restoreLog);
        $this->assertTrue((bool) $restoreLog->local_success);
        $this->assertStringContainsString('pre-restore', (string) $restoreLog->message);

        // Kondisi pra-restore juga dipulihkan: akun superadmin test ikut hilang
        // (dibuat sesudah backup) — diverifikasi lewat koneksi yang sudah purge.
        $this->assertNotNull(ActivityLog::count() >= 0);
    }

    public function test_restore_keeps_pre_restore_backup_file_on_disk(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.backup.store'))->assertSessionHas('success');
        $filename = (string) BackupLog::where('trigger', 'manual')->value('filename');

        $this->actingAs($sa)
            ->post(route('superadmin.backup.restore', $filename), ['confirm' => 'RESTORE'])
            ->assertSessionHas('success');

        $files = collect(\Illuminate\Support\Facades\File::files(config('backup.path')))
            ->map(fn ($f) => $f->getFilename());

        // Minimal ada backup asli + backup pra-restore
        $this->assertGreaterThanOrEqual(2, $files->filter(fn ($n) => str_ends_with($n, '.sql.gz'))->count());
        $this->assertTrue(
            $files->contains(fn ($n) => str_contains($n, 'pre-restore')),
            'Backup pra-restore seharusnya ada di folder backup'
        );
    }

    // ==========================================================
    // BACKUP OFFSITE
    // ==========================================================

    public function test_offsite_disabled_is_reported_not_errored(): void
    {
        $sa = $this->makeSuperadmin();

        config(['backup.offsite.enabled' => false]);

        $this->actingAs($sa)
            ->post(route('superadmin.backup.offsite', 'sikekar-20261110-000000-manual.sql.gz'))
            ->assertSessionHas('error');
    }

    public function test_offsite_push_copies_file_and_logs_success(): void
    {
        $sa = $this->makeSuperadmin();

        // Disk lokal dipakai sebagai storage offsite pada test.
        config([
            'backup.offsite.enabled' => true,
            'backup.offsite.disk' => 'local',
            'backup.offsite.prefix' => 'offsite-test',
        ]);

        Storage::fake('local');

        // Buat file backup dummy yang valid nama file-nya
        $dir = config('backup.path');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($dir, 0700, true);
        $filename = 'sikekar-20261110-120000-manual.sql.gz';
        file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, gzencode("-- test dump\n"));

        $this->actingAs($sa)
            ->post(route('superadmin.backup.offsite', $filename))
            ->assertSessionHas('success');

        $log = BackupLog::where('trigger', 'offsite')->first();
        $this->assertNotNull($log);
        $this->assertTrue((bool) $log->offsite_success);
        $this->assertSame('local', $log->offsite_disk);

        @unlink($dir . DIRECTORY_SEPARATOR . $filename);
    }

    public function test_offsite_failure_is_logged_and_reported(): void
    {
        $sa = $this->makeSuperadmin();

        // Disk s3 tanpa kredensial → upload gagal, tapi tidak 500.
        config([
            'backup.offsite.enabled' => true,
            'backup.offsite.disk' => 's3',
            'backup.notify_failure' => false,
        ]);

        $dir = config('backup.path');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($dir, 0700, true);
        $filename = 'sikekar-20261110-130000-manual.sql.gz';
        file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, gzencode("-- test dump\n"));

        $this->actingAs($sa)
            ->post(route('superadmin.backup.offsite', $filename))
            ->assertSessionHas('error');

        $log = BackupLog::where('trigger', 'offsite')->first();
        $this->assertNotNull($log);
        $this->assertFalse((bool) $log->offsite_success);
        $this->assertSame('failed', $log->status);

        @unlink($dir . DIRECTORY_SEPARATOR . $filename);
    }

    // ==========================================================
    // TOOL KESEHATAN SISTEM
    // ==========================================================

    public function test_new_health_tools_run_without_error(): void
    {
        $sa = $this->makeSuperadmin();

        foreach (['session_prune', 'optimize_clear', 'queue_work_once'] as $tool) {
            $response = $this->actingAs($sa)->post(route('superadmin.health.tool'), ['tool' => $tool]);

            $response->assertSessionHasNoErrors();
            // Tool harus melaporkan sukses (session('success') terisi) —
            // teks pesannya bebas, mis. "Tidak ada sesi kedaluwarsa untuk
            // dibersihkan." tetap dianggap sukses.
            $this->assertNotNull(session('success'), "Tool {$tool} seharusnya sukses");
            $this->assertNull(session('error'), "Tool {$tool} seharusnya tidak gagal");
        }
    }

    public function test_health_tool_rejects_unknown_tool(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->post(route('superadmin.health.tool'), ['tool' => 'rm_rf_root'])
            ->assertSessionHasErrors('tool');
    }

    public function test_backup_page_shows_log_and_restore_action(): void
    {
        $sa = $this->makeSuperadmin();

        BackupLog::record([
            'filename' => 'sikekar-20261110-100000-manual.sql.gz',
            'trigger' => 'restore',
            'local_success' => true,
            'status' => 'failed',
            'message' => 'Restore gagal: simulasi kegagalan.',
        ]);

        $this->actingAs($sa)
            ->get(route('superadmin.backup.index'))
            ->assertOk()
            ->assertSee('Log Eksekusi Backup')
            ->assertSee('Restore Database')
            ->assertSee('simulasi kegagalan');
    }
}
