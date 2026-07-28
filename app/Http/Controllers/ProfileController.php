<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\SunnahDaily;
use App\Models\FhlAbsensi;
use App\Models\KhatamanAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'kode_pegawai' => 'required|unique:karyawans,kode_pegawai,' . $user->id,
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:karyawans,email,' . $user->id,
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'nama_ibu_kandung' => 'nullable|string|max:100',
            'nik' => 'nullable|string|max:16|unique:karyawans,nik,' . $user->id,
            'no_kk' => 'nullable|string|max:20',
            'status_pernikahan' => 'nullable|in:Belum Menikah,Menikah,Cerai',
            'jumlah_anak' => 'nullable|integer|min:0',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'npwp' => 'nullable|string|max:20',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'perguruan_tinggi' => 'nullable|string|max:100',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nomor_telepon' => 'nullable|string|max:20',
            'no_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telepon_kontak_darurat' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'tanggal_pengangkatan_tetap' => 'nullable|date',
            'nomor_rekening' => 'nullable|string|max:50',
            'ipk_terakhir' => 'nullable|numeric|min:0|max:4',
            'alamat_domisili' => 'nullable|string',
            'jabatan' => 'required|string|max:100',
            'status' => 'required|in:Karyawan Tetap,Contract,Internship',
            'tanggal_bergabung' => 'required|date',
            'divisi' => 'required|string|max:50',
            'is_continuing_education' => 'nullable|boolean',
            'continuing_program' => 'required_if:is_continuing_education,1|nullable|in:D3,D4/S1,S2,S3',
            'continuing_perguruan_tinggi' => 'required_if:is_continuing_education,1|nullable|string|max:100',
            'continuing_jurusan' => 'required_if:is_continuing_education,1|nullable|string|max:100',
        ];

        $request->validate($rules);

        $data = $request->except(['foto_profil', '_token', '_method']);

        $data['jumlah_anak'] = $data['jumlah_anak'] ?? 0;
        $data['nama_bank'] = 'BSI';
        // PERBAIKAN: cek nilai radio, bukan keberadaan field
        $data['is_continuing_education'] = $request->input('is_continuing_education') == 1;

        $data['posisi'] = $this->determinePosisi($request->divisi);

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $file = $request->file('foto_profil');
            $filename = time() . '_' . Str::slug($request->nama_lengkap) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto_profil', $filename, 'public');
            $data['foto_profil'] = $path;
        }

        $user->update($data);
        $user->refresh();

        return redirect()->route('profile.show')->with('success', 'Profile berhasil diupdate');
    }

    public function showChangePassword()
    {
        return view('profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->kata_sandi)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        $user->update([
            'kata_sandi' => Hash::make($request->password)
        ]);

        return redirect()->route('profile.show')->with('success', 'Password berhasil diupdate');
    }

    private function determinePosisi($divisi)
    {
        return trim($divisi) === 'HRD' ? 'hr' : 'karyawan';
    }

    /**
     * Menampilkan halaman achievement
     */
    public function achievement(Request $request)
    {
        $user = Auth::user();
        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));

        if ($user->isHr()) {
            // HR: tampilkan semua karyawan aktif dengan peringkat
            $karyawans = Karyawan::where('is_resigned', false)->get();
            $data = $karyawans->map(function ($karyawan) use ($month, $year) {
                return $this->getKaryawanAchievement($karyawan, $month, $year);
            })->sortByDesc('total_score')->values();

            // Pagination manual (10 per halaman)
            $perPage = 10;
            $currentPage = Paginator::resolveCurrentPage('page');
            $currentItems = $data->slice(($currentPage - 1) * $perPage, $perPage)->all();

            $paginatedData = new LengthAwarePaginator(
                $currentItems,
                $data->count(),
                $perPage,
                $currentPage,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                    'query' => $request->only(['month', 'year']) // penting! agar filter tetap terjaga
                ]
            );

            return view('profile.achievement', compact('paginatedData', 'user', 'month', 'year'));
        } else {
            // Karyawan biasa: hanya data sendiri
            $data = collect([$this->getKaryawanAchievement($user, $month, $year)]);
            return view('profile.achievement', compact('data', 'user', 'month', 'year'));
        }
    }

    /**
     * Ambil data achievement untuk satu karyawan
     */
    private function getKaryawanAchievement($karyawan, $month, $year)
    {
        // ---- SUNNAH ----
        $sunnah = SunnahDaily::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('status_approval', 'approved')
            ->get();
        $sunnahTotalPoin = $sunnah->sum('total_poin');
        $sunnahCount = $sunnah->count();
        $sunnahAvg = $sunnahCount > 0 ? round($sunnahTotalPoin / $sunnahCount, 1) : 0;

        // ---- FHL ----
        $fhlHadir = FhlAbsensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('status', 'hadir')
            ->count();
        $totalFridays = $this->countDayInMonth($month, $year, 5); // 5 = Jumat
        $fhlPercentage = $totalFridays > 0 ? round(($fhlHadir / $totalFridays) * 100, 1) : 0;

        // ---- KHATAMAN ----
        $khatamanHadir = KhatamanAbsensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('status', 'hadir')
            ->count();
        $totalThursdays = $this->countDayInMonth($month, $year, 4); // 4 = Kamis
        $khatamanPercentage = $totalThursdays > 0 ? round(($khatamanHadir / $totalThursdays) * 100, 1) : 0;

        // ---- SCORE GABUNGAN ----
        $score = ($sunnahAvg * 0.5) + ($fhlPercentage * 0.25) + ($khatamanPercentage * 0.25);

        return [
            'karyawan' => $karyawan,
            'sunnah' => [
                'total_poin' => $sunnahTotalPoin,
                'avg' => $sunnahAvg,
                'count' => $sunnahCount,
            ],
            'fhl' => [
                'hadir' => $fhlHadir,
                'total' => $totalFridays,
                'percentage' => $fhlPercentage,
            ],
            'khataman' => [
                'hadir' => $khatamanHadir,
                'total' => $totalThursdays,
                'percentage' => $khatamanPercentage,
            ],
            'total_score' => $score,
            'badges' => $this->getAchievementBadges($sunnahAvg, $fhlPercentage, $khatamanPercentage),
        ];
    }

    /**
     * Hitung jumlah hari tertentu dalam bulan
     */
    private function countDayInMonth($month, $year, $dayOfWeek)
    {
        $date = Carbon::create($year, $month, 1);
        $count = 0;
        while ($date->month == $month) {
            if ($date->dayOfWeekIso == $dayOfWeek) {
                $count++;
            }
            $date->addDay();
        }
        return $count;
    }

    /**
     * Tentukan badge berdasarkan kriteria
     */
    private function getAchievementBadges($sunnahAvg, $fhlPercent, $khatamanPercent)
    {
        $badges = [];

        // Sunnah
        if ($sunnahAvg >= 100) {
            $badges[] = ['name' => 'Sunnah Master', 'icon' => '🏆', 'level' => 'gold'];
        } elseif ($sunnahAvg >= 70) {
            $badges[] = ['name' => 'Sunnah Pro', 'icon' => '🥇', 'level' => 'silver'];
        } elseif ($sunnahAvg >= 40) {
            $badges[] = ['name' => 'Sunnah Learner', 'icon' => '🥈', 'level' => 'bronze'];
        }

        // FHL
        if ($fhlPercent >= 80) {
            $badges[] = ['name' => 'FHL Loyal', 'icon' => '🕌', 'level' => 'gold'];
        } elseif ($fhlPercent >= 60) {
            $badges[] = ['name' => 'FHL Regular', 'icon' => '🕌', 'level' => 'silver'];
        } elseif ($fhlPercent >= 40) {
            $badges[] = ['name' => 'FHL Beginner', 'icon' => '🕌', 'level' => 'bronze'];
        }

        // Khataman
        if ($khatamanPercent >= 80) {
            $badges[] = ['name' => 'Khataman Loyal', 'icon' => '📖', 'level' => 'gold'];
        } elseif ($khatamanPercent >= 60) {
            $badges[] = ['name' => 'Khataman Regular', 'icon' => '📖', 'level' => 'silver'];
        } elseif ($khatamanPercent >= 40) {
            $badges[] = ['name' => 'Khataman Beginner', 'icon' => '📖', 'level' => 'bronze'];
        }

        return $badges;
    }
}
