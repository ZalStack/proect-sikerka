<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\KaryawanMenuDisabled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Fitur Manajemen Akses Karyawan (Superadmin):
 * - Suspend akun dengan rentang tanggal → login DIBLOKIR selama periode.
 * - Suspend kedaluwarsa otomatis diclear → login kembali jalan.
 * - Hak akses menu per karyawan → route fitur membalas 403 & menu hilang dari sidebar.
 * - Guard: tidak bisa suspend diri sendiri / menyisakan 0 Super Admin aktif.
 */
class SuperAdminAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-AKSES-' . $counter . '-' . uniqid(),
            'email' => 'akses' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Akses ' . $counter,
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
            'nama_lengkap' => 'Super Admin Test',
        ], $overrides));
    }

    private function suspendAsSuperadmin(Karyawan $superadmin, Karyawan $target, array $overrides = [])
    {
        return $this->actingAs($superadmin)->post(
            route('superadmin.access.suspend', $target->id),
            array_merge([
                'suspend_start' => now()->toDateString(),
                'suspend_end' => now()->addDays(7)->toDateString(),
                'suspend_reason' => 'Pelanggaran kebijakan kerja - uji coba fitur suspend',
            ], $overrides)
        );
    }

    // ==========================================================
    // HALAMAN BARU
    // ==========================================================

    public function test_superadmin_can_open_access_page(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.access.index'))
            ->assertOk()
            ->assertSee('Manajemen Akses Karyawan')
            ->assertSee('Suspend Akun', false);
    }

    public function test_karyawan_can_not_open_access_page(): void
    {
        $k = $this->makeKaryawan();

        $this->actingAs($k)
            ->get(route('superadmin.access.index'))
            ->assertRedirect(route('karyawan.dashboard'));
    }

    public function test_hr_can_not_open_access_page(): void
    {
        $hr = $this->makeKaryawan(['posisi' => 'hr', 'divisi' => 'HRD']);

        $this->actingAs($hr)
            ->get(route('superadmin.access.index'))
            ->assertRedirect(route('hr.dashboard'));
    }

    // ==========================================================
    // SUSPEND: BLOKIR LOGIN PADA RENTANG TANGGAL
    // ==========================================================

    public function test_suspend_blocks_login_until_end_date(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->suspendAsSuperadmin($sa, $k)->assertSessionHasNoErrors();

        $k->refresh();
        $this->assertNotNull($k->suspend_start);
        $this->assertNotNull($k->suspend_end);
        $this->assertNotNull($k->suspend_reason);
        $this->assertSame($sa->id, $k->suspended_by);
        $this->assertTrue($k->isSuspended());

        // Login ditolak dengan pesan jelas
        $response = $this->post(route('login'), [
            'email' => $k->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('ditangguhkan', session('error'));
        $this->assertGuest();
    }

    public function test_suspended_account_session_is_terminated(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->suspendAsSuperadmin($sa, $k);

        // Sesi aktif (sudah login sebelum suspend) ditendang oleh middleware
        $this->actingAs($k->fresh())
            ->get(route('karyawan.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_scheduled_suspension_does_not_block_login_before_start_date(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->suspendAsSuperadmin($sa, $k, [
            'suspend_start' => now()->addDay()->toDateString(),
            'suspend_end' => now()->addDays(10)->toDateString(),
        ]);

        $this->assertFalse($k->fresh()->isSuspended());

        $this->post(route('login'), [
            'email' => $k->email,
            'password' => 'password',
        ])->assertRedirect(route('karyawan.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_expired_suspension_clears_automatically_and_login_works(): void
    {
        $k = $this->makeKaryawan([
            'suspend_start' => now()->subDays(10)->toDateString(),
            'suspend_end' => now()->subDays(3)->toDateString(),
            'suspend_reason' => 'Masa suspend sudah berakhir',
        ]);

        $this->post(route('login'), [
            'email' => $k->email,
            'password' => 'password',
        ])->assertRedirect(route('karyawan.dashboard'));

        $this->assertAuthenticated();

        // Kolom suspend dibersihkan otomatis (auto-expiry)
        $k->refresh();
        $this->assertNull($k->suspend_start);
        $this->assertNull($k->suspend_end);
        $this->assertNull($k->suspend_reason);
        $this->assertNull($k->suspended_by);
    }

    public function test_unsuspend_restores_login(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->suspendAsSuperadmin($sa, $k);

        // Pastikan login tertolak dulu
        $this->post(route('login'), ['email' => $k->email, 'password' => 'password']);
        $this->assertGuest();

        $this->actingAs($sa)
            ->post(route('superadmin.access.unsuspend', $k->id))
            ->assertSessionHasNoErrors();

        $k->refresh();
        $this->assertNull($k->suspend_start);
        $this->assertNull($k->suspend_end);

        // Login kembali berhasil
        $this->post(route('login'), ['email' => $k->email, 'password' => 'password'])
            ->assertRedirect(route('karyawan.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_unsuspend_on_non_suspended_account_returns_error(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)
            ->post(route('superadmin.access.unsuspend', $k->id))
            ->assertSessionHas('error');
    }

    // ==========================================================
    // GUARD
    // ==========================================================

    public function test_superadmin_can_not_suspend_own_account(): void
    {
        $sa = $this->makeSuperadmin();

        $response = $this->suspendAsSuperadmin($sa, $sa);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('sendiri', session('error'));

        $sa->refresh();
        $this->assertNull($sa->suspend_start);
        $this->assertNull($sa->suspend_end);
    }

    public function test_can_not_suspend_the_last_active_superadmin(): void
    {
        // Actor: Superadmin yang sedang disuspend (diperbolehkan menembus middleware
        // demi keamanan sistem, tetapi tetap terkena guard last-superadmin)
        $actor = $this->makeSuperadmin([
            'suspend_start' => now()->toDateString(),
            'suspend_end' => now()->addDays(3)->toDateString(),
            'suspend_reason' => 'Sedang ditinjau',
        ]);
        $target = $this->makeSuperadmin(['nama_lengkap' => 'Super Admin Terakhir']);

        $response = $this->suspendAsSuperadmin($actor, $target);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('satu-satunya Super Admin', session('error'));

        $target->refresh();
        $this->assertNull($target->suspend_start);
        $this->assertNull($target->suspend_end);
    }

    public function test_suspend_validates_date_range(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $response = $this->suspendAsSuperadmin($sa, $k, [
            'suspend_start' => now()->addDays(5)->toDateString(),
            'suspend_end' => now()->toDateString(), // sebelum tanggal mulai
        ]);

        $response->assertSessionHasErrors('suspend_end');

        $k->refresh();
        $this->assertNull($k->suspend_start);
        $this->assertNull($k->suspend_end);
    }

    public function test_suspend_requires_reason(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->suspendAsSuperadmin($sa, $k, ['suspend_reason' => ''])
            ->assertSessionHasErrors('suspend_reason');
    }

    // ==========================================================
    // HAK AKSES MENU
    // ==========================================================

    public function test_menu_restriction_blocks_feature_route_for_that_karyawan(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        // Hanya Cuti yang aktif, 7 menu lain dimatikan
        $this->actingAs($sa)
            ->post(route('superadmin.access.menus', $k->id), ['menus' => ['cuti']])
            ->assertSessionHasNoErrors();

        $this->assertSame(7, KaryawanMenuDisabled::where('karyawan_id', $k->id)->count());
        $this->assertTrue($k->fresh()->isMenuDisabled('absensi'));

        // Route fitur yang dimatikan → 403 dengan halaman "Akses Dibatasi"
        $blocked = $this->actingAs($k)->get(route('karyawan.absensi'));
        $blocked->assertForbidden()->assertSee('Akses Dibatasi');

        // Route fitur yang tetap aktif → 200
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertOk();
        // Catatan: Superadmin tidak diuji lewat route karyaahan/hr karena
        // middleware role (bukan middleware fitur) yang membatasi lintas role.
    }

    public function test_disabled_menu_disappears_from_karyawan_sidebar(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)
            ->post(route('superadmin.access.menus', $k->id), ['menus' => ['cuti']]);

        $response = $this->actingAs($k)->get(route('karyawan.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Presensi Kehadiran');   // menu absensi hilang
        $response->assertSee('Cuti');                     // menu cuti tetap ada
    }

    public function test_re_enabling_all_menus_removes_all_restrictions(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)->post(route('superadmin.access.menus', $k->id), ['menus' => ['cuti']]);
        $this->assertSame(7, KaryawanMenuDisabled::where('karyawan_id', $k->id)->count());

        // Semua menu dikirim aktif (checkbox dicentang semua)
        $this->actingAs($sa)
            ->post(route('superadmin.access.menus', $k->id), [
                'menus' => array_keys(\App\Models\SystemSetting::AVAILABLE_FEATURES),
            ])
            ->assertSessionHasNoErrors();

        // Semua menu diaktifkan kembali
        $this->assertSame(0, KaryawanMenuDisabled::where('karyawan_id', $k->id)->count());
        $this->assertFalse($k->fresh()->isMenuDisabled('absensi'));

        $this->actingAs($k)->get(route('karyawan.absensi'))->assertOk();
    }

    public function test_menu_restriction_applies_to_hr_accounts_too(): void
    {
        $sa = $this->makeSuperadmin();
        $hr = $this->makeKaryawan(['posisi' => 'hr', 'divisi' => 'HRD']);

        // Hanya Cuti yang aktif → absensi & menu lain nonaktif untuk HR ini
        $this->actingAs($sa)
            ->post(route('superadmin.access.menus', $hr->id), ['menus' => ['cuti']]);

        // HR: presensi dimatikan → 403, menu lain tetap jalan
        $this->actingAs($hr)->get(route('hr.absensi.index'))->assertForbidden();
        $this->actingAs($hr)->get(route('hr.cuti.index'))->assertOk();
    }

    public function test_menu_restriction_is_rejected_for_superadmin_accounts(): void
    {
        $sa = $this->makeSuperadmin();
        $other = $this->makeSuperadmin(['nama_lengkap' => 'Super Admin Lain']);

        $this->actingAs($sa)
            ->post(route('superadmin.access.menus', $other->id), ['menus' => []])
            ->assertSessionHas('error');

        $this->assertSame(0, KaryawanMenuDisabled::count());
    }

    public function test_unknown_menu_key_is_rejected(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)
            ->post(route('superadmin.access.menus', $k->id), ['menus' => ['menu-tidak-ada']])
            ->assertSessionHasErrors('menus.0');

        $this->assertSame(0, KaryawanMenuDisabled::count());
    }

    // ==========================================================
    // FILTER HALAMAN
    // ==========================================================

    public function test_access_page_filters_by_suspension_status(): void
    {
        $sa = $this->makeSuperadmin();
        $suspended = $this->makeKaryawan();
        $active = $this->makeKaryawan();

        $this->suspendAsSuperadmin($sa, $suspended);

        $response = $this->actingAs($sa)
            ->get(route('superadmin.access.index', ['status' => 'suspended']))
            ->assertOk()
            ->assertSee($suspended->nama_lengkap)
            ->assertDontSee($active->nama_lengkap);

        $this->actingAs($sa)
            ->get(route('superadmin.access.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee($active->nama_lengkap)
            ->assertDontSee($suspended->nama_lengkap);
    }
}
