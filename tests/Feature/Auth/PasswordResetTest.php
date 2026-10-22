<?php

namespace Tests\Feature\Auth;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Lupa password memakai alur khusus aplikasi ini (tanpa email):
 * GET /forgot-password -> verifikasi email + CAPTCHA -> form reset -> simpan password baru.
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

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_reset_password_screen_requires_prior_email_verification(): void
    {
        $this->get('/reset-password')->assertRedirect(route('password.request', absolute: false));
    }

    public function test_unknown_email_is_rejected(): void
    {
        $this->post('/forgot-password/verify', [
            'email' => 'tidak-terdaftar@test.local',
            'captcha_input' => '5',
        ])->assertSessionHasErrors('email');
    }

    public function test_wrong_captcha_is_rejected(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->withSession(['captcha_result' => 5])
            ->post('/forgot-password/verify', [
                'email' => $karyawan->email,
                'captcha_input' => '99',
            ])
            ->assertSessionHasErrors('captcha_input');
    }

    public function test_valid_email_and_captcha_unlock_the_reset_form(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->withSession(['captcha_result' => 5])
            ->post('/forgot-password/verify', [
                'email' => $karyawan->email,
                'captcha_input' => '5',
            ])
            ->assertRedirect(route('password.reset.form'));

        $this->assertTrue((bool) session('reset_verified'));
        $this->assertSame($karyawan->email, session('reset_email'));
    }

    public function test_password_can_be_reset_after_verification(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->withSession(['reset_verified' => true, 'reset_email' => $karyawan->email])
            ->post('/reset-password', [
                'email' => $karyawan->email,
                'password' => 'password-baru-123',
                'password_confirmation' => 'password-baru-123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('password-baru-123', $karyawan->fresh()->kata_sandi));

        // Session reset sudah dipakai habis
        $this->assertNull(session('reset_email'));

        // Login dengan password baru berhasil
        $this->post('/login', [
            'email' => $karyawan->email,
            'password' => 'password-baru-123',
        ]);
        $this->assertAuthenticated();
    }

    public function test_reset_rejects_email_that_does_not_match_the_verified_session(): void
    {
        $karyawan = $this->makeKaryawan();
        $other = $this->makeKaryawan();

        $this->withSession(['reset_verified' => true, 'reset_email' => $karyawan->email])
            ->post('/reset-password', [
                'email' => $other->email,
                'password' => 'password-baru-123',
                'password_confirmation' => 'password-baru-123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $other->fresh()->kata_sandi));
    }
}
