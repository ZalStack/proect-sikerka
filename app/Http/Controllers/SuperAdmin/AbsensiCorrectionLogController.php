<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiCorrectionLog;
use Illuminate\Http\Request;

class AbsensiCorrectionLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $action = $request->input('action');
        $operator = $request->input('operator');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = AbsensiCorrectionLog::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('karyawan_nama', 'like', "%{$search}%")
                  ->orWhere('karyawan_kode', 'like', "%{$search}%")
                  ->orWhere('operator_name', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($action) {
            $query->where('action', $action);
        }

        if ($operator) {
            $query->where('operator_name', $operator);
        }

        if ($from) {
            $query->whereDate('tanggal_presensi', '>=', $from);
        }

        if ($to) {
            $query->whereDate('tanggal_presensi', '<=', $to);
        }

        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'total' => AbsensiCorrectionLog::count(),
            'today' => AbsensiCorrectionLog::whereDate('created_at', today())->count(),
            'unique_karyawan' => AbsensiCorrectionLog::distinct('karyawan_id')->count('karyawan_id'),
            'operators_count' => AbsensiCorrectionLog::distinct('operator_name')->count('operator_name'),
        ];

        $operatorList = AbsensiCorrectionLog::distinct()->pluck('operator_name')->filter()->values();

        return view('superadmin.secret-logs.absensi-correction', compact('logs', 'stats', 'operatorList', 'search', 'action', 'operator', 'from', 'to'));
    }

    /**
     * Hapus satu data riwayat log koreksi
     */
    public function destroy($id)
    {
        $log = AbsensiCorrectionLog::findOrFail($id);
        $log->delete();

        return redirect()->back()->with('success', 'Catatan riwayat koreksi presensi berhasil dihapus.');
    }

    /**
     * Hapus riwayat log terpilih secara massal
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada catatan yang dipilih untuk dihapus.');
        }

        $count = AbsensiCorrectionLog::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', "Sebanyak {$count} catatan riwayat koreksi presensi berhasil dihapus.");
    }

    /**
     * Kosongkan seluruh riwayat log koreksi presensi
     */
    public function clearAll()
    {
        $total = AbsensiCorrectionLog::count();
        if ($total === 0) {
            return redirect()->back()->with('info', 'Riwayat log koreksi presensi sudah kosong.');
        }

        AbsensiCorrectionLog::truncate();

        return redirect()->back()->with('success', "Seluruh riwayat catatan koreksi presensi ({$total} catatan) berhasil dikosongkan.");
    }
}
