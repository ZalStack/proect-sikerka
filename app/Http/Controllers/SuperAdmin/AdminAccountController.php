<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Kelola akun admin (Super Admin & HR) — Fitur A3.
 * Berbeda dengan halaman role di SuperAdminController::karyawanIndex
 * (yang hanya mengubah posisi), controller ini mengelola kredensial
 * & pembuatan/penghapusan akun admin secara utuh.
 */
class AdminAccountController extends Controller
{
    /** Role yang boleh dikelola lewat halaman ini. */
    private const ADMIN_ROLES = ['superadmin', 'hr'];

    public function index()
    {
        $admins = Karyawan::withTrashed()
            ->whereIn('posisi', self::ADMIN_ROLES)
            ->orderBy('posisi')
            ->orderBy('nama_lengkap')
            ->get();

        return view('superadmin.admin-accounts', [
            'admins' => $admins,
            'roles' => self::ADMIN_ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:karyawans,email'],
            'kata_sandi' => ['required', 'string', 'min:10'],
            'posisi' => ['required', 'in:superadmin,hr'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'divisi' => ['nullable', 'string', 'max:50'],
            'nomor_telepon' => ['nullable', 'string', 'max:20'],
        ], [
            'kata_sandi.min' => 'Kata sandi minimal 10 karakter untuk akun admin.',
        ]);

        $karyawan = DB::transaction(function () use ($data) {
            $kode = $this->generateKode($data['posisi']);

            return Karyawan::create([
                'kode_pegawai' => $kode,
                'email' => $data['email'],
                'kata_sandi' => Hash::make($data['kata_sandi']),
                'nama_lengkap' => $data['nama_lengkap'],
                'posisi' => $data['posisi'],
                'jabatan' => $data['jabatan'] ?? ($data['posisi'] === 'superadmin' ? 'Super Administrator' : 'HR Officer'),
                'divisi' => $data['divisi'] ?? ($data['posisi'] === 'superadmin' ? 'IT' : 'HRD'),
                'nomor_telepon' => $data['nomor_telepon'] ?? null,
                'no_wa' => $data['nomor_telepon'] ?? null,
                'tanggal_bergabung' => now()->toDateString(),
                'status' => 'Karyawan Tetap',
                'is_resigned' => false,
                'violation_count' => 0,
                'is_banned_permanent' => false,
            ]);
        });

        ActivityLog::record(
            'admin.create',
            'Akun admin baru dibuat: ' . $karyawan->email . ' (' . $karyawan->posisi . ').',
            ['module' => 'users', 'subject_type' => 'karyawans', 'subject_id' => $karyawan->id]
        );

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Akun admin berhasil dibuat: ' . $karyawan->kode_pegawai);
    }

    public function update(Request $request, int $id)
    {
        $karyawan = Karyawan::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:karyawans,email,' . $karyawan->id],
            'posisi' => ['required', 'in:superadmin,hr'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'divisi' => ['nullable', 'string', 'max:50'],
            'kata_sandi' => ['nullable', 'string', 'min:10'],
        ], [
            'kata_sandi.min' => 'Kata sandi minimal 10 karakter untuk akun admin.',
        ]);

        // Guard: superadmin terakhir tidak boleh diturunkan posisinya.
        if ($karyawan->posisi === 'superadmin' && $data['posisi'] !== 'superadmin') {
            $remaining = Karyawan::where('posisi', 'superadmin')
                ->where('id', '!=', $karyawan->id)
                ->where('is_resigned', false)
                ->count();

            if ($remaining === 0) {
                return back()->with('error', 'Tidak dapat menurunkan posisi Super Admin terakhir. Buat Super Admin lain terlebih dahulu.');
            }
        }

        $karyawan->nama_lengkap = $data['nama_lengkap'];
        $karyawan->email = $data['email'];
        $karyawan->posisi = $data['posisi'];
        $karyawan->jabatan = $data['jabatan'] ?? $karyawan->jabatan;
        $karyawan->divisi = $data['divisi'] ?? $karyawan->divisi;

        if (!empty($data['kata_sandi'])) {
            $karyawan->kata_sandi = Hash::make($data['kata_sandi']);
        }

        $karyawan->save();

        ActivityLog::record(
            'admin.update',
            'Akun admin diperbarui: ' . $karyawan->email . '.',
            ['module' => 'users', 'subject_type' => 'karyawans', 'subject_id' => $karyawan->id]
        );

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Akun admin berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id)
    {
        $karyawan = Karyawan::withTrashed()->findOrFail($id);

        if ($karyawan->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($karyawan->posisi === 'superadmin') {
            $remaining = Karyawan::where('posisi', 'superadmin')
                ->where('id', '!=', $karyawan->id)
                ->where('is_resigned', false)
                ->count();

            if ($remaining === 0) {
                return back()->with('error', 'Tidak dapat menghapus Super Admin terakhir.');
            }
        }

        // Putuskan seluruh sesi aktif milik akun ini sebelum dihapus.
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                $loginKey = 'login_' . config('auth.defaults.guard', 'web') . '_' . sha1(\Illuminate\Auth\SessionGuard::class);

                foreach (DB::table('sessions')->pluck('payload') as $payload) {
                    $decoded = @unserialize(base64_decode((string) $payload)) ?: [];

                    if (isset($decoded[$loginKey]) && (int) $decoded[$loginKey] === $karyawan->id) {
                        DB::table('sessions')->where('payload', $payload)->delete();
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $email = $karyawan->email;
        $karyawan->delete();

        ActivityLog::record(
            'admin.delete',
            'Akun admin dihapus: ' . $email . '.',
            ['module' => 'users', 'subject_type' => 'karyawans', 'subject_id' => $karyawan->id]
        );

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Akun admin berhasil dihapus.');
    }

    /** Reset kata sandi akun admin → kata sandi acak yang ditampilkan sekali. */
    public function resetPassword(Request $request, int $id)
    {
        $karyawan = Karyawan::withTrashed()->findOrFail($id);

        $plain = 'SA-' . Str::upper(Str::random(14));
        $karyawan->kata_sandi = Hash::make($plain);
        $karyawan->save();

        ActivityLog::record(
            'admin.password_reset',
            'Kata sandi akun admin direset: ' . $karyawan->email . '.',
            ['module' => 'users', 'subject_type' => 'karyawans', 'subject_id' => $karyawan->id]
        );

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Kata sandi baru untuk ' . $karyawan->email . ': ' . $plain . ' (catat sekarang, hanya ditampilkan sekali).');
    }

    private function generateKode(string $posisi): string
    {
        $prefix = $posisi === 'superadmin' ? 'SA' : 'HR';

        do {
            $kode = $prefix . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Karyawan::withTrashed()->where('kode_pegawai', $kode)->exists());

        return $kode;
    }
}
