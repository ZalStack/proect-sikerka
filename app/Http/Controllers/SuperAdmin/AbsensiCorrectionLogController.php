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
}
