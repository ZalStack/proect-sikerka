<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminProfileTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(): Karyawan
    {
        return Karyawan::create([
            'kode_pegawai' => 'SA-001',
            'email' => 'superadmin@sikekar.com',
            'kata_sandi' => Hash::make('superadmin123'),
            'nama_lengkap' => 'Super Administrator',
            'posisi' => 'superadmin',
            'jabatan' => 'Super Administrator Sistem',
            'divisi' => 'IT & Systems',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);
    }

    public function test_super_admin_can_view_profile(): void
    {
        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->get('/profile');

        $response->assertOk();
        $response->assertSee('Profile Saya');
        $response->assertSee($admin->nama_lengkap);
        $response->assertSee('Super Admin');
        $response->assertSee('Informasi Akun');
        // Data karyawan tidak ditampilkan untuk Super Admin
        $response->assertDontSee('Informasi Pribadi');
        $response->assertDontSee('Informasi Profesional');
    }

    public function test_super_admin_can_view_restricted_edit_profile(): void
    {
        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->get('/profile/edit');

        $response->assertOk();
        // Hanya nama & email yang boleh diedit
        $response->assertSee('name="nama_lengkap"', false);
        $response->assertSee('name="email"', false);
        $response->assertDontSee('name="nik"', false);
        $response->assertDontSee('name="divisi"', false);
        $response->assertDontSee('name="jabatan"', false);
        $response->assertDontSee('name="foto_profil"', false);
        $response->assertDontSee('name="posisi"', false);
    }

    public function test_super_admin_can_update_name_and_email_only(): void
    {
        $admin = $this->makeSuperAdmin();

        // Kirim hanya nama + email (tanpa field wajib karyawan lain)
        $response = $this->actingAs($admin)->put('/profile/update', [
            'nama_lengkap' => 'Nama Baru Super Admin',
            'email' => 'superadmin.baru@sikekar.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('profile');

        $admin->refresh();
        $this->assertSame('Nama Baru Super Admin', $admin->nama_lengkap);
        $this->assertSame('superadmin.baru@sikekar.com', $admin->email);
        $this->assertSame('superadmin', $admin->posisi, 'Role super admin tidak boleh berubah saat update profil');
        $this->assertSame('SA-001', $admin->kode_pegawai);
        $this->assertSame('Super Administrator Sistem', $admin->jabatan);
        $this->assertSame('IT & Systems', $admin->divisi);
        $this->assertSame('Karyawan Tetap', $admin->status);
    }

    public function test_super_admin_cannot_change_other_fields_or_role_via_profile_update(): void
    {
        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->put('/profile/update', [
            'nama_lengkap' => 'Super Administrator',
            'email' => 'superadmin@sikekar.com',
            // Upaya manipulasi field lain
            'kode_pegawai' => 'HACK-123',
            'jabatan' => 'Direktur Utama',
            'divisi' => 'HRD',
            'status' => 'Contract',
            'tanggal_bergabung' => '2000-01-01',
            'nik' => '3273000000000001',
            'alamat' => 'Alamat Hacker',
            'posisi' => 'karyawan',
        ]);

        $response->assertSessionHasNoErrors();

        $admin->refresh();
        $this->assertSame('superadmin', $admin->posisi);
        $this->assertSame('SA-001', $admin->kode_pegawai);
        $this->assertSame('Super Administrator Sistem', $admin->jabatan);
        $this->assertSame('IT & Systems', $admin->divisi);
        $this->assertSame('Karyawan Tetap', $admin->status);
        $this->assertNull($admin->nik);
        $this->assertNull($admin->alamat);
    }

    public function test_super_admin_profile_update_requires_name_and_email(): void
    {
        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->from('/profile/edit')->put('/profile/update', [
            'nama_lengkap' => '',
            'email' => 'bukan-email',
        ]);

        $response->assertSessionHasErrors(['nama_lengkap', 'email']);
        $this->assertSame('Super Administrator', $admin->fresh()->nama_lengkap);
    }

    public function test_super_admin_cannot_use_another_users_email(): void
    {
        $admin = $this->makeSuperAdmin();

        Karyawan::create([
            'kode_pegawai' => 'K-001',
            'email' => 'karyawan.lain@sikekar.com',
            'kata_sandi' => Hash::make('rahasia123'),
            'nama_lengkap' => 'Karyawan Lain',
            'posisi' => 'karyawan',
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'status' => 'Contract',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);

        $response = $this->actingAs($admin)->put('/profile/update', [
            'nama_lengkap' => 'Super Administrator',
            'email' => 'karyawan.lain@sikekar.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('superadmin@sikekar.com', $admin->fresh()->email);
    }

    public function test_super_admin_can_change_password(): void
    {
        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->get('/profile/change-password');
        $response->assertOk();

        $response = $this->actingAs($admin)->put('/profile/update-password', [
            'current_password' => 'superadmin123',
            'password' => 'rahasia12345',
            'password_confirmation' => 'rahasia12345',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('profile');

        $this->assertTrue(Hash::check('rahasia12345', $admin->refresh()->kata_sandi));
    }

    public function test_super_admin_password_change_rejects_wrong_current_password(): void
    {
        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->from('/profile/change-password')->put('/profile/update-password', [
            'current_password' => 'salah',
            'password' => 'rahasia12345',
            'password_confirmation' => 'rahasia12345',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('superadmin123', $admin->fresh()->kata_sandi));
    }

    public function test_super_admin_still_has_access_to_super_admin_panel_after_profile_update(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)->put('/profile/update', [
            'nama_lengkap' => 'Super Administrator Updated',
            'email' => 'superadmin@sikekar.com',
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin->fresh())->get('/superadmin/dashboard')->assertOk();
        $this->actingAs($admin->fresh())->get('/profile')->assertOk();
        $this->actingAs($admin->fresh())->get('/profile/edit')->assertOk();
    }
}
