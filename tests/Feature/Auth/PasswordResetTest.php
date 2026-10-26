<?php

namespace Tests\Feature\Auth;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Lupa password memakai alur link reset (tanpa email & tanpa CAPTCHA):
 * halaman lupa password -> minta link via WhatsApp Admin -> Super Admin
 * generate link -> pengguna membuka /reset-password/{token} -> password baru.
 * Link berlaku 60 menit dan sekali pakai.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-RST-' . $counter . '-' . uniqid(),
            'email' => 'reset' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Reset ' . $counter,
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    private function makeResetToken(Karyawan $karyawan, ?string $createdAt = null): string
    {
        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $karyawan->email,
            'token' => $token,
            'created_at' => $createdAt ?? now(),
        ]);

        return $token;
    }

    public function test_forgot_password_screen_shows_whatsapp_admin_contact(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('https://wa.me/6282123439604', false)
            ->assertSee('Chat WhatsApp Admin');
    }

    public function test_reset_screen_requires_valid_token(): void
    {
        $this->get('/reset-password/token-tidak-ada')
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHas('error');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $karyawan = $this->makeKaryawan();
        $token = $this->makeResetToken($karyawan);

        $this->post("/reset-password/{$token}", [
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        $this->assertTrue(Hash::check('password-baru-123', $karyawan->fresh()->kata_sandi));

        // Token sekali pakai — langsung habis setelah dipakai
        $this->assertDatabaseMissing('password_resets', ['token' => $token]);

        // Login dengan password baru (portal /login wajib ceklis kebijakan)
        $this->post('/login', [
            'email' => $karyawan->email,
            'password' => 'password-baru-123',
            'agree_policy' => '1',
        ]);

        $this->assertAuthenticated();
    }

    public function test_expired_token_is_rejected_and_deleted(): void
    {
        $karyawan = $this->makeKaryawan();
        $token = $this->makeResetToken($karyawan, now()->subMinutes(61)->toDateTimeString());

        $this->get("/reset-password/{$token}")
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('password_resets', ['token' => $token]);
        $this->assertTrue(Hash::check('password', $karyawan->fresh()->kata_sandi));
    }

    public function test_reset_requires_matching_and_long_enough_password(): void
    {
        $karyawan = $this->makeKaryawan();
        $token = $this->makeResetToken($karyawan);

        $this->post("/reset-password/{$token}", [
            'password' => 'abc',
            'password_confirmation' => 'xyz',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $karyawan->fresh()->kata_sandi));
    }

    public function test_superadmin_can_generate_reset_link(): void
    {
        $superadmin = $this->makeKaryawan(['posisi' => 'superadmin']);
        $karyawan = $this->makeKaryawan();

        $this->actingAs($superadmin)
            ->post("/superadmin/karyawan/{$karyawan->id}/generate-reset-link")
            ->assertSessionHasNoErrors()
            ->assertSessionHas('reset_link')
            ->assertSessionHas('reset_link_expires');

        $link = (string) session('reset_link');
        $this->assertNotSame('', $link);

        $token = basename((string) parse_url($link, PHP_URL_PATH));
        $this->assertDatabaseHas('password_resets', ['token' => $token, 'email' => $karyawan->email]);

        // Link yang dihasilkan valid dan membuka form reset (keluar sesi dulu karena route bersifat guest)
        $this->post('/logout');
        $this->get("/reset-password/{$token}")->assertOk();
    }

    public function test_generating_new_link_revokes_the_previous_one(): void
    {
        $superadmin = $this->makeKaryawan(['posisi' => 'superadmin']);
        $karyawan = $this->makeKaryawan();

        $oldToken = $this->makeResetToken($karyawan);

        $this->actingAs($superadmin)
            ->post("/superadmin/karyawan/{$karyawan->id}/generate-reset-link");

        $newToken = basename((string) parse_url((string) session('reset_link'), PHP_URL_PATH));

        $this->assertDatabaseMissing('password_resets', ['token' => $oldToken]);
        $this->assertDatabaseHas('password_resets', ['token' => $newToken, 'email' => $karyawan->email]);
    }

    public function test_superadmin_can_not_generate_link_for_resigned_account(): void
    {
        $superadmin = $this->makeKaryawan(['posisi' => 'superadmin']);
        $resigned = $this->makeKaryawan(['is_resigned' => true]);

        $this->actingAs($superadmin)
            ->post("/superadmin/karyawan/{$resigned->id}/generate-reset-link")
            ->assertSessionHas('error');

        $this->assertDatabaseCount('password_resets', 0);
    }

    public function test_role_page_shows_reset_button_and_copy_modal(): void
    {
        $superadmin = $this->makeKaryawan(['posisi' => 'superadmin']);
        $karyawan = $this->makeKaryawan();

        $this->actingAs($superadmin)
            ->post("/superadmin/karyawan/{$karyawan->id}/generate-reset-link");

        $link = (string) session('reset_link');

        $this->get('/superadmin/karyawan')
            ->assertOk()
            ->assertSee('Reset Password')
            ->assertSee('resetLinkModal', false)
            ->assertSee('reset-link-input', false)
            ->assertSee($link, false)
            ->assertSee('Berlaku hingga', false);
    }

    public function test_karyawan_can_not_access_generate_link_route(): void
    {
        $karyawan = $this->makeKaryawan(['posisi' => 'karyawan']);
        $target = $this->makeKaryawan();

        $this->actingAs($karyawan)
            ->post("/superadmin/karyawan/{$target->id}/generate-reset-link")
            ->assertRedirect(route('karyawan.dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('password_resets', 0);
    }
}
