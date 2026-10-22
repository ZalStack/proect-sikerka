<?php

namespace Tests\Feature\Auth;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Autentikasi aplikasi ini memakai model Karyawan (bukan User):
 * login -> redirect ke dashboard sesuai role (karyawan / hr / superadmin).
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-AUTH-' . $counter . '-' . uniqid(),
            'email' => 'user' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'User Test ' . $counter,
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_karyawan_can_authenticate_using_the_login_screen(): void
    {
        $karyawan = $this->makeKaryawan(['posisi' => 'karyawan']);

        $response = $this->post('/login', [
            'email' => $karyawan->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('karyawan.dashboard', absolute: false));
    }

    public function test_hr_is_redirected_to_hr_dashboard(): void
    {
        $hr = $this->makeKaryawan(['posisi' => 'hr']);

        $response = $this->post('/login', [
            'email' => $hr->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('hr.dashboard', absolute: false));
    }

    public function test_superadmin_is_redirected_to_superadmin_dashboard(): void
    {
        $superadmin = $this->makeKaryawan(['posisi' => 'superadmin']);

        $response = $this->post('/login', [
            'email' => $superadmin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('superadmin.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->post('/login', [
            'email' => $karyawan->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_resigned_user_can_not_stay_logged_in(): void
    {
        $resigned = $this->makeKaryawan(['is_resigned' => true]);

        $this->post('/login', [
            'email' => $resigned->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $karyawan = $this->makeKaryawan();

        $response = $this->actingAs($karyawan)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
