<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Cuti;
use App\Models\CutiPolicy;
use App\Models\CutiType;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase7ManajemenCutiTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperadmin(): Karyawan
    {
        return Karyawan::create([
            'kode_pegawai' => 'SA-' . uniqid(),
            'email' => 'sa.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Super Admin Test',
            'posisi' => 'superadmin',
            'jabatan' => 'Super Administrator',
            'divisi' => 'IT',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);
    }

    private function makeHr(): Karyawan
    {
        return Karyawan::create([
            'kode_pegawai' => 'HR-' . uniqid(),
            'email' => 'hr.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'HRD Test',
            'posisi' => 'hr',
            'jabatan' => 'HR Specialist',
            'divisi' => 'HRD',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ]);
    }

    private function makeKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_pegawai' => 'K-' . uniqid(),
            'email' => 'k.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan Test',
            'posisi' => 'karyawan',
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $attributes));
    }

    public function test_superadmin_can_access_manajemen_cuti_page_and_both_tabs(): void
    {
        $sa = $this->makeSuperadmin();

        // Tab default: pengaturan
        $response = $this->actingAs($sa)->get(route('superadmin.manajemen-cuti.index'));
        $response->assertOk();
        $response->assertSee('Pengaturan Jenis Cuti');
        $response->assertSee('Tambah Jenis Cuti');

        // Tab pengajuan
        $responsePengajuan = $this->actingAs($sa)->get(route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan']));
        $responsePengajuan->assertOk();
        $responsePengajuan->assertSee('Daftar Pengajuan Cuti Seluruh Karyawan');
        $responsePengajuan->assertSee('Aksi Massal');
    }

    public function test_hr_and_karyawan_cannot_access_superadmin_manajemen_cuti(): void
    {
        $hr = $this->makeHr();
        $k = $this->makeKaryawan();

        // HR diblokir dari route superadmin
        $this->actingAs($hr)->get(route('superadmin.manajemen-cuti.index'))->assertRedirect();

        // Karyawan biasa diblokir dari route superadmin
        $this->actingAs($k)->get(route('superadmin.manajemen-cuti.index'))->assertRedirect();
    }

    public function test_seeder_creates_four_default_active_cuti_types(): void
    {
        CutiType::seedDefaults();

        $this->assertSame(4, CutiType::count());

        $expected = ['Cuti Tahunan', 'Cuti Besar', 'Cuti Melahirkan', 'Cuti Penting'];
        foreach ($expected as $nama) {
            $type = CutiType::where('nama', $nama)->first();
            $this->assertNotNull($type, "Jenis cuti {$nama} harus ada.");
            $this->assertTrue($type->is_active);
            $this->assertSame(12, $type->kuota_hari);
        }
    }

    public function test_superadmin_can_create_new_cuti_type(): void
    {
        $sa = $this->makeSuperadmin();

        $response = $this->actingAs($sa)->post(route('superadmin.manajemen-cuti.type.store'), [
            'nama' => 'Cuti Menikah',
            'kuota_hari' => 3,
            'scope_golongan' => 'Golongan I,Golongan II',
            'sort_order' => 5,
            'keterangan' => 'Cuti untuk pernikahan karyawan yang bersangkutan',
        ]);

        $response->assertRedirect(route('superadmin.manajemen-cuti.index', ['tab' => 'pengaturan']));
        $response->assertSessionHas('success');

        $type = CutiType::where('nama', 'Cuti Menikah')->first();
        $this->assertNotNull($type);
        $this->assertSame(3, $type->kuota_hari);
        $this->assertSame('Golongan I,Golongan II', $type->scope_golongan);
        $this->assertTrue($type->is_active);

        // Audit log tercatat
        $log = ActivityLog::where('action', 'cuti.type_create')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Cuti Menikah', $log->description);
    }

    public function test_superadmin_cannot_create_duplicate_cuti_type_case_insensitive(): void
    {
        $sa = $this->makeSuperadmin();
        CutiType::create([
            'nama' => 'Cuti Khusus',
            'kuota_hari' => 5,
        ]);

        $response = $this->actingAs($sa)->post(route('superadmin.manajemen-cuti.type.store'), [
            'nama' => 'cuti khusus', // huruf kecil beda case
            'kuota_hari' => 10,
        ]);

        $response->assertSessionHasErrors('nama');
        $this->assertSame(1, CutiType::where('nama', 'like', 'cuti khusus')->count());
    }

    public function test_superadmin_can_update_cuti_type(): void
    {
        $sa = $this->makeSuperadmin();
        $type = CutiType::create([
            'nama' => 'Cuti Ibadah',
            'kuota_hari' => 40,
            'is_active' => true,
        ]);

        $response = $this->actingAs($sa)->put(route('superadmin.manajemen-cuti.type.update', $type->id), [
            'nama' => 'Cuti Ibadah Haji',
            'kuota_hari' => 50,
            'sort_order' => 10,
            'is_active' => '0',
            'keterangan' => 'Khusus haji',
        ]);

        $response->assertRedirect(route('superadmin.manajemen-cuti.index', ['tab' => 'pengaturan']));
        $response->assertSessionHas('success');

        $type->refresh();
        $this->assertSame('Cuti Ibadah Haji', $type->nama);
        $this->assertSame(50, $type->kuota_hari);
        $this->assertFalse($type->is_active);

        // Audit log tercatat
        $log = ActivityLog::where('action', 'cuti.type_update')->latest('id')->first();
        $this->assertNotNull($log);
    }

    public function test_superadmin_can_delete_unused_cuti_type(): void
    {
        $sa = $this->makeSuperadmin();
        $type = CutiType::create([
            'nama' => 'Cuti Hapus',
            'kuota_hari' => 1,
        ]);

        $response = $this->actingAs($sa)->delete(route('superadmin.manajemen-cuti.type.destroy', $type->id));
        $response->assertRedirect(route('superadmin.manajemen-cuti.index', ['tab' => 'pengaturan']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('cuti_types', ['id' => $type->id]);

        $log = ActivityLog::where('action', 'cuti.type_delete')->latest('id')->first();
        $this->assertNotNull($log);
    }

    public function test_superadmin_cannot_delete_used_cuti_type(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $type = CutiType::create([
            'nama' => 'Cuti Terpakai',
            'kuota_hari' => 10,
        ]);

        // Ada pengajuan dengan jenis ini
        Cuti::create([
            'karyawan_id' => $k->id,
            'jenis_cuti' => 'Cuti Terpakai',
            'jatah_cuti' => 10,
            'sisa_cuti' => 8,
            'cuti_digunakan' => 2,
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(6)->toDateString(),
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
        ]);

        $response = $this->actingAs($sa)->delete(route('superadmin.manajemen-cuti.type.destroy', $type->id));
        $response->assertSessionHas('error');

        // Data tidak terhapus
        $this->assertDatabaseHas('cuti_types', ['id' => $type->id]);
    }

    public function test_karyawan_create_page_shows_only_active_cuti_types(): void
    {
        $k = $this->makeKaryawan();

        CutiType::create(['nama' => 'Cuti Aktif Test', 'kuota_hari' => 10, 'is_active' => true]);
        CutiType::create(['nama' => 'Cuti Nonaktif Test', 'kuota_hari' => 10, 'is_active' => false]);

        $response = $this->actingAs($k)->get(route('karyawan.cuti.create'));
        $response->assertOk();
        $response->assertSee('Cuti Aktif Test');
        $response->assertDontSee('Cuti Nonaktif Test');
    }

    public function test_karyawan_cannot_submit_inactive_or_invalid_cuti_type(): void
    {
        $k = $this->makeKaryawan();

        CutiType::create(['nama' => 'Cuti Nonaktif Test', 'kuota_hari' => 10, 'is_active' => false]);

        $response = $this->actingAs($k)->post(route('karyawan.cuti.store'), [
            'jenis_cuti' => 'Cuti Nonaktif Test',
            'tanggal_mulai' => now()->addDays(1)->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'keterangan' => 'Pengajuan tidak valid',
        ]);

        $response->assertSessionHasErrors('jenis_cuti');
    }

    public function test_karyawan_pengajuan_reduces_specific_cuti_type_quota(): void
    {
        $k = $this->makeKaryawan();

        CutiType::create(['nama' => 'Cuti Besar', 'kuota_hari' => 12, 'is_active' => true]);
        CutiType::create(['nama' => 'Cuti Tahunan', 'kuota_hari' => 12, 'is_active' => true]);

        // Buat kuota awal cuti tahunan
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertOk();

        // Ajukan Cuti Besar 3 hari
        $mulai = now()->addDays(5)->toDateString();
        $selesai = now()->addDays(7)->toDateString();

        $response = $this->actingAs($k)->post(route('karyawan.cuti.store'), [
            'jenis_cuti' => 'Cuti Besar',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
            'keterangan' => 'Libur panjang masa kerja',
        ]);

        $response->assertRedirect(route('karyawan.cuti.dashboard'));
        $response->assertSessionHas('success');

        // Kuota Cuti Besar harus berkurang jadi 9 (12 - 3)
        $kuotaBesar = Cuti::kuota()->where('karyawan_id', $k->id)->where('jenis_cuti', 'Cuti Besar')->first();
        $this->assertNotNull($kuotaBesar);
        $this->assertSame(9, (int) $kuotaBesar->sisa_cuti);
        $this->assertSame(3, (int) $kuotaBesar->cuti_digunakan);

        // Kuota Cuti Tahunan tidak boleh terpengaruh (tetap 12)
        $kuotaTahunan = Cuti::kuota()->where('karyawan_id', $k->id)->where('jenis_cuti', 'Cuti Tahunan')->first();
        $this->assertSame(12, (int) $kuotaTahunan->sisa_cuti);
    }

    public function test_karyawan_pengajuan_fails_when_duration_exceeds_specific_quota(): void
    {
        $k = $this->makeKaryawan();

        CutiType::create(['nama' => 'Cuti Singkat', 'kuota_hari' => 2, 'is_active' => true]);

        // Ajukan 5 hari padahal kuota hanya 2 hari
        $mulai = now()->addDays(2)->toDateString();
        $selesai = now()->addDays(6)->toDateString();

        $response = $this->actingAs($k)->post(route('karyawan.cuti.store'), [
            'jenis_cuti' => 'Cuti Singkat',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
            'keterangan' => 'Melebihi kuota',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, Cuti::pengajuan()->where('karyawan_id', $k->id)->where('jenis_cuti', 'Cuti Singkat')->count());
    }

    public function test_karyawan_cancel_pending_pengajuan_restores_specific_cuti_type_quota(): void
    {
        $k = $this->makeKaryawan();

        CutiType::create(['nama' => 'Cuti Penting', 'kuota_hari' => 12, 'is_active' => true]);

        $mulai = now()->addDays(3)->toDateString();
        $selesai = now()->addDays(5)->toDateString(); // 3 hari

        $this->actingAs($k)->post(route('karyawan.cuti.store'), [
            'jenis_cuti' => 'Cuti Penting',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
            'keterangan' => 'Urusan keluarga',
        ]);

        $pengajuan = Cuti::pengajuan()->where('karyawan_id', $k->id)->where('jenis_cuti', 'Cuti Penting')->first();
        $this->assertNotNull($pengajuan);

        $kuota = Cuti::kuota()->where('karyawan_id', $k->id)->where('jenis_cuti', 'Cuti Penting')->first();
        $this->assertSame(9, (int) $kuota->sisa_cuti);

        // Batalkan
        $response = $this->actingAs($k)->delete(route('karyawan.cuti.cancel', $pengajuan->id));
        $response->assertRedirect(route('karyawan.cuti.dashboard'));

        $this->assertSame(12, (int) $kuota->fresh()->sisa_cuti);
        $this->assertSame(0, (int) $kuota->fresh()->cuti_digunakan);
        $this->assertDatabaseMissing('cuti', ['id' => $pengajuan->id]);
    }

    public function test_all_cuti_types_appear_in_hr_and_superadmin_views(): void
    {
        $sa = $this->makeSuperadmin();
        $hr = $this->makeHr();
        $k = $this->makeKaryawan();

        Cuti::create([
            'karyawan_id' => $k->id,
            'jenis_cuti' => 'Cuti Melahirkan',
            'jatah_cuti' => 90,
            'sisa_cuti' => 0,
            'cuti_digunakan' => 90,
            'tanggal_mulai' => now()->addDays(10)->toDateString(),
            'tanggal_selesai' => now()->addDays(99)->toDateString(),
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
            'keterangan' => 'Pengajuan melahirkan',
        ]);

        // Cuti non-tahunan harus muncul di HR
        $responseHr = $this->actingAs($hr)->get(route('hr.cuti.index'));
        $responseHr->assertOk();
        $responseHr->assertSee('Cuti Melahirkan');

        // Cuti non-tahunan harus muncul di Superadmin tab pengajuan
        $responseSa = $this->actingAs($sa)->get(route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan']));
        $responseSa->assertOk();
        $responseSa->assertSee('Cuti Melahirkan');
    }

    public function test_superadmin_can_approve_cuti_and_records_approved_by(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $pengajuan = Cuti::create([
            'karyawan_id' => $k->id,
            'jenis_cuti' => 'Cuti Besar',
            'jatah_cuti' => 12,
            'sisa_cuti' => 10,
            'cuti_digunakan' => 2,
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(6)->toDateString(),
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
        ]);

        $response = $this->actingAs($sa)->post(route('superadmin.manajemen-cuti.approve', $pengajuan->id), [
            'status' => 'approved',
            'catatan_hr' => 'Disetujui oleh Super Administrator',
        ]);

        $response->assertRedirect(route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan']));
        $response->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertSame('approved', $pengajuan->status);
        $this->assertSame($sa->id, $pengajuan->approved_by);
        $this->assertNotNull($pengajuan->approved_at);
    }

    public function test_superadmin_can_bulk_approve_cuti(): void
    {
        $sa = $this->makeSuperadmin();
        $k1 = $this->makeKaryawan();
        $k2 = $this->makeKaryawan();

        $p1 = Cuti::create([
            'karyawan_id' => $k1->id,
            'jenis_cuti' => 'Cuti Besar',
            'jatah_cuti' => 12,
            'sisa_cuti' => 10,
            'cuti_digunakan' => 2,
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(6)->toDateString(),
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
        ]);

        $p2 = Cuti::create([
            'karyawan_id' => $k2->id,
            'jenis_cuti' => 'Cuti Penting',
            'jatah_cuti' => 12,
            'sisa_cuti' => 11,
            'cuti_digunakan' => 1,
            'tanggal_mulai' => now()->addDays(8)->toDateString(),
            'tanggal_selesai' => now()->addDays(8)->toDateString(),
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
        ]);

        $response = $this->actingAs($sa)->post(route('superadmin.manajemen-cuti.bulk-approve'), [
            'ids' => [$p1->id, $p2->id],
            'target_status' => 'approved',
            'catatan_hr' => 'Bulk approved SA',
        ]);

        $response->assertRedirect(route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan']));
        $this->assertSame('approved', $p1->fresh()->status);
        $this->assertSame('approved', $p2->fresh()->status);
        $this->assertSame($sa->id, $p1->fresh()->approved_by);
        $this->assertSame($sa->id, $p2->fresh()->approved_by);
    }

    public function test_reject_restores_quota_for_correct_cuti_type(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $kuota = Cuti::create([
            'karyawan_id' => $k->id,
            'jenis_cuti' => 'Cuti Besar',
            'jatah_cuti' => 12,
            'sisa_cuti' => 8,
            'cuti_digunakan' => 4,
            'status' => 'approved',
            'tanggal_pengajuan' => now(),
        ]);

        $pengajuan = Cuti::create([
            'karyawan_id' => $k->id,
            'jenis_cuti' => 'Cuti Besar',
            'jatah_cuti' => 12,
            'sisa_cuti' => 8,
            'cuti_digunakan' => 4,
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(8)->toDateString(), // 4 hari
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
        ]);

        $this->actingAs($sa)->post(route('superadmin.manajemen-cuti.approve', $pengajuan->id), [
            'status' => 'rejected',
            'catatan_hr' => 'Tidak memenuhi syarat',
        ]);

        $kuota->refresh();
        $this->assertSame(12, (int) $kuota->sisa_cuti);
        $this->assertSame(0, (int) $kuota->cuti_digunakan);
    }

    public function test_cuti_policy_priority_over_cuti_type_default(): void
    {
        $k = $this->makeKaryawan();

        CutiType::create([
            'nama' => 'Cuti Tahunan',
            'kuota_hari' => 14,
        ]);

        // Tanpa policy tahun ini -> memakai kuota dari CutiType
        $this->assertSame(14, CutiPolicy::kuotaFor($k, now()->year, 'Cuti Tahunan'));

        // Jika ada policy untuk tahun berjalan -> policy harus MENANG (prioritas 1)
        CutiPolicy::create([
            'tahun' => now()->year,
            'jenis_cuti' => 'Cuti Tahunan',
            'kuota_hari' => 20,
            'is_active' => true,
        ]);

        $this->assertSame(20, CutiPolicy::kuotaFor($k, now()->year, 'Cuti Tahunan'));
    }
}
