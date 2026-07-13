<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CutiController extends Controller
{
    // Dashboard Cuti untuk HR
    public function index(Request $request)
    {
        $query = Cuti::with('karyawan');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter karyawan
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $cuti = $query->orderBy('created_at', 'desc')->paginate(10);
        $karyawans = Karyawan::orderBy('nama_lengkap')->get();

        // Statistik
        $statistik = [
            'total' => Cuti::count(),
            'pending' => Cuti::where('status', 'pending')->count(),
            'approved' => Cuti::where('status', 'approved')->count(),
            'rejected' => Cuti::where('status', 'rejected')->count(),
        ];

        return view('hr.cuti.index', compact('cuti', 'karyawans', 'statistik'));
    }

    // Dashboard Cuti untuk Karyawan
    public function dashboard()
    {
        $user = Auth::user();

        // Ambil data cuti karyawan
        $cuti = Cuti::where('karyawan_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung sisa cuti
        $cutiTahunan = Cuti::where('karyawan_id', $user->id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->first();

        if (!$cutiTahunan) {
            // Buat data cuti baru jika belum ada
            $cutiTahunan = Cuti::create([
                'karyawan_id' => $user->id,
                'jenis_cuti' => 'Cuti Tahunan',
                'jatah_cuti' => 12,
                'sisa_cuti' => 12,
                'cuti_digunakan' => 0,
                'status' => 'approved',
                'tanggal_pengajuan' => Carbon::now(),
            ]);
        }

        return view('karyawan.cuti.dashboard', compact('cuti', 'cutiTahunan'));
    }

    // Form Pengajuan Cuti (Karyawan)
    public function create()
    {
        return view('karyawan.cuti.create');
    }

    // Store Pengajuan Cuti
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
        $durasi = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        // Cek sisa cuti
        $cutiTahunan = Cuti::where('karyawan_id', $user->id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->first();

        if (!$cutiTahunan) {
            $cutiTahunan = Cuti::create([
                'karyawan_id' => $user->id,
                'jenis_cuti' => 'Cuti Tahunan',
                'jatah_cuti' => 12,
                'sisa_cuti' => 12,
                'cuti_digunakan' => 0,
                'status' => 'approved',
                'tanggal_pengajuan' => Carbon::now(),
            ]);
        }

        if ($cutiTahunan->sisa_cuti < $durasi) {
            return back()->with('error', 'Sisa cuti Anda tidak mencukupi. Sisa cuti: ' . $cutiTahunan->sisa_cuti . ' hari');
        }

        // Simpan pengajuan cuti
        $cuti = Cuti::create([
            'karyawan_id' => $user->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'jatah_cuti' => $cutiTahunan->jatah_cuti,
            'sisa_cuti' => $cutiTahunan->sisa_cuti - $durasi,
            'cuti_digunakan' => $cutiTahunan->cuti_digunakan + $durasi,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
            'status' => 'pending',
            'tanggal_pengajuan' => Carbon::now(),
        ]);

        // Update sisa cuti di data cuti tahunan
        $cutiTahunan->update([
            'sisa_cuti' => $cutiTahunan->sisa_cuti - $durasi,
            'cuti_digunakan' => $cutiTahunan->cuti_digunakan + $durasi,
        ]);

        return redirect()->route('karyawan.cuti.dashboard')
            ->with('success', 'Pengajuan cuti berhasil dikirim. Menunggu persetujuan HR.');
    }

    // Detail Cuti
    public function show($id)
    {
        $cuti = Cuti::with('karyawan')->findOrFail($id);
        return view('hr.cuti.show', compact('cuti'));
    }

    // Approve Cuti (HR)
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'catatan_hr' => 'nullable|string',
        ]);

        $cuti = Cuti::findOrFail($id);
        $cuti->status = $request->status;
        $cuti->catatan_hr = $request->catatan_hr;
        $cuti->save();

        // Jika ditolak, kembalikan sisa cuti
        if ($request->status === 'rejected') {
            $cutiTahunan = Cuti::where('karyawan_id', $cuti->karyawan_id)
                ->where('jenis_cuti', 'Cuti Tahunan')
                ->first();

            if ($cutiTahunan) {
                $cutiTahunan->update([
                    'sisa_cuti' => $cutiTahunan->sisa_cuti + $cuti->durasi,
                    'cuti_digunakan' => $cutiTahunan->cuti_digunakan - $cuti->durasi,
                ]);
            }
        }

        $statusLabel = $request->status === 'approved' ? 'Disetujui' : ($request->status === 'rejected' ? 'Ditolak' : 'Menunggu');

        return redirect()->route('hr.cuti.index')
            ->with('success', "Pengajuan cuti berhasil {$statusLabel}");
    }

    // Bulk Approve (HR)
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:cuti,id',
            'target_status' => 'required|in:approved,rejected',
            'catatan_hr' => 'nullable|string',
        ]);

        $ids = $request->input('ids');
        $targetStatus = $request->input('target_status');

        foreach ($ids as $id) {
            $cuti = Cuti::find($id);
            if ($cuti && $cuti->status === 'pending') {
                $cuti->status = $targetStatus;
                $cuti->catatan_hr = $request->input('catatan_hr');
                $cuti->save();

                // Jika ditolak, kembalikan sisa cuti
                if ($targetStatus === 'rejected') {
                    $cutiTahunan = Cuti::where('karyawan_id', $cuti->karyawan_id)
                        ->where('jenis_cuti', 'Cuti Tahunan')
                        ->first();

                    if ($cutiTahunan) {
                        $cutiTahunan->update([
                            'sisa_cuti' => $cutiTahunan->sisa_cuti + $cuti->durasi,
                            'cuti_digunakan' => $cutiTahunan->cuti_digunakan - $cuti->durasi,
                        ]);
                    }
                }
            }
        }

        $statusLabel = $targetStatus === 'approved' ? 'Disetujui' : 'Ditolak';

        return redirect()->route('hr.cuti.index')
            ->with('success', "{$ids->count()} pengajuan cuti berhasil {$statusLabel}");
    }
}
