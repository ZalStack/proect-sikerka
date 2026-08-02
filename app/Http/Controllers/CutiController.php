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
        $query = Cuti::with('karyawan')->where('jenis_cuti', 'Cuti Tahunan');

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
            'total' => Cuti::where('jenis_cuti', 'Cuti Tahunan')->count(),
            'pending' => Cuti::where('jenis_cuti', 'Cuti Tahunan')->where('status', 'pending')->count(),
            'approved' => Cuti::where('jenis_cuti', 'Cuti Tahunan')->where('status', 'approved')->count(),
            'rejected' => Cuti::where('jenis_cuti', 'Cuti Tahunan')->where('status', 'rejected')->count(),
        ];

        return view('hr.cuti.index', compact('cuti', 'karyawans', 'statistik'));
    }

    // Dashboard Cuti untuk Karyawan
    public function dashboard()
    {
        $user = Auth::user();

        // Ambil data cuti karyawan (hanya cuti tahunan yang sudah diajukan)
        $cuti = Cuti::where('karyawan_id', $user->id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung sisa cuti - ambil data cuti tahunan yang sudah ada
        $cutiTahunan = Cuti::where('karyawan_id', $user->id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->where('status', 'approved')
            ->first();

        // Jika tidak ada data cuti tahunan, buat data default tapi hanya untuk ditampilkan
        // Jangan simpan ke database
        if (!$cutiTahunan) {
            // Buat object temporary untuk ditampilkan
            $cutiTahunan = new \stdClass();
            $cutiTahunan->jatah_cuti = 12;
            $cutiTahunan->sisa_cuti = 12;
            $cutiTahunan->cuti_digunakan = 0;
        }

        return view('karyawan.cuti.dashboard', compact('cuti', 'cutiTahunan'));
    }

    // Form Pengajuan Cuti (Karyawan)
    public function create()
    {
        $user = Auth::user();

        // Cek apakah karyawan sudah memiliki data cuti tahunan yang disetujui
        $cutiTahunan = Cuti::where('karyawan_id', $user->id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->where('status', 'approved')
            ->first();

        // Jika belum ada, buat data awal
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

        return view('karyawan.cuti.create', compact('cutiTahunan'));
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
            ->where('status', 'approved')
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

    // Form Edit Cuti untuk Karyawan (hanya yang status pending)
    public function edit($id)
    {
        $user = Auth::user();
        $cuti = Cuti::where('id', $id)
            ->where('karyawan_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        return view('karyawan.cuti.edit', compact('cuti'));
    }

    // Update Cuti untuk Karyawan (hanya yang status pending)
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Cari data cuti yang akan diupdate
        $cuti = Cuti::where('id', $id)
            ->where('karyawan_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
        ]);

        $tanggalMulaiBaru = Carbon::parse($request->tanggal_mulai);
        $tanggalSelesaiBaru = Carbon::parse($request->tanggal_selesai);
        $durasiBaru = $tanggalMulaiBaru->diffInDays($tanggalSelesaiBaru) + 1;

        // Ambil data cuti tahunan
        $cutiTahunan = Cuti::where('karyawan_id', $user->id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->where('status', 'approved')
            ->first();

        if (!$cutiTahunan) {
            return back()->with('error', 'Data cuti tahunan tidak ditemukan.');
        }

        // Hitung selisih durasi
        $durasiLama = $cuti->durasi;
        $selisihDurasi = $durasiBaru - $durasiLama;

        // Cek apakah sisa cuti mencukupi untuk penambahan durasi
        if ($selisihDurasi > 0 && $cutiTahunan->sisa_cuti < $selisihDurasi) {
            return back()->with('error', 'Sisa cuti tidak mencukupi untuk penambahan durasi. Sisa cuti: ' . $cutiTahunan->sisa_cuti . ' hari');
        }

        // UPDATE data cuti yang sudah ada (BUKAN membuat baru)
        $cuti->update([
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
        ]);

        // Update sisa cuti jika ada perubahan durasi
        if ($selisihDurasi != 0) {
            // Update sisa cuti di data cuti tahunan
            $cutiTahunan->update([
                'sisa_cuti' => $cutiTahunan->sisa_cuti - $selisihDurasi,
                'cuti_digunakan' => $cutiTahunan->cuti_digunakan + $selisihDurasi,
            ]);

            // Update sisa cuti di data pengajuan (data yang sedang diedit)
            $cuti->update([
                'sisa_cuti' => $cuti->sisa_cuti - $selisihDurasi,
                'cuti_digunakan' => $cuti->cuti_digunakan + $selisihDurasi,
            ]);
        }

        return redirect()->route('karyawan.cuti.dashboard')
            ->with('success', 'Pengajuan cuti berhasil diperbarui.');
    }

    // Cancel Cuti (Hapus pengajuan yang masih pending)
    public function cancel($id)
    {
        $user = Auth::user();
        $cuti = Cuti::where('id', $id)
            ->where('karyawan_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Kembalikan sisa cuti
        $cutiTahunan = Cuti::where('karyawan_id', $user->id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->where('status', 'approved')
            ->first();

        if ($cutiTahunan) {
            $cutiTahunan->update([
                'sisa_cuti' => $cutiTahunan->sisa_cuti + $cuti->durasi,
                'cuti_digunakan' => $cutiTahunan->cuti_digunakan - $cuti->durasi,
            ]);
        }

        $cuti->delete();

        return redirect()->route('karyawan.cuti.dashboard')
            ->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }

    // Detail Cuti (HR)
    public function show($id)
    {
        $cuti = Cuti::with('karyawan')->findOrFail($id);
        return view('hr.cuti.show', compact('cuti'));
    }

    // Form Edit Cuti untuk HR
    public function editHr($id)
    {
        $cuti = Cuti::with('karyawan')->findOrFail($id);
        $karyawans = Karyawan::orderBy('nama_lengkap')->get();
        return view('hr.cuti.edit', compact('cuti', 'karyawans'));
    }

    // Update Cuti untuk HR
    public function updateHr(Request $request, $id)
    {
        $cuti = Cuti::findOrFail($id);

        $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'jenis_cuti' => 'required|string',
            'jatah_cuti' => 'required|integer|min:0',
            'sisa_cuti' => 'required|integer|min:0',
            'cuti_digunakan' => 'required|integer|min:0',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string|max:500',
            'status' => 'required|in:pending,approved,rejected',
            'catatan_hr' => 'nullable|string|max:500',
        ]);

        $cuti->update([
            'karyawan_id' => $request->karyawan_id,
            'jenis_cuti' => $request->jenis_cuti,
            'jatah_cuti' => $request->jatah_cuti,
            'sisa_cuti' => $request->sisa_cuti,
            'cuti_digunakan' => $request->cuti_digunakan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
            'status' => $request->status,
            'catatan_hr' => $request->catatan_hr,
        ]);

        return redirect()->route('hr.cuti.index')
            ->with('success', 'Data cuti berhasil diperbarui.');
    }

    // Approve Cuti (HR)
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'catatan_hr' => 'nullable|string',
        ]);

        $cuti = Cuti::findOrFail($id);

        // Jika status sebelumnya pending dan sekarang berubah
        $oldStatus = $cuti->status;
        $cuti->status = $request->status;
        $cuti->catatan_hr = $request->catatan_hr;
        $cuti->save();

        // Jika ditolak, kembalikan sisa cuti
        if ($request->status === 'rejected' && $oldStatus === 'pending') {
            $cutiTahunan = Cuti::where('karyawan_id', $cuti->karyawan_id)
                ->where('jenis_cuti', 'Cuti Tahunan')
                ->where('status', 'approved')
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
                        ->where('status', 'approved')
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
            ->with('success', count($ids) . " pengajuan cuti berhasil {$statusLabel}");
    }

    // Delete Cuti (HR)
    public function destroy($id)
    {
        $cuti = Cuti::findOrFail($id);

        // Jika statusnya pending, kembalikan sisa cuti
        if ($cuti->status === 'pending') {
            $cutiTahunan = Cuti::where('karyawan_id', $cuti->karyawan_id)
                ->where('jenis_cuti', 'Cuti Tahunan')
                ->where('status', 'approved')
                ->first();

            if ($cutiTahunan) {
                $cutiTahunan->update([
                    'sisa_cuti' => $cutiTahunan->sisa_cuti + $cuti->durasi,
                    'cuti_digunakan' => $cutiTahunan->cuti_digunakan - $cuti->durasi,
                ]);
            }
        }

        $cuti->delete();

        return redirect()->route('hr.cuti.index')
            ->with('success', 'Data cuti berhasil dihapus.');
    }
}
