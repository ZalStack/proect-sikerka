<?php

namespace Tests\Feature;

use App\Models\Cuti;
use App\Models\CutiPolicy;
use App\Models\Karyawan;
use App\Models\MasterData;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fase 5 — Import massal (D12), Data Master (D13),
 * Kebijakan Cuti (D14), Offboarding terpusat (D15).
 */
class Phase5DataKaryawanTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperadmin(array $overrides = []): Karyawan
    {
        static $counter = 0;
        $counter++;

        return Karyawan::create(array_merge([
            'kode_pegawai' => 'NIP-P5-' . $counter . '-' . uniqid(),
            'email' => 'p5' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Super Admin P5 ' . $counter,
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
            'kode_pegawai' => 'NIP-K5-' . $counter . '-' . uniqid(),
            'email' => 'k5' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'Karyawan P5 ' . $counter,
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
            'kode_pegawai' => 'NIP-H5-' . $counter . '-' . uniqid(),
            'email' => 'h5' . $counter . '.' . uniqid() . '@test.local',
            'kata_sandi' => Hash::make('password'),
            'nama_lengkap' => 'HR P5 ' . $counter,
            'jabatan' => 'HR',
            'divisi' => 'HRD',
            'posisi' => 'hr',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
            'is_resigned' => false,
        ], $overrides));
    }

    // ==========================================================
    // IMPORT MASSAL (D12)
    // ==========================================================

    public function test_import_page_renders(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.karyawan-import.index'))
            ->assertOk()
            ->assertSee('Import Massal Karyawan')
            ->assertSee('kode_pegawai');
    }

    public function test_import_blocked_for_hr(): void
    {
        $hr = $this->makeHr();

        $this->actingAs($hr)
            ->get(route('superadmin.karyawan-import.index'))
            ->assertRedirect(route('hr.dashboard'));
    }

    public function test_import_preview_requires_file(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan-import.preview'), [])
            ->assertSessionHasErrors('import_file');
    }

    public function test_import_preview_does_not_touch_database(): void
    {
        $sa = $this->makeSuperadmin();

        $csv = "kode_pegawai,nama_lengkap,email,jabatan,divisi,status,tanggal_bergabung\n"
            . "NIP-CSV-001,Budi Santoso,budi1@test.local,Staff,Operasional,Karyawan Tetap,2026-01-15\n";

        $file = UploadedFile::fake()->createWithContent('karyawan.csv', $csv);

        $before = Karyawan::count();

        $response = $this->actingAs($sa)->post(
            route('superadmin.karyawan-import.preview'),
            ['import_file' => $file]
        );

        $response->assertOk()->assertSee('Preview');
        $this->assertSame($before, Karyawan::count(), 'Preview tidak boleh membuat karyawan.');
        $this->assertNotNull(session('karyawan_import'));
    }

    public function test_import_commit_creates_valid_rows_and_skips_errors(): void
    {
        $sa = $this->makeSuperadmin();
        // Email duplikat dengan data yang sudah ada.
        $existing = $this->makeKaryawan();

        $csv = "kode_pegawai,nama_lengkap,email,jabatan,divisi,status,tanggal_bergabung\n"
            . "NIP-CSV-101,Siti Aminah,siti101@test.local,Staff,Produksi,Karyawan Tetap,2026-02-01\n"
            . "NIP-CSV-102,Andi Wijaya,andi102@test.local,Supervisor,Produksi,Contract,2026-03-01\n"
            . "NIP-CSV-101,Duplikat Kode,duplikat@test.local,Staff,Operasional,Karyawan Tetap,2026-04-01\n"
            . ",Tanpa Kode,tanpaKode@test.local,Staff,Operasional,Karyawan Tetap,2026-05-01\n"
            . "NIP-CSV-104,Email Duplikat,{$existing->email},Staff,Operasional,Karyawan Tetap,2026-06-01\n"
            . "NIP-CSV-105,Status Ngawur,status@test.local,Staff,Operasional,Permanent Banget,2026-07-01\n"
            . "NIP-CSV-106,Tanggal Rusak,tanggal@test.local,Staff,Operasional,Karyawan Tetap,bukan-tanggal\n";

        $file = UploadedFile::fake()->createWithContent('karyawan.csv', $csv);

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan-import.preview'), ['import_file' => $file])
            ->assertOk();

        $before = Karyawan::count();

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan-import.commit'))
            ->assertSessionHas('success');

        // 2 baris valid (NIP-CSV-101 & 102) yang tersimpan.
        $this->assertSame($before + 2, Karyawan::count());
        $this->assertDatabaseHas('karyawans', ['kode_pegawai' => 'NIP-CSV-101', 'nama_lengkap' => 'Siti Aminah']);
        $this->assertDatabaseHas('karyawans', ['kode_pegawai' => 'NIP-CSV-102', 'status' => 'Contract']);

        // Duplikat & baris rusak tidak masuk.
        $this->assertSame(0, Karyawan::where('nama_lengkap', 'Duplikat Kode')->count());
        $this->assertSame(0, Karyawan::where('nama_lengkap', 'Status Ngawur')->count());
        $this->assertSame(0, Karyawan::where('nama_lengkap', 'Tanggal Rusak')->count());

        // Akun baru punya password acak (tidak kosong) & posisi karyawan.
        $new = Karyawan::where('kode_pegawai', 'NIP-CSV-101')->first();
        $this->assertNotEmpty($new->kata_sandi);
        $this->assertSame('karyawan', $new->posisi);

        // Log audit tercatat.
        $log = ActivityLog::where('action', 'karyawan.import')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('2 berhasil', $log->description);

        // Sesi dibersihkan setelah commit.
        $this->assertNull(session('karyawan_import'));
    }

    public function test_import_commit_without_session_shows_error_not_500(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan-import.commit'))
            ->assertSessionHas('error');
    }

    public function test_import_rejects_unknown_extension(): void
    {
        $sa = $this->makeSuperadmin();

        $file = UploadedFile::fake()->create('karyawan.pdf', 10, 'application/pdf');

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan-import.preview'), ['import_file' => $file])
            ->assertSessionHasErrors('import_file');
    }

    public function test_import_syncs_new_values_to_master_data_on_commit(): void
    {
        $sa = $this->makeSuperadmin();

        $csv = "kode_pegawai,nama_lengkap,email,jabatan,divisi,status,tanggal_bergabung\n"
            . "NIP-MD-001,Master Sync,mdsync@test.local,QA Engineer,Quality,Karyawan Tetap,2026-01-10\n";

        $file = UploadedFile::fake()->createWithContent('karyawan.csv', $csv);

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan-import.preview'), ['import_file' => $file])
            ->assertOk();

        // Preview tidak membuat master data.
        $this->assertSame(0, MasterData::where('name', 'Quality')->count());

        $this->actingAs($sa)->post(route('superadmin.karyawan-import.commit'));

        $this->assertDatabaseHas('master_data', ['type' => 'divisi', 'name' => 'Quality']);
        $this->assertDatabaseHas('master_data', ['type' => 'jabatan', 'name' => 'QA Engineer']);
    }

    public function test_import_handles_xlsx_file(): void
    {
        $sa = $this->makeSuperadmin();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Kode Pegawai', 'Nama Lengkap', 'Email', 'Jabatan', 'Divisi', 'Status', 'Tanggal Bergabung'],
            ['NIP-XLS-001', 'Rina Marlina', 'rina@test.local', 'Staff', 'Logistik', 'Karyawan Tetap', '2026-02-10'],
        ], null, 'A1');

        $tmp = tempnam(sys_get_temp_dir(), 'imp') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);
        $spreadsheet->disconnectWorksheets();

        $file = new UploadedFile($tmp, 'karyawan.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan-import.preview'), ['import_file' => $file])
            ->assertOk()
            ->assertSee('Rina Marlina');

        $this->actingAs($sa)
            ->post(route('superadmin.karyawan-import.commit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('karyawans', ['kode_pegawai' => 'NIP-XLS-001', 'divisi' => 'Logistik']);

        @unlink($tmp);
    }

    // ==========================================================
    // DATA MASTER (D13)
    // ==========================================================

    public function test_master_data_page_renders_with_tabs(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.master-data.index'))
            ->assertOk()
            ->assertSee('Data Master Organisasi')
            ->assertSee('Divisi');
    }

    public function test_master_data_crud_lifecycle(): void
    {
        $sa = $this->makeSuperadmin();

        // CREATE
        $this->actingAs($sa)->post(route('superadmin.master-data.store'), [
            'type' => 'divisi',
            'name' => 'Produksi',
            'code' => 'PRD',
            'sort_order' => 1,
        ])->assertSessionHas('success');

        $item = MasterData::where('name', 'Produksi')->first();
        $this->assertNotNull($item);
        $this->assertSame('divisi', $item->type);
        $this->assertTrue($item->is_active);

        // DUPLICATE DITOLAK (case-insensitive)
        $this->actingAs($sa)->post(route('superadmin.master-data.store'), [
            'type' => 'divisi',
            'name' => 'produksi',
        ])->assertSessionHasErrors('name');
        $this->assertSame(1, MasterData::where('type', 'divisi')->count());

        // UPDATE
        $this->actingAs($sa)
            ->put(route('superadmin.master-data.update', $item->id), [
                'name' => 'Produksi Utama',
                'code' => 'PRD1',
                'sort_order' => 5,
                'is_active' => 1,
            ])
            ->assertSessionHas('success');

        $item->refresh();
        $this->assertSame('Produksi Utama', $item->name);
        $this->assertSame('PRD1', $item->code);

        // DELETE
        $this->actingAs($sa)
            ->delete(route('superadmin.master-data.destroy', $item->id))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('master_data', ['id' => $item->id]);

        // Log audit
        $this->assertNotNull(ActivityLog::where('action', 'master.create')->latest('id')->first());
        $this->assertNotNull(ActivityLog::where('action', 'master.delete')->latest('id')->first());
    }

    public function test_master_data_invalid_type_rejected(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.master-data.store'), [
            'type' => 'jenissalah',
            'name' => 'X',
        ])->assertSessionHasErrors('type');
    }

    public function test_master_data_options_feed_karyawan_form(): void
    {
        MasterData::create(['type' => 'divisi', 'name' => 'Marketing', 'is_active' => true]);
        MasterData::create(['type' => 'jabatan', 'name' => 'Staf Promosi', 'is_active' => true]);
        MasterData::create(['type' => 'golongan', 'name' => 'Golongan II', 'is_active' => true]);
        MasterData::create(['type' => 'divisi', 'name' => 'Nonaktif', 'is_active' => false]);

        $this->assertSame(['Marketing'], MasterData::options('divisi'));
        $this->assertSame(['Staf Promosi'], MasterData::options('jabatan'));
        $this->assertSame(['Golongan II'], MasterData::options('golongan'));
    }

    public function test_karyawan_create_form_shows_master_options(): void
    {
        $hr = $this->makeHr();
        MasterData::create(['type' => 'divisi', 'name' => 'Pemasaran', 'is_active' => true]);

        $response = $this->actingAs($hr)->get(route('hr.karyawan.create'));

        $response->assertOk()
            ->assertSee('divisi-options', false)
            ->assertSee('Pemasaran');
    }

    public function test_karyawan_store_accepts_golongan(): void
    {
        $hr = $this->makeHr();

        $this->actingAs($hr)->post(route('hr.karyawan.store'), [
            'kode_pegawai' => 'NIP-GOL-001',
            'email' => 'gol001@test.local',
            'password' => 'passwordkuat123',
            'password_confirmation' => 'passwordkuat123',
            'nama_lengkap' => 'Karyawan Golongan',
            'jabatan' => 'Staff',
            'divisi' => 'Operasional',
            'golongan' => 'Golongan III',
            'status' => 'Karyawan Tetap',
            'tanggal_bergabung' => now()->toDateString(),
        ])->assertRedirect(route('hr.karyawan.index'));

        $this->assertDatabaseHas('karyawans', [
            'kode_pegawai' => 'NIP-GOL-001',
            'golongan' => 'Golongan III',
        ]);
    }

    // ==========================================================
    // KEBIJAKAN CUTI (D14)
    // ==========================================================

    public function test_cuti_policy_page_renders(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->get(route('superadmin.cuti-policies.index'))
            ->assertOk()
            ->assertSee('Kebijakan')
            ->assertSee('12 hari');
    }

    public function test_cuti_policy_crud(): void
    {
        $sa = $this->makeSuperadmin();

        $this->actingAs($sa)->post(route('superadmin.cuti-policies.store'), [
            'tahun' => now()->year,
            'jenis_cuti' => 'Cuti Tahunan',
            'kuota_hari' => 20,
            'keterangan' => 'Kebijakan baru',
        ])->assertSessionHas('success');

        $policy = CutiPolicy::where('kuota_hari', 20)->first();
        $this->assertNotNull($policy);
        $this->assertTrue($policy->is_active);

        // Duplikat ditolak.
        $this->actingAs($sa)->post(route('superadmin.cuti-policies.store'), [
            'tahun' => now()->year,
            'jenis_cuti' => 'Cuti Tahunan',
            'kuota_hari' => 15,
        ])->assertSessionHasErrors('jenis_cuti');

        // Update.
        $this->actingAs($sa)
            ->put(route('superadmin.cuti-policies.update', $policy->id), [
                'jenis_cuti' => 'Cuti Tahunan',
                'kuota_hari' => 25,
                'is_active' => 1,
            ])
            ->assertSessionHas('success');
        $this->assertSame(25, $policy->fresh()->kuota_hari);

        // Delete.
        $this->actingAs($sa)
            ->delete(route('superadmin.cuti-policies.destroy', $policy->id))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('cuti_policies', ['id' => $policy->id]);

        $this->assertNotNull(ActivityLog::where('action', 'cuti.policy_update')->latest('id')->first());
    }

    public function test_cuti_policy_drives_new_quota_row(): void
    {
        CutiPolicy::create([
            'tahun' => now()->year,
            'jenis_cuti' => 'Cuti Tahunan',
            'kuota_hari' => 18,
            'is_active' => true,
        ]);

        $k = $this->makeKaryawan();

        // Halaman dashboard cuti membuat baris kuota dengan jatah dari kebijakan.
        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertOk();

        $kuota = Cuti::kuota()->where('karyawan_id', $k->id)->first();
        $this->assertNotNull($kuota);
        $this->assertSame(18, (int) $kuota->jatah_cuti);
        $this->assertSame(18, (int) $kuota->sisa_cuti);
    }

    public function test_cuti_quota_defaults_to_12_without_policy(): void
    {
        $k = $this->makeKaryawan();

        $this->actingAs($k)->get(route('karyawan.cuti.dashboard'))->assertOk();

        $kuota = Cuti::kuota()->where('karyawan_id', $k->id)->first();
        $this->assertSame(12, (int) $kuota->jatah_cuti);
    }

    public function test_cuti_policy_scope_golongan_matches_employee(): void
    {
        $kGol3 = $this->makeKaryawan(['golongan' => 'Golongan III']);

        CutiPolicy::create([
            'tahun' => now()->year,
            'jenis_cuti' => 'Cuti Tahunan',
            'kuota_hari' => 10,
            'scope_golongan' => 'Golongan I',
            'is_active' => true,
        ]);
        CutiPolicy::create([
            'tahun' => now()->year,
            'jenis_cuti' => 'Cuti Tahunan',
            'kuota_hari' => 22,
            'scope_golongan' => 'Golongan III',
            'is_active' => true,
        ]);

        $this->assertSame(22, CutiPolicy::kuotaFor($kGol3, now()->year));

        // Karyawan tanpa golongan scope → fallback kebijakan umum bila ada.
        $kTanpaGol = $this->makeKaryawan();
        $this->assertSame(12, CutiPolicy::kuotaFor($kTanpaGol, now()->year));
    }

    public function test_inactive_policy_is_ignored(): void
    {
        $k = $this->makeKaryawan();

        CutiPolicy::create([
            'tahun' => now()->year,
            'jenis_cuti' => 'Cuti Tahunan',
            'kuota_hari' => 30,
            'is_active' => false,
        ]);

        $this->assertSame(12, CutiPolicy::kuotaFor($k, now()->year));
    }

    public function test_cuti_approval_records_approver(): void
    {
        $hr = $this->makeHr();
        $k = $this->makeKaryawan();

        // HR membuatkan kuota & pengajuan langsung.
        $kuota = Cuti::create([
            'karyawan_id' => $k->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'jatah_cuti' => 12,
            'sisa_cuti' => 10,
            'cuti_digunakan' => 2,
            'status' => 'approved',
            'tanggal_pengajuan' => now(),
        ]);

        $pengajuan = Cuti::create([
            'karyawan_id' => $k->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'jatah_cuti' => 12,
            'sisa_cuti' => 10,
            'cuti_digunakan' => 2,
            'tanggal_mulai' => now()->addDays(10)->toDateString(),
            'tanggal_selesai' => now()->addDays(11)->toDateString(),
            'keterangan' => 'Keperluan keluarga',
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
        ]);

        $this->actingAs($hr)->post(route('hr.cuti.approve', $pengajuan->id), [
            'status' => 'approved',
            'catatan_hr' => 'OK',
        ])->assertRedirect(route('hr.cuti.index'));

        $pengajuan->refresh();
        $this->assertSame('approved', $pengajuan->status);
        $this->assertSame($hr->id, $pengajuan->approved_by);
        $this->assertNotNull($pengajuan->approved_at);

        // Bulk approve mencatat juga.
        $pengajuan2 = Cuti::create([
            'karyawan_id' => $k->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'jatah_cuti' => 12,
            'sisa_cuti' => 10,
            'cuti_digunakan' => 2,
            'tanggal_mulai' => now()->addDays(20)->toDateString(),
            'tanggal_selesai' => now()->addDays(21)->toDateString(),
            'keterangan' => 'Lainnya',
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
        ]);

        $this->actingAs($hr)->post(route('hr.cuti.bulk-approve'), [
            'ids' => [$pengajuan2->id],
            'target_status' => 'approved',
        ])->assertRedirect(route('hr.cuti.index'));

        $pengajuan2->refresh();
        $this->assertSame($hr->id, $pengajuan2->approved_by);
        $this->assertNotNull($pengajuan2->approved_at);
    }

    // ==========================================================
    // OFFBOARDING (D15)
    // ==========================================================

    public function test_offboarding_page_renders(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)
            ->get(route('superadmin.offboarding.index'))
            ->assertOk()
            ->assertSee('Offboarding Karyawan Terpusat')
            ->assertSee($k->nama_lengkap);
    }

    public function test_offboard_marks_resigned_and_blocks_login(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->assertTrue($k->canLogin());

        $this->actingAs($sa)->post(
            route('superadmin.offboarding.offboard', $k->id),
            ['tanggal_resign' => now()->toDateString(), 'keterangan' => 'Pengunduran diri']
        )->assertSessionHas('success');

        $k->refresh();
        $this->assertTrue($k->is_resigned);
        $this->assertNotNull($k->tanggal_resign);
        $this->assertFalse($k->canLogin());

        // Riwayat tidak hilang (soft path).
        $this->assertDatabaseHas('karyawans', ['id' => $k->id]);

        $log = ActivityLog::where('action', 'karyawan.offboard')->latest('id')->first();
        $this->assertNotNull($log);
    }

    public function test_offboard_validates_date_not_before_join(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan(['tanggal_bergabung' => now()->subYear()->toDateString()]);

        $this->actingAs($sa)->post(
            route('superadmin.offboarding.offboard', $k->id),
            ['tanggal_resign' => now()->subYears(2)->toDateString()]
        )->assertSessionHasErrors('tanggal_resign');

        $this->assertFalse($k->fresh()->is_resigned);
    }

    public function test_reactivate_cancels_resign(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan(['is_resigned' => true, 'tanggal_resign' => now()->toDateString()]);

        $this->actingAs($sa)
            ->post(route('superadmin.offboarding.reactivate', $k->id))
            ->assertSessionHas('success');

        $k->refresh();
        $this->assertFalse($k->is_resigned);
        $this->assertNull($k->tanggal_resign);
        $this->assertTrue($k->canLogin());
    }

    public function test_archive_is_soft_delete_and_restorable(): void
    {
        $sa = $this->makeSuperadmin();
        $k = $this->makeKaryawan();

        $this->actingAs($sa)
            ->post(route('superadmin.offboarding.archive', $k->id))
            ->assertSessionHas('success');

        // Soft delete: baris ada dengan deleted_at, global scope menyembunyikan.
        $this->assertDatabaseHas('karyawans', ['id' => $k->id]);
        $this->assertNull(Karyawan::find($k->id));
        $this->assertNotNull(Karyawan::onlyTrashed()->find($k->id));

        // Pulihkan.
        $this->actingAs($sa)
            ->post(route('superadmin.offboarding.restore', $k->id))
            ->assertSessionHas('success');

        $this->assertNotNull(Karyawan::find($k->id));
        $this->assertNull(Karyawan::onlyTrashed()->find($k->id));

        $logs = ActivityLog::where('action', 'karyawan.delete')->count();
        $this->assertGreaterThanOrEqual(2, $logs);
    }

    public function test_hr_destroy_soft_deletes_not_hard_delete(): void
    {
        $hr = $this->makeHr();
        $k = $this->makeKaryawan();

        $this->actingAs($hr)
            ->delete(route('hr.karyawan.destroy', $k->id))
            ->assertRedirect(route('hr.karyawan.index'));

        $this->assertDatabaseHas('karyawans', ['id' => $k->id]);
        $this->assertNotNull(Karyawan::onlyTrashed()->find($k->id));
        $this->assertNotNull(ActivityLog::where('action', 'karyawan.delete')->latest('id')->first());
    }

    public function test_superadmin_cannot_be_offboarded_or_archived(): void
    {
        $sa = $this->makeSuperadmin();
        $other = $this->makeSuperadmin();

        $this->actingAs($sa)
            ->post(route('superadmin.offboarding.offboard', $other->id), ['tanggal_resign' => now()->toDateString()])
            ->assertSessionHas('error');
        $this->assertFalse($other->fresh()->is_resigned);

        $this->actingAs($sa)
            ->post(route('superadmin.offboarding.archive', $other->id))
            ->assertSessionHas('error');
        $this->assertNull($other->fresh()->deleted_at);
    }
}
