<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Karyawan;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Fase 4 — Pengaturan SMTP (B9) & Jadwal Maintenance Otomatis (B10):
 *  - Simpan/kubah konfigurasi SMTP ke system_settings + Config runtime
 *  - Password tidak pernah diecho balik ke browser
 *  - Uji kirim email (Mail::fake)
 *  - CRUD jadwal maintenance: sakelar, validasi rentang, status aktif
 */
class SmtpAndMaintenanceScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperadmin(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-SMTP-' . $counter . '-' . uniqid(),
            'email' => 'smtp' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Super Admin SMTP ' . $counter,
            'jabatan' => 'Super Administrator Sistem',
            'divisi' => 'IT',
            'posisi' => 'superadmin',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    // ==========================================================
    // HALAMAN
    // ==========================================================

    public function test_smtp_page_renders(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.smtp.index'))
            ->assertOk()
            ->assertSee('Pengaturan SMTP')
            ->assertSee('Belum Dikonfigurasi');
    }

    public function test_smtp_page_blocked_for_hr(): void
    {
        $hr = $this->makeKaryawanHr();

        $this->actingAs($hr)
            ->get(route('superadmin.smtp.index'))
            ->assertRedirect(route('hr.dashboard'));
    }

    private function makeKaryawanHr(): Karyawan
    {
        static $c = 0;
        $c++;

        return Karyawan::create([
            'kode_pegawai' => 'NIP-HR-' . $c . '-' . uniqid(),
            'email' => 'hr' . $c . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'HR ' . $c,
            'jabatan' => 'HR',
            'divisi' => 'HR',
            'posisi' => 'hr',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);
    }

    // ==========================================================
    // SIMPAN SMTP
    // ==========================================================

    public function test_smtp_settings_are_saved_and_applied(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.smtp.update'), [
            'mail_mailer' => 'smtp',
            'smtp_host' => 'smtp.kantor.example',
            'smtp_port' => '587',
            'smtp_username' => 'no-reply@kantor.example',
            'smtp_password' => 'rahasia-123',
            'smtp_encryption' => 'tls',
            'smtp_from_address' => 'no-reply@kantor.example',
            'smtp_from_name' => 'SIKEKAR Kantor',
        ])->assertSessionHas('success');

        $settings = SystemSetting::smtpSettings();

        $this->assertSame('smtp.kantor.example', $settings['host']);
        $this->assertSame('587', $settings['port']);
        $this->assertSame('no-reply@kantor.example', $settings['username']);
        // Password didekripsi kembali saat dibaca.
        $this->assertSame('rahasia-123', $settings['password']);

        // Konfigurasi runtime ikut diperbarui.
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.kantor.example', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('no-reply@kantor.example', config('mail.from.address'));

        // Disimpan TERENKRIPTI, bukan teks polos.
        $raw = SystemSetting::where('key', 'smtp_password')->value('value');
        $this->assertStringNotContainsString('rahasia-123', (string) $raw);

        $this->assertNotNull(
            ActivityLog::where('action', 'settings.smtp_update')->latest()->first()
        );
    }

    public function test_smtp_password_never_echoed_back_to_browser(): void
    {
        $sa = $this->makeSuperadmin();

        SystemSetting::setSmtpSettings([
            'mail_mailer' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '465',
            'smtp_username' => 'user@example.com',
            'smtp_password' => 'password-super-rahasia',
            'smtp_encryption' => 'ssl',
            'smtp_from_address' => 'user@example.com',
            'smtp_from_name' => 'SIKEKAR',
        ]);

        $this->actingAs($sa)
            ->get(route('superadmin.smtp.index'))
            ->assertOk()
            ->assertDontSee('password-super-rahasia')
            ->assertSee('Tersimpan', false);
    }

    public function test_blank_password_keeps_existing_one(): void
    {
        $sa = $this->makeSuperadmin();

        SystemSetting::setSmtpSettings([
            'mail_mailer' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '587',
            'smtp_username' => 'user@example.com',
            'smtp_password' => 'lama-tersembunyi',
            'smtp_encryption' => 'tls',
            'smtp_from_address' => 'user@example.com',
            'smtp_from_name' => 'SIKEKAR',
        ]);

        $this->actingAs($sa)->post(route('superadmin.smtp.update'), [
            'mail_mailer' => 'smtp',
            'smtp_host' => 'smtp-baru.example.com',
            'smtp_port' => '587',
            'smtp_username' => 'user@example.com',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'smtp_from_address' => 'user@example.com',
            'smtp_from_name' => 'SIKEKAR',
        ])->assertSessionHas('success');

        $settings = SystemSetting::smtpSettings();
        $this->assertSame('smtp-baru.example.com', $settings['host']);
        $this->assertSame('lama-tersembunyi', $settings['password']);
    }

    public function test_smtp_validation_requires_host_for_smtp_mailer(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.smtp.update'), [
            'mail_mailer' => 'smtp',
            'smtp_host' => '',
            'smtp_port' => '',
            'smtp_from_address' => 'bukan-email',
            'smtp_from_name' => '',
        ])->assertSessionHasErrors(['smtp_host', 'smtp_port', 'smtp_from_address', 'smtp_from_name']);

        $this->assertSame('', SystemSetting::smtpSettings()['host']);
    }

    public function test_log_mailer_does_not_require_host(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.smtp.update'), [
            'mail_mailer' => 'log',
            'smtp_host' => '',
            'smtp_port' => '',
            'smtp_from_address' => 'log@kantor.example',
            'smtp_from_name' => 'SIKEKAR Log',
        ])->assertSessionHas('success');

        $this->assertSame('log', SystemSetting::smtpSettings()['mailer']);
    }

    public function test_smtp_configured_flag_reflects_host(): void
    {
        $this->assertFalse(SystemSetting::isSmtpConfigured());

        SystemSetting::setSmtpSettings(['smtp_host' => 'smtp.example.com']);

        $this->assertTrue(SystemSetting::isSmtpConfigured());
    }

    // ==========================================================
    // UJI KIRIM
    // ==========================================================

    public function test_smtp_test_sends_email(): void
    {
        Mail::fake();

        $sa = $this->makeSuperadmin();

        SystemSetting::setSmtpSettings([
            'mail_mailer' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '587',
            'smtp_from_address' => 'no-reply@kantor.example',
            'smtp_from_name' => 'SIKEKAR',
        ]);

        $this->actingAs($sa)->post(route('superadmin.smtp.test'), [
            'test_email' => 'penerima@contoh.com',
        ])->assertSessionHas('success');

        Mail::assertSent(\App\Mail\SmtpTestMail::class, function ($mail) {
            return $mail->hasTo('penerima@contoh.com');
        });
    }

    public function test_smtp_test_validates_email(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.smtp.test'), [
            'test_email' => 'bukan-email',
        ])->assertSessionHasErrors('test_email');
    }

    public function test_smtp_test_failure_shows_error_not_500(): void
    {
        $sa = $this->makeSuperadmin();

        // Port 1 di localhost → connection refused seketika (tanpa menunggu DNS).
        SystemSetting::setSmtpSettings([
            'mail_mailer' => 'smtp',
            'smtp_host' => '127.0.0.1',
            'smtp_port' => '1',
            'smtp_from_address' => 'no-reply@kantor.example',
            'smtp_from_name' => 'SIKEKAR',
        ]);

        $response = $this->actingAs($sa)->post(route('superadmin.smtp.test'), [
            'test_email' => 'penerima@contoh.com',
        ]);

        $response->assertRedirect(route('superadmin.smtp.index'));
        $this->assertNotNull(session('error'));
    }

    // ==========================================================
    // APPLY SAAT BOOT
    // ==========================================================

    public function test_mail_config_applied_on_boot_from_database(): void
    {
        SystemSetting::setSmtpSettings([
            'mail_mailer' => 'smtp',
            'smtp_host' => 'smtp.boot.example',
            'smtp_port' => '2525',
            'smtp_username' => 'boot@example.com',
            'smtp_password' => 'boot-secret',
            'smtp_encryption' => 'none',
            'smtp_from_address' => 'boot@example.com',
            'smtp_from_name' => 'Boot Test',
        ]);

        SystemSetting::applyMailConfig();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.boot.example', config('mail.mailers.smtp.host'));
        $this->assertSame(2525, config('mail.mailers.smtp.port'));
        $this->assertNull(config('mail.mailers.smtp.scheme'));
        $this->assertSame('Boot Test', config('mail.from.name'));
    }

    public function test_apply_mail_config_is_noop_when_not_configured(): void
    {
        $before = config('mail.default');

        SystemSetting::applyMailConfig();

        $this->assertSame($before, config('mail.default'));
    }

    // ==========================================================
    // JADWAL MAINTENANCE (B10)
    // ==========================================================

    public function test_features_page_shows_schedule_form(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.features.index'))
            ->assertOk()
            ->assertSee('Jadwal Maintenance Otomatis')
            ->assertSee('starts_at');
    }

    public function test_schedule_can_be_saved_and_becomes_active_in_range(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.maintenance.schedule'), [
            'schedule_enabled' => '1',
            'starts_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHas('success');

        $this->assertTrue(SystemSetting::isMaintenanceScheduleEnabled());
        $this->assertTrue(SystemSetting::isMaintenanceScheduleActive());
        // Maintenance aktif karena jadwal berjalan walau sakelar manual mati.
        $this->assertFalse(SystemSetting::isMaintenance());
        $this->assertTrue(SystemSetting::isMaintenanceActive());

        $this->assertNotNull(
            ActivityLog::where('action', 'settings.maintenance_schedule')->latest()->first()
        );
    }

    public function test_schedule_outside_range_is_not_active(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.maintenance.schedule'), [
            'schedule_enabled' => '1',
            'starts_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(4)->format('Y-m-d\TH:i'),
        ])->assertSessionHas('success');

        $this->assertTrue(SystemSetting::isMaintenanceScheduleEnabled());
        $this->assertFalse(SystemSetting::isMaintenanceScheduleActive());
        $this->assertFalse(SystemSetting::isMaintenanceActive());
    }

    public function test_disabled_schedule_is_stored_but_inactive(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.maintenance.schedule'), [
            'schedule_enabled' => '0',
            'starts_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHas('success');

        $this->assertFalse(SystemSetting::isMaintenanceScheduleEnabled());
        $this->assertFalse(SystemSetting::isMaintenanceScheduleActive());
        // Rentang tetap tersimpan.
        $this->assertNotEmpty(SystemSetting::maintenanceSchedule()['starts_at']);
    }

    public function test_schedule_validation_requires_end_after_start(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.maintenance.schedule'), [
            'schedule_enabled' => '1',
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('ends_at');
    }

    public function test_active_schedule_requires_both_times(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.maintenance.schedule'), [
            'schedule_enabled' => '1',
            'starts_at' => now()->format('Y-m-d\TH:i'),
            'ends_at' => '',
        ])->assertSessionHasErrors();

        $this->assertFalse(SystemSetting::isMaintenanceScheduleActive());
    }

    public function test_invalid_range_is_ignored(): void
    {
        SystemSetting::setMaintenanceSchedule(
            now()->addDay()->toDateTimeString(),
            now()->subDay()->toDateTimeString(),
            true
        );

        $this->assertFalse(SystemSetting::isMaintenanceScheduleActive());
    }

    public function test_middleware_blocks_karyawan_when_schedule_active(): void
    {
        SystemSetting::setMaintenanceSchedule(
            now()->subMinutes(5)->toDateTimeString(),
            now()->addHour()->toDateTimeString(),
            true
        );

        $k = Karyawan::create([
            'kode_pegawai' => 'NIP-MT-' . uniqid(),
            'email' => 'mt.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Maintenance',
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);

        // Middleware maintenance mengembalikan 503 + view 'maintenance'.
        $this->actingAs($k)
            ->get(route('karyawan.dashboard'))
            ->assertStatus(503)
            ->assertSee('Pemeliharaan', false);
    }

    public function test_superadmin_bypasses_schedule_maintenance(): void
    {
        SystemSetting::setMaintenanceSchedule(
            now()->subMinutes(5)->toDateTimeString(),
            now()->addHour()->toDateTimeString(),
            true
        );

        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.dashboard'))
            ->assertOk();
    }
}
