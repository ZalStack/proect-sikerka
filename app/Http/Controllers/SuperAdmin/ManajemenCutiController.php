<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Cuti;
use App\Models\CutiType;
use App\Models\Karyawan;
use App\Services\AbsensiSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller Manajemen Cuti Super Admin:
 * Hub 3 tab:
 * 1. Tab Pengaturan: CRUD jenis cuti master yang aktif & tampil di role karyawan.
 * 2. Tab Pengajuan: Melihat & mengelola jadwal/pengajuan cuti seluruh karyawan (tambah/edit/hapus/setujui/tolak).
 * 3. Tab Kuota: Melihat & menyesuaikan sisa kuota cuti karyawan yang tersedia (tambah/kurang/atur kuota).
 */
class ManajemenCutiController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pengaturan');
        if (!in_array($tab, ['pengaturan', 'pengajuan', 'kuota'], true)) {
            $tab = 'pengaturan';
        }

        // Pastikan jenis cuti bawaan sudah ter-seed jika tabel masih kosong
        if (CutiType::count() === 0) {
            CutiType::seedDefaults();
        }

        // Data untuk Tab Pengaturan Jenis Cuti
        $cutiTypes = CutiType::ordered()->get();
        $activeTypes = CutiType::active()->ordered()->get();
        $typeStats = [
            'total' => $cutiTypes->count(),
            'active' => $cutiTypes->where('is_active', true)->count(),
            'inactive' => $cutiTypes->where('is_active', false)->count(),
        ];

        // Data untuk Tab Pengajuan & Jadwal Cuti Karyawan
        $query = Cuti::pengajuan()->with('karyawan');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        if ($request->filled('jenis_cuti')) {
            $query->where('jenis_cuti', $request->jenis_cuti);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('karyawan', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('kode_pegawai', 'like', "%{$search}%");
            });
        }

        $cuti = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $karyawans = Karyawan::orderBy('nama_lengkap')->get();

        $statistik = [
            'total' => Cuti::pengajuan()->count(),
            'pending' => Cuti::pengajuan()->where('status', 'pending')->count(),
            'approved' => Cuti::pengajuan()->where('status', 'approved')->count(),
            'rejected' => Cuti::pengajuan()->where('status', 'rejected')->count(),
        ];

        // Data untuk Tab Kuota Cuti Karyawan
        $kuotaQuery = Cuti::kuota()->with('karyawan');

        if ($request->filled('kuota_karyawan_id')) {
            $kuotaQuery->where('karyawan_id', $request->kuota_karyawan_id);
        }

        if ($request->filled('kuota_jenis_cuti')) {
            $kuotaQuery->where('jenis_cuti', $request->kuota_jenis_cuti);
        }

        if ($request->filled('kuota_search')) {
            $kSearch = trim($request->kuota_search);
            $kuotaQuery->whereHas('karyawan', function ($q) use ($kSearch) {
                $q->where('nama_lengkap', 'like', "%{$kSearch}%")
                  ->orWhere('kode_pegawai', 'like', "%{$kSearch}%");
            });
        }

        $kuotaList = $kuotaQuery->orderBy('karyawan_id')->paginate(15, ['*'], 'kuota_page')->withQueryString();

        $kuotaStats = [
            'total_records' => Cuti::kuota()->count(),
            'total_jatah' => (int) Cuti::kuota()->sum('jatah_cuti'),
            'total_digunakan' => (int) Cuti::kuota()->sum('cuti_digunakan'),
            'total_sisa' => (int) Cuti::kuota()->sum('sisa_cuti'),
        ];

        return view('superadmin.manajemen-cuti', compact(
            'tab',
            'cutiTypes',
            'activeTypes',
            'typeStats',
            'cuti',
            'karyawans',
            'statistik',
            'kuotaList',
            'kuotaStats'
        ));
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kuota_hari' => ['required', 'integer', 'min:0', 'max:365'],
            'scope_golongan' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'nama.required' => 'Nama jenis cuti wajib diisi.',
            'kuota_hari.required' => 'Kuota hari wajib diisi.',
            'kuota_hari.min' => 'Kuota hari minimal 0 hari.',
            'kuota_hari.max' => 'Kuota hari maksimal 365 hari.',
        ]);

        $namaTrimmed = trim($data['nama']);

        $exists = CutiType::whereRaw('lower(nama) = ?', [mb_strtolower($namaTrimmed)])->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['nama' => 'Jenis cuti "' . $namaTrimmed . '" sudah ada.']);
        }

        $type = CutiType::create([
            'nama' => $namaTrimmed,
            'kuota_hari' => (int) $data['kuota_hari'],
            'scope_golongan' => trim((string) ($data['scope_golongan'] ?? '')) ?: null,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'is_active' => true,
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        ActivityLog::record(
            'cuti.type_create',
            'Jenis cuti "' . $type->nama . '" ditambahkan dengan kuota ' . $type->kuota_hari . ' hari.',
            [
                'module' => 'cuti',
                'subject_type' => CutiType::class,
                'subject_id' => $type->id,
                'metadata' => [
                    'nama' => $type->nama,
                    'kuota_hari' => $type->kuota_hari,
                    'scope_golongan' => $type->scope_golongan,
                    'sort_order' => $type->sort_order,
                ],
            ]
        );

        return redirect()
            ->route('superadmin.manajemen-cuti.index', ['tab' => 'pengaturan'])
            ->with('success', 'Jenis cuti "' . $type->nama . '" berhasil ditambahkan.');
    }

    public function updateType(Request $request, CutiType $cutiType)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kuota_hari' => ['required', 'integer', 'min:0', 'max:365'],
            'scope_golongan' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'nama.required' => 'Nama jenis cuti wajib diisi.',
            'kuota_hari.required' => 'Kuota hari wajib diisi.',
        ]);

        $namaTrimmed = trim($data['nama']);

        $exists = CutiType::where('id', '!=', $cutiType->id)
            ->whereRaw('lower(nama) = ?', [mb_strtolower($namaTrimmed)])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['nama' => 'Jenis cuti "' . $namaTrimmed . '" sudah digunakan jenis lain.']);
        }

        $oldNama = $cutiType->nama;
        $isActive = $request->boolean('is_active');

        $cutiType->update([
            'nama' => $namaTrimmed,
            'kuota_hari' => (int) $data['kuota_hari'],
            'scope_golongan' => trim((string) ($data['scope_golongan'] ?? '')) ?: null,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : $cutiType->sort_order,
            'is_active' => $isActive,
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        ActivityLog::record(
            'cuti.type_update',
            'Jenis cuti "' . $oldNama . '" diperbarui'
                . ($oldNama !== $cutiType->nama ? ' menjadi "' . $cutiType->nama . '"' : '')
                . ($cutiType->is_active ? '' : ' (dinonaktifkan)') . '.',
            [
                'module' => 'cuti',
                'subject_type' => CutiType::class,
                'subject_id' => $cutiType->id,
                'metadata' => [
                    'nama' => $cutiType->nama,
                    'kuota_hari' => $cutiType->kuota_hari,
                    'is_active' => $cutiType->is_active,
                ],
            ]
        );

        return redirect()
            ->route('superadmin.manajemen-cuti.index', ['tab' => 'pengaturan'])
            ->with('success', 'Jenis cuti "' . $cutiType->nama . '" berhasil diperbarui.');
    }

    public function destroyType(CutiType $cutiType)
    {
        if ($cutiType->isUsed()) {
            return back()->with('error', 'Jenis cuti "' . $cutiType->nama . '" tidak dapat dihapus karena sudah pernah digunakan dalam pengajuan karyawan. Anda dapat menonaktifkannya melalui tombol edit.');
        }

        $nama = $cutiType->nama;
        $cutiType->delete();

        ActivityLog::record(
            'cuti.type_delete',
            'Jenis cuti "' . $nama . '" dihapus.',
            [
                'module' => 'cuti',
                'metadata' => ['nama' => $nama],
            ]
        );

        return redirect()
            ->route('superadmin.manajemen-cuti.index', ['tab' => 'pengaturan'])
            ->with('success', 'Jenis cuti "' . $nama . '" berhasil dihapus.');
    }

    /**
     * Tambah Jadwal Cuti Karyawan langsung oleh Super Admin.
     */
    public function storeJadwal(Request $request)
    {
        $request->validate([
            'karyawan_id' => ['required', 'exists:karyawans,id'],
            'jenis_cuti' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['required', 'string', 'max:500'],
            'status' => ['required', 'in:pending,approved'],
        ], [
            'karyawan_id.required' => 'Pilih karyawan terlebih dahulu.',
            'jenis_cuti.required' => 'Pilih jenis cuti.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'keterangan.required' => 'Keterangan cuti wajib diisi.',
            'status.required' => 'Status cuti wajib dipilih.',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
        $durasi = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        if ($this->adaBentrokTanggal((int) $request->karyawan_id, $tanggalMulai, $tanggalSelesai)) {
            return back()->withInput()->with('error', 'Karyawan sudah memiliki jadwal cuti pada rentang tanggal tersebut.');
        }

        $kuota = Cuti::getOrCreateKuota((int) $request->karyawan_id, $request->jenis_cuti);
        $karyawan = Karyawan::findOrFail($request->karyawan_id);

        if ($kuota->sisa_cuti < $durasi) {
            return back()->withInput()->with('error', "Sisa kuota {$request->jenis_cuti} untuk {$karyawan->nama_lengkap} tidak mencukupi (Tersedia: {$kuota->sisa_cuti} hari, Dibutuhkan: {$durasi} hari). Anda dapat menambahkan kuota cuti terlebih dahulu di tab Kuota Cuti.");
        }

        DB::transaction(function () use ($request, $durasi, $kuota, $karyawan) {
            $kuota->update([
                'sisa_cuti' => $kuota->sisa_cuti - $durasi,
                'cuti_digunakan' => $kuota->cuti_digunakan + $durasi,
            ]);

            $cuti = Cuti::create([
                'karyawan_id' => $request->karyawan_id,
                'jenis_cuti' => $request->jenis_cuti,
                'jatah_cuti' => $kuota->jatah_cuti,
                'sisa_cuti' => $kuota->sisa_cuti,
                'cuti_digunakan' => $kuota->cuti_digunakan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
                'tanggal_pengajuan' => now(),
                'approved_by' => $request->status === 'approved' ? Auth::id() : null,
                'approved_at' => $request->status === 'approved' ? now() : null,
            ]);

            app(AbsensiSyncService::class)->syncCuti($cuti);

            ActivityLog::record(
                'cuti.jadwal_create',
                "Super Admin menambahkan jadwal cuti {$cuti->jenis_cuti} untuk {$karyawan->nama_lengkap} ({$cuti->durasi} hari: " . Carbon::parse($cuti->tanggal_mulai)->format('d/m/Y') . " s/d " . Carbon::parse($cuti->tanggal_selesai)->format('d/m/Y') . ").",
                [
                    'module' => 'cuti',
                    'subject_type' => Cuti::class,
                    'subject_id' => $cuti->id,
                    'metadata' => [
                        'karyawan_id' => $cuti->karyawan_id,
                        'durasi' => $cuti->durasi,
                        'status' => $cuti->status,
                    ],
                ]
            );
        });

        return redirect()->route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan'])
            ->with('success', 'Jadwal cuti karyawan berhasil ditambahkan.');
    }

    /**
     * Edit Jadwal Cuti Karyawan (tambah atau kurangi durasi jadwal cuti, ubah status/keterangan).
     */
    public function updateJadwal(Request $request, Cuti $cuti)
    {
        $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['required', 'string', 'max:500'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'catatan_hr' => ['nullable', 'string', 'max:500'],
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'keterangan.required' => 'Keterangan cuti wajib diisi.',
            'status.required' => 'Status cuti wajib dipilih.',
        ]);

        $tanggalMulaiBaru = Carbon::parse($request->tanggal_mulai);
        $tanggalSelesaiBaru = Carbon::parse($request->tanggal_selesai);
        $durasiBaru = $tanggalMulaiBaru->diffInDays($tanggalSelesaiBaru) + 1;

        if ($this->adaBentrokTanggal($cuti->karyawan_id, $tanggalMulaiBaru, $tanggalSelesaiBaru, $cuti->id)) {
            return back()->withInput()->with('error', 'Karyawan sudah memiliki jadwal cuti lain pada rentang tanggal tersebut.');
        }

        $kuota = Cuti::getOrCreateKuota($cuti->karyawan_id, $cuti->jenis_cuti);
        $durasiLama = $cuti->durasi;
        $oldStatus = $cuti->status;
        $newStatus = $request->status;

        $selisih = 0;
        if (in_array($oldStatus, ['pending', 'approved'], true) && $newStatus === 'rejected') {
            $selisih = -$durasiLama;
        } elseif ($oldStatus === 'rejected' && in_array($newStatus, ['pending', 'approved'], true)) {
            $selisih = $durasiBaru;
        } elseif (in_array($oldStatus, ['pending', 'approved'], true) && in_array($newStatus, ['pending', 'approved'], true)) {
            $selisih = $durasiBaru - $durasiLama;
        }

        if ($selisih > 0 && $kuota->sisa_cuti < $selisih) {
            return back()->withInput()->with('error', "Sisa cuti tidak mencukupi untuk penambahan durasi jadwal cuti (Sisa tersedia: {$kuota->sisa_cuti} hari, Tambahan hari: {$selisih} hari).");
        }

        DB::transaction(function () use ($cuti, $kuota, $request, $selisih, $newStatus) {
            if ($selisih !== 0) {
                $kuota->update([
                    'sisa_cuti' => max(0, $kuota->sisa_cuti - $selisih),
                    'cuti_digunakan' => max(0, $kuota->cuti_digunakan + $selisih),
                ]);
            }

            // Bersihkan absensi periode lama
            app(AbsensiSyncService::class)->removeCuti($cuti);

            $cuti->update([
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'keterangan' => $request->keterangan,
                'status' => $newStatus,
                'catatan_hr' => $request->catatan_hr,
                'sisa_cuti' => $kuota->sisa_cuti,
                'cuti_digunakan' => $kuota->cuti_digunakan,
                'approved_by' => $newStatus === 'approved' ? Auth::id() : ($newStatus === 'rejected' ? null : $cuti->approved_by),
                'approved_at' => $newStatus === 'approved' ? now() : ($newStatus === 'rejected' ? null : $cuti->approved_at),
            ]);

            if ($newStatus !== 'rejected') {
                app(AbsensiSyncService::class)->syncCuti($cuti);
            }

            $namaKaryawan = $cuti->karyawan->nama_lengkap ?? 'Karyawan';
            ActivityLog::record(
                'cuti.jadwal_update',
                "Super Admin memperbarui jadwal cuti {$cuti->jenis_cuti} untuk {$namaKaryawan} (durasi: {$cuti->durasi} hari, status: {$cuti->status}).",
                [
                    'module' => 'cuti',
                    'subject_type' => Cuti::class,
                    'subject_id' => $cuti->id,
                ]
            );
        });

        return redirect()->route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan'])
            ->with('success', 'Jadwal cuti karyawan berhasil diperbarui.');
    }

    /**
     * Hapus / Batalkan Jadwal Cuti Karyawan.
     */
    public function destroyJadwal(Cuti $cuti)
    {
        DB::transaction(function () use ($cuti) {
            if (in_array($cuti->status, ['pending', 'approved'], true)) {
                $kuota = Cuti::findKuota($cuti->karyawan_id, $cuti->jenis_cuti);
                if ($kuota) {
                    $kuota->update([
                        'sisa_cuti' => $kuota->sisa_cuti + $cuti->durasi,
                        'cuti_digunakan' => max(0, $kuota->cuti_digunakan - $cuti->durasi),
                    ]);
                }
            }

            app(AbsensiSyncService::class)->removeCuti($cuti);

            $namaKaryawan = $cuti->karyawan->nama_lengkap ?? 'Karyawan';
            $jenisCuti = $cuti->jenis_cuti;
            $durasi = $cuti->durasi;
            $cuti->delete();

            ActivityLog::record(
                'cuti.jadwal_delete',
                "Super Admin menghapus jadwal cuti {$jenisCuti} ({$durasi} hari) untuk {$namaKaryawan}.",
                ['module' => 'cuti']
            );
        });

        return redirect()->route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan'])
            ->with('success', 'Jadwal cuti berhasil dihapus dan sisa cuti telah dikembalikan.');
    }

    /**
     * Sesuaikan Kuota Cuti Karyawan (Tambah, Kurangi, atau Set Saldo Cuti Tersedia).
     */
    public function adjustKuota(Request $request)
    {
        $request->validate([
            'karyawan_id' => ['required', 'exists:karyawans,id'],
            'jenis_cuti' => ['required', 'string'],
            'action_type' => ['required', 'in:tambah,kurang,set'],
            'jumlah_hari' => ['required_if:action_type,tambah,kurang', 'nullable', 'integer', 'min:1', 'max:365'],
            'jatah_baru' => ['required_if:action_type,set', 'nullable', 'integer', 'min:0', 'max:365'],
            'sisa_baru' => ['required_if:action_type,set', 'nullable', 'integer', 'min:0', 'max:365'],
            'alasan' => ['required', 'string', 'max:500'],
        ], [
            'karyawan_id.required' => 'Pilih karyawan terlebih dahulu.',
            'jenis_cuti.required' => 'Pilih jenis cuti.',
            'action_type.required' => 'Pilih jenis aksi penyesuaian kuota.',
            'jumlah_hari.required_if' => 'Jumlah hari wajib diisi.',
            'jatah_baru.required_if' => 'Jatah kuota baru wajib diisi.',
            'sisa_baru.required_if' => 'Sisa kuota baru wajib diisi.',
            'alasan.required' => 'Alasan penyesuaian kuota wajib diisi.',
        ]);

        $kuota = Cuti::getOrCreateKuota((int) $request->karyawan_id, $request->jenis_cuti);
        $karyawan = Karyawan::findOrFail($request->karyawan_id);

        $sisaLama = $kuota->sisa_cuti;
        $jatahLama = $kuota->jatah_cuti;

        if ($request->action_type === 'tambah') {
            $jumlah = (int) $request->jumlah_hari;
            $kuota->jatah_cuti += $jumlah;
            $kuota->sisa_cuti += $jumlah;
            $pesanLog = "Super Admin MENAMBAH kuota cuti {$request->jenis_cuti} untuk {$karyawan->nama_lengkap} sebanyak +{$jumlah} hari (Sisa: {$sisaLama} → {$kuota->sisa_cuti} hari). Alasan: {$request->alasan}";
        } elseif ($request->action_type === 'kurang') {
            $jumlah = (int) $request->jumlah_hari;
            $kuota->sisa_cuti = max(0, $kuota->sisa_cuti - $jumlah);
            $kuota->jatah_cuti = max(0, $kuota->jatah_cuti - $jumlah);
            $pesanLog = "Super Admin MENGURANGI kuota cuti {$request->jenis_cuti} untuk {$karyawan->nama_lengkap} sebanyak -{$jumlah} hari (Sisa: {$sisaLama} → {$kuota->sisa_cuti} hari). Alasan: {$request->alasan}";
        } else {
            $kuota->jatah_cuti = (int) $request->jatah_baru;
            $kuota->sisa_cuti = (int) $request->sisa_baru;
            $kuota->cuti_digunakan = max(0, $kuota->jatah_cuti - $kuota->sisa_cuti);
            $pesanLog = "Super Admin MENGATUR MANUAL kuota cuti {$request->jenis_cuti} untuk {$karyawan->nama_lengkap} (Jatah: {$kuota->jatah_cuti}, Sisa: {$kuota->sisa_cuti}). Alasan: {$request->alasan}";
        }

        $kuota->save();

        ActivityLog::record('cuti.kuota_adjust', $pesanLog, [
            'module' => 'cuti',
            'subject_type' => Cuti::class,
            'subject_id' => $kuota->id,
            'metadata' => [
                'karyawan_id' => $karyawan->id,
                'jenis_cuti' => $request->jenis_cuti,
                'action_type' => $request->action_type,
                'sisa_sebelum' => $sisaLama,
                'sisa_sesudah' => $kuota->sisa_cuti,
                'alasan' => $request->alasan,
            ],
        ]);

        return redirect()->route('superadmin.manajemen-cuti.index', ['tab' => 'kuota'])
            ->with('success', 'Kuota cuti karyawan berhasil disesuaikan.');
    }

    private function adaBentrokTanggal(int $karyawanId, Carbon $mulai, Carbon $selesai, ?int $excludeId = null): bool
    {
        $query = Cuti::pengajuan()
            ->where('karyawan_id', $karyawanId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('tanggal_mulai', '<=', $selesai->format('Y-m-d'))
            ->where('tanggal_selesai', '>=', $mulai->format('Y-m-d'));

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
