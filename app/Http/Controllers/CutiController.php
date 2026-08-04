<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CutiController extends Controller
{
    /**
     * Ambil (atau buat jika belum ada) baris kuota cuti tahunan milik seorang karyawan.
     * Baris kuota dibedakan dari pengajuan asli lewat tanggal_mulai yang kosong,
     * supaya tidak pernah tertukar dengan pengajuan yang kebetulan sudah "approved".
     */
    private function getOrCreateKuota(int $karyawanId): Cuti
    {
        $kuota = Cuti::kuota()
            ->where('karyawan_id', $karyawanId)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->first();

        if (!$kuota) {
            $kuota = Cuti::create([
                'karyawan_id' => $karyawanId,
                'jenis_cuti' => 'Cuti Tahunan',
                'jatah_cuti' => 12,
                'sisa_cuti' => 12,
                'cuti_digunakan' => 0,
                'status' => 'approved',
                'tanggal_pengajuan' => Carbon::now(),
            ]);
        }

        return $kuota;
    }

    /**
     * Cari baris kuota tanpa membuat baru. Dipakai di alur HR (restore sisa cuti)
     * yang seharusnya tidak membuat kuota baru kalau memang belum pernah ada.
     */
    private function findKuota(int $karyawanId): ?Cuti
    {
        return Cuti::kuota()
            ->where('karyawan_id', $karyawanId)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->first();
    }

    /**
     * Cek apakah karyawan sudah punya pengajuan cuti (pending/approved) yang
     * tanggalnya bentrok dengan rentang baru. $excludeId dipakai saat edit
     * supaya pengajuan yang sedang diedit tidak membentur dirinya sendiri.
     */
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

    /**
     * Buat/perbarui baris Absensi berstatus "Cuti" untuk setiap tanggal dalam
     * periode pengajuan cuti yang BARU SAJA disetujui HR, supaya periode cuti
     * otomatis muncul di halaman Manajemen Absensi HR (dan ikut ter-export ke Excel).
     *
     * Tidak menimpa hari yang karyawan sudah benar-benar check-in (data kehadiran
     * asli tetap diprioritaskan/tidak ditindih oleh status Cuti).
     */
    private function syncAbsensiCuti(Cuti $cuti): void
    {
        if (!$cuti->tanggal_mulai || !$cuti->tanggal_selesai) {
            return;
        }

        $keteranganAbsensi = 'Cuti disetujui HR' . ($cuti->keterangan ? ' - ' . $cuti->keterangan : '');

        for ($tanggal = $cuti->tanggal_mulai->copy(); $tanggal->lte($cuti->tanggal_selesai); $tanggal->addDay()) {
            $absensiHariIni = Absensi::where('karyawan_id', $cuti->karyawan_id)
                ->whereDate('tanggal', $tanggal->format('Y-m-d'))
                ->first();

            // Kalau karyawan sudah punya check-in asli di tanggal ini, jangan ditindih.
            if ($absensiHariIni && $absensiHariIni->check_in) {
                continue;
            }

            Absensi::updateOrCreate(
                [
                    'karyawan_id' => $cuti->karyawan_id,
                    'tanggal' => $tanggal->format('Y-m-d'),
                ],
                [
                    'status' => 'Cuti',
                    'keterangan' => $keteranganAbsensi,
                ]
            );
        }
    }

    /**
     * Hapus baris Absensi berstatus "Cuti" pada rentang tanggal tertentu milik
     * seorang karyawan. Dipakai saat pengajuan cuti yang sebelumnya approved
     * dibatalkan/ditolak/diubah tanggalnya/dihapus oleh HR, supaya data absensi
     * tidak menyisakan periode cuti yang sudah tidak berlaku.
     *
     * Hanya menghapus baris yang statusnya masih "Cuti" (tidak menyentuh baris
     * yang sudah diubah manual oleh HR ke status lain, mis. Hadir).
     */
    private function removeAbsensiCuti(?int $karyawanId, $tanggalMulai, $tanggalSelesai): void
    {
        if (!$karyawanId || !$tanggalMulai || !$tanggalSelesai) {
            return;
        }

        Absensi::where('karyawan_id', $karyawanId)
            ->where('status', 'Cuti')
            ->whereBetween('tanggal', [
                \Carbon\Carbon::parse($tanggalMulai)->format('Y-m-d'),
                \Carbon\Carbon::parse($tanggalSelesai)->format('Y-m-d'),
            ])
            ->delete();
    }

    // Dashboard Cuti untuk HR
    public function index(Request $request)
    {
        $query = Cuti::pengajuan()->with('karyawan')->where('jenis_cuti', 'Cuti Tahunan');

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

        // Statistik (hanya pengajuan asli, bukan baris kuota)
        $statistik = [
            'total' => Cuti::pengajuan()->where('jenis_cuti', 'Cuti Tahunan')->count(),
            'pending' => Cuti::pengajuan()->where('jenis_cuti', 'Cuti Tahunan')->where('status', 'pending')->count(),
            'approved' => Cuti::pengajuan()->where('jenis_cuti', 'Cuti Tahunan')->where('status', 'approved')->count(),
            'rejected' => Cuti::pengajuan()->where('jenis_cuti', 'Cuti Tahunan')->where('status', 'rejected')->count(),
        ];

        return view('hr.cuti.index', compact('cuti', 'karyawans', 'statistik'));
    }

    // Dashboard Cuti untuk Karyawan
    public function dashboard()
    {
        $user = Auth::user();

        // Ambil data cuti karyawan (hanya pengajuan asli, bukan baris kuota)
        $cuti = Cuti::pengajuan()
            ->where('karyawan_id', $user->id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->orderBy('created_at', 'desc')
            ->get();

        // Baris kuota cuti tahunan (dibuat otomatis kalau belum ada)
        $cutiTahunan = $this->getOrCreateKuota($user->id);

        return view('karyawan.cuti.dashboard', compact('cuti', 'cutiTahunan'));
    }

    // Form Pengajuan Cuti (Karyawan)
    public function create()
    {
        $user = Auth::user();

        $cutiTahunan = $this->getOrCreateKuota($user->id);

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

        if ($this->adaBentrokTanggal($user->id, $tanggalMulai, $tanggalSelesai)) {
            return back()->withInput()->with('error', 'Anda sudah memiliki pengajuan cuti pada rentang tanggal tersebut.');
        }

        $cutiTahunan = $this->getOrCreateKuota($user->id);

        if ($cutiTahunan->sisa_cuti < $durasi) {
            return back()->withInput()->with('error', 'Sisa cuti Anda tidak mencukupi. Sisa cuti: ' . $cutiTahunan->sisa_cuti . ' hari');
        }

        Cuti::create([
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

        $cutiTahunan->update([
            'sisa_cuti' => $cutiTahunan->sisa_cuti - $durasi,
            'cuti_digunakan' => $cutiTahunan->cuti_digunakan + $durasi,
        ]);

        return redirect()->route('karyawan.cuti.dashboard')
            ->with('success', 'Pengajuan cuti berhasil dikirim. Menunggu persetujuan HR.');
    }

    // Form Edit Cuti untuk Karyawan
    public function edit($id)
    {
        $user = Auth::user();
        $cuti = Cuti::pengajuan()
            ->where('id', $id)
            ->where('karyawan_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        return view('karyawan.cuti.edit', compact('cuti'));
    }

    // Update Cuti untuk Karyawan
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $cuti = Cuti::pengajuan()
            ->where('id', $id)
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

        if ($this->adaBentrokTanggal($user->id, $tanggalMulaiBaru, $tanggalSelesaiBaru, $cuti->id)) {
            return back()->withInput()->with('error', 'Anda sudah memiliki pengajuan cuti lain pada rentang tanggal tersebut.');
        }

        $cutiTahunan = $this->findKuota($user->id);

        if (!$cutiTahunan) {
            return back()->with('error', 'Data cuti tahunan tidak ditemukan.');
        }

        $durasiLama = $cuti->durasi;
        $selisihDurasi = $durasiBaru - $durasiLama;

        if ($selisihDurasi > 0 && $cutiTahunan->sisa_cuti < $selisihDurasi) {
            return back()->withInput()->with('error', 'Sisa cuti tidak mencukupi untuk penambahan durasi. Sisa cuti: ' . $cutiTahunan->sisa_cuti . ' hari');
        }

        $cuti->update([
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
        ]);

        if ($selisihDurasi != 0) {
            $cutiTahunan->update([
                'sisa_cuti' => $cutiTahunan->sisa_cuti - $selisihDurasi,
                'cuti_digunakan' => $cutiTahunan->cuti_digunakan + $selisihDurasi,
            ]);

            $cuti->update([
                'sisa_cuti' => $cuti->sisa_cuti - $selisihDurasi,
                'cuti_digunakan' => $cuti->cuti_digunakan + $selisihDurasi,
            ]);
        }

        return redirect()->route('karyawan.cuti.dashboard')
            ->with('success', 'Pengajuan cuti berhasil diperbarui.');
    }

    // Cancel Cuti
    public function cancel($id)
    {
        $user = Auth::user();
        $cuti = Cuti::pengajuan()
            ->where('id', $id)
            ->where('karyawan_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $cutiTahunan = $this->findKuota($user->id);

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
        $cuti = Cuti::pengajuan()->with('karyawan')->findOrFail($id);
        return view('hr.cuti.show', compact('cuti'));
    }

    // Form Edit Cuti untuk HR
    public function editHr($id)
    {
        $cuti = Cuti::pengajuan()->with('karyawan')->findOrFail($id);
        $karyawans = Karyawan::orderBy('nama_lengkap')->get();
        return view('hr.cuti.edit', compact('cuti', 'karyawans'));
    }

    // Update Cuti untuk HR
    public function updateHr(Request $request, $id)
    {
        $cuti = Cuti::pengajuan()->findOrFail($id);

        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
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

        $oldStatus = $cuti->status;
        $karyawanIdLama = $cuti->karyawan_id;
        $durasiLama = $cuti->durasi;
        $tanggalMulaiLama = $cuti->tanggal_mulai;
        $tanggalSelesaiLama = $cuti->tanggal_selesai;

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

        // Jika HR mengubah status pengajuan yang sebelumnya pending menjadi rejected,
        // kembalikan jatah cuti tahunan milik karyawan yang bersangkutan.
        if ($oldStatus === 'pending' && $request->status === 'rejected') {
            $cutiTahunan = $this->findKuota($karyawanIdLama);
            if ($cutiTahunan) {
                $cutiTahunan->update([
                    'sisa_cuti' => $cutiTahunan->sisa_cuti + $durasiLama,
                    'cuti_digunakan' => $cutiTahunan->cuti_digunakan - $durasiLama,
                ]);
            }
        }

        // Sinkronkan tabel Absensi: bersihkan periode cuti lama (kalau sebelumnya
        // approved), lalu buat lagi kalau statusnya sekarang approved.
        if ($oldStatus === 'approved') {
            $this->removeAbsensiCuti($karyawanIdLama, $tanggalMulaiLama, $tanggalSelesaiLama);
        }
        if ($cuti->status === 'approved') {
            $this->syncAbsensiCuti($cuti);
        }

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

        $cuti = Cuti::pengajuan()->findOrFail($id);
        $oldStatus = $cuti->status;

        $cuti->status = $request->status;
        $cuti->catatan_hr = $request->catatan_hr;
        $cuti->save();

        if ($request->status === 'rejected' && $oldStatus === 'pending') {
            $cutiTahunan = $this->findKuota($cuti->karyawan_id);

            if ($cutiTahunan) {
                $cutiTahunan->update([
                    'sisa_cuti' => $cutiTahunan->sisa_cuti + $cuti->durasi,
                    'cuti_digunakan' => $cutiTahunan->cuti_digunakan - $cuti->durasi,
                ]);
            }
        }

        // Sinkronkan tabel Absensi supaya periode cuti yang disetujui langsung
        // muncul dengan status "Cuti" di Manajemen Absensi HR (dan di export Excel).
        if ($oldStatus === 'approved' && $request->status !== 'approved') {
            $this->removeAbsensiCuti($cuti->karyawan_id, $cuti->tanggal_mulai, $cuti->tanggal_selesai);
        }
        if ($request->status === 'approved') {
            $this->syncAbsensiCuti($cuti);
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
        $catatanHr = $request->input('catatan_hr');

        // Jika ids berupa string JSON, parse dulu
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }

        $processed = 0;

        foreach ($ids as $id) {
            $cuti = Cuti::pengajuan()->find($id);
            if ($cuti && $cuti->status === 'pending') {
                $cuti->status = $targetStatus;
                $cuti->catatan_hr = $catatanHr;
                $cuti->save();

                if ($targetStatus === 'rejected') {
                    $cutiTahunan = $this->findKuota($cuti->karyawan_id);

                    if ($cutiTahunan) {
                        $cutiTahunan->update([
                            'sisa_cuti' => $cutiTahunan->sisa_cuti + $cuti->durasi,
                            'cuti_digunakan' => $cutiTahunan->cuti_digunakan - $cuti->durasi,
                        ]);
                    }
                }

                // Sinkronkan tabel Absensi kalau pengajuan ini disetujui lewat bulk approve.
                if ($targetStatus === 'approved') {
                    $this->syncAbsensiCuti($cuti);
                }

                $processed++;
            }
        }

        $statusLabel = $targetStatus === 'approved' ? 'Disetujui' : 'Ditolak';

        return redirect()->route('hr.cuti.index')
            ->with('success', "{$processed} pengajuan cuti berhasil {$statusLabel}");
    }

    // Delete Cuti (HR)
    public function destroy($id)
    {
        $cuti = Cuti::pengajuan()->findOrFail($id);

        if ($cuti->status === 'pending') {
            $cutiTahunan = $this->findKuota($cuti->karyawan_id);

            if ($cutiTahunan) {
                $cutiTahunan->update([
                    'sisa_cuti' => $cutiTahunan->sisa_cuti + $cuti->durasi,
                    'cuti_digunakan' => $cutiTahunan->cuti_digunakan - $cuti->durasi,
                ]);
            }
        }

        // Kalau pengajuan ini sudah approved, bersihkan juga periode "Cuti" yang
        // sebelumnya otomatis dibuat di tabel Absensi.
        if ($cuti->status === 'approved') {
            $this->removeAbsensiCuti($cuti->karyawan_id, $cuti->tanggal_mulai, $cuti->tanggal_selesai);
        }

        $cuti->delete();

        return redirect()->route('hr.cuti.index')
            ->with('success', 'Data cuti berhasil dihapus.');
    }
}
