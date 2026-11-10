<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FeatureAssignment;
use App\Models\Karyawan;
use App\Models\MasterData;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

/**
 * Penugasan fitur per cakupan (Fitur F22).
 *
 * Satu fitur bisa diberi penugasan per role / divisi / karyawan individual.
 * Prioritas saat menentukan akses: karyawan > divisi > role, dan
 * sakelar global NONAKTIF selalu mengalahkan seluruh penugasan
 * (lihat SystemSetting::isFeatureEnabledFor).
 */
class FeatureAssignmentController extends Controller
{
    /** Role yang tersedia sebagai cakupan. */
    private const ROLES = ['karyawan', 'hr', 'superadmin'];

    public function index(Request $request)
    {
        $features = SystemSetting::AVAILABLE_FEATURES;

        $featureKey = (string) $request->query('feature', array_key_first($features));
        if (!array_key_exists($featureKey, $features)) {
            $featureKey = array_key_first($features);
        }

        $assignments = FeatureAssignment::with('creator')
            ->orderBy('feature_key')
            ->orderBy('scope_type')
            ->orderBy('scope_value')
            ->get();

        $counts = $assignments->groupBy('feature_key')->map(fn ($g) => $g->count());

        // Opsi cakupan untuk formulir.
        $roles = self::ROLES;
        $divisiOptions = collect(MasterData::options('divisi'))
            ->merge(Karyawan::query()->whereNotNull('divisi')->distinct()->pluck('divisi'))
            ->filter()
            ->unique(fn ($v) => mb_strtolower($v))
            ->sort()
            ->values()
            ->all();
        $karyawanOptions = Karyawan::active()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'divisi', 'posisi']);

        $stats = [
            'total' => $assignments->count(),
            'aktif' => $assignments->where('enabled', true)->count(),
            'fitur' => $counts->count(),
        ];

        return view('superadmin.feature-assignments', compact(
            'features',
            'featureKey',
            'assignments',
            'counts',
            'roles',
            'divisiOptions',
            'karyawanOptions',
            'stats'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'feature_key' => ['required', 'string'],
            'scope_type' => ['required', 'in:role,divisi,karyawan'],
            'scope_value' => ['required', 'string', 'max:100'],
            'enabled' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'feature_key.required' => 'Pilih fitur yang akan diberi penugasan.',
            'scope_value.required' => 'Nilai cakupan wajib diisi.',
        ]);

        if (!array_key_exists($data['feature_key'], SystemSetting::AVAILABLE_FEATURES)) {
            return back()->withErrors(['feature_key' => 'Fitur tidak dikenal.'])->withInput();
        }

        // Validasi nilai cakupan sesuai tipenya.
        if ($data['scope_type'] === 'role') {
            if (!in_array($data['scope_value'], self::ROLES, true)) {
                return back()->withErrors(['scope_value' => 'Role "' . $data['scope_value'] . '" tidak dikenal.'])->withInput();
            }
        } elseif ($data['scope_type'] === 'karyawan') {
            if (!Karyawan::whereKey((int) $data['scope_value'])->exists()) {
                return back()->withErrors(['scope_value' => 'Karyawan target tidak ditemukan.'])->withInput();
            }
        } elseif (trim($data['scope_value']) === '') {
            return back()->withErrors(['scope_value' => 'Divisi tidak boleh kosong.'])->withInput();
        }

        $enabled = $request->boolean('enabled', true);

        $assignment = SystemSetting::assignFeature(
            $data['feature_key'],
            $data['scope_type'],
            trim($data['scope_value']),
            $enabled,
            $data['notes'] ?? null,
            Auth()->id()
        );

        $meta = SystemSetting::AVAILABLE_FEATURES[$data['feature_key']];

        ActivityLog::record(
            'feature.assignment',
            'Penugasan fitur "' . $meta['name'] . '" untuk ' . $assignment->scopeTypeLabel()
                . ' di' . ($enabled ? 'aktifkan' : 'nonaktifkan') . '.',
            [
                'module' => 'features',
                'subject_type' => FeatureAssignment::class,
                'subject_id' => $assignment->id,
                'metadata' => [
                    'feature_key' => $data['feature_key'],
                    'scope_type' => $data['scope_type'],
                    'scope_value' => $data['scope_value'],
                    'enabled' => $enabled,
                ],
            ]
        );

        return redirect()
            ->route('superadmin.features.assignments.index', ['feature' => $data['feature_key']])
            ->with('success', 'Penugasan fitur berhasil disimpan. Penugasan berlaku dalam ±1 menit (masa cache).');
    }

    public function destroy(FeatureAssignment $assignment)
    {
        $meta = SystemSetting::AVAILABLE_FEATURES[$assignment->feature_key] ?? ['name' => $assignment->feature_key];
        $label = $assignment->scopeTypeLabel();
        $assignment->delete();

        SystemSetting::forgetFeatureAssignmentCache($assignment->feature_key);

        ActivityLog::record(
            'feature.assignment',
            'Penugasan fitur "' . $meta['name'] . '" untuk ' . $label . ' dihapus.',
            [
                'module' => 'features',
                'metadata' => [
                    'feature_key' => $assignment->feature_key,
                    'scope_type' => $assignment->scope_type,
                    'scope_value' => $assignment->scope_value,
                ],
            ]
        );

        return back()->with('success', 'Penugasan fitur berhasil dihapus.');
    }
}
