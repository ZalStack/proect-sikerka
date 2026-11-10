<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CutiController extends Controller
{
    /**
     * Ambil (atau buat jika belum ada) baris kuota cuti tahunan milik seorang karyawan.
     * Baris kuota dibedakan dari pengajuan asli lewat tanggal_mulai yang kosong,
     * supaya tidak pernah tertukar dengan pengajuan yang kebetulan sudah "approved".
     *
     * Kuota diambil dari kebijakan cuti tahun berjalan bila ada (Fitur D14),
     * selain itu memakai default 12 hari.
     */
    private function getOrCreateKuota(int $karyawanId, string $jenisCuti = 'Cuti Tahunan'): Cuti
    {
        $kuota = Cuti::kuota()
            ->where('karyawan_id', $karyawanId)
            ->where('jenis_cuti', $jenisCuti)
            ->first();

        if (!$kuota) {
            $karyawan = Karyawan::find($karyawanId);
            $jatah = $karyawan
                ? \App\Models\CutiPolicy::kuotaFor($karyawan, now()->year, $jenisCuti)
                : 12;

            $kuota = Cuti::create([
                'karyawan_id' => $karyawanId,
                'jenis_cuti' => $jenisCuti,
                'jatah_cuti' => $jatah,
                'sisa_cuti' => $jatah,
                'cuti_digunakan' => 0,
                'status' => 'approved',
                'tanggal_pengajuan' => Carbon::now(),
            ]);
        }

        return $kuota;
    }

    /**
     * Cari baris kuota tanpa membuat baru. Dipakai di alur HR/Superadmin (restore sisa cuti)
     * yang seharusnya tidak membuat kuota baru kalau memang belum pernah ada.
     */
    private function findKuota(int $karyawanId, string $jenisCuti = 'Cuti Tahunan'): ?Cuti
    {
        return Cuti::kuota()
            ->where('karyawan_id', $karyawanId)
            ->where('jenis_cuti', $jenisCuti)
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
     * periode pengajuan cuti (dipanggil begitu karyawan MENGAJUKAN cuti/pending,
     * bukan menunggu approval HR, supaya HR langsung melihatnya di Manajemen
     * Absensi -- keterangan otomatis membedakan status "menunggu" vs "disetujui").
     *
     * Tidak menimpa hari yang karyawan sudah benar-benar check-in (data kehadiran
     * asli tetap diprioritaskan/tidak ditindih oleh status Cuti).
     */
    private function syncAbsensiCuti(Cuti $cuti): void
    {
        if (!$cuti->tanggal_mulai || !$cuti->tanggal_selesai) {
            return;
        }

        if ($cuti->status === 'rejected') {
            app(\App\Services\AbsensiSyncService::class)->removeCuti($cuti);
            return;
        }

        app(\App\Services\AbsensiSyncService::class)->syncCuti($cuti);
    }

    /**
     * Wrapper untuk dipakai di alur HR (approve/updateHr) yang bisa mengubah
     * tanggal dan/atau karyawan pengajuan sekaligus.
     */
    private function syncAbsensiUntukCuti(Cuti $cuti, ?int $karyawanIdLama = null, $tanggalMulaiLama = null, $tanggalSelesaiLama = null): void
    {
        if ($karyawanIdLama && $tanggalMulaiLama && $tanggalSelesaiLama) {
            $this->removeAbsensiCuti($karyawanIdLama, $tanggalMulaiLama, $tanggalSelesaiLama);
        }

        $this->syncAbsensiCuti($cuti);
    }

    /**
     * Hapus baris Absensi berstatus "Cuti" pada rentang tanggal tertentu.
     */
    private function removeAbsensiCuti(?int $karyawanId, $tanggalMulai, $tanggalSelesai): void
    {
        if (!$karyawanId || !$tanggalMulai || !$tanggalSelesai) {
            return;
        }

        Absensi::where('karyawan_id', $karyawanId)
            ->where('status', 'Cuti')
            ->whereNull('check_in')
            ->whereBetween('tanggal', [
                \Carbon\Carbon::parse($tanggalMulai)->format('Y-m-d'),
                \Carbon\Carbon::parse($tanggalSelesai)->format('Y-m-d'),
            ])
            ->delete();
    }

    // Dashboard Cuti untuk HR
    public function index(Request $request)
    {
        $query = Cuti::pengajuan()->with('karyawan');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter karyawan
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        // Filter jenis cuti
        if ($request->filled('jenis_cuti')) {
            $query->where('jenis_cuti', $request->jenis_cuti);
        }

        $cuti = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $karyawans = Karyawan::orderBy('nama_lengkap')->get();
        $types = \App\Models\CutiType::ordered()->get();

        // Statistik (hanya pengajuan asli, bukan baris kuota)
        $statistik = [
            'total' => Cuti::pengajuan()->count(),
            'pending' => Cuti::pengajuan()->where('status', 'pending')->count(),
            'approved' => Cuti::pengajuan()->where('status', 'approved')->count(),
            'rejected' => Cuti::pengajuan()->where('status', 'rejected')->count(),
        ];

        return view('hr.cuti.index', compact('cuti', 'karyawans', 'statistik', 'types'));
    }

    // Dashboard Cuti untuk Karyawan
    public function dashboard()
    {
        $user = Auth::user();

        // Ambil data cuti karyawan (hanya pengajuan asli, bukan baris kuota)
        $cuti = Cuti::pengajuan()
            ->where('karyawan_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Baris kuota cuti tahunan (dibuat otomatis kalau belum ada)
        $cutiTahunan = $this->getOrCreateKuota($user->id, 'Cuti Tahunan');

        $activeTypes = \App\Models\CutiType::active()->ordered()->get();
        if ($activeTypes->isEmpty()) {
            $kuotaCards = collect([$cutiTahunan]);
        } else {
            $kuotaCards = $activeTypes->map(function ($type) use ($user) {
                return $this->getOrCreateKuota($user->id, $type->nama);
            });
        }

        return view('karyawan.cuti.dashboard', compact('cuti', 'cutiTahunan', 'kuotaCards'));
    }

    // Form Pengajuan Cuti (Karyawan)
    public function create()
    {
        $user = Auth::user();

        $cutiTahunan = $this->getOrCreateKuota($user->id, 'Cuti Tahunan');

        $activeTypes = \App\Models\CutiType::active()->ordered()->get();
        if ($activeTypes->isEmpty()) {
            \App\Models\CutiType::seedDefaults();
            $activeTypes = \App\Models\CutiType::active()->ordered()->get();
        }

        $typesWithKuota = $activeTypes->map(function ($type) use ($user) {
            $kuota = $this->getOrCreateKuota($user->id, $type->nama);
            return [
                'nama' => $type->nama,
                'sisa_cuti' => $kuota->sisa_cuti,
                'jatah_cuti' => $kuota->jatah_cuti,
            ];
        });

        return view('karyawan.cuti.create', compact('cutiTahunan', 'typesWithKuota'));
    }

    // Store Pengajuan Cuti
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'jenis_cuti' => ['required', 'string', function ($attribute, $value, $fail) {
                $type = \App\Models\CutiType::where('nama', $value)->where('is_active', true)->first();
                if (!$type && \App\Models\CutiType::count() === 0 && $value === 'Cuti Tahunan') {
                    \App\Models\CutiType::seedDefaults();
                    $type = \App\Models\CutiType::where('nama', $value)->where('is_active', true)->first();
                }
                if (!$type) {
                    $fail('Jenis cuti yang dipilih tidak valid atau sedang dinonaktifkan.');
                }
            }],
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

        $jenisCuti = $request->jenis_cuti;
        $kuota = $this->getOrCreateKuota($user->id, $jenisCuti);

        if ($kuota->sisa_cuti < $durasi) {
            return back()->withInput()->with('error', 'Sisa cuti ' . $jenisCuti . ' Anda tidak mencukupi. Sisa cuti: ' . $kuota->sisa_cuti . ' hari');
        }

        DB::transaction(function () use ($user, $durasi, $request, $kuota, $jenisCuti) {
            $cutiBaru = Cuti::create([
                'karyawan_id' => $user->id,
                'jenis_cuti' => $jenisCuti,
                'jatah_cuti' => $kuota->jatah_cuti,
                'sisa_cuti' => $kuota->sisa_cuti - $durasi,
                'cuti_digunakan' => $kuota->cuti_digunakan + $durasi,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'keterangan' => $request->keterangan,
                'status' => 'pending',
                'tanggal_pengajuan' => Carbon::now(),
            ]);

            $kuota->update([
                'sisa_cuti' => $kuota->sisa_cuti - $durasi,
                'cuti_digunakan' => $kuota->cuti_digunakan + $durasi,
            ]);

            $this->syncAbsensiCuti($cutiBaru);
        });

        return redirect()->route('karyawan.cuti.dashboard')
            ->with('success', 'Pengajuan cuti berhasil dikirim. Menunggu persetujuan.');
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

        $kuota = $this->findKuota($user->id, $cuti->jenis_cuti);

        if (!$kuota) {
            return back()->with('error', 'Data kuota cuti tidak ditemukan.');
        }

        $durasiLama = $cuti->durasi;
        $selisihDurasi = $durasiBaru - $durasiLama;

        if ($selisihDurasi > 0 && $kuota->sisa_cuti < $selisihDurasi) {
            return back()->withInput()->with('error', 'Sisa cuti tidak mencukupi untuk penambahan durasi. Sisa cuti: ' . $kuota->sisa_cuti . ' hari');
        }

        $tanggalMulaiLama = $cuti->tanggal_mulai;
        $tanggalSelesaiLama = $cuti->tanggal_selesai;

        $cuti->update([
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
        ]);

        if ($selisihDurasi != 0) {
            $kuota->update([
                'sisa_cuti' => $kuota->sisa_cuti - $selisihDurasi,
                'cuti_digunakan' => $kuota->cuti_digunakan + $selisihDurasi,
            ]);

            $cuti->update([
                'sisa_cuti' => $cuti->sisa_cuti - $selisihDurasi,
                'cuti_digunakan' => $cuti->cuti_digunakan + $selisihDurasi,
            ]);
        }

        // Resinkronkan Absensi: hapus baris Cuti di periode lama, buat lagi di periode baru.
        $this->syncAbsensiUntukCuti($cuti, $user->id, $tanggalMulaiLama, $tanggalSelesaiLama);

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

        $kuota = $this->findKuota($user->id, $cuti->jenis_cuti);

        if ($kuota) {
            $kuota->update([
                'sisa_cuti' => $kuota->sisa_cuti + $cuti->durasi,
                'cuti_digunakan' => $kuota->cuti_digunakan - $cuti->durasi,
            ]);
        }

        // Hapus baris Cuti di Absensi yang sebelumnya dibuat otomatis saat pengajuan ini dibuat.
        $this->removeAbsensiCuti($user->id, $cuti->tanggal_mulai, $cuti->tanggal_selesai);

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
            // Atribusi keputusan (Fitur D14): siapa & kapan menyetujui.
            'approved_by' => $request->status === 'approved' ? Auth::id() : null,
            'approved_at' => $request->status === 'approved' ? now() : null,
        ]);

        // Jika status pengajuan yang sebelumnya pending diubah menjadi rejected,
        // kembalikan jatah cuti milik karyawan yang bersangkutan untuk jenis cuti tersebut.
        if ($oldStatus === 'pending' && $request->status === 'rejected') {
            $kuota = $this->findKuota($karyawanIdLama, $cuti->jenis_cuti);
            if ($kuota) {
                $kuota->update([
                    'sisa_cuti' => $kuota->sisa_cuti + $durasiLama,
                    'cuti_digunakan' => $kuota->cuti_digunakan - $durasiLama,
                ]);
            }
        }

        // Sinkronkan tabel Absensi: bersihkan periode lama (kalau tanggal/karyawan
        // berubah), lalu sesuaikan lagi berdasarkan status cuti yang terbaru
        // (pending/approved -> tetap tampil sebagai Cuti, rejected -> dihapus).
        $this->syncAbsensiUntukCuti($cuti, $karyawanIdLama, $tanggalMulaiLama, $tanggalSelesaiLama);

        $redirectUrl = (Auth::user()?->isSuperAdmin())
            ? route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan'])
            : route('hr.cuti.index');

        return redirect($redirectUrl)
            ->with('success', 'Data cuti berhasil diperbarui.');
    }

    // Approve Cuti (HR & Superadmin)
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

        // Atribusi keputusan (Fitur D14): siapa & kapan memutus.
        if ($request->status === 'approved') {
            $cuti->approved_by = Auth::id();
            $cuti->approved_at = now();
        } elseif ($request->status === 'pending') {
            $cuti->approved_by = null;
            $cuti->approved_at = null;
        }

        $cuti->save();

        if ($request->status === 'rejected' && $oldStatus === 'pending') {
            $kuota = $this->findKuota($cuti->karyawan_id, $cuti->jenis_cuti);

            if ($kuota) {
                $kuota->update([
                    'sisa_cuti' => $kuota->sisa_cuti + $cuti->durasi,
                    'cuti_digunakan' => $kuota->cuti_digunakan - $cuti->durasi,
                ]);
            }
        }

        // Sinkronkan tabel Absensi: kalau ditolak, baris "Cuti" yang sudah dibuat
        // sejak pengajuan (status pending) akan dihapus; kalau disetujui/menunggu,
        // keterangannya disesuaikan (menunggu persetujuan -> disetujui).
        $this->syncAbsensiUntukCuti($cuti);

        $statusLabel = $request->status === 'approved' ? 'Disetujui' : ($request->status === 'rejected' ? 'Ditolak' : 'Menunggu');

        $redirectUrl = (Auth::user()?->isSuperAdmin())
            ? route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan'])
            : route('hr.cuti.index');

        return redirect($redirectUrl)
            ->with('success', "Pengajuan cuti berhasil {$statusLabel}");
    }

    // Bulk Approve (HR & Superadmin)
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

        DB::transaction(function () use ($ids, $targetStatus, $catatanHr, &$processed) {
            foreach ($ids as $id) {
                $cuti = Cuti::pengajuan()->find($id);
                if ($cuti && $cuti->status === 'pending') {
                    $cuti->status = $targetStatus;
                    $cuti->catatan_hr = $catatanHr;

                    if ($targetStatus === 'approved') {
                        $cuti->approved_by = Auth::id();
                        $cuti->approved_at = now();
                    }

                    $cuti->save();

                    if ($targetStatus === 'rejected') {
                        $kuota = $this->findKuota($cuti->karyawan_id, $cuti->jenis_cuti);

                        if ($kuota) {
                            $kuota->update([
                                'sisa_cuti' => $kuota->sisa_cuti + $cuti->durasi,
                                'cuti_digunakan' => $kuota->cuti_digunakan - $cuti->durasi,
                            ]);
                        }
                    }

                    $this->syncAbsensiUntukCuti($cuti);
                    $processed++;
                }
            }
        });

        $statusLabel = $targetStatus === 'approved' ? 'Disetujui' : 'Ditolak';

        $redirectUrl = (Auth::user()?->isSuperAdmin())
            ? route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan'])
            : route('hr.cuti.index');

        return redirect($redirectUrl)
            ->with('success', "{$processed} pengajuan cuti berhasil {$statusLabel}");
    }

    // Delete Cuti (HR & Superadmin)
    public function destroy($id)
    {
        $cuti = Cuti::pengajuan()->findOrFail($id);

        DB::transaction(function () use ($cuti) {
            if ($cuti->status === 'pending') {
                $kuota = $this->findKuota($cuti->karyawan_id, $cuti->jenis_cuti);

                if ($kuota) {
                    $kuota->update([
                        'sisa_cuti' => $kuota->sisa_cuti + $cuti->durasi,
                        'cuti_digunakan' => $kuota->cuti_digunakan - $cuti->durasi,
                    ]);
                }
            }

            $this->removeAbsensiCuti($cuti->karyawan_id, $cuti->tanggal_mulai, $cuti->tanggal_selesai);
            $cuti->delete();
        });

        $redirectUrl = (Auth::user()?->isSuperAdmin())
            ? route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan'])
            : route('hr.cuti.index');

        return redirect($redirectUrl)
            ->with('success', 'Data cuti berhasil dihapus.');
    }
}
