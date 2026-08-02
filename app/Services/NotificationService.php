<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\FhlAbsensi;
use App\Models\FhlKode;
use App\Models\Karyawan;
use App\Models\KhatamanAbsensi;
use App\Models\KhatamanKode;
use App\Models\Pengumuman;
use App\Models\PerjalananDinas;
use App\Models\SunnahDaily;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * ==========================================================
 * NOTIFICATION SERVICE
 * ==========================================================
 * Notifikasi di aplikasi ini SENGAJA tidak memakai tabel tersendiri
 * (tanpa migration baru) -- melainkan dirakit "on the fly" dari data yang
 * SUDAH ADA di tabel-tabel terkait (Cuti, Perjalanan Dinas, 7SPS/Sunnah,
 * Pengumuman, Absensi, kode FHL/Khataman, dan profil karyawan), lalu
 * digabung & diurutkan berdasarkan waktu kejadian terbaru.
 *
 * Status "sudah dibaca" ditandai lewat CACHE (bukan kolom database):
 * key `notif_last_seen_{karyawan_id}` menyimpan waktu terakhir user
 * membuka dropdown/halaman notifikasi. Item yang lebih baru dari
 * timestamp itu dianggap "baru" (badge merah di navbar).
 *
 * CATATAN: "Pamer Suku" dan "English Today" adalah aplikasi terpisah yang
 * di-hosting di domain lain (lihat sidebar) dan tidak mengirim data apa pun
 * ke aplikasi ini, sehingga TIDAK BISA dibuatkan notifikasi otomatis dari
 * sini kecuali suatu saat disediakan API/webhook dari sistem tersebut.
 *
 * Menambah sumber notifikasi baru cukup dengan menambah method builder di
 * bawah lalu memanggilnya dari getAll() -- tanpa perlu migration baru
 * selama datanya sudah tersedia di tabel yang ada.
 */
class NotificationService
{
    /** Berapa hari ke belakang data notifikasi diambil (supaya query tetap ringan). */
    private int $lookbackDays = 60;

    /**
     * Ambil seluruh notifikasi milik user, sudah terurut dari yang terbaru.
     */
    public function getAll(Karyawan $user): Collection
    {
        $since = Carbon::now()->subDays($this->lookbackDays);
        $isHr = $user->posisi === 'hr';

        $items = collect();
        $items = $items->merge($this->pengumuman($user, $since));

        if ($isHr) {
            $items = $items->merge($this->hrCutiPending($since));
            $items = $items->merge($this->hrDinasPending($since));
            $items = $items->merge($this->hrSunnahPending($since));
            $items = $items->merge($this->hrAbsensiMencurigakan($since));
        } else {
            $items = $items->merge($this->karyawanCutiStatus($user, $since));
            $items = $items->merge($this->karyawanDinasStatus($user, $since));
            $items = $items->merge($this->karyawanSunnahStatus($user, $since));
            $items = $items->merge($this->karyawanProfileUpdate($user, $since));
            $items = $items->merge($this->karyawanFhlReminder($user));
            $items = $items->merge($this->karyawanKhatamanReminder($user));
        }

        return $items->sortByDesc(fn ($item) => $item['created_at'])->values();
    }

    /**
     * Daftar tipe notifikasi yang tersedia untuk user ini (dipakai untuk dropdown filter).
     */
    public function availableTypes(Karyawan $user): array
    {
        if ($user->posisi === 'hr') {
            return [
                'pengumuman' => 'Pengumuman',
                'cuti' => 'Pengajuan Cuti',
                'dinas' => 'Perjalanan Dinas',
                'sunnah' => '7SPS',
                'absensi' => 'Absensi Mencurigakan',
            ];
        }

        return [
            'pengumuman' => 'Pengumuman',
            'cuti' => 'Status Cuti',
            'dinas' => 'Status Perjalanan Dinas',
            'sunnah' => 'Status 7SPS',
            'profile' => 'Profil',
            'fhl' => 'FHL',
            'khataman' => 'Khataman',
        ];
    }

    /**
     * Waktu terakhir user membuka notifikasi (dari cache). Null kalau belum pernah.
     */
    public function lastSeen(Karyawan $user): ?Carbon
    {
        $value = Cache::get($this->cacheKey($user));

        return $value ? Carbon::parse($value) : null;
    }

    /**
     * Tandai notifikasi sudah dilihat (update cache), dipakai saat dropdown/halaman dibuka.
     */
    public function markSeen(Karyawan $user): void
    {
        Cache::put($this->cacheKey($user), Carbon::now()->toDateTimeString(), Carbon::now()->addDays(30));
    }

    private function cacheKey(Karyawan $user): string
    {
        return 'notif_last_seen_' . $user->id;
    }

