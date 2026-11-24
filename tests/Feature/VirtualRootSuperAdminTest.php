<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class VirtualRootSuperAdminTest extends TestCase
{
    use RefreshDatabase;
    public function test_virtual_superadmin_can_login_without_database_record(): void
    {
        // Pastikan tidak ada karyawan di database
        $this->assertEquals(0, Karyawan::count());

        // 1. Coba login dengan kredensial salah
        $failResponse = $this->post(route('superadmin.login.store'), [
            'email' => config('auth.root_superadmin.email'),
            'password' => 'WrongPassword123!',
        ]);
        $failResponse->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());

        // 2. Login dengan kredensial root .env yang benar
        $response = $this->post(route('superadmin.login.store'), [
            'email' => config('auth.root_superadmin.email'),
            'password' => config('auth.root_superadmin.password'),
        ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $this->assertTrue(Auth::check());

        $user = Auth::user();
        $this->assertTrue($user->isVirtual());
        $this->assertTrue($user->isSuperAdmin());
        $this->assertEquals(config('auth.root_superadmin.email'), $user->email);
        $this->assertEquals(\App\Auth\VirtualSuperAdminProvider::VIRTUAL_ID, $user->id);

        // Pastikan tabel karyawans tetap 0 (tidak ada row yang tersimpan di DB)
        $this->assertEquals(0, Karyawan::count());

        // 3. Akses dashboard superadmin
        $dashResponse = $this->get(route('superadmin.dashboard'));
        $dashResponse->assertStatus(200);

        // 4. Logout
        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }

    public function test_virtual_superadmin_can_read_credentials_from_external_env_file(): void
    {
        // Buat file env dummy simulasi milik Pamersuku
        $tempEnv = sys_get_temp_dir() . '/.env.pamersuku-test-' . uniqid();
        file_put_contents($tempEnv, implode("\n", [
            'SUPERADMIN_ROOT_ENABLED=true',
            'SUPERADMIN_ROOT_EMAIL=root@pamersuku.test',
            'SUPERADMIN_ROOT_PASSWORD=PasswordPamersuku123!',
            'SUPERADMIN_ROOT_NAME="Pamersuku Root Admin"',
        ]));

        config(['auth.root_superadmin.pamersuku_env_path' => $tempEnv]);

        // Login dengan kredensial yang diatur di Pamersuku .env
        $response = $this->post(route('superadmin.login.store'), [
            'email' => 'root@pamersuku.test',
            'password' => 'PasswordPamersuku123!',
        ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals('root@pamersuku.test', Auth::user()->email);
        $this->assertEquals('Pamersuku Root Admin', Auth::user()->nama_lengkap);

        // Hapus file temporary
        @unlink($tempEnv);
    }
}
