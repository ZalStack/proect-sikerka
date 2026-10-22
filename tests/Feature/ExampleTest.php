<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_authenticated_user_is_redirected_to_their_dashboard(): void
    {
        $karyawan = Karyawan::create([
            'kode_pegawai' => 'NIP-ROOT-1',
            'email' => 'root@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Root Test',
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);

        $this->actingAs($karyawan)
            ->get('/')
            ->assertRedirect(route('karyawan.dashboard', absolute: false));
    }
}
