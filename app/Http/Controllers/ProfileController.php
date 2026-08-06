<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\SunnahDaily;
use App\Models\FhlAbsensi;
use App\Models\KhatamanAbsensi;
use App\Services\EnglishTodayService;
use App\Services\PamerSukuService;
use App\Services\NotificationService;
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
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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
            'cropped_image' => 'nullable|string',
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

        // Ambil semua data kecuali foto_profil dan cropped_image
        $data = $request->except(['foto_profil', 'cropped_image', '_token', '_method']);

        $data['jumlah_anak'] = $data['jumlah_anak'] ?? 0;
        $data['nama_bank'] = 'BSI';
        $data['is_continuing_education'] = $request->input('is_continuing_education') == 1;
        $data['posisi'] = $this->determinePosisi($request->divisi);

        // ========== HANDLE FOTO PROFIL ==========
        $fotoSaved = false;

        // PRIORITAS 1: Cek apakah ada cropped_image (dari modal crop)
        if ($request->filled('cropped_image')) {
            try {
                // Decode base64 image
                $imageData = $request->cropped_image;

                // Cek apakah formatnya valid (data:image/jpeg;base64,...)
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $imageType = strtolower($type[1]); // jpg, jpeg, png, etc

                    // Decode base64
                    $imageData = base64_decode($imageData);

                    if ($imageData === false) {
                        throw new \Exception('Gagal decode base64 image');
                    }

                    // Generate nama file unik
                    $filename = 'foto_profil/' . time() . '_' . Str::slug($request->nama_lengkap) . '.' . $imageType;

                    // Hapus foto lama jika ada
                    if ($user->foto_profil) {
                        Storage::disk('public')->delete($user->foto_profil);
                    }

                    // Simpan ke storage
                    Storage::disk('public')->put($filename, $imageData);

                    $data['foto_profil'] = $filename;
                    $fotoSaved = true;
                }
            } catch (\Exception $e) {
                return back()->withErrors(['cropped_image' => 'Gagal menyimpan foto: ' . $e->getMessage()]);
            }
        }

        // PRIORITAS 2: Jika tidak ada cropped_image, cek upload file biasa
        if (!$fotoSaved && $request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }

            $file = $request->file('foto_profil');
            $filename = time() . '_' . Str::slug($request->nama_lengkap) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto_profil', $filename, 'public');
            $data['foto_profil'] = $path;
            $fotoSaved = true;
        }

        // Update data user
        $user->update($data);
        $user->refresh();

        /**
         * ==========================================================
         * NOTIFIKASI OTOMATIS KE HRD
         * ==========================================================
         * Saat karyawan mengupdate profil, kita panggil method
         * notifyHrAboutProfileUpdate() untuk mengirim notifikasi
         * ke semua HRD. Ini akan memastikan HRD mendapat notifikasi
         * real-time di dropdown notifikasi mereka.
         */
        $this->notifyHrAboutProfileUpdate($user);

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
            'kata_sandi' => Hash::make($request->password),
        ]);

        // Update updated_at untuk trigger notifikasi
        $user->touch();

        return redirect()->route('profile.show')->with('success', 'Password berhasil diupdate');
    }

    private function determinePosisi($divisi)
    {
        return trim($divisi) === 'HRD' ? 'hr' : 'karyawan';
    }

    /**
     * ==========================================================
     * NOTIFIKASI KE HRD SAAT PROFIL KARYAWAN DIUPDATE
     * ==========================================================
     * Method ini akan mengirim notifikasi ke semua HRD
     * bahwa ada karyawan yang mengupdate profilnya.
     *
     * Cara kerja:
     * 1. Cari semua HRD (posisi = 'hr')
     * 2. Untuk setiap HRD, update cache last_seen mereka
     *    agar notifikasi muncul sebagai "baru"
     * 3. Notifikasi akan otomatis terdeteksi oleh NotificationService
     *    karena method hrProfileUpdates() akan mendeteksi perubahan
     *    pada tabel karyawans
     */
    private function notifyHrAboutProfileUpdate(Karyawan $karyawan)
    {
        try {
            // Cari semua HRD
            $hrs = Karyawan::where('posisi', 'hr')
                ->where('is_resigned', false)
                ->get();

            if ($hrs->isEmpty()) {
                return;
            }

            /**
             * Untuk setiap HRD, kita reset last_seen mereka
             * agar notifikasi muncul sebagai "baru" di dropdown
             *
             * Ini penting karena NotificationService menggunakan
             * cache untuk menentukan mana notifikasi yang sudah dibaca.
             */
            foreach ($hrs as $hr) {
                // Set last_seen ke waktu sebelum update
                // agar notifikasi ini dianggap "baru"
                $this->notificationService->markSeen($hr);

                // Lalu set ulang last_seen ke waktu sebelum update
                // sehingga notifikasi ini muncul sebagai "baru"
                // Ini dilakukan dengan menggeser cache ke belakang
                cache()->put(
                    'notif_last_seen_' . $hr->id,
                    Carbon::now()->subSeconds(5)->toDateTimeString(),
                    Carbon::now()->addDays(30)
                );
            }

            /**
             * Catatan Penting:
             * Sebenarnya notifikasi akan otomatis terdeteksi oleh
             * NotificationService@hrProfileUpdates() karena method itu
             * akan mengambil semua karyawan yang updated_at-nya berubah.
             *
             * Jadi sebenarnya kita tidak perlu melakukan apa-apa di sini
             * selain memastikan updated_at berubah (sudah otomatis dari
             * $user->update($data)).
             *
             * Namun untuk memastikan notifikasi muncul sebagai "baru"
             * di dropdown HRD, kita reset cache last_seen mereka.
             *
             * Alternatif: kita bisa langsung menggunakan
             * NotificationService@markSeen dengan timestamp yang tepat.
             */
        } catch (\Exception $e) {
            // Log error jika terjadi masalah
            // Tapi jangan sampai mengganggu proses update profil
            \Log::error('Gagal mengirim notifikasi HRD: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman achievement
     */
    public function achievement(Request $request, EnglishTodayService $englishToday, PamerSukuService $pamerSuku)
    {
        $user = Auth::user();
        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));

        // Ambil skor dari SEMUA quiz English Today yang ada (bukan quiz tertentu saja),
        // jadi kalau nanti ada quiz baru (quiz 3, 4, dst) otomatis ikut kehitung
        // tanpa perlu ubah kode.
        $englishScores = $englishToday->getScoresByEmail();
        $englishSummary = $englishToday->getOverallSummary();

        $videoSubmissions = $englishToday->getVideoSubmissionsByEmail();
        $videoSummary = $englishToday->getVideoChallengeSummary();

        // Pamer Suku: rekap performa leaderboard lintas semua volume + siapa
        // yang jadi kepala suku bulan ini (global & PKA)
        $pamerSukuSummary = $pamerSuku->getLeaderboardSummaryByKodePegawai();
        $kepalaSukuGlobal = $this->resolveKepalaSuku($pamerSuku->getCurrentKepalaSuku('global'));
        $kepalaSukuPka = $this->resolveKepalaSuku($pamerSuku->getCurrentKepalaSuku('pka'));
        $pamerSukuTotalVolumes = count($pamerSuku->getVolumes());

        if ($user->isHr()) {
            // HR: tampilkan semua karyawan aktif dengan peringkat
            $karyawans = Karyawan::where('is_resigned', false)->get();
            $data = $karyawans
                ->map(function ($karyawan) use ($month, $year, $englishScores, $videoSubmissions, $pamerSukuSummary, $kepalaSukuGlobal, $kepalaSukuPka) {
                    $achievement = $this->getKaryawanAchievement($karyawan, $month, $year);
                    $achievement['english_today'] = $this->matchEnglishTodayScore($karyawan, $englishScores);
                    $achievement['video_challenges'] = $this->matchVideoSubmissions($karyawan, $videoSubmissions);
                    $achievement['pamer_suku'] = $this->matchPamerSuku($karyawan, $pamerSukuSummary);
                    $achievement['is_kepala_suku_global'] = $this->isSameKepalaSuku($karyawan, $kepalaSukuGlobal);
                    $achievement['is_kepala_suku_pka'] = $this->isSameKepalaSuku($karyawan, $kepalaSukuPka);
                    return $achievement;
                })
                ->sortByDesc('total_score')
                ->values();

            // Pagination manual (10 per halaman)
            $perPage = 10;
            $currentPage = Paginator::resolveCurrentPage('page');
            $currentItems = $data->slice(($currentPage - 1) * $perPage, $perPage)->all();

            $paginatedData = new LengthAwarePaginator($currentItems, $data->count(), $perPage, $currentPage, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
                'query' => $request->only(['month', 'year']), // penting! agar filter tetap terjaga
            ]);

            return view('profile.achievement', compact('paginatedData', 'user', 'month', 'year', 'englishSummary', 'videoSummary', 'kepalaSukuGlobal', 'kepalaSukuPka', 'pamerSukuTotalVolumes'));
        } else {
            // Karyawan biasa: hanya data sendiri
            $achievement = $this->getKaryawanAchievement($user, $month, $year);
            $achievement['english_today'] = $this->matchEnglishTodayScore($user, $englishScores);
            $achievement['video_challenges'] = $this->matchVideoSubmissions($user, $videoSubmissions);
            $achievement['pamer_suku'] = $this->matchPamerSuku($user, $pamerSukuSummary);
            $achievement['is_kepala_suku_global'] = $this->isSameKepalaSuku($user, $kepalaSukuGlobal);
            $achievement['is_kepala_suku_pka'] = $this->isSameKepalaSuku($user, $kepalaSukuPka);
            $data = collect([$achievement]);
            return view('profile.achievement', compact('data', 'user', 'month', 'year', 'englishSummary', 'videoSummary', 'kepalaSukuGlobal', 'kepalaSukuPka', 'pamerSukuTotalVolumes'));
        }
    }

    /**
     * Cocokkan data karyawan lokal dengan hasil quiz English Today berdasarkan email.
     * Return null kalau karyawan belum pernah mengerjakan quiz apapun.
     */
    private function matchEnglishTodayScore($karyawan, array $englishScores)
    {
        $email = strtolower(trim($karyawan->email ?? ''));
        if (!$email) {
            return null;
        }

        $result = $englishScores[$email] ?? null;

        if ($result) {
            // Badge dihitung dari rata-rata skor lintas semua quiz yang sudah diikuti
            $result['badge'] = $this->getEnglishTodayBadge($result['average_score']);
        }

        return $result;
    }

    /**
     * Cocokkan data karyawan lokal dengan submission video challenge berdasarkan email.
     * Return array kosong kalau karyawan belum pernah submit video challenge apapun.
     */
    private function matchVideoSubmissions($karyawan, array $videoSubmissions): array
    {
        $email = strtolower(trim($karyawan->email ?? ''));
        if (!$email) {
            return [];
        }

        return $videoSubmissions[$email] ?? [];
    }

    /**
     * Cocokkan data karyawan lokal dengan rekap leaderboard Pamer Suku
     * berdasarkan kode_pegawai. Return null kalau belum pernah masuk
     * leaderboard sama sekali (global maupun PKA).
     */
    private function matchPamerSuku($karyawan, array $pamerSukuSummary)
    {
        $kode = trim($karyawan->kode_pegawai ?? '');
        if (!$kode) {
            return null;
        }

        return $pamerSukuSummary[$kode] ?? null;
    }

    /**
     * Tambahkan info karyawan lokal (nama, foto) ke data kepala suku dari API,
     * kalau kode_pegawai-nya berhasil ditemukan di tabel karyawans.
     * Tetap dikembalikan apa adanya kalau kode_pegawai kosong/tidak match,
     * supaya flyer-nya minimal tetap bisa ditampilkan.
     */
    private function resolveKepalaSuku(?array $entry): ?array
    {
        if (!$entry) {
            return null;
        }

        $kode = trim($entry['kode_pegawai'] ?? '');
        if ($kode) {
            $karyawan = Karyawan::where('kode_pegawai', $kode)->first();
            if ($karyawan) {
                $entry['resolved_name'] = $karyawan->nama_lengkap ?? ($entry['player_name'] ?? null);
                $entry['resolved_foto'] = $karyawan->foto_profil ?? null;
                $entry['resolved_divisi'] = $karyawan->divisi ?? ($entry['division'] ?? null);
            }
        }

        return $entry;
    }

    /**
     * Cek apakah satu karyawan adalah kepala suku (global/PKA) bulan ini,
     * dicocokkan lewat kode_pegawai.
     */
    private function isSameKepalaSuku($karyawan, ?array $kepalaSukuEntry): bool
    {
        if (!$kepalaSukuEntry) {
            return false;
        }

        $entryKode = trim($kepalaSukuEntry['kode_pegawai'] ?? '');
        $karyawanKode = trim($karyawan->kode_pegawai ?? '');

        return $entryKode !== '' && $karyawanKode !== '' && strcasecmp($entryKode, $karyawanKode) === 0;
    }

    /**
     * Tentukan badge English Today berdasarkan skor (0-100).
     * Silakan sesuaikan ambang batasnya sesuai kebutuhan.
     */
    private function getEnglishTodayBadge($score)
    {
        if ($score >= 90) {
            return ['name' => 'English Master', 'icon' => '🌟', 'level' => 'gold'];
        } elseif ($score >= 70) {
            return ['name' => 'English Pro', 'icon' => '🗣️', 'level' => 'silver'];
        } elseif ($score >= 50) {
            return ['name' => 'English Learner', 'icon' => '📚', 'level' => 'bronze'];
        }

        return null;
    }

    /**
     * Ambil data achievement untuk satu karyawan
     */
    private function getKaryawanAchievement($karyawan, $month, $year)
    {
        // ---- SUNNAH ----
        $sunnah = SunnahDaily::where('karyawan_id', $karyawan->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->where('status_approval', 'approved')->get();
        $sunnahTotalPoin = $sunnah->sum('total_poin');
        $sunnahCount = $sunnah->count();
        $sunnahAvg = $sunnahCount > 0 ? round($sunnahTotalPoin / $sunnahCount, 1) : 0;

        // ---- FHL ----
        $fhlHadir = FhlAbsensi::where('karyawan_id', $karyawan->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->where('status', 'hadir')->count();
        $totalFridays = $this->countDayInMonth($month, $year, 5); // 5 = Jumat
        $fhlPercentage = $totalFridays > 0 ? round(($fhlHadir / $totalFridays) * 100, 1) : 0;

        // ---- KHATAMAN ----
        $khatamanHadir = KhatamanAbsensi::where('karyawan_id', $karyawan->id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->where('status', 'hadir')->count();
        $totalThursdays = $this->countDayInMonth($month, $year, 4); // 4 = Kamis
        $khatamanPercentage = $totalThursdays > 0 ? round(($khatamanHadir / $totalThursdays) * 100, 1) : 0;

        // ---- SCORE GABUNGAN ----
        $score = $sunnahAvg * 0.5 + $fhlPercentage * 0.25 + $khatamanPercentage * 0.25;

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
