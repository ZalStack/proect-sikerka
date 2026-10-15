<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $posisi): Karyawan
    {
        return Karyawan::create([
            'kode_pegawai' => strtoupper(substr($posisi, 0, 2)) . '-001',
            'email' => $posisi . '@sikekar.com',
            'kata_sandi' => Hash::make('rahasia123'),
            'nama_lengkap' => 'User ' . ucfirst($posisi),
            'posisi' => $posisi,
            'jabatan' => $posisi === 'hr' ? 'HRD' : 'Staff',
            'divisi' => $posisi === 'hr' ? 'HRD' : 'Operasional',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);
    }

    public function test_hr_can_view_full_profile_and_edit_form(): void
    {
        $hr = $this->makeUser('hr');

        $show = $this->actingAs($hr)->get('/profile');
        $show->assertOk();
        $show->assertSee('Informasi Pribadi');
        $show->assertSee('Informasi Profesional');
        $show->assertSee('HR');

        $edit = $this->actingAs($hr)->get('/profile/edit');
        $edit->assertOk();
        // Form lengkap untuk HR (bukan form terbatas Super Admin)
        $edit->assertSee('name="nik"', false);
        $edit->assertSee('name="divisi"', false);
        $edit->assertSee('name="jabatan"', false);

        $this->actingAs($hr)->get('/profile/change-password')->assertOk();
    }

    public function test_hr_can_update_full_profile_and_keeps_role(): void
    {
        $hr = $this->makeUser('hr');

        $response = $this->actingAs($hr)->put('/profile/update', [
            'kode_pegawai' => $hr->kode_pegawai,
            'nama_lengkap' => 'HR Updated',
            'email' => 'hr@sikekar.com',
            'jabatan' => 'HRD',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => $hr->tanggal_bergabung->toDateString(),
            'divisi' => 'HRD',
            'nomor_telepon' => '08111111111',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('profile');

        $hr->refresh();
        $this->assertSame('HR Updated', $hr->nama_lengkap);
        $this->assertSame('hr', $hr->posisi);
        $this->assertSame('08111111111', $hr->nomor_telepon);
    }

    public function test_karyawan_can_view_and_update_profile(): void
    {
        $karyawan = $this->makeUser('karyawan');

        $this->actingAs($karyawan)->get('/profile')->assertOk()->assertSee('Karyawan');

        $response = $this->actingAs($karyawan)->put('/profile/update', [
            'kode_pegawai' => $karyawan->kode_pegawai,
            'nama_lengkap' => 'Karyawan Updated',
            'email' => 'karyawan@sikekar.com',
            'jabatan' => 'Staff',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => $karyawan->tanggal_bergabung->toDateString(),
            'divisi' => 'Operasional',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('karyawan', $karyawan->fresh()->posisi);
        $this->assertSame('Karyawan Updated', $karyawan->fresh()->nama_lengkap);
    }

    public function test_guest_is_redirected_to_login_for_profile_routes(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/profile/edit')->assertRedirect('/login');
        $this->get('/profile/change-password')->assertRedirect('/login');
    }
}
