<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Cuti;
use App\Models\CutiType;
use App\Models\Karyawan;
use Illuminate\Http\Request;

/**
 * Controller Manajemen Cuti Super Admin:
 * Hub 2 tab:
 * 1. Tab Pengaturan: CRUD jenis cuti master yang aktif & tampil di role karyawan.
 * 2. Tab Pengajuan: Melihat seluruh pengajuan cuti semua jenis dari karyawan serta memprosesnya.
 */
class ManajemenCutiController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pengaturan');
        if (!in_array($tab, ['pengaturan', 'pengajuan'], true)) {
            $tab = 'pengaturan';
        }

        // Pastikan jenis cuti bawaan sudah ter-seed jika tabel masih kosong
        if (CutiType::count() === 0) {
            CutiType::seedDefaults();
        }

        // Data untuk Tab Pengaturan Jenis Cuti
        $cutiTypes = CutiType::ordered()->get();
        $typeStats = [
            'total' => $cutiTypes->count(),
            'active' => $cutiTypes->where('is_active', true)->count(),
            'inactive' => $cutiTypes->where('is_active', false)->count(),
        ];

        // Data untuk Tab Pengajuan Cuti Karyawan
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

        $cuti = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $karyawans = Karyawan::orderBy('nama_lengkap')->get();

        $statistik = [
            'total' => Cuti::pengajuan()->count(),
            'pending' => Cuti::pengajuan()->where('status', 'pending')->count(),
            'approved' => Cuti::pengajuan()->where('status', 'approved')->count(),
            'rejected' => Cuti::pengajuan()->where('status', 'rejected')->count(),
        ];

        return view('superadmin.manajemen-cuti', compact(
            'tab',
            'cutiTypes',
            'typeStats',
            'cuti',
            'karyawans',
            'statistik'
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
}
