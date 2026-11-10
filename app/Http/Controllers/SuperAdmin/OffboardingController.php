<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use Illuminate\Http\Request;

/**
 * Offboarding terpusat (Fitur D15).
 *
 * Satu halaman untuk: menandai karyawan resign (is_resigned + tanggal
 * resign + blokir login), mengarsipkan (soft delete) karyawan, serta
 * memulihkan arsip — semuanya tercatat sebagai aktivitas audit.
 */
class OffboardingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $active = Karyawan::where('is_resigned', false)
            ->where('posisi', '!=', 'superadmin')
            ->where(function ($q) use ($search) {
                if ($search !== '') {
                    $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                        ->orWhere('kode_pegawai', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                }
            })
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        $resigned = Karyawan::resigned()
            ->orderByDesc('tanggal_resign')
            ->limit(10)
            ->get();

        $archived = Karyawan::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->limit(10)
            ->get();

        return view('superadmin.offboarding', compact('active', 'resigned', 'archived', 'search'));
    }

    /**
     * Tandai karyawan resign. Berbeda dari hapus: data & riwayat tetap
     * utuh, hanya login yang diblokir (canLogin() = false).
     */
    public function offboard(Request $request, Karyawan $karyawan)
    {
        if ($karyawan->posisi === 'superadmin') {
            return back()->with('error', 'Akun Super Admin tidak bisa di-offboard dari halaman ini.');
        }

        $data = $request->validate([
            'tanggal_resign' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        // Validasi manual: aturan after_or_equal butuh field lain di request,
        // sedangkan tanggal bergabung ada di model.
        if ($karyawan->tanggal_bergabung
            && \Carbon\Carbon::parse($data['tanggal_resign'])->lt($karyawan->tanggal_bergabung->startOfDay())) {
            return back()->withErrors([
                'tanggal_resign' => 'Tanggal resign tidak boleh sebelum tanggal bergabung.',
            ])->withInput();
        }

        $karyawan->update([
            'is_resigned' => true,
            'tanggal_resign' => $data['tanggal_resign'],
        ]);

        // Putus sesi aktif agar karyawan langsung keluar dari sistem.
        // Tabel sesi tidak selalu ada (driver array/database belum dipakai).
        try {
            if (\Schema::hasTable('sessions')) {
                \DB::table('sessions')->where('user_id', $karyawan->id)->delete();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        ActivityLog::record(
            'karyawan.offboard',
            'Karyawan ' . $karyawan->nama_lengkap . ' (' . $karyawan->kode_pegawai . ') ditandai resign efektif '
                . $data['tanggal_resign'] . '.',
            [
                'module' => 'users',
                'subject_type' => Karyawan::class,
                'subject_id' => $karyawan->id,
                'metadata' => [
                    'tanggal_resign' => $data['tanggal_resign'],
                    'keterangan' => $data['keterangan'] ?? null,
                ],
            ]
        );

        return back()->with(
            'success',
            $karyawan->nama_lengkap . ' berhasil di-offboard. Akun diblokir login, seluruh riwayat tetap tersimpan.'
        );
    }

    /** Batalkan resign (salah tandai). */
    public function reactivate(Karyawan $karyawan)
    {
        $karyawan->update([
            'is_resigned' => false,
            'tanggal_resign' => null,
        ]);

        ActivityLog::record(
            'karyawan.offboard',
            'Status resign karyawan ' . $karyawan->nama_lengkap . ' (' . $karyawan->kode_pegawai . ') dibatalkan.',
            ['module' => 'users', 'subject_type' => Karyawan::class, 'subject_id' => $karyawan->id]
        );

        return back()->with('success', 'Status resign ' . $karyawan->nama_lengkap . ' dibatalkan. Akun aktif kembali.');
    }

    /**
     * Arsipkan karyawan (soft delete) — baris tetap ada di database
     * (deleted_at terisi), riwayat presensi/cuti tidak ikut terhapus.
     */
    public function archive(Karyawan $karyawan)
    {
        if ($karyawan->posisi === 'superadmin') {
            return back()->with('error', 'Akun Super Admin tidak bisa diarsipkan.');
        }

        $karyawan->delete();

        ActivityLog::record(
            'karyawan.delete',
            'Karyawan ' . $karyawan->nama_lengkap . ' (' . $karyawan->kode_pegawai . ') diarsipkan (soft delete).',
            ['module' => 'users', 'subject_type' => Karyawan::class, 'subject_id' => $karyawan->id]
        );

        return back()->with(
            'success',
            $karyawan->nama_lengkap . ' berhasil diarsipkan. Data masih dapat dipulihkan lewat menu Arsip.'
        );
    }

    /** Pulihkan karyawan dari arsip. */
    public function restore(int $id)
    {
        $karyawan = Karyawan::onlyTrashed()->findOrFail($id);
        $karyawan->restore();

        ActivityLog::record(
            'karyawan.delete',
            'Arsip karyawan ' . $karyawan->nama_lengkap . ' (' . $karyawan->kode_pegawai . ') dipulihkan.',
            ['module' => 'users', 'subject_type' => Karyawan::class, 'subject_id' => $karyawan->id]
        );

        return back()->with('success', 'Karyawan berhasil dipulihkan dari arsip.');
    }
}
