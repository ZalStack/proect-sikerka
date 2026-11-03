<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Karyawan;
use App\Models\RemoteAccessViolation;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Uji seluruh ALUR AKSI (POST/PUT/DELETE) role Superadmin — bukan hanya
 * halaman render-nya, tetapi aksi tombol/form benar-benar bekerja:
 * - Fitur & maintenance toggle
 * - CRUD presensi manual (kalkulasi jam kerja, duplikat, hapus)
 * - Ubah role + guard superadmin terakhir
 * - Generate link reset password
 * - Konfirmasi / tolak pelanggaran + unban + pesan ban
 * - Kelola hari libur
 */
class SuperAdminActionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-AKSI-' . $counter . '-' . uniqid(),
            'email' => 'aksi' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Aksi ' . $counter,
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    private function makeSuperadmin(array $overrides = []): Karyawan
    {
        return $this->makeKaryawan(array_merge([
            'posisi' => 'superadmin',
            'jabatan' => 'Super Administrator Sistem',
            'nama_lengkap' => 'Super Admin Test',
        ], $overrides));
    }

    // ==========================================================
    // FITUR & MAINTENANCE
    // ==========================================================

    public function test_single_feature_toggle_flips_status(): void
    {
        $sa = $this->makeSuperadmin();
        $this->assertTrue(SystemSetting::isFeatureEnabled('absensi'));

        $this->actingAs($sa)
            ->post(route('superadmin.features.toggle', 'absensi'))
            ->assertSessionHasNoErrors();

        $this->assertFalse(SystemSetting::isFeatureEnabled('absensi'));

        // Toggle lagi → aktif kembali
        $this->actingAs($sa)->post(route('superadmin.features.toggle', 'absensi'));
        $this->assertTrue(SystemSetting::isFeatureEnabled('absensi'));
    }

    public function test_toggle_all_disables_then_enables_every_feature(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->post(route('superadmin.features.toggle-all'), ['action' => 'disable_all'])
            ->assertSessionHasNoErrors();

        foreach (array_keys(SystemSetting::AVAILABLE_FEATURES) as $feature) {
            $this->assertFalse(SystemSetting::isFeatureEnabled($feature), "Fitur {$feature} seharusnya mati");
        }

        $this->actingAs($sa)
            ->post(route('superadmin.features.toggle-all'), ['action' => 'enable_all'])
            ->assertSessionHasNoErrors();

        foreach (array_keys(SystemSetting::AVAILABLE_FEATURES) as $feature) {
            $this->assertTrue(SystemSetting::isFeatureEnabled($feature), "Fitur {$feature} seharusnya hidup");
        }
    }

    public function test_maintenance_mode_toggle(): void
    {
        $sa = $this->makeSuperadmin();
        $this->assertFalse(SystemSetting::isMaintenance());

        // Aktifkan → sistem offline untuk karyawan, superadmin tetap tembus
        $this->actingAs($sa)
            ->post(route('superadmin.maintenance.toggle'))
            ->assertSessionHasNoErrors();
        $this->assertTrue(SystemSetting::isMaintenance());

        // Nonaktifkan kembali
        $this->actingAs($sa)
            ->post(route('superadmin.maintenance.toggle'))
            ->assertSessionHasNoErrors();
        $this->assertFalse(SystemSetting::isMaintenance());
    }

    // ==========================================================
    // PRESENSI MANUAL
    // ==========================================================

    public function test_absensi_manual_store_calculates_work_hours(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)->post(route('superadmin.absensi.store'), [
            'karyawan_id' => $k->id,
            'tanggal' => now()->toDateString(),
            'check_in' => '08:00',
            'check_out' => '17:00',
            'status' => 'Hadir',
            'keterangan' => 'Input manual uji coba',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $absensi = Absensi::where('karyawan_id', $k->id)->whereDate('tanggal', now()->toDateString())->first();
        $this->assertNotNull($absensi);
        $this->assertGreaterThan(0, $absensi->total_jam_kerja, 'Jam kerja wajib terhitung otomatis');
    }

    public function test_absensi_store_rejects_duplicate_date(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $payload = [
            'karyawan_id' => $k->id,
            'tanggal' => now()->toDateString(),
            'check_in' => '08:00',
            'status' => 'Hadir',
        ];

        $this->actingAs($sa)->post(route('superadmin.absensi.store'), $payload);
        $this->actingAs($sa)->post(route('superadmin.absensi.store'), $payload)
            ->assertSessionHas('error');

        $this->assertSame(1, Absensi::where('karyawan_id', $k->id)->count());
    }

    public function test_absensi_update_recalculates_work_hours(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)->post(route('superadmin.absensi.store'), [
            'karyawan_id' => $k->id,
            'tanggal' => now()->toDateString(),
            'check_in' => '08:00',
            'check_out' => '17:00',
            'status' => 'Hadir',
        ]);

        $absensi = Absensi::where('karyawan_id', $k->id)->first();
        $before = $absensi->total_jam_kerja;

        $this->actingAs($sa)->put(route('superadmin.absensi.update', $absensi->id), [
            'tanggal' => now()->toDateString(),
            'check_in' => '08:00',
            'check_out' => '16:00',
            'status' => 'Hadir',
            'keterangan' => 'Dikoreksi',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $absensi->refresh();
        $this->assertLessThan($before, $absensi->total_jam_kerja, 'Jam kerja wajib dihitung ulang');
    }

    public function test_absensi_delete(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)->post(route('superadmin.absensi.store'), [
            'karyawan_id' => $k->id,
            'tanggal' => now()->toDateString(),
            'status' => 'Hadir',
        ]);

        $absensi = Absensi::where('karyawan_id', $k->id)->firstOrFail();

        $this->actingAs($sa)
            ->delete(route('superadmin.absensi.destroy', $absensi->id))
            ->assertSessionHas('success');

        $this->assertNull(Absensi::find($absensi->id));
    }

    // ==========================================================
    // ROLE PENGGUNA
    // ==========================================================

    public function test_role_update_changes_position(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan(['posisi' => 'karyawan']);

        $this->actingAs($sa)
            ->put(route('superadmin.karyawan.update-role', $k->id), ['posisi' => 'hr'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('hr', $k->fresh()->posisi);
    }

    public function test_role_update_blocks_demoting_the_last_superadmin(): void
    {
        $sa = $this->makeSuperadmin();

        // Pastikan hanya $sa yang berposisi superadmin (nonaktifkan sisanya)
        Karyawan::where('posisi', 'superadmin')
            ->where('id', '!=', $sa->id)
            ->update(['posisi' => 'karyawan']);

        $this->actingAs($sa)
            ->put(route('superadmin.karyawan.update-role', $sa->id), ['posisi' => 'karyawan'])
            ->assertSessionHas('error');

        $this->assertSame('superadmin', $sa->fresh()->posisi);
    }

    public function test_generate_reset_link_stores_single_use_token(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan.generate-reset-link', $k->id))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('reset_link');

        $this->assertDatabaseHas('password_resets', ['email' => $k->email]);
        $this->assertStringContainsString(route('password.reset.form', ['token' => 'x']) === '' ? '' : 'reset-password', session('reset_link'));
    }

    // ==========================================================
    // PELANGGARAN PRESENSI (BAN)
    // ==========================================================

    private function makeViolation(Karyawan $k): RemoteAccessViolation
    {
        return RemoteAccessViolation::create([
            'karyawan_id' => $k->id,
            'tanggal' => now()->toDateString(),
            'konteks' => 'checkin',
            'signals' => ['teamviewer_process' => true],
            'risk_score' => 90,
            'risk_level' => RemoteAccessViolation::RISK_HIGH,
            'status' => RemoteAccessViolation::STATUS_PENDING,
        ]);
    }

    public function test_violation_confirm_applies_first_ban(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();
        $v = $this->makeViolation($k);

        $this->actingAs($sa)
            ->post(route('superadmin.violations.confirm', $v->id), ['pesan' => 'Dilarang akses remote'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $v->refresh();
        $k->refresh();

        $this->assertSame(RemoteAccessViolation::STATUS_CONFIRMED, $v->status);
        $this->assertSame(1, $v->strike_number);
        $this->assertSame(1, $k->violation_count);
        $this->assertNotNull($k->banned_until, 'Blokir presensi 1 hari wajib diterapkan');
        $this->assertSame('Dilarang akses remote', $k->ban_message);
        $this->assertSame($sa->id, $v->reviewed_by);
    }

    public function test_violation_dismiss_marks_false_positive(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();
        $v = $this->makeViolation($k);

        $this->actingAs($sa)
            ->post(route('superadmin.violations.dismiss', $v->id), ['catatan' => 'GPS error'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $v->refresh();
        $this->assertSame(RemoteAccessViolation::STATUS_DISMISSED, $v->status);
        $this->assertStringContainsString('GPS error', (string) $v->notes);
        $this->assertSame(0, $k->fresh()->violation_count);
    }

    public function test_unban_lifts_block_and_resets_counter(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();
        $v = $this->makeViolation($k);

        $this->actingAs($sa)->post(route('superadmin.violations.confirm', $v->id));
        $this->assertNotNull($k->fresh()->banned_until);

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan.unban', $k->id))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $k->refresh();
        $this->assertNull($k->banned_until);
        $this->assertFalse($k->is_banned_permanent);
        $this->assertSame(0, $k->violation_count);
    }

    public function test_ban_message_can_be_updated(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan.ban-message', $k->id), [
                'ban_message' => 'Presensi diblokir karena indikasi remote access.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('Presensi diblokir karena indikasi remote access.', $k->fresh()->ban_message);
    }

    // ==========================================================
    // HARI LIBUR
    // ==========================================================

    public function test_hari_libur_store_and_destroy(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.hari-libur.store'), [
            'tanggal' => '2026-12-25',
            'keterangan' => 'Natal (uji coba)',
            'jenis' => 'nasional',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        // SQLite menyimpan nilai date sebagai 'Y-m-d 00:00:00' → bandingkan via whereDate.
        $this->assertTrue(HariLibur::whereDate('tanggal', '2026-12-25')->exists());

        $id = HariLibur::whereDate('tanggal', '2026-12-25')->firstOrFail()->id;

        $this->actingAs($sa)
            ->delete(route('superadmin.hari-libur.destroy', $id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('hari_liburs', ['id' => $id]);
    }

    public function test_hari_libur_rejects_duplicate_date(): void
    {
        $sa = $this->makeSuperadmin();

        $payload = [
            'tanggal' => '2026-12-25',
            'keterangan' => 'Natal',
            'jenis' => 'nasional',
        ];

        $this->actingAs($sa)->post(route('superadmin.hari-libur.store'), $payload);
        $this->actingAs($sa)->post(route('superadmin.hari-libur.store'), $payload)
            ->assertSessionHasErrors('tanggal');

        $this->assertSame(1, HariLibur::whereDate('tanggal', '2026-12-25')->count());
    }
}
