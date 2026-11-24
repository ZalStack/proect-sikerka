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

    public function test_stealth_mode_root_superadmin_leaves_no_traces_in_activity_logs(): void
    {
        // Login root superadmin
        $this->post(route('superadmin.login.store'), [
            'email' => config('auth.root_superadmin.email'),
            'password' => config('auth.root_superadmin.password'),
        ]);

        // Coba simpan activity log
        $log = \App\Models\ActivityLog::record('test.action', 'Aksi coba-coba');
        $this->assertNull($log);

        // Pastikan tabel activity_logs dan login_histories kosong dari user root
        $this->assertEquals(0, \App\Models\ActivityLog::where('user_name', 'like', '%Root%')->count());
        $this->assertEquals(0, \App\Models\LoginHistory::where('email', config('auth.root_superadmin.email'))->count());
    }

    public function test_secret_koreksi_presensi_menu_access_and_logging(): void
    {
        // 1. Buat karyawan dan data presensi
        $karyawan = Karyawan::create([
            'nama_lengkap' => 'Budi Santoso',
            'kode_pegawai' => 'PEG-001',
            'email' => 'budi@example.com',
            'kata_sandi' => bcrypt('secret'),
            'posisi' => 'karyawan',
            'jabatan' => 'Staff IT',
            'divisi' => 'IT',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
        ]);

        $absensi = \App\Models\Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => today()->toDateString(),
            'check_in' => '08:30:00',
            'check_out' => '17:00:00',
            'status' => 'Hadir',
            'total_jam_kerja' => 8,
        ]);

        // 2. Buat user superadmin database biasa
        $dbSuperAdmin = Karyawan::create([
            'nama_lengkap' => 'Admin Biasa',
            'kode_pegawai' => 'SA-002',
            'email' => 'admin.biasa@example.com',
            'kata_sandi' => bcrypt('secret'),
            'posisi' => 'superadmin',
            'jabatan' => 'Super Admin',
            'divisi' => 'IT',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
        ]);

        // Coba akses menu rahasia dengan superadmin biasa -> DITOLAK (403)
        $this->actingAs($dbSuperAdmin);
        $forbiddenResponse = $this->get(route('superadmin.secret.absensi-logs'));
        $forbiddenResponse->assertStatus(403);

        // Superadmin biasa melakukan koreksi presensi
        $this->post(route('superadmin.absensi.update', $absensi->id), [
            'tanggal' => today()->toDateString(),
            'check_in' => '08:00',
            'check_out' => '17:00',
            'status' => 'Hadir',
            'keterangan' => 'Koreksi jam masuk oleh HR',
        ]);

        // Pastikan tercatat di tabel AbsensiCorrectionLog
        $this->assertEquals(1, \App\Models\AbsensiCorrectionLog::count());
        $correctionLog = \App\Models\AbsensiCorrectionLog::first();
        $this->assertEquals('Budi Santoso', $correctionLog->karyawan_nama);
        $this->assertEquals('Admin Biasa', $correctionLog->operator_name);
        $this->assertEquals('koreksi', $correctionLog->action);

        // 3. Login sebagai Root Super Admin
        Auth::logout();
        $this->post(route('superadmin.login.store'), [
            'email' => config('auth.root_superadmin.email'),
            'password' => config('auth.root_superadmin.password'),
        ]);

        // Root Super Admin BISA membuka menu rahasia (200)
        $secretResponse = $this->get(route('superadmin.secret.absensi-logs'));
        $secretResponse->assertStatus(200);
        $secretResponse->assertSee('Audit Log Koreksi Presensi');
        $secretResponse->assertSee('Budi Santoso');
        $secretResponse->assertSee('Admin Biasa');
        $secretResponse->assertSee('Nilai Awal');
        $secretResponse->assertSee('Nilai Terakhir');
        $secretResponse->assertSee('Sebelum');
        $secretResponse->assertSee('Sesudah');

        // Test hapus satu persatu
        $deleteResponse = $this->delete(route('superadmin.secret.absensi-logs.destroy', $correctionLog->id));
        $deleteResponse->assertRedirect();
        $this->assertEquals(0, \App\Models\AbsensiCorrectionLog::count());

        // Buat 3 log contoh
        \App\Models\AbsensiCorrectionLog::record($absensi, 'koreksi', [], [], 'Log 1');
        \App\Models\AbsensiCorrectionLog::record($absensi, 'koreksi', [], [], 'Log 2');
        \App\Models\AbsensiCorrectionLog::record($absensi, 'koreksi', [], [], 'Log 3');
        $this->assertEquals(3, \App\Models\AbsensiCorrectionLog::count());

        // Test hapus massal (bulk delete 2 dari 3)
        $logs = \App\Models\AbsensiCorrectionLog::take(2)->get();
        $bulkResponse = $this->post(route('superadmin.secret.absensi-logs.bulk-destroy'), [
            'ids' => $logs->pluck('id')->toArray(),
        ]);
        $bulkResponse->assertRedirect();
        $this->assertEquals(1, \App\Models\AbsensiCorrectionLog::count());

        // Test kosongkan semua
        $clearResponse = $this->post(route('superadmin.secret.absensi-logs.clear-all'));
        $clearResponse->assertRedirect();
        $this->assertEquals(0, \App\Models\AbsensiCorrectionLog::count());
    }
}
