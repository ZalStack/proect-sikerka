<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CutiPolicy;
use Illuminate\Http\Request;

/**
 * Kebijakan & kuota cuti per tahun (Fitur D14).
 *
 * Menggantikan hardcode jatah 12 hari: kuota dibuat per tahun & jenis
 * cuti, dengan cakupan golongan opsional. Berlaku untuk pengajuan baru
 * maupun saat baris kuota karyawan dibuat (CutiController::getOrCreateKuota).
 */
class CutiPolicyController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);

        $policies = CutiPolicy::where('tahun', $tahun)
            ->orderBy('jenis_cuti')
            ->orderBy('scope_golongan')
            ->get();

        $tahunList = CutiPolicy::select('tahun')->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $stats = [
            'aktif' => CutiPolicy::where('tahun', $tahun)->where('is_active', true)->count(),
            'total' => CutiPolicy::where('tahun', $tahun)->count(),
        ];

        return view('superadmin.cuti-policies', compact('policies', 'tahun', 'tahunList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'jenis_cuti' => ['required', 'string', 'max:50'],
            'kuota_hari' => ['required', 'integer', 'min:0', 'max:365'],
            'scope_golongan' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'tahun.required' => 'Tahun kebijakan wajib diisi.',
            'kuota_hari.required' => 'Kuota hari wajib diisi.',
            'kuota_hari.max' => 'Kuota hari tidak boleh melebihi 365.',
        ]);

        $scope = trim((string) ($data['scope_golongan'] ?? '')) ?: null;

        $exists = CutiPolicy::where('tahun', $data['tahun'])
            ->where('jenis_cuti', $data['jenis_cuti'])
            ->where(function ($q) use ($scope) {
                $scope === null
                    ? $q->whereNull('scope_golongan')->orWhere('scope_golongan', '')
                    : $q->where('scope_golongan', $scope);
            })
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['jenis_cuti' => 'Kebijakan "' . $data['jenis_cuti'] . '" untuk tahun ' . $data['tahun'] . ' sudah ada.'])
                ->withInput();
        }

        $policy = CutiPolicy::create($data + [
            'scope_golongan' => $scope,
            'is_active' => true,
        ]);

        $this->logPolicy('create', $policy);

        return redirect()
            ->route('superadmin.cuti-policies.index', ['tahun' => $policy->tahun])
            ->with('success', 'Kebijakan cuti berhasil dibuat. Kuota berlaku untuk pengajuan berikutnya.');
    }

    public function update(Request $request, CutiPolicy $cutiPolicy)
    {
        $data = $request->validate([
            'jenis_cuti' => ['required', 'string', 'max:50'],
            'kuota_hari' => ['required', 'integer', 'min:0', 'max:365'],
            'scope_golongan' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        $scope = trim((string) ($data['scope_golongan'] ?? '')) ?: null;

        $exists = CutiPolicy::where('tahun', $cutiPolicy->tahun)
            ->where('id', '!=', $cutiPolicy->id)
            ->where('jenis_cuti', $data['jenis_cuti'])
            ->where(function ($q) use ($scope) {
                $scope === null
                    ? $q->whereNull('scope_golongan')->orWhere('scope_golongan', '')
                    : $q->where('scope_golongan', $scope);
            })
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['jenis_cuti' => 'Kebijakan "' . $data['jenis_cuti'] . '" tersebut sudah ada untuk tahun ini.'])
                ->withInput();
        }

        $data['scope_golongan'] = $scope;
        $data['is_active'] = $request->boolean('is_active');

        $cutiPolicy->update($data);

        $this->logPolicy('update', $cutiPolicy);

        return redirect()
            ->route('superadmin.cuti-policies.index', ['tahun' => $cutiPolicy->tahun])
            ->with('success', 'Kebijakan cuti berhasil diperbarui.');
    }

    public function destroy(CutiPolicy $cutiPolicy)
    {
        $tahun = $cutiPolicy->tahun;
        $jenis = $cutiPolicy->jenis_cuti;
        $cutiPolicy->delete();

        ActivityLog::record(
            'cuti.policy_update',
            'Kebijakan cuti "' . $jenis . '" tahun ' . $tahun . ' dihapus.',
            ['module' => 'cuti', 'metadata' => ['tahun' => $tahun, 'jenis_cuti' => $jenis]]
        );

        return redirect()
            ->route('superadmin.cuti-policies.index', ['tahun' => $tahun])
            ->with('success', 'Kebijakan cuti dihapus. Kuota karyawan yang sudah terlanjur dibuat tidak berubah.');
    }

    private function logPolicy(string $verb, CutiPolicy $policy): void
    {
        ActivityLog::record(
            'cuti.policy_update',
            'Kebijakan cuti ' . ($verb === 'create' ? 'dibuat' : 'diperbarui')
                . ': ' . $policy->jenis_cuti . ' tahun ' . $policy->tahun
                . ', kuota ' . $policy->kuota_hari . ' hari'
                . ($policy->scope_golongan ? ', golongan ' . $policy->scope_golongan : ' (semua golongan)')
                . ($policy->is_active ? '' : ' [nonaktif]') . '.',
            [
                'module' => 'cuti',
                'metadata' => [
                    'tahun' => $policy->tahun,
                    'jenis_cuti' => $policy->jenis_cuti,
                    'kuota_hari' => $policy->kuota_hari,
                    'scope_golongan' => $policy->scope_golongan,
                    'is_active' => $policy->is_active,
                ],
            ]
        );
    }
}
