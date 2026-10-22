<?php
// app/Models/Karyawan.php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Karyawan extends Authenticatable
{
    use Notifiable;

    protected $table = 'karyawans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_pegawai',
        'email',
        'kata_sandi',
        'nama_lengkap',
        'posisi',
        'jabatan',
        'divisi',
        'golongan_darah',
        'no_kk',
        'jumlah_anak',
        'foto_profil',
        'nomor_telepon',
        'no_wa',
        'alamat',
        'tanggal_bergabung',
        'end_date',
        'status',
        'nik',
        'npwp',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'status_pernikahan',
        'pendidikan_terakhir',
        'perguruan_tinggi',
        'jurusan',
        'tahun_lulus',
        'nama_ibu_kandung',
        'nama_kontak_darurat',
        'telepon_kontak_darurat',
        'tanggal_pengangkatan_tetap',
        'nomor_rekening',
        'nama_bank',
        'ipk_terakhir',
        'alamat_domisili',
        'is_resigned',
        'tanggal_resign',
        'suspend_start',
        'suspend_end',
        'suspend_reason',
        'suspended_by',
        'violation_count',
        'banned_until',
        'is_banned_permanent',
        'ban_message',
        'is_continuing_education',
        'continuing_program',
        'continuing_perguruan_tinggi',
        'continuing_jurusan',
    ];

    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    /** Memo hak akses menu per instance (diisi lazy oleh disabledMenuKeys()). */
    private ?array $disabledMenuKeysCache = null;

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    public function isSuperAdmin()
    {
        return $this->posisi === 'superadmin';
    }

    public function isHr()
    {
        return $this->posisi === 'hr';
    }

    public function isKaryawan()
    {
        return $this->posisi === 'karyawan';
    }

    public function isAdminOrHr()
    {
        return in_array($this->posisi, ['superadmin', 'hr']);
    }

    public function canLogin()
    {
        return !$this->is_resigned;
    }

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'tanggal_lahir' => 'date',
        'end_date' => 'date',
        'tanggal_pengangkatan_tetap' => 'date',
        'tanggal_resign' => 'date',
        'suspend_start' => 'date',
        'suspend_end' => 'date',
        'is_resigned' => 'boolean',
        'violation_count' => 'integer',
        'banned_until' => 'datetime',
        'is_banned_permanent' => 'boolean',
        'is_continuing_education' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getStatusLabelAttribute()
    {
        if ($this->is_resigned) {
            return 'Resign';
        }
        $labels = [
            'Karyawan Tetap' => 'Karyawan Tetap',
            'Contract' => 'Kontrak',
            'Internship' => 'Magang'
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->is_resigned) {
            return 'bg-[#ec1d1d] text-white';
        }
        $colors = [
            'Karyawan Tetap' => 'bg-[#2E7D3E] text-white',
            'Contract' => 'bg-[#FCC626] text-[#1B1B1B]',
            'Internship' => 'bg-[#00a2e9] text-white'
        ];
        return $colors[$this->status] ?? 'bg-gray-500 text-white';
    }

    public function getNamaBankAttribute($value)
    {
        return $value ?: 'BSI';
    }

    public function scopeActive($query)
    {
        return $query->where('is_resigned', false);
    }

    public function scopeResigned($query)
    {
        return $query->where('is_resigned', true);
    }

    // ==========================================================
    // ANTI REMOTE ACCESS: BLOKIR PRESENSI (BAN)
    // ==========================================================

    public function violations()
    {
        return $this->hasMany(RemoteAccessViolation::class, 'karyawan_id');
    }

    /**
     * Status blokir presensi akun saat ini.
     * Ban sementara yang masa berlakunya sudah lewat dibersihkan otomatis (auto-expiry).
     */
    public function isBanned(): bool
    {
        if ($this->is_banned_permanent) {
            return true;
        }

        if ($this->banned_until) {
            if ($this->banned_until->isFuture()) {
                return true;
            }

            // Masa blokir sementara sudah berakhir → hapus otomatis
            $this->banned_until = null;
            $this->saveQuietly();
        }

        return false;
    }

    /**
     * Sisa hari blokir sementara (null kalau permanen / tidak sedang diblokir).
     */
    public function banDaysRemaining(): ?int
    {
        if ($this->is_banned_permanent || !$this->banned_until) {
            return null;
        }

        $secondsLeft = Carbon::now()->diffInSeconds($this->banned_until, false);

        return $secondsLeft > 0 ? (int) ceil($secondsLeft / 86400) : 0;
    }

    /**
     * Terapkan blokir presensi sesuai tangga pelanggaran:
     * Pelanggaran ke-1 → 1 hari, ke-2 → 2 hari, ke-3 (dan seterusnya) → PERMANEN.
     *
     * @return int Nomor strike yang diterapkan (1, 2, atau 3+).
     */
    public function applyBan(): int
    {
        $strike = (int) $this->violation_count + 1;
        $this->violation_count = $strike;

        if ($strike >= 3) {
            $this->is_banned_permanent = true;
            $this->banned_until = null;
        } else {
            $this->is_banned_permanent = false;
            $this->banned_until = Carbon::now()->addDays($strike);
        }

        $this->save();

        return $strike;
    }

    /**
     * Lepas blokir & reset hitungan pelanggaran ke 0 (hanya dipanggil Superadmin).
     */
    public function liftBan(): void
    {
        $this->is_banned_permanent = false;
        $this->banned_until = null;
        $this->violation_count = 0;
        $this->ban_message = null;
        $this->save();
    }

    /**
     * Cabut satu strike ketika pelanggaran yang sudah terkonfirmasi ditinjau ulang
     * dan dinyatakan false positive oleh Superadmin.
     */
    public function revokeStrike(): void
    {
        $this->violation_count = max(0, (int) $this->violation_count - 1);

        if ($this->violation_count < 3) {
            $this->is_banned_permanent = false;
        }

        // Blokir aktif yang disebabkan strike ini dilepas
        $this->banned_until = null;
        $this->save();
    }

    // ==========================================================
    // SUSPEND AKUN (PENANGGUHAN LOGIN SAMPAI TANGGAL TERTENTU)
    // ==========================================================

    /**
     * Status suspend akun: hari ini berada di rentang suspend_start..suspend_end.
     * Masa suspend yang sudah lewat dibersihkan otomatis (auto-expiry),
     * sehingga akun kembali bisa login tanpa perlu aksi Superadmin.
     */
    public function isSuspended(): bool
    {
        if (!$this->suspend_start || !$this->suspend_end) {
            return false;
        }

        $today = now()->startOfDay();

        // Masa suspend sudah berakhir → bersihkan otomatis
        if ($this->suspend_end->copy()->startOfDay()->lt($today)) {
            $this->forceFill([
                'suspend_start' => null,
                'suspend_end' => null,
                'suspend_reason' => null,
                'suspended_by' => null,
            ])->saveQuietly();

            return false;
        }

        // Rentang suspend belum dimulai → akun masih bebas
        return $today->betweenIncluded(
            $this->suspend_start->copy()->startOfDay(),
            $this->suspend_end->copy()->startOfDay()
        );
    }

    /**
     * Sisa hari penangguhan (0 bila tidak sedang disuspend).
     */
    public function suspensionDaysRemaining(): int
    {
        if (!$this->suspend_end || !$this->isSuspended()) {
            return 0;
        }

        return (int) max(0, now()->startOfDay()->diffInDays($this->suspend_end->copy()->startOfDay(), false));
    }

    /**
     * Teks alasan suspend yang aman ditampilkan (null → teks default).
     */
    public function suspensionReasonLabel(): string
    {
        return $this->suspend_reason ?: 'Tidak ada alasan yang dicantumkan.';
    }

    // ==========================================================
    // HAK AKSES MENU PER KARYAWAN
    // ==========================================================

    public function menuDisabled()
    {
        return $this->hasMany(KaryawanMenuDisabled::class, 'karyawan_id');
    }

    /**
     * Daftar menu_key yang dinonaktifkan untuk karyawan ini saat ini
     * (hanya pembatasan yang sedang berlaku pada rentang tanggalnya).
     * Pembatasan yang sudah kedaluwarsa dibersihkan otomatis (auto-expiry),
     * sehingga menu kembali aktif tanpa perlu aksi Superadmin.
     */
    public function disabledMenuKeys(): array
    {
        if ($this->disabledMenuKeysCache === null) {
            $today = now()->toDateString();

            // Bersihkan pembatasan menu yang sudah kedaluwarsa
            KaryawanMenuDisabled::where('karyawan_id', $this->id)
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', '<', $today)
                ->delete();

            if ($this->relationLoaded('menuDisabled')) {
                // Rapikan koleksi ter-load: buang baris kedaluwarsa, saring yang berlaku
                $this->setRelation(
                    'menuDisabled',
                    $this->menuDisabled
                        ->reject(fn ($row) => $row->valid_until && $row->valid_until->copy()->startOfDay()->lt(\Carbon\Carbon::today()))
                        ->values()
                );

                $this->disabledMenuKeysCache = $this->menuDisabled
                    ->filter(fn ($row) => method_exists($row, 'isActiveNow') ? $row->isActiveNow() : true)
                    ->pluck('menu_key')
                    ->all();
            } else {
                $this->disabledMenuKeysCache = $this->activeMenuRestrictions()->pluck('menu_key')->all();
            }
        }

        return $this->disabledMenuKeysCache;
    }

    /**
     * Koleksi pembatasan menu yang SEDANG berlaku untuk karyawan ini.
     */
    public function activeMenuRestrictions()
    {
        $today = now()->toDateString();

        return $this->menuDisabled()
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
            })
            ->get();
    }

    /**
     * Info ringkas pembatasan menu aktif (periode & alasan) — dipakai
     * untuk prefill modal Hak Akses Menu di halaman Superadmin.
     */
    public function menuRestrictionMeta(): array
    {
        $rows = $this->relationLoaded('menuDisabled')
            ? $this->menuDisabled->filter(fn ($row) => method_exists($row, 'isActiveNow') ? $row->isActiveNow() : true)->values()
            : $this->activeMenuRestrictions();

        if ($rows->isEmpty()) {
            return ['active' => false, 'count' => 0, 'start' => '', 'end' => '', 'reason' => ''];
        }

        $starts = $rows->pluck('valid_from')->filter();
        $ends = $rows->pluck('valid_until')->filter();

        return [
            'active' => true,
            'count' => $rows->count(),
            'start' => $starts->min() ? $starts->min()->toDateString() : '',
            'end' => $ends->max() ? $ends->max()->toDateString() : '',
            'reason' => (string) ($rows->first()->reason ?? ''),
        ];
    }

    /**
     * Pembatasan menu tertentu yang sedang berlaku (untuk halaman 403).
     */
    public function menuRestrictionFor(string $menuKey): ?KaryawanMenuDisabled
    {
        return $this->activeMenuRestrictions()->firstWhere('menu_key', $menuKey);
    }

    /**
     * Apakah menu/fitur tertentu dinonaktifkan khusus untuk karyawan ini?
     */
    public function isMenuDisabled(string $menuKey): bool
    {
        return in_array($menuKey, $this->disabledMenuKeys(), true);
    }

    /**
     * Bersihkan memo hak akses — dipanggil setelah Superadmin mengubah menu.
     */
    public function flushMenuAccessCache(): void
    {
        $this->disabledMenuKeysCache = null;
    }
}
