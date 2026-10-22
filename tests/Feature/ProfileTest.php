<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Profil karyawan: halaman profil, update data diri, dan ganti password.
 * Route aktual: GET /profile, PUT /profile/update, PUT /profile/update-password.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-PROF-' . $counter . '-' . uniqid(),
            'email' => 'profil' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Profil ' . $counter,
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    public function test_profile_page_is_displayed(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->actingAs($karyawan)->get('/profile')->assertOk();
    }

    public function test_guest_can_not_access_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_karyawan_can_update_profile_information(): void
    {
        $karyawan = $this->makeKaryawan();

        $response = $this->actingAs($karyawan)->put('/profile/update', [
            'kode_pegawai' => $karyawan->kode_pegawai,
            'nama_lengkap' => 'Nama Baru Diubah',
            'email' => 'baru.diubah@test.local',
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => $karyawan->tanggal_bergabung->toDateString(),
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.show', absolute: false));

        $karyawan->refresh();
        $this->assertSame('Nama Baru Diubah', $karyawan->nama_lengkap);
        $this->assertSame('baru.diubah@test.local', $karyawan->email);
        $this->assertSame('karyawan', $karyawan->posisi);
    }

    public function test_profile_update_requires_required_fields(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->actingAs($karyawan)
            ->from('/profile')
            ->put('/profile/update', [])
            ->assertSessionHasErrors([
                'kode_pegawai',
                'nama_lengkap',
                'email',
                'jabatan',
                'divisi',
                'status',
                'tanggal_bergabung',
            ]);
    }

    public function test_password_can_be_updated(): void
    {
        $karyawan = $this->makeKaryawan();

        $response = $this->actingAs($karyawan)->put('/profile/update-password', [
            'current_password' => 'password',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.show', absolute: false));

        $this->assertTrue(Hash::check('password-baru-123', $karyawan->fresh()->kata_sandi));
    }

    public function test_correct_current_password_must_be_provided_to_update_password(): void
    {
        $karyawan = $this->makeKaryawan();

        $this->actingAs($karyawan)
            ->put('/profile/update-password', [
                'current_password' => 'wrong-password',
                'password' => 'password-baru-123',
                'password_confirmation' => 'password-baru-123',
            ])
            ->assertSessionHasErrors('current_password');

        // Password lama tetap berlaku
        $this->assertTrue(Hash::check('password', $karyawan->fresh()->kata_sandi));
    }
}
