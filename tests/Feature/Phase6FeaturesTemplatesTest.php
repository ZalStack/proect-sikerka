<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\FeatureAssignment;
use App\Models\Karyawan;
use App\Models\MessageTemplate;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Fase 6 — Penugasan Fitur per cakupan (F22) & Template Pesan (F23).
 */
class Phase6FeaturesTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperadmin(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-P6-' . $counter . '-' . uniqid(),
            'email' => 'p6' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Super Admin P6 ' . $counter,
            'jabatan' => 'Super Administrator Sistem',
            'divisi' => 'IT',
            'posisi' => 'superadmin',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    private function makeKaryawan(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-K6-' . $counter . '-' . uniqid(),
            'email' => 'k6' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan P6 ' . $counter,
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'posisi' => 'karyawan',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    private function makeHr(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-H6-' . $counter . '-' . uniqid(),
            'email' => 'h6' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'HR P6 ' . $counter,
            'jabatan' => 'HR',
            'divisi' => 'HRD',
            'posisi' => 'hr',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    // ==========================================================
    // PENUGASAN FITUR (F22)
    // ==========================================================

    public function test_assignment_page_renders(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.features.assignments.index'))
            ->assertOk()
            ->assertSee('Penugasan Fitur per Pengguna')
            ->assertSee('Cara Kerja Penugasan');
    }

    public function test_assignment_page_blocked_for_hr(): void
    {
        $hr = $this->makeHr();

        $this->actingAs($hr)
            ->get(route('superadmin.features.assignments.index'))
            ->assertRedirect(route('hr.dashboard'));
    }

    public function test_store_assignment_role_scope(): void
    {
        $sa = $this->makeSuperadmin();
        SystemSetting::setFeature('cuti', true);

        $this->actingAs($sa)->post(route('superadmin.features.assignments.store'), [
            'feature_key' => 'cuti',
            'scope_type' => 'role',
            'scope_value' => 'karyawan',
            'enabled' => '0',
            'notes' => 'Cut ditutup sementara untuk karyawan',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('feature_assignments', [
            'feature_key' => 'cuti',
            'scope_type' => 'role',
            'scope_value' => 'karyawan',
            'enabled' => 0,
        ]);

        $log = ActivityLog::where('action', 'feature.assignment')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Role: karyawan', $log->description);
    }

    public function test_store_assignment_rejects_unknown_feature(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.features.assignments.store'), [
            'feature_key' => 'fitur_ngawur',
            'scope_type' => 'role',
            'scope_value' => 'karyawan',
            'enabled' => '1',
        ])->assertSessionHasErrors('feature_key');

        $this->assertSame(0, FeatureAssignment::count());
    }

    public function test_store_assignment_rejects_unknown_role_value(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.features.assignments.store'), [
            'feature_key' => 'cuti',
            'scope_type' => 'role',
            'scope_value' => 'bos_besar',
            'enabled' => '1',
        ])->assertSessionHasErrors('scope_value');
    }

    public function test_divisi_assignment_blocks_only_matching_divisi(): void
    {
        $sa = $this->makeSuperadmin();
        SystemSetting::setFeature('cuti', true);

        $hrKeuangan = $this->makeHr(['divisi' => 'Keuangan']);
        $hrLain = $this->makeHr(['divisi' => 'HRD']);

        // Sebelum penugasan: keduanya bisa akses.
        $this->actingAs($hrKeuangan)->get(route('hr.cuti.index'))->assertOk();
        $this->actingAs($hrLain)->get(route('hr.cuti.index'))->assertOk();

        SystemSetting::assignFeature('cuti', 'divisi', 'Keuangan', false, 'Uji coba', $sa->id);

        // Divisi Keuangan terblokir, divisi lain tetap terbuka.
        $this->actingAs($hrKeuangan)->get(route('hr.cuti.index'))->assertStatus(403);
        $this->actingAs($hrLain)->get(route('hr.cuti.index'))->assertOk();

        $this->assertFalse(SystemSetting::isFeatureEnabledFor($hrKeuangan, 'cuti'));
        $this->assertTrue(SystemSetting::isFeatureEnabledFor($hrLain, 'cuti'));
    }

    public function test_karyawan_scope_overrides_divisi_scope(): void
    {
        $sa = $this->makeSuperadmin();
        SystemSetting::setFeature('cuti', true);

        $k = $this->makeKaryawan(['divisi' => 'Operasional']);

        SystemSetting::assignFeature('cuti', 'divisi', 'Operasional', false, null, $sa->id);
        SystemSetting::assignFeature('cuti', 'karyawan', (string) $k->id, true, 'Dikecualikan', $sa->id);

        // Karyawan individual mengalahkan pemblokiran divisi.
        $this->assertTrue(SystemSetting::isFeatureEnabledFor($k, 'cuti'));
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertOk();

        // Karyawan lain di divisi yang sama tetap terblokir.
        $lain = $this->makeKaryawan(['divisi' => 'Operasional']);
        $this->assertFalse(SystemSetting::isFeatureEnabledFor($lain, 'cuti'));
        $this->actingAs($lain)->get(route('karyawan.cuti.dashboard'))->assertStatus(403);
    }

    public function test_role_scope_blocks_karyawan_when_global_on(): void
    {
        $sa = $this->makeSuperadmin();
        SystemSetting::setFeature('cuti', true);

        $k = $this->makeKaryawan();

        SystemSetting::assignFeature('cuti', 'role', 'karyawan', false, null, $sa->id);

        $this->assertFalse(SystemSetting::isFeatureEnabledFor($k, 'cuti'));
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertStatus(403);

        // HR tidak terpengaruh penugasan role=karyawan.
        $hr = $this->makeHr();
        $this->assertTrue(SystemSetting::isFeatureEnabledFor($hr, 'cuti'));
        $this->actingAs($hr)->get(route('hr.cuti.index'))->assertOk();
    }

    public function test_global_off_beats_enabled_assignment(): void
    {
        $sa = $this->makeSuperadmin();

        // Global dimatikan, lalu ada penugasan yang mencoba membuka.
        SystemSetting::setFeature('cuti', false);
        SystemSetting::assignFeature('cuti', 'role', 'karyawan', true, 'Paksa buka', $sa->id);

        $k = $this->makeKaryawan();

        $this->assertFalse(SystemSetting::isFeatureEnabledFor($k, 'cuti'));
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertStatus(403);
    }

    public function test_no_matching_assignment_follows_global(): void
    {
        $sa = $this->makeSuperadmin();
        SystemSetting::setFeature('cuti', true);

        $k = $this->makeKaryawan();

        // Tidak ada penugasan sama sekali → ikut global (terbuka).
        $this->assertTrue(SystemSetting::isFeatureEnabledFor($k, 'cuti'));
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertOk();

        // Penugasan untuk cakupan lain tidak mempengaruhi user ini.
        SystemSetting::assignFeature('cuti', 'divisi', 'Divisi Lain', false, null, $sa->id);
        $this->assertTrue(SystemSetting::isFeatureEnabledFor($k, 'cuti'));
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertOk();
    }

    public function test_destroy_assignment_restores_access(): void
    {
        $sa = $this->makeSuperadmin();
        SystemSetting::setFeature('cuti', true);

        $k = $this->makeKaryawan();

        $this->actingAs($sa)->post(route('superadmin.features.assignments.store'), [
            'feature_key' => 'cuti',
            'scope_type' => 'role',
            'scope_value' => 'karyawan',
            'enabled' => '0',
        ])->assertSessionHas('success');

        $assignment = FeatureAssignment::first();
        $this->assertNotNull($assignment);
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertStatus(403);

        $this->actingAs($sa)
            ->delete(route('superadmin.features.assignments.destroy', $assignment->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('feature_assignments', ['id' => $assignment->id]);
        $this->assertTrue(SystemSetting::isFeatureEnabledFor($k, 'cuti'));
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertOk();
    }

    // ==========================================================
    // TEMPLATE PESAN (F23)
    // ==========================================================

    public function test_template_page_renders_with_tabs(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.message-templates.index'))
            ->assertOk()
            ->assertSee('Template Pesan Siap Pakai')
            ->assertSee('Pesan Ban')
            ->assertSee('Broadcast Pengumuman');
    }

    public function test_template_page_blocked_for_hr(): void
    {
        $hr = $this->makeHr();

        $this->actingAs($hr)
            ->get(route('superadmin.message-templates.index'))
            ->assertRedirect(route('hr.dashboard'));
    }

    public function test_template_crud_lifecycle(): void
    {
        $sa = $this->makeSuperadmin();

        // CREATE
        $this->actingAs($sa)->post(route('superadmin.message-templates.store'), [
            'type' => 'ban',
            'title' => 'Peringatan Pertama',
            'body' => 'Pelanggaran pertama. Blokir berlaku 1 hari.',
            'is_default' => '1',
            'is_active' => '1',
        ])->assertSessionHas('success');

        $t = MessageTemplate::where('title', 'Peringatan Pertama')->first();
        $this->assertNotNull($t);
        $this->assertSame('ban', $t->type);
        $this->assertTrue($t->is_default);
        $this->assertSame($sa->id, $t->created_by);

        // DUPLICATE DITOLAK (case-insensitive, per tipe)
        $this->actingAs($sa)->post(route('superadmin.message-templates.store'), [
            'type' => 'ban',
            'title' => 'peringatan pertama',
            'body' => 'Isi lain',
        ])->assertSessionHasErrors('title');
        $this->assertSame(1, MessageTemplate::where('type', 'ban')->count());

        // Type berbeda boleh berjudul sama.
        $this->actingAs($sa)->post(route('superadmin.message-templates.store'), [
            'type' => 'violation',
            'title' => 'Peringatan Pertama',
            'body' => 'Versi pelanggaran',
        ])->assertSessionHas('success');
        $this->assertSame(2, MessageTemplate::count());

        // UPDATE + pindah default
        $violation = MessageTemplate::where('type', 'violation')->first();
        $this->actingAs($sa)->put(route('superadmin.message-templates.update', $t->id), [
            'type' => 'ban',
            'title' => 'Peringatan Pertama v2',
            'body' => 'Isi diperbarui.',
            'is_default' => '1',
            'is_active' => '1',
        ])->assertSessionHas('success');
        $this->assertSame('Peringatan Pertama v2', $t->fresh()->title);

        // DELETE
        $this->actingAs($sa)
            ->delete(route('superadmin.message-templates.destroy', $violation->id))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('message_templates', ['id' => $violation->id]);

        // Log audit lengkap.
        $this->assertNotNull(ActivityLog::where('action', 'template.create')->latest('id')->first());
        $this->assertNotNull(ActivityLog::where('action', 'template.update')->latest('id')->first());
        $this->assertNotNull(ActivityLog::where('action', 'template.delete')->latest('id')->first());
    }

    public function test_default_flag_moves_between_templates_of_same_type(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.message-templates.store'), [
            'type' => 'broadcast',
            'title' => 'Template A',
            'body' => 'Isi A',
            'is_default' => '1',
            'is_active' => '1',
        ]);
        $this->actingAs($sa)->post(route('superadmin.message-templates.store'), [
            'type' => 'broadcast',
            'title' => 'Template B',
            'body' => 'Isi B',
            'is_default' => '1',
            'is_active' => '1',
        ]);

        // Hanya satu default per tipe: yang terakhir menang.
        $this->assertSame(0, MessageTemplate::where('type', 'broadcast')
            ->where('title', 'Template A')->where('is_default', true)->count());
        $this->assertSame(1, MessageTemplate::where('type', 'broadcast')
            ->where('title', 'Template B')->where('is_default', true)->count());
    }

    public function test_template_store_rejects_invalid_type(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.message-templates.store'), [
            'type' => 'ngawur',
            'title' => 'X',
            'body' => 'Y',
        ])->assertSessionHasErrors('type');
    }

    public function test_template_render_replaces_placeholders(): void
    {
        $t = MessageTemplate::create([
            'type' => 'violation',
            'title' => 'Contoh',
            'body' => 'Halo {nama}, pelanggaran ke-{strike}.',
            'is_active' => true,
        ]);

        $this->assertSame(
            'Halo Budi, pelanggaran ke-2.',
            $t->render(['nama' => 'Budi', 'strike' => 2])
        );
    }

    public function test_broadcast_page_lists_broadcast_templates(): void
    {
        $sa = $this->makeSuperadmin();

        MessageTemplate::create([
            'type' => 'broadcast',
            'title' => 'Edaran Gajian',
            'body' => 'Penggajian bulan ini cair tanggal 25.',
            'is_default' => true,
            'is_active' => true,
            'created_by' => $sa->id,
        ]);

        $this->actingAs($sa)
            ->get(route('superadmin.broadcast.index'))
            ->assertOk()
            ->assertSee('Edaran Gajian')
            ->assertSee('template-picker', false);
    }

    public function test_violations_page_lists_violation_and_ban_templates(): void
    {
        $sa = $this->makeSuperadmin();

        MessageTemplate::create([
            'type' => 'violation',
            'title' => 'Konfirmasi Remote',
            'body' => 'Akses remote terdeteksi saat presensi.',
            'is_active' => true,
            'created_by' => $sa->id,
        ]);
        MessageTemplate::create([
            'type' => 'ban',
            'title' => 'Alert Blokir 1 Hari',
            'body' => 'Presensi diblokir 1 hari karena pelanggaran.',
            'is_active' => true,
            'created_by' => $sa->id,
        ]);
        // Template nonaktif tidak boleh muncul.
        MessageTemplate::create([
            'type' => 'ban',
            'title' => 'Template Nonaktif',
            'body' => 'Tidak boleh tampil.',
            'is_active' => false,
        ]);

        // Ada karyawan terblokir supaya form pesan ban ikut tampil,
        // dan ada pelanggaran pending supaya form konfirmasi tampil.
        $k = $this->makeKaryawan([
            'banned_until' => now()->addDay(),
            'ban_message' => 'Pesan lama',
        ]);
        \App\Models\RemoteAccessViolation::create([
            'karyawan_id' => $k->id,
            'tanggal' => now()->toDateString(),
            'konteks' => 'checkin',
            'signals' => [['source' => 'client_flag', 'code' => 'resolusi_layar_tidak_wajar', 'weight' => 40, 'detail' => 'tes']],
            'risk_score' => 40,
            'risk_level' => \App\Models\RemoteAccessViolation::RISK_HIGH,
            'status' => \App\Models\RemoteAccessViolation::STATUS_PENDING,
        ]);

        $response = $this->actingAs($sa)->get(route('superadmin.violations.index'));

        $response->assertOk()
            ->assertSee('violation-template-picker', false)
            ->assertSee('ban-template-picker', false)
            ->assertSee('Konfirmasi Remote')
            ->assertSee('Alert Blokir 1 Hari')
            ->assertDontSee('Template Nonaktif');
    }

    public function test_inactive_templates_are_not_offered(): void
    {
        $sa = $this->makeSuperadmin();

        MessageTemplate::create([
            'type' => 'broadcast',
            'title' => 'Template Mati',
            'body' => 'Sembunyikan saya.',
            'is_active' => false,
        ]);

        $response = $this->actingAs($sa)->get(route('superadmin.broadcast.index'));

        $response->assertOk()->assertDontSee('Template Mati');
    }
}
