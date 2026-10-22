<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\RemoteAccessViolation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Test mekanisme Anti Remote Access (TeamViewer / AnyDesk / RDP):
 * deteksi → pelanggaran tercatat ke Superadmin → tangga ban
 * (1× = 1 hari, 2× = 2 hari, 3× = permanen) → hanya Superadmin yang bisa unban.
 */
class RemoteAccessBanTest extends TestCase
{
    use RefreshDatabase;

    /** Koordinat kantor pertama (dipakai agar lolos validasi radius 50m). */
    private const OFFICE_LAT = -6.586886661039424;
    private const OFFICE_LNG = 106.75890044642712;

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-TEST-' . $counter . '-' . uniqid(),
            'email' => 'karyawan' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Test ' . $counter,
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'tanggal_bergabung' => '2026-01-01',
        ], $overrides));
    }

    private function makeSuperadmin(): Karyawan
    {
        return $this->makeKaryawan(['posisi' => 'superadmin', 'nama_lengkap' => 'Super Admin Test']);
    }

    private function checkinPayload(array $extra = []): array
    {
        return array_merge([
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
            'accuracy' => 5,
            'client_flags' => [],
        ], $extra);
    }

    // ==========================================================
    // PEMBLOKIRAN OLEH MIDDLEWARE
    // ==========================================================

    public function test_karyawan_banned_temporarily_cannot_checkin(): void
    {
        $karyawan = $this->makeKaryawan([
            'banned_until' => Carbon::now()->addHours(12),
            'violation_count' => 1,
        ]);

        $response = $this->actingAs($karyawan)
            ->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload());

        $response->assertStatus(403)->assertJson([
            'success' => false,
            'code' => 'ACCOUNT_BANNED',
        ]);

        $this->assertNull(Absensi::where('karyawan_id', $karyawan->id)->first());
    }

    public function test_permanently_banned_karyawan_cannot_checkout(): void
    {
        $karyawan = $this->makeKaryawan([
            'is_banned_permanent' => true,
            'violation_count' => 3,
        ]);

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'check_in' => '07:00:00',
            'status' => 'Hadir',
        ]);

        $response = $this->actingAs($karyawan)
            ->postJson(route('karyawan.absensi.checkout'), $this->checkinPayload());

        $response->assertStatus(403)->assertJson([
            'success' => false,
            'code' => 'ACCOUNT_BANNED',
            'ban' => ['permanent' => true],
        ]);

        $this->assertNull($karyawan->fresh()->banned_until);
        $this->assertNull(Absensi::where('karyawan_id', $karyawan->id)->first()->check_out);
    }

    public function test_ban_auto_expires_and_karyawan_can_checkin_again(): void
    {
        $karyawan = $this->makeKaryawan([
            'banned_until' => Carbon::now()->subHour(),
            'violation_count' => 1,
        ]);

        $response = $this->actingAs($karyawan)
            ->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload());

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertNull($karyawan->fresh()->banned_until, 'Ban sementara yang sudah lewat harus dibersihkan otomatis');
        $this->assertSame(1, $karyawan->fresh()->violation_count);
    }

    // ==========================================================
    // DETEKSI & AUTO-BAN
    // ==========================================================

    public function test_high_risk_remote_signal_rejects_checkin_and_auto_bans(): void
    {
        $karyawan = $this->makeKaryawan();

        $response = $this->actingAs($karyawan)->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload([
            'client_flags' => ['driver_remote_desktop_terdeteksi:teamviewer'],
        ]));

        $response->assertStatus(403)->assertJson([
            'success' => false,
            'code' => 'REMOTE_ACCESS_DETECTED',
            'ban' => ['violation_count' => 1, 'permanent' => false],
        ]);

        // Pelanggaran tercatat & terkonfirmasi otomatis, masuk log Superadmin
        $violation = RemoteAccessViolation::where('karyawan_id', $karyawan->id)->first();
        $this->assertNotNull($violation);
        $this->assertSame(RemoteAccessViolation::STATUS_CONFIRMED, $violation->status);
        $this->assertSame(RemoteAccessViolation::RISK_HIGH, $violation->risk_level);
        $this->assertSame(1, $violation->strike_number);
        $this->assertSame('checkin', $violation->konteks);
        $this->assertNotEmpty($violation->signals);

        // Ban 1 hari (strike ke-1) diterapkan
        $karyawan->refresh();
        $this->assertSame(1, $karyawan->violation_count);
        $this->assertFalse($karyawan->is_banned_permanent);
        $this->assertNotNull($karyawan->banned_until);
        $this->assertTrue($karyawan->banned_until->isFuture());

        // Absensi tidak boleh tercatat
        $this->assertNull(Absensi::where('karyawan_id', $karyawan->id)->first());
    }

    public function test_high_risk_signal_on_checkout_also_rejects(): void
    {
        $karyawan = $this->makeKaryawan();

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'check_in' => '07:00:00',
            'status' => 'Hadir',
        ]);

        $response = $this->actingAs($karyawan)->postJson(route('karyawan.absensi.checkout'), $this->checkinPayload([
            'client_flags' => ['driver_remote_desktop_terdeteksi:anydesk'],
        ]));

        $response->assertStatus(403)->assertJson(['code' => 'REMOTE_ACCESS_DETECTED']);

        $violation = RemoteAccessViolation::where('karyawan_id', $karyawan->id)->first();
        $this->assertSame('checkout', $violation->konteks);
        $this->assertSame(RemoteAccessViolation::STATUS_CONFIRMED, $violation->status);
        $this->assertSame(1, $karyawan->fresh()->violation_count);
    }

    public function test_webgl_driver_remote_tool_in_fingerprint_is_high_risk(): void
    {
        $karyawan = $this->makeKaryawan();

        $response = $this->actingAs($karyawan)->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload([
            'device_fingerprint' => [
                'renderer' => 'TeamViewer GPU Engine',
                'vendor' => 'TeamViewer Germany GmbH',
                'screen' => '1920x1080',
                'avail' => '1920x1040',
                'dpr' => 1,
                'tz' => 'Asia/Jakarta',
                'lang' => 'id-ID',
                'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
            ],
        ]));

        $response->assertStatus(403)->assertJson(['code' => 'REMOTE_ACCESS_DETECTED']);

        $violation = RemoteAccessViolation::where('karyawan_id', $karyawan->id)->first();
        $this->assertSame(RemoteAccessViolation::RISK_HIGH, $violation->risk_level);
        $codes = array_column($violation->signals, 'code');
        $this->assertNotEmpty(array_filter($codes, fn ($c) => str_starts_with($c, 'driver_remote_desktop_terdeteksi')));
    }

    public function test_low_risk_signal_creates_pending_violation_without_blocking(): void
    {
        $karyawan = $this->makeKaryawan();

        $response = $this->actingAs($karyawan)->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload([
            'client_flags' => ['resolusi_layar_tidak_wajar'],
        ]));

        $response->assertOk()->assertJson(['success' => true]);

        // Check-in tetap terekam, hanya ditandai mencurigakan
        $absensi = Absensi::where('karyawan_id', $karyawan->id)->first();
        $this->assertNotNull($absensi);
        $this->assertNotNull($absensi->check_in);
        $this->assertTrue((bool) $absensi->is_suspicious);

        // Masuk antrean review Superadmin, TANPA ban
        $violation = RemoteAccessViolation::where('karyawan_id', $karyawan->id)->first();
        $this->assertNotNull($violation);
        $this->assertSame(RemoteAccessViolation::STATUS_PENDING, $violation->status);
        $this->assertSame(RemoteAccessViolation::RISK_LOW, $violation->risk_level);

        $karyawan->refresh();
        $this->assertSame(0, $karyawan->violation_count);
        $this->assertNull($karyawan->banned_until);
        $this->assertFalse($karyawan->is_banned_permanent);
    }

    public function test_clean_checkin_is_not_flagged(): void
    {
        $karyawan = $this->makeKaryawan();

        $response = $this->actingAs($karyawan)->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload());

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertSame(0, RemoteAccessViolation::count());
        $this->assertSame(0, $karyawan->fresh()->violation_count);
    }

    // ==========================================================
    // TANGGA BAN: 1 HARI → 2 HARI → PERMANEN
    // ==========================================================

    public function test_violation_ladder_progresses_to_permanent_ban(): void
    {
        $karyawan = $this->makeKaryawan();

        // Pelanggaran ke-1 → ban 1 hari
        $this->actingAs($karyawan)->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload([
            'client_flags' => ['driver_remote_desktop_terdeteksi:teamviewer'],
        ]))->assertStatus(403);

        $karyawan->refresh();
        $this->assertSame(1, $karyawan->violation_count);
        $this->assertFalse($karyawan->is_banned_permanent);
        $this->assertTrue($karyawan->banned_until->isFuture());

        // Hari ke-2: ban sudah berakhir, pelanggaran lagi → ban 2 hari
        $this->travel(2)->days();

        $this->actingAs($karyawan->fresh())->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload([
            'client_flags' => ['driver_remote_desktop_terdeteksi:anydesk'],
        ]))->assertStatus(403);

        $karyawan->refresh();
        $this->assertSame(2, $karyawan->violation_count);
        $this->assertFalse($karyawan->is_banned_permanent);
        $this->assertTrue($karyawan->banned_until->greaterThan(Carbon::now()->addDay()));

        // Hari ke-5: ban berakhir, pelanggaran ketiga → PERMANEN
        $this->travel(3)->days();

        $this->actingAs($karyawan->fresh())->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload([
            'client_flags' => ['driver_remote_desktop_terdeteksi:rustdesk'],
        ]))->assertStatus(403);

        $karyawan->refresh();
        $this->assertSame(3, $karyawan->violation_count);
        $this->assertTrue($karyawan->is_banned_permanent);
        $this->assertNull($karyawan->banned_until);

        // Setelah permanen, semua percobaan presensi diblokir middleware
        $this->actingAs($karyawan)->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload())
            ->assertStatus(403)
            ->assertJson(['code' => 'ACCOUNT_BANNED']);

        // Tiga record pelanggaran tercatat, masing-masing terkonfirmasi
        $this->assertSame(3, RemoteAccessViolation::where('karyawan_id', $karyawan->id)
            ->where('status', RemoteAccessViolation::STATUS_CONFIRMED)->count());
    }

    public function test_multiple_detections_same_day_only_count_one_strike(): void
    {
        $karyawan = $this->makeKaryawan();

        // Check-in ditolak (sinyal kuat) → strike 1
        $this->actingAs($karyawan)->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload([
            'client_flags' => ['driver_remote_desktop_terdeteksi:teamviewer'],
        ]))->assertStatus(403);

        // Percobaan check-out di hari yang sama (dilakukan sebelum ban aktif
        // sempat diblokir middleware, misalnya lewat request beruntun) → TANPA strike tambahan
        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'check_in' => '07:00:00',
            'status' => 'Hadir',
        ]);

        $this->actingAs($karyawan->fresh())->postJson(route('karyawan.absensi.checkout'), $this->checkinPayload([
            'client_flags' => ['driver_remote_desktop_terdeteksi:anydesk'],
        ]))->assertStatus(403);

        $karyawan->refresh();
        $this->assertSame(1, $karyawan->violation_count, 'Deteksi berulang di hari yang sama tidak boleh menambah strike');
        $this->assertSame(1, RemoteAccessViolation::where('karyawan_id', $karyawan->id)->count());
    }

    // ==========================================================
    // SUPERADMIN: KONFIRMASI / TOLAK / UNBAN / PESAN ALERT
    // ==========================================================

    public function test_karyawan_role_cannot_access_violation_pages(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->actingAs($karyawan)
            ->get(route('superadmin.violations.index'))
            ->assertRedirect(route('karyawan.dashboard'));
    }

    public function test_superadmin_can_confirm_pending_violation_and_ban_is_applied(): void
    {
        $superadmin = $this->makeSuperadmin();
        $karyawan = $this->makeKaryawan();

        $violation = RemoteAccessViolation::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'konteks' => 'checkin',
            'signals' => [['source' => 'client_flag', 'code' => 'resolusi_layar_tidak_wajar', 'weight' => 40, 'detail' => 'tes']],
            'risk_score' => 40,
            'risk_level' => RemoteAccessViolation::RISK_LOW,
            'status' => RemoteAccessViolation::STATUS_PENDING,
        ]);

        $this->actingAs($superadmin)
            ->from(route('superadmin.violations.index'))
            ->post(route('superadmin.violations.confirm', $violation->id), [
                'pesan' => 'Pelanggaran terkonfirmasi. Jangan ulangi akses remote saat absensi!',
            ])
            ->assertRedirect(route('superadmin.violations.index'))
            ->assertSessionHas('success');

        $violation->refresh();
        $this->assertSame(RemoteAccessViolation::STATUS_CONFIRMED, $violation->status);
        $this->assertSame(1, $violation->strike_number);
        $this->assertSame($superadmin->id, $violation->reviewed_by);

        $karyawan->refresh();
        $this->assertSame(1, $karyawan->violation_count);
        $this->assertTrue($karyawan->banned_until->isFuture());
        $this->assertSame('Pelanggaran terkonfirmasi. Jangan ulangi akses remote saat absensi!', $karyawan->ban_message);
    }

    public function test_superadmin_dismiss_pending_violation_does_not_ban(): void
    {
        $superadmin = $this->makeSuperadmin();
        $karyawan = $this->makeKaryawan();

        $violation = RemoteAccessViolation::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'konteks' => 'checkin',
            'signals' => [['source' => 'screen', 'code' => 'resolusi_layar_tidak_wajar', 'weight' => 40, 'detail' => 'tes']],
            'risk_score' => 40,
            'risk_level' => RemoteAccessViolation::RISK_LOW,
            'status' => RemoteAccessViolation::STATUS_PENDING,
        ]);

        $this->actingAs($superadmin)
            ->post(route('superadmin.violations.dismiss', $violation->id), ['catatan' => 'Layar kecil, karyawan pakai laptop lama'])
            ->assertSessionHas('success');

        $violation->refresh();
        $this->assertSame(RemoteAccessViolation::STATUS_DISMISSED, $violation->status);

        $karyawan->refresh();
        $this->assertSame(0, $karyawan->violation_count);
        $this->assertFalse($karyawan->is_banned_permanent);
        $this->assertNull($karyawan->banned_until);
    }

    public function test_superadmin_can_unban_and_reset_counter(): void
    {
        $superadmin = $this->makeSuperadmin();
        $karyawan = $this->makeKaryawan([
            'is_banned_permanent' => true,
            'violation_count' => 3,
            'ban_message' => 'Pesan lama',
        ]);

        $this->actingAs($superadmin)
            ->from(route('superadmin.violations.index'))
            ->post(route('superadmin.karyawan.unban', $karyawan->id))
            ->assertRedirect(route('superadmin.violations.index'))
            ->assertSessionHas('success');

        $karyawan->refresh();
        $this->assertFalse($karyawan->is_banned_permanent);
        $this->assertNull($karyawan->banned_until);
        $this->assertSame(0, $karyawan->violation_count);
        $this->assertNull($karyawan->ban_message);
    }

    public function test_superadmin_can_update_ban_message(): void
    {
        $superadmin = $this->makeSuperadmin();
        $karyawan = $this->makeKaryawan([
            'banned_until' => Carbon::now()->addDay(),
            'violation_count' => 1,
        ]);

        $this->actingAs($superadmin)
            ->post(route('superadmin.karyawan.ban-message', $karyawan->id), [
                'ban_message' => 'Blokir berlaku 1 hari. Pelanggaran berikutnya berakhir permanen!',
            ])
            ->assertSessionHas('success');

        $this->assertSame(
            'Blokir berlaku 1 hari. Pelanggaran berikutnya berakhir permanen!',
            $karyawan->fresh()->ban_message
        );
    }

    // ==========================================================
    // TAMPILAN BANNER & STATUS
    // ==========================================================

    public function test_banned_karyawan_sees_ban_banner_and_alert_on_dashboard(): void
    {
        $karyawan = $this->makeKaryawan([
            'banned_until' => Carbon::now()->addDays(2),
            'violation_count' => 2,
            'ban_message' => 'PERINGATAN: Pelanggaran kedua terdeteksi. Kebijakan perusahaan melarang akses remote saat absensi.',
        ]);

        $response = $this->actingAs($karyawan)->get(route('karyawan.dashboard'));

        $response->assertOk();
        $response->assertSee('AKUN ANDA DIBLOKIR DARI FITUR PRESENSI', false);
        $response->assertSee('PERINGATAN: Pelanggaran kedua terdeteksi.', false);
        $response->assertSee('sisa', false);
    }

    public function test_status_endpoint_reports_ban_info(): void
    {
        $karyawan = $this->makeKaryawan([
            'banned_until' => Carbon::now()->addHours(6),
            'violation_count' => 1,
        ]);

        $response = $this->actingAs($karyawan)->get(route('karyawan.absensi.status'));

        $response->assertOk();
        $response->assertJsonPath('data.account_ban.is_banned', true);
        $response->assertJsonPath('data.account_ban.permanent', false);
        $response->assertJsonPath('data.account_ban.violation_count', 1);
        $this->assertNotNull($response->json('data.account_ban.sisa_hari'));
    }

    public function test_superadmin_violation_page_renders(): void
    {
        $superadmin = $this->makeSuperadmin();
        $karyawan = $this->makeKaryawan(['banned_until' => Carbon::now()->addDay(), 'violation_count' => 1]);

        RemoteAccessViolation::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'konteks' => 'checkin',
            'signals' => [['source' => 'client_flag', 'code' => 'resolusi_layar_tidak_wajar', 'weight' => 40, 'detail' => 'tes']],
            'risk_score' => 40,
            'risk_level' => RemoteAccessViolation::RISK_LOW,
            'status' => RemoteAccessViolation::STATUS_PENDING,
        ]);

        $response = $this->actingAs($superadmin)->get(route('superadmin.violations.index'));

        $response->assertOk();
        $response->assertSee('Monitoring Pelanggaran Presensi', false);
        $response->assertSee('resolusi_layar_tidak_wajar', false);
        $response->assertSee('Karyawan yang Sedang Diblokir Presensi', false);
        $response->assertSee('Unban', false);
    }

    public function test_banned_karyawan_absensi_page_renders_with_banner(): void
    {
        $karyawan = $this->makeKaryawan([
            'is_banned_permanent' => true,
            'violation_count' => 3,
            'ban_message' => 'Blokir permanen karena 3 pelanggaran akses remote.',
        ]);

        $response = $this->actingAs($karyawan)->get(route('karyawan.absensi'));

        $response->assertOk();
        $response->assertSee('AKUN ANDA DIBLOKIR DARI FITUR PRESENSI', false);
        $response->assertSee('BAN PERMANEN', false);
        $response->assertSee('Blokir permanen karena 3 pelanggaran akses remote.', false);
    }

    // ==========================================================
    // LULUS TANPA SINYAL & AUTO-EXPIRY MODEL
    // ==========================================================

    public function test_model_ban_auto_expiry_clears_temporary_ban(): void
    {
        $karyawan = $this->makeKaryawan([
            'banned_until' => Carbon::now()->subMinutes(5),
            'violation_count' => 1,
        ]);

        $this->assertFalse($karyawan->isBanned());
        $this->assertNull($karyawan->fresh()->banned_until);
        $this->assertSame(0, $karyawan->fresh()->banDaysRemaining() ?? 0);
    }

    public function test_apply_ban_ladder_and_lift_ban(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->assertSame(1, $karyawan->applyBan());
        $this->assertTrue($karyawan->banned_until->isFuture());
        $this->assertFalse((bool) $karyawan->is_banned_permanent);

        $this->assertSame(2, $karyawan->applyBan());
        $this->assertFalse((bool) $karyawan->is_banned_permanent);

        $this->assertSame(3, $karyawan->applyBan());
        $this->assertTrue((bool) $karyawan->is_banned_permanent);
        $this->assertNull($karyawan->banned_until);

        $karyawan->liftBan();
        $this->assertSame(0, $karyawan->violation_count);
        $this->assertFalse((bool) $karyawan->is_banned_permanent);
        $this->assertFalse($karyawan->isBanned());
    }

    // ==========================================================
    // NOTIFIKASI PELANGGARAN: HANYA SUPERADMIN & HR, BUKAN KARYAWAN
    // ==========================================================

    public function test_pending_violation_notifies_superadmin_and_hr_but_not_karyawan(): void
    {
        $superadmin = $this->makeSuperadmin();
        $hr = $this->makeKaryawan(['posisi' => 'hr', 'nama_lengkap' => 'HR Tester']);
        $pelaku = $this->makeKaryawan(['nama_lengkap' => 'Karyawan Pelanggar']);

        RemoteAccessViolation::create([
            'karyawan_id' => $pelaku->id,
            'tanggal' => Carbon::today(),
            'konteks' => 'checkin',
            'signals' => [['source' => 'client_flag', 'code' => 'resolusi_layar_tidak_wajar', 'weight' => 40, 'detail' => 'tes']],
            'risk_score' => 40,
            'risk_level' => RemoteAccessViolation::RISK_LOW,
            'status' => RemoteAccessViolation::STATUS_PENDING,
        ]);

        // Superadmin menerima notifikasi pelanggaran + filter tipe tersedia
        $responseSuper = $this->actingAs($superadmin)->get(route('notifications.index'));
        $responseSuper->assertOk();
        $responseSuper->assertSee('Pelanggaran Presensi Perlu Review', false);
        $responseSuper->assertSee('Karyawan Pelanggar', false);
        $responseSuper->assertSee('Menunggu review', false);

        // HR juga menerima notifikasi yang sama
        $responseHr = $this->actingAs($hr)->get(route('notifications.index'));
        $responseHr->assertOk();
        $responseHr->assertSee('Pelanggaran Presensi Perlu Review', false);
        $responseHr->assertSee('Karyawan Pelanggar', false);

        // Karyawan yang melanggar TIDAK melihat notifikasi pelanggaran apa pun
        $responseKaryawan = $this->actingAs($pelaku)->get(route('notifications.index'));
        $responseKaryawan->assertOk();
        $responseKaryawan->assertDontSee('Pelanggaran Presensi', false);
        $responseKaryawan->assertDontSee('Menunggu review', false);

        // Dropdown lonceng karyawan juga tidak memuat notifikasi pelanggaran
        $latest = $this->actingAs($pelaku)->getJson(route('notifications.latest'));
        $latest->assertOk();
        $this->assertNotContains('pelanggaran', array_column($latest->json('items'), 'type'));
    }

    public function test_auto_confirmed_violation_appears_in_superadmin_and_hr_notifications_only(): void
    {
        $superadmin = $this->makeSuperadmin();
        $hr = $this->makeKaryawan(['posisi' => 'hr', 'nama_lengkap' => 'HR Tester 2']);
        $pelaku = $this->makeKaryawan(['nama_lengkap' => 'Karyawan Bobol']);

        // Pelanggaran terkonfirmasi otomatis lewat percobaan check-in berisiko tinggi
        $this->actingAs($pelaku)->postJson(route('karyawan.absensi.checkin'), $this->checkinPayload([
            'client_flags' => ['driver_remote_desktop_terdeteksi:teamviewer'],
        ]))->assertStatus(403);

        $responseSuper = $this->actingAs($superadmin)->get(route('notifications.index'));
        $responseSuper->assertOk();
        $responseSuper->assertSee('Pelanggaran Presensi Dikonfirmasi', false);
        $responseSuper->assertSee('strike ke-1', false);
        $responseSuper->assertSee('Karyawan Bobol', false);

        $responseHr = $this->actingAs($hr)->get(route('notifications.index'));
        $responseHr->assertOk();
        $responseHr->assertSee('Pelanggaran Presensi Dikonfirmasi', false);

        // Karyawan pelaku tidak melihat notifikasi pelanggaran di daftarnya
        $responseKaryawan = $this->actingAs($pelaku->fresh())->get(route('notifications.index'));
        $responseKaryawan->assertOk();
        $responseKaryawan->assertDontSee('Pelanggaran Presensi', false);
        $responseKaryawan->assertDontSee('Karyawan Bobol terbukti', false);
    }

    public function test_violation_filter_type_only_offered_to_superadmin_and_hr(): void
    {
        $superadmin = $this->makeSuperadmin();
        $hr = $this->makeKaryawan(['posisi' => 'hr']);
        $karyawan = $this->makeKaryawan();

        $this->assertArrayHasKey('pelanggaran', app(\App\Services\NotificationService::class)->availableTypes($superadmin));
        $this->assertArrayHasKey('pelanggaran', app(\App\Services\NotificationService::class)->availableTypes($hr));
        $this->assertArrayNotHasKey('pelanggaran', app(\App\Services\NotificationService::class)->availableTypes($karyawan));

        // Halaman karyawan tidak menampilkan filter "Pelanggaran Presensi"
        $this->actingAs($karyawan)->get(route('notifications.index'))
            ->assertOk()
            ->assertDontSee('Pelanggaran Presensi', false);
    }
}
