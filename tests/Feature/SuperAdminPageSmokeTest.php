<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Smoke test seluruh halaman GET role Superadmin:
 * - Semua halaman wajib render 200 (menangkap variabel blade yang hilang,
 *   route rusak, maupun error runtime).
 * - Halaman pemeliharaan IT wajib tetap tampil walau folder backup tidak
 *   dapat diakses — jangan sampai fitur monitoring ikut gagal 500.
 */
class SuperAdminPageSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperadmin(): Karyawan
    {
        return Karyawan::create([
            'kode_pegawai' => 'NIP-SMOKE-' . uniqid(),
            'email' => 'smoke.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Super Admin Smoke',
            'jabatan' => 'Staff',
            'divisi' => 'IT',
            'posisi' => 'superadmin',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);
    }

    public function test_all_superadmin_pages_render(): void
    {
        $sa = $this->makeSuperadmin();

        $routes = [
            'superadmin.dashboard',
            'superadmin.features.index',
            'superadmin.absensi.index',
            'superadmin.karyawan.index',
            'superadmin.violations.index',
            'superadmin.access.index',
            'superadmin.hari-libur.index',
            'superadmin.backup.index',
            'superadmin.stats.index',
            'superadmin.logs.index',
            'superadmin.logs.export',
            'superadmin.health.index',
            'profile.show',
            'profile.edit',
            'profile.change-password',
            'profile.achievement',
            'notifications.index',
        ];

        $failures = [];
        foreach ($routes as $name) {
            try {
                $response = $this->actingAs($sa)->get(route($name));
                if ($response->getStatusCode() !== 200) {
                    $failures[] = "{$name} => HTTP {$response->getStatusCode()} ({$response->headers->get('Location')})";
                }
            } catch (\Throwable $e) {
                $failures[] = "{$name} => EXCEPTION: " . get_class($e) . ': ' . $e->getMessage();
            }
        }

        if ($failures) {
            $this->fail("HALAMAN GAGAL RENDER:\n" . implode("\n", $failures));
        }

        $this->assertTrue(true);
    }

    public function test_backup_page_still_renders_when_backup_folder_unreachable(): void
    {
        $sa = $this->makeSuperadmin();

        // Folder backup tidak dapat dibuat/ditulis → service melempar exception,
        // tetapi halaman daftar backup harus tetap tampil (200) dengan pesan
        // jelas di dalam halaman (session()->now tampil saat render).
        config(['backup.path' => '/proc/sikekar-folder-backup-tidak-valid']);

        $this->actingAs($sa)
            ->get(route('superadmin.backup.index'))
            ->assertOk()
            ->assertSee('Folder backup tidak dapat diakses', false);
    }

    public function test_health_page_still_renders_when_backup_folder_unreachable(): void
    {
        $sa = $this->makeSuperadmin();

        config(['backup.path' => '/proc/sikekar-folder-backup-tidak-valid']);

        $this->actingAs($sa)
            ->get(route('superadmin.health.index'))
            ->assertOk();
    }
}