    /**
     * Bentuk satu item notifikasi dalam format array yang seragam.
     */
    private function item(string $id, string $type, string $title, string $message, string $color, string $url, Carbon $createdAt): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'color' => $color,
            'url' => $url,
            'created_at' => $createdAt,
        ];
    }

    // ==========================================================
    // BUILDER PER SUMBER DATA
    // ==========================================================

    /**
     * Pengumuman yang relevan untuk role user (target: semua / hr / karyawan).
     */
    private function pengumuman(Karyawan $user, Carbon $since): Collection
    {
        $isHr = $user->posisi === 'hr';
        $targets = $isHr ? ['semua', 'hr'] : ['semua', 'karyawan'];

        return Pengumuman::whereIn('target', $targets)
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($p) use ($isHr) {
                return $this->item(
                    'pengumuman-' . $p->id,
                    'pengumuman',
                    'Pengumuman Baru',
                    $p->judul,
                    'blue',
                    // Karyawan belum punya halaman detail pengumuman tersendiri,
                    // jadi diarahkan ke dashboard (menampilkan pengumuman terbaru).
                    $isHr ? route('hr.pengumuman.show', $p->id) : route('karyawan.dashboard'),
                    $p->created_at,
                );
            });
    }

    /**
     * [HR] Pengajuan cuti baru yang masih menunggu persetujuan.
     */
    private function hrCutiPending(Carbon $since): Collection
    {
        return Cuti::pengajuan()
            ->with('karyawan')
            ->where('status', 'pending')
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($c) {
                $nama = $c->karyawan->nama_lengkap ?? 'Karyawan';

                return $this->item(
                    'cuti-pending-' . $c->id,
                    'cuti',
                    'Pengajuan Cuti Baru',
                    $nama . ' mengajukan ' . ($c->jenis_cuti ?? 'cuti') . ' (' . $c->durasi . ' hari), menunggu persetujuan.',
                    'amber',
                    route('hr.cuti.show', $c->id),
                    $c->created_at,
                );
            });
    }

    /**
     * [HR] Pengajuan perjalanan dinas baru yang masih menunggu persetujuan.
     */
    private function hrDinasPending(Carbon $since): Collection
    {
        return PerjalananDinas::pending()
            ->with('karyawan')
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($d) {
                $nama = $d->karyawan->nama_lengkap ?? 'Karyawan';

                return $this->item(
                    'dinas-pending-' . $d->id,
                    'dinas',
                    'Pengajuan Perjalanan Dinas Baru',
                    $nama . ' mengajukan perjalanan dinas: ' . $d->judul,
                    'violet',
                    route('hr.perjalanan-dinas.show', $d->id),
                    $d->created_at,
                );
            });
    }

    /**
     * [HR] Rekap 7SPS harian yang masih menunggu approval.
     */
    private function hrSunnahPending(Carbon $since): Collection
    {
        return SunnahDaily::with('karyawan')
            ->where('status_approval', 'pending')
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($s) {
                $nama = $s->karyawan->nama_lengkap ?? 'Karyawan';
                $tanggal = $s->tanggal ? $s->tanggal->format('d-m-Y') : '-';

                return $this->item(
                    'sunnah-pending-' . $s->id,
                    'sunnah',
                    'Rekap 7SPS Menunggu Persetujuan',
                    $nama . ' mengisi checklist 7SPS tanggal ' . $tanggal . '.',
                    'teal',
                    route('hr.sunnah.detail', $s->id),
                    $s->created_at,
                );
            });
    }

    /**
     * [HR] Absensi yang terindikasi mencurigakan (fake GPS / lokasi tidak wajar).
     */
    private function hrAbsensiMencurigakan(Carbon $since): Collection
    {
        return Absensi::with('karyawan')
            ->where('is_suspicious', true)
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($a) {
                $nama = $a->karyawan->nama_lengkap ?? 'Karyawan';
                $tanggal = $a->tanggal ? $a->tanggal->format('d-m-Y') : '-';

                return $this->item(
                    'absensi-mencurigakan-' . $a->id,
                    'absensi',
                    'Absensi Mencurigakan',
                    $nama . ' (' . $tanggal . ') — ' . ($a->suspicious_reason ?? 'terindikasi lokasi tidak wajar'),
                    'rose',
                    route('hr.absensi.detail', $a->id),
                    $a->created_at,
                );
            });
    }

    /**
     * [Karyawan] Status pengajuan cuti milik sendiri yang baru saja disetujui/ditolak.
     */
    private function karyawanCutiStatus(Karyawan $user, Carbon $since): Collection
    {
        return Cuti::pengajuan()
            ->where('karyawan_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '>=', $since)
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->map(function ($c) {
                $approved = $c->status === 'approved';
                $periode = ($c->tanggal_mulai ? $c->tanggal_mulai->format('d-m-Y') : '-') . ' s/d ' . ($c->tanggal_selesai ? $c->tanggal_selesai->format('d-m-Y') : '-');

                return $this->item(
                    'cuti-status-' . $c->id . '-' . $c->status,
                    'cuti',
                    $approved ? 'Cuti Disetujui' : 'Cuti Ditolak',
                    'Pengajuan ' . ($c->jenis_cuti ?? 'cuti') . ' (' . $periode . ') telah ' . ($approved ? 'disetujui HR.' : 'ditolak HR.'),
                    $approved ? 'emerald' : 'rose',
                    route('karyawan.cuti.dashboard'),
                    $c->updated_at,
                );
            });
    }

    /**
     * [Karyawan] Status pengajuan perjalanan dinas milik sendiri.
     */
    private function karyawanDinasStatus(Karyawan $user, Carbon $since): Collection
    {
        return PerjalananDinas::where('karyawan_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '>=', $since)
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->map(function ($d) {
                $approved = $d->status === 'approved';

                return $this->item(
                    'dinas-status-' . $d->id . '-' . $d->status,
                    'dinas',
                    $approved ? 'Perjalanan Dinas Disetujui' : 'Perjalanan Dinas Ditolak',
                    'Pengajuan "' . $d->judul . '" telah ' . ($approved ? 'disetujui HR.' : 'ditolak HR.'),
                    $approved ? 'emerald' : 'rose',
                    route('karyawan.perjalanan-dinas.show', $d->id),
                    $d->updated_at,
                );
            });
    }

    /**
     * [Karyawan] Status rekap 7SPS milik sendiri.
     */
    private function karyawanSunnahStatus(Karyawan $user, Carbon $since): Collection
    {
        return SunnahDaily::where('karyawan_id', $user->id)
            ->whereIn('status_approval', ['approved', 'rejected'])
            ->where('updated_at', '>=', $since)
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->map(function ($s) {
                $approved = $s->status_approval === 'approved';
                $tanggal = $s->tanggal ? $s->tanggal->format('d-m-Y') : '-';

                return $this->item(
                    'sunnah-status-' . $s->id . '-' . $s->status_approval,
                    'sunnah',
                    $approved ? '7SPS Disetujui' : '7SPS Ditolak',
                    'Rekap 7SPS tanggal ' . $tanggal . ' telah ' . ($approved ? 'disetujui HR.' : 'ditolak HR.'),
                    $approved ? 'emerald' : 'rose',
                    route('karyawan.sunnah.dashboard'),
                    $s->updated_at,
                );
            });
    }

    /**
     * [Karyawan] Reminder ringan kalau data profil sendiri baru saja diperbarui
     * (dibandingkan dari updated_at vs created_at, tanpa perlu kolom/log tambahan).
     */
    private function karyawanProfileUpdate(Karyawan $user, Carbon $since): Collection
    {
        if (!$user->updated_at || !$user->created_at) {
            return collect();
        }

        if ($user->updated_at->lte($user->created_at) || $user->updated_at->lt($since)) {
            return collect();
        }

        return collect([
            $this->item(
                'profile-update-' . $user->id . '-' . $user->updated_at->timestamp,
                'profile',
                'Profil Diperbarui',
                'Data profil Anda baru saja diperbarui. Silakan periksa kembali datanya.',
                'sky',
                route('profile.show'),
                $user->updated_at,
            ),
        ]);
    }

    /**
     * [Karyawan] Reminder kalau hari ini hari aktif FHL, kode sudah tersedia,
     * tapi karyawan belum check-in.
     */
    private function karyawanFhlReminder(Karyawan $user): Collection
    {
        if (!FhlAbsensi::isFriday()) {
            return collect();
        }

        $today = Carbon::today();

        if (!FhlKode::hasKodeForDate($today) || FhlAbsensi::hasCheckedInToday($user->id)) {
            return collect();
        }

        return collect([
            $this->item(
                'fhl-reminder-' . $today->toDateString(),
                'fhl',
                'Kode FHL Hari Ini Sudah Tersedia',
                'Jangan lupa check-in FHL hari ini sebelum berakhir.',
                'indigo',
                route('karyawan.fhl.dashboard'),
                Carbon::now(),
            ),
        ]);
    }

    /**
     * [Karyawan] Reminder kalau hari ini hari aktif Khataman, kode sudah tersedia,
     * tapi karyawan belum check-in.
     */
    private function karyawanKhatamanReminder(Karyawan $user): Collection
    {
        if (!KhatamanAbsensi::isActiveDay()) {
            return collect();
        }

        $today = Carbon::today();

        if (!KhatamanKode::hasKodeForDate($today) || KhatamanAbsensi::hasCheckedInToday($user->id)) {
            return collect();
        }

        return collect([
            $this->item(
                'khataman-reminder-' . $today->toDateString(),
                'khataman',
                'Kode Khataman Hari Ini Sudah Tersedia',
                'Jangan lupa check-in Khataman hari ini sebelum berakhir.',
                'cyan',
                route('karyawan.khataman.dashboard'),
                Carbon::now(),
            ),
        ]);
    }
}
