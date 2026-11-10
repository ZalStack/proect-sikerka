<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MasterData;
use Illuminate\Http\Request;

/**
 * CRUD data master divisi / jabatan / golongan (Fitur D13).
 *
 * Data master menjadi sumber dropdown pada form karyawan sehingga entri
 * data konsisten (tidak ada "HRD" vs "Hrd" vs "HR Dept").
 */
class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'divisi');
        if (!array_key_exists($type, MasterData::TYPES)) {
            $type = 'divisi';
        }

        $items = MasterData::where('type', $type)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $counts = MasterData::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('superadmin.master-data', compact('type', 'items', 'counts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:divisi,jabatan,golongan'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'type.in' => 'Tipe data master tidak dikenal.',
            'name.required' => 'Nama data master wajib diisi.',
        ]);

        $exists = MasterData::where('type', $data['type'])
            ->whereRaw('lower(name) = ?', [mb_strtolower($data['name'])])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['name' => 'Data "' . $data['name'] . '" sudah ada pada tipe ini.'])
                ->withInput();
        }

        MasterData::create($data + ['is_active' => true, 'sort_order' => $data['sort_order'] ?? 0]);

        ActivityLog::record(
            'master.create',
            'Data master ' . MasterData::typeLabel($data['type']) . ' "' . $data['name'] . '" ditambahkan.',
            ['module' => 'system']
        );

        return redirect()
            ->route('superadmin.master-data.index', ['type' => $data['type']])
            ->with('success', 'Data master berhasil ditambahkan.');
    }

    public function update(Request $request, MasterData $masterData)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $exists = MasterData::where('type', $masterData->type)
            ->where('id', '!=', $masterData->id)
            ->whereRaw('lower(name) = ?', [mb_strtolower($data['name'])])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['name' => 'Data "' . $data['name'] . '" sudah ada pada tipe ini.'])
                ->withInput();
        }

        $old = $masterData->name;
        $masterData->fill($data);
        $masterData->is_active = $request->boolean('is_active');
        $masterData->sort_order = $data['sort_order'] ?? $masterData->sort_order;
        $masterData->save();

        ActivityLog::record(
            'master.update',
            'Data master ' . MasterData::typeLabel($masterData->type) . ' "' . $old . '" diperbarui'
                . ($old !== $masterData->name ? ' menjadi "' . $masterData->name . '"' : '')
                . ($masterData->is_active ? '' : ' (dinonaktifkan)') . '.',
            ['module' => 'system']
        );

        return redirect()
            ->route('superadmin.master-data.index', ['type' => $masterData->type])
            ->with('success', 'Data master berhasil diperbarui.');
    }

    public function destroy(MasterData $masterData)
    {
        $type = $masterData->type;
        $name = $masterData->name;
        $masterData->delete();

        ActivityLog::record(
            'master.delete',
            'Data master ' . MasterData::typeLabel($type) . ' "' . $name . '" dihapus.',
            ['module' => 'system']
        );

        return redirect()
            ->route('superadmin.master-data.index', ['type' => $type])
            ->with('success', 'Data master berhasil dihapus. Data karyawan lama tidak ikut berubah.');
    }
}
