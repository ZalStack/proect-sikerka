<?php

namespace Tests\Feature;

use App\Http\Middleware\Impersonation;
use App\Models\Karyawan;
use App\Models\SystemSetting;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * Fase 1 — Keamanan & akun Super Admin:
 *  - Halaman Pusat Keamanan, setup & challenge 2FA
 *  - Whitelist IP + sakelar kewajiban 2FA
 *  - Manajemen sesi aktif
 *  - CRUD akun admin (guard superadmin terakhir)
 *  - Impersonation mulai/berhenti
 */
class SuperAdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-SEC-' . $counter . '-' . uniqid(),
            'email' => 'sec' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Pengguna Keamanan ' . $counter,
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
            'nama_lengkap' => 'Super Admin Security Test',
        ], $overrides));
    }

    // ==========================================================
    // HALAMAN
    // ==========================================================

    public function test_security_page_renders_for_superadmin(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.security.index'))
            ->assertOk()
            ->assertSee('Pusat Keamanan')
            ->assertSee('Two-Factor');
    }

    public function test_security_page_blocked_for_non_superadmin(): void
    {
        $hr = $this->makeKaryawan(['posisi' => 'hr']);

        // Middleware superadmin mengarahkan role lain ke dashboard mereka (302),
        // bukan 403 — perilaku bawaan aplikasi.
        $this->actingAs($hr)
            ->get(route('superadmin.security.index'))
            ->assertRedirect(route('hr.dashboard'));

        $this->actingAs($this->makeKaryawan(['posisi' => 'karyawan']))
            ->get(route('superadmin.security.index'))
            ->assertRedirect(route('karyawan.dashboard'));
    }

    public function test_admin_accounts_page_renders(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.admins.index'))
            ->assertOk()
            ->assertSee('Kelola Akun Admin');
    }

    // ==========================================================
    // TWO-FACTOR AUTHENTICATION
    // ==========================================================

    public function test_two_factor_setup_page_shows_secret(): void
    {
        $sa = $this->makeSuperadmin();

        $response = $this->actingAs($sa)->get(route('superadmin.security.two-factor'));

        $response->assertOk()->assertSee('Kunci Manual', false);

        $this->assertNotNull(session('two_factor_pending_secret'));
    }

    public function test_two_factor_enable_requires_valid_code(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->get(route('superadmin.security.two-factor'));

        // Kode salah → tidak aktif
        $this->actingAs($sa)
            ->post(route('superadmin.security.two-factor.enable'), ['code' => '000000'])
            ->assertSessionHas('error');

        $sa->refresh();
        $this->assertNull($sa->two_factor_confirmed_at);
    }

    public function test_two_factor_enable_with_correct_code_works(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->get(route('superadmin.security.two-factor'));

        $secret = session('two_factor_pending_secret');
        $this->assertNotEmpty($secret);

        $totp = app(TwoFactorService::class);
        $code = $totp->codeAt($secret);

        $this->actingAs($sa)
            ->post(route('superadmin.security.two-factor.enable'), ['code' => $code])
            ->assertRedirect(route('superadmin.security.index'));

        $sa->refresh();
        $this->assertNotNull($sa->two_factor_confirmed_at);
        $this->assertNotEmpty($sa->two_factor_secret);

        // Recovery code tersimpan (hash, bukan mentah)
        $recovery = json_decode((string) $sa->two_factor_recovery_codes, true);
        $this->assertIsArray($recovery);
        $this->assertCount(8, $recovery);

        // Secret ter-encrypt (tidak tersimpan mentah)
        $this->assertNotSame($secret, $sa->two_factor_secret);
        $this->assertSame($secret, $totp->decryptSecret($sa->two_factor_secret));
    }

    public function test_two_factor_challenge_gates_critical_action(): void
    {
        $sa = $this->makeSuperadmin();
        $target = $this->makeKaryawan();

        $totp = app(TwoFactorService::class);
        $secret = $totp->generateSecret();
        $sa->forceFill([
            'two_factor_secret' => $totp->encryptSecret($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Belum lolos challenge → dialihkan ke halaman challenge
        $this->actingAs($sa)
            ->put(route('superadmin.karyawan.update-role', $target->id), ['posisi' => 'hr'])
            ->assertRedirect();

        $this->assertSame('karyawan', $target->refresh()->posisi);

        // Lolos challenge → aksi berhasil
        $this->actingAs($sa)
            ->post(route('superadmin.security.two-factor-challenge.store'), ['code' => $totp->codeAt($secret)])
            ->assertSessionHasNoErrors();

        $this->actingAs($sa)
            ->put(route('superadmin.karyawan.update-role', $target->id), ['posisi' => 'hr'])
            ->assertSessionHasNoErrors();

        $this->assertSame('hr', $target->refresh()->posisi);
    }

    public function test_two_factor_challenge_rejects_wrong_code(): void
    {
        $sa = $this->makeSuperadmin();
        $totp = app(TwoFactorService::class);
        $secret = $totp->generateSecret();
        $sa->forceFill([
            'two_factor_secret' => $totp->encryptSecret($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($sa)
            ->post(route('superadmin.security.two-factor-challenge.store'), ['code' => '999999'])
            ->assertSessionHas('error');

        $this->assertNull(session('twofactor_verified_at'));
    }

    public function test_two_factor_disable_works_with_valid_code(): void
    {
        $sa = $this->makeSuperadmin();
        $totp = app(TwoFactorService::class);
        $secret = $totp->generateSecret();
        $sa->forceFill([
            'two_factor_secret' => $totp->encryptSecret($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($totp->hashRecoveryCodes(['ABCDE-FGHJK'])),
        ])->save();

        $this->actingAs($sa)
            ->post(route('superadmin.security.two-factor.disable'), ['code' => $totp->codeAt($secret)])
            ->assertRedirect(route('superadmin.security.index'));

        $sa->refresh();
        $this->assertNull($sa->two_factor_confirmed_at);
        $this->assertNull($sa->two_factor_secret);
    }

    public function test_whitelist_and_require_2fa_setting_saves(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->post(route('superadmin.security.whitelist.update'), [
                'whitelist' => "192.168.1.5, 10.0.0.0/24",
                'require_2fa' => '1',
            ])
            ->assertRedirect(route('superadmin.security.index'));

        $this->assertSame('192.168.1.5, 10.0.0.0/24', SystemSetting::superadminIpWhitelist());
        $this->assertTrue(SystemSetting::isTwoFactorRequired());
    }

    // ==========================================================
    // SESI AKTIF
    // ==========================================================

    public function test_session_list_renders_and_revoke_others_runs(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.security.index'))
            ->assertOk();

        $this->actingAs($sa)
            ->post(route('superadmin.security.sessions.revoke-others'))
            ->assertRedirect(route('superadmin.security.index'));
    }

    // ==========================================================
    // CRUD AKUN ADMIN
    // ==========================================================

    public function test_create_admin_account(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.admins.store'), [
            'nama_lengkap' => 'HR Baru',
            'email' => 'hr.baru@test.local',
            'kata_sandi' => 'kata-sandi-rahasia-1',
            'posisi' => 'hr',
        ])->assertSessionHasNoErrors();

        $created = Karyawan::where('email', 'hr.baru@test.local')->first();
        $this->assertNotNull($created);
        $this->assertSame('hr', $created->posisi);
        $this->assertTrue(Hash::check('kata-sandi-rahasia-1', $created->kata_sandi));
    }

    public function test_create_admin_rejects_weak_password(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.admins.store'), [
            'nama_lengkap' => 'HR Lemah',
            'email' => 'hr.lemah@test.local',
            'kata_sandi' => 'pendek',
            'posisi' => 'hr',
        ])->assertSessionHasErrors('kata_sandi');
    }

    public function test_last_superadmin_cannot_be_demoted_or_deleted(): void
    {
        $sa = $this->makeSuperadmin();

        // Turunkan posisi → ditolak
        $this->actingAs($sa)
            ->post(route('superadmin.admins.update', $sa->id), [
                'nama_lengkap' => $sa->nama_lengkap,
                'email' => $sa->email,
                'posisi' => 'hr',
            ])
            ->assertSessionHas('error');

        $this->assertSame('superadmin', $sa->refresh()->posisi);

        // Hapus diri sendiri → ditolak
        $this->actingAs($sa)
            ->delete(route('superadmin.admins.destroy', $sa->id))
            ->assertSessionHas('error');

        $this->assertNotNull($sa->fresh());
    }

    public function test_reset_admin_password_returns_new_credential(): void
    {
        $sa = $this->makeSuperadmin();
        $hr = $this->makeKaryawan(['posisi' => 'hr']);

        $this->actingAs($sa)
            ->post(route('superadmin.admins.reset-password', $hr->id))
            ->assertSessionHas('success');

        // Sandi lama tidak berlaku lagi (kecuali kebetulan sama → cek hash berubah)
        $this->assertFalse(Hash::check('password', $hr->refresh()->kata_sandi));
    }

    public function test_delete_admin_account(): void
    {
        $sa = $this->makeSuperadmin();
        $hr = $this->makeKaryawan(['posisi' => 'hr']);

        $this->actingAs($sa)
            ->delete(route('superadmin.admins.destroy', $hr->id))
            ->assertRedirect(route('superadmin.admins.index'));

        $this->assertTrue($hr->refresh()->trashed());
    }

    // ==========================================================
    // IMPERSONATION
    // ==========================================================

    public function test_impersonation_switches_user_and_can_stop(): void
    {
        $sa = $this->makeSuperadmin();
        $karyawan = $this->makeKaryawan();

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan.impersonate', $karyawan->id))
            ->assertRedirect(route('karyawan.dashboard'));

        $this->assertSame($karyawan->id, auth()->id());
        $this->assertSame($sa->id, session(Impersonation::SESSION_KEY));

        $this->post(route('superadmin.impersonate.stop'))
            ->assertRedirect(route('superadmin.dashboard'));

        $this->assertSame($sa->id, auth()->id());
        $this->assertNull(session(Impersonation::SESSION_KEY));
    }

    public function test_impersonation_rejects_self_and_superadmin_target(): void
    {
        $sa = $this->makeSuperadmin();
        $other = $this->makeSuperadmin(['nama_lengkap' => 'Super Admin Lain']);

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan.impersonate', $sa->id))
            ->assertSessionHas('error');

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan.impersonate', $other->id))
            ->assertSessionHas('error');

        $this->assertSame($sa->id, auth()->id());
    }
}
