<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Definisi semua fitur sistem yang dapat diaktifkan / dimatikan.
     */
    const AVAILABLE_FEATURES = [
        'absensi' => [
            'name' => 'Presensi / Absensi Karyawan',
            'description' => 'Fitur check-in, check-out, verifikasi lokasi kantor, audit trail, dan riwayat presensi.',
            'category' => 'Core Presensi',
            'icon' => 'fa-clock',
            'color' => '#00a2e9',
        ],
        'cuti' => [
            'name' => 'Pengajuan & Persetujuan Cuti',
            'description' => 'Fitur permohonan cuti karyawan, verifikasi, approval HR, dan rekap saldo cuti.',
            'category' => 'Kepegawaian',
            'icon' => 'fa-calendar-check',
            'color' => '#2E7D3E',
        ],
        'perjalanan_dinas' => [
            'name' => 'Perjalanan Dinas',
            'description' => 'Fitur pengajuan tugas luar kota/kantor, approval, unduh surat tugas, dan laporan.',
            'category' => 'Kepegawaian',
            'icon' => 'fa-plane-departure',
            'color' => '#FCC626',
        ],
        'perizinan' => [
            'name' => 'Perizinan Karyawan (Sakit & Izin)',
            'description' => 'Fitur pengajuan izin/sakit beserta upload surat keterangan dokter / bukti pendukung.',
            'category' => 'Kepegawaian',
            'icon' => 'fa-file-signature',
            'color' => '#9333ea',
        ],
        'sunnah' => [
            'name' => '7SPS (Sunnah Daily)',
            'description' => 'Fitur pencatatan amalan yaumiyah/sunnah harian, rekapitulasi individu & divisi.',
            'category' => 'Ibadah & Karakter',
            'icon' => 'fa-kaaba',
            'color' => '#059669',
        ],
        'fhl' => [
            'name' => 'FHL (Fastabiqul Khairat)',
            'description' => 'Fitur presensi kegiatan FHL dengan kode verifikasi harian & konfigurasi jadwal.',
            'category' => 'Ibadah & Karakter',
            'icon' => 'fa-heart',
            'color' => '#ec1d1d',
        ],
        'khataman' => [
            'name' => 'Khataman Al-Qur\'an',
            'description' => 'Fitur absensi khataman mingguan/bulanan, kode token dinamis, dan riwayat juz.',
            'category' => 'Ibadah & Karakter',
            'icon' => 'fa-book-quran',
            'color' => '#2563eb',
        ],
        'pengumuman' => [
            'name' => 'Pengumuman & Informasi',
            'description' => 'Pusat informasi, broadcast edaran kantor, dan pengumuman HR ke seluruh karyawan.',
            'category' => 'Informasi',
            'icon' => 'fa-bullhorn',
            'color' => '#ea580c',
        ],
        'pelanggaran_presensi' => [
            'name' => 'Deteksi Pelanggaran Presensi (Anti Remote)',
            'description' => 'Mendeteksi indikasi akses remote desktop (TeamViewer/AnyDesk/RDP) saat presensi dan memasukkannya ke antrean review.',
            'category' => 'Core Presensi',
            'icon' => 'fa-shield-halved',
            'color' => '#dc2626',
        ],
    ];

    /**
     * Menu yang BOLEH dibatasi per karyawan/HR pada halaman Manajemen Akses.
     * Hanya fitur yang benar-benar tampil sebagai menu di sidebar karyawan/HR.
     * Fitur superadmin-only (mis. 'pelanggaran_presensi' — monitoring anti remote
     * access) tidak disertakan karena tidak memiliki menu/route untuk karyawan
     * maupun HR, sehingga pembatasannya tidak akan berpengaruh apa pun.
     */
    const RESTRICTABLE_MENU_KEYS = [
        'absensi',
        'cuti',
        'perjalanan_dinas',
        'perizinan',
        'sunnah',
        'fhl',
        'khataman',
        'pengumuman',
    ];

    /**
     * Ambil metadata menu yang dapat dibatasi (dipakai halaman Manajemen Akses).
     */
    public static function getRestrictableMenus(): array
    {
        return array_intersect_key(
            self::AVAILABLE_FEATURES,
            array_flip(self::RESTRICTABLE_MENU_KEYS)
        );
    }

    /**
     * Ambil nilai pengaturan berdasarkan key.
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember('sys_setting_' . $key, 60, function () use ($key, $default) {
            try {
                $setting = self::where('key', $key)->first();
            } catch (QueryException $e) {
                // Tabel system_settings belum tersedia (mis. sebelum `php artisan migrate`
                // atau DB sedang bermasalah). Pakai nilai default agar aplikasi tetap
                // berjalan, alih-alih melempar 500 di semua halaman.
                Log::warning('SystemSetting::getValue gagal membaca database: ' . $e->getMessage());

                return $default;
            }

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set nilai pengaturan berdasarkan key.
     */
    public static function setValue(string $key, $value, string $group = 'general', ?string $description = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget('sys_setting_' . $key);
        Cache::forget('sys_all_features_status');
        Cache::forget('sys_maintenance_info');

        return $setting;
    }

    /**
     * Cek apakah sistem sedang dalam mode maintenance.
     */
    public static function isMaintenance(): bool
    {
        $val = self::getValue('maintenance_mode', '0');
        return in_array($val, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Ambil informasi detail status maintenance.
     */
    public static function getMaintenanceInfo(): array
    {
        return Cache::remember('sys_maintenance_info', 60, function () {
            $scheduled = self::isMaintenanceScheduleActive();
            $info = [
                'is_active' => self::isMaintenanceActive(),
                'title' => self::getValue('maintenance_title', 'Sistem Sedang Dalam Pemeliharaan'),
                'message' => self::getValue(
                    'maintenance_message',
                    'Mohon maaf atas ketidaknyamanannya. Saat ini kami sedang melakukan pemeliharaan dan pembaruan sistem untuk meningkatkan performa dan layanan. Silakan coba kembali beberapa saat lagi.'
                ),
                'end_time' => self::getValue('maintenance_end_time', 'Segera Kembali'),
                'scheduled' => $scheduled,
                'schedule' => self::maintenanceSchedule(),
                'updated_at' => self::where('key', 'maintenance_mode')->value('updated_at'),
            ];

            if ($scheduled && !empty($info['schedule']['ends_at'])) {
                try {
                    $end = \Carbon\Carbon::parse($info['schedule']['ends_at']);
                    $info['end_time'] = $end->translatedFormat('d M Y, H:i') . ' WIB';
                } catch (\Throwable $e) {
                    // Biarkan teks default.
                }
            }

            return $info;
        });
    }

    /**
     * Ubah status maintenance mode.
     */
    public static function setMaintenance(bool $enabled, ?string $title = null, ?string $message = null, ?string $endTime = null)
    {
        self::setValue('maintenance_mode', $enabled ? '1' : '0', 'system', 'Status mode pemeliharaan sistem');

        if ($title !== null) {
            self::setValue('maintenance_title', $title, 'system', 'Judul halaman pemeliharaan');
        }

        if ($message !== null) {
            self::setValue('maintenance_message', $message, 'system', 'Pesan penjelasan pemeliharaan');
        }

        if ($endTime !== null) {
            self::setValue('maintenance_end_time', $endTime, 'system', 'Estimasi waktu selesai pemeliharaan');
        }

        Cache::forget('sys_setting_maintenance_mode');
        Cache::forget('sys_maintenance_info');
    }

    /**
     * Apakah seluruh Super Admin diwajibkan mengaktifkan 2FA?
     * Default false — fitur opsional sampai dinyalakan manual.
     */
    public static function isTwoFactorRequired(): bool
    {
        $val = self::getValue('require_2fa_superadmin', '0');

        return in_array($val, ['1', 'true', 'yes', 'on'], true);
    }

    public static function setTwoFactorRequired(bool $enabled): void
    {
        self::setValue('require_2fa_superadmin', $enabled ? '1' : '0', 'security', 'Wajibkan 2FA untuk Super Admin');
    }

    /**
     * Whitelist IP akses Super Admin (dipisah koma / baris baru).
     * String kosong = tanpa pembatasan.
     */
    public static function superadminIpWhitelist(): string
    {
        return (string) self::getValue('superadmin_ip_whitelist', '');
    }

    public static function setSuperadminIpWhitelist(string $raw): void
    {
        self::setValue(
            'superadmin_ip_whitelist',
            trim($raw),
            'security',
            'Daftar IP yang diizinkan mengakses panel Super Admin (CIDR / wildcard didukung)'
        );
    }

    /**
     * Jadwal maintenance OTOMATIS (Fitur B10).
     *
     * Sistem masuk maintenance saat `now` berada di rentang
     * maintenance_starts_at .. maintenance_ends_at, terlepas dari sakelar manual.
     * Kedua nilai kosong = tidak ada jadwal.
     */
    public static function maintenanceSchedule(): array
    {
        return Cache::remember('sys_maintenance_schedule', 60, function () {
            return [
                'starts_at' => self::getValue('maintenance_starts_at', null),
                'ends_at' => self::getValue('maintenance_ends_at', null),
            ];
        });
    }

    public static function setMaintenanceSchedule(?string $startsAt, ?string $endsAt, ?bool $enabled = null): void
    {
        self::setValue('maintenance_starts_at', $startsAt ?? '', 'system', 'Jadwal maintenance mulai');
        self::setValue('maintenance_ends_at', $endsAt ?? '', 'system', 'Jadwal maintenance selesai');

        if ($enabled !== null) {
            self::setValue(
                'maintenance_schedule_enabled',
                $enabled ? '1' : '0',
                'system',
                'Sakelar jadwal maintenance otomatis (Fitur B10)'
            );
        }

        Cache::forget('sys_maintenance_schedule');
        Cache::forget('sys_maintenance_info');
    }

    /** Sakelar jadwal maintenance otomatis (Fitur B10). */
    public static function isMaintenanceScheduleEnabled(): bool
    {
        $val = self::getValue('maintenance_schedule_enabled', '1');

        return in_array($val, ['1', 'true', 'yes', 'on'], true);
    }

    // ================================================================
    // SMTP / EMAIL OUTGOING (Fitur B9)
    // ================================================================

    /**
     * Pengaturan SMTP tersimpan — dikembalikan sebagai array konfigurasi
     * siap pakai. Nilai kosong = belum dikonfigurasi.
     *
     * Password dienkripsi saat disimpan (Crypt) dan didekripsi saat dibaca.
     */
    public static function smtpSettings(): array
    {
        return [
            'mailer' => self::getValue('mail_mailer', ''),
            'host' => self::getValue('smtp_host', ''),
            'port' => self::getValue('smtp_port', ''),
            'username' => self::getValue('smtp_username', ''),
            'password' => self::decryptSecret(self::getValue('smtp_password', '')),
            'encryption' => self::getValue('smtp_encryption', 'tls'),
            'from_address' => self::getValue('smtp_from_address', ''),
            'from_name' => self::getValue('smtp_from_name', ''),
        ];
    }

    public static function setSmtpSettings(array $data): void
    {
        $map = [
            'mail_mailer' => ['mail', 'Mailer keluaran (smtp/log/array)'],
            'smtp_host' => ['mail', 'Host server SMTP'],
            'smtp_port' => ['mail', 'Port server SMTP'],
            'smtp_username' => ['mail', 'Username akun SMTP'],
            'smtp_password' => ['mail', 'Password akun SMTP (terenkripsi)'],
            'smtp_encryption' => ['mail', 'Enkripsi SMTP (tls/ssl/none)'],
            'smtp_from_address' => ['mail', 'Alamat email pengirim'],
            'smtp_from_name' => ['mail', 'Nama pengirim email'],
        ];

        foreach ($map as $key => [$group, $desc]) {
            if (array_key_exists($key, $data)) {
                $value = (string) ($data[$key] ?? '');

                // Enkripsi rahasia (password) sebelum masuk database.
                if ($key === 'smtp_password' && $value !== '') {
                    $value = self::encryptSecret($value);
                }

                self::setValue($key, $value, $group, $desc);
            }
        }

        Cache::forget('sys_smtp_settings');
    }

    /** Enkripsi rahasia; gagal enkripsi → simpan apa adanya (tidak hilang). */
    protected static function encryptSecret(string $value): string
    {
        try {
            return 'enc:' . encrypt($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /** Dekripsi rahasia; bila bukan hasil enkripsi → kembalikan apa adanya. */
    protected static function decryptSecret(string $value): string
    {
        if ($value === '' || !str_starts_with($value, 'enc:')) {
            return $value;
        }

        try {
            return (string) decrypt(substr($value, 4));
        } catch (\Throwable $e) {
            // APP_KEY berubah / data korup — jangan menjatuhkan aplikasi.
            return '';
        }
    }

    /** Terapkan pengaturan SMTP ke konfigurasi runtime aplikasi. */
    public static function applyMailConfig(): void
    {
        $s = self::smtpSettings();

        // Belum dikonfigurasi lewat UI → biarkan .env yang mengatur.
        if ($s['host'] === '' && $s['mailer'] === '') {
            return;
        }

        try {
            if ($s['mailer'] !== '') {
                config(['mail.default' => $s['mailer']]);
            }

            if ($s['host'] !== '') {
                config([
                    'mail.mailers.smtp.host' => $s['host'],
                    'mail.mailers.smtp.port' => $s['port'] !== '' ? (int) $s['port'] : 587,
                    'mail.mailers.smtp.username' => $s['username'],
                    'mail.mailers.smtp.password' => $s['password'],
                    'mail.mailers.smtp.scheme' => $s['encryption'] === 'none' ? null : $s['encryption'],
                ]);
            }

            if ($s['from_address'] !== '') {
                config([
                    'mail.from.address' => $s['from_address'],
                    'mail.from.name' => $s['from_name'] !== '' ? $s['from_name'] : config('mail.from.name'),
                ]);
            }
        } catch (\Throwable $e) {
            // Pengaturan email tidak boleh menjatuhkan aplikasi.
            report($e);
        }
    }

    /** Apakah SMTP sudah pernah dikonfigurasi? */
    public static function isSmtpConfigured(): bool
    {
        return self::getValue('smtp_host', '') !== '';
    }

    /**
     * Apakah jadwal maintenance sedang berlaku pada waktu sekarang?
     */
    public static function isMaintenanceScheduleActive(): bool
    {
        $schedule = self::maintenanceSchedule();

        if (empty($schedule['starts_at']) || empty($schedule['ends_at'])) {
            return false;
        }

        // Sakelar jadwal (Fitur B10) — mematikan jadwal tanpa
        // menghapus rentang tanggalnya.
        $enabled = self::getValue('maintenance_schedule_enabled', '1');
        if (!in_array($enabled, ['1', 'true', 'yes', 'on'], true)) {
            return false;
        }

        try {
            $now = now();
            $start = \Carbon\Carbon::parse($schedule['starts_at']);
            $end = \Carbon\Carbon::parse($schedule['ends_at']);

            if ($end->lt($start)) {
                // Rentang tidak valid (selesai sebelum mulai) → jadwal diabaikan.
                return false;
            }

            return $now->betweenIncluded($start, $end);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Maintenance aktif bila sakelar manual menyala ATAU jadwal sedang berjalan.
     */
    public static function isMaintenanceActive(): bool
    {
        return self::isMaintenance() || self::isMaintenanceScheduleActive();
    }

    /**
     * Cek apakah fitur tertentu aktif.
     */
    public static function isFeatureEnabled(string $featureKey): bool
    {
        // Jika feature key tidak terdaftar, default aktif
        if (!array_key_exists($featureKey, self::AVAILABLE_FEATURES)) {
            return true;
        }

        $val = self::getValue('feature_' . $featureKey, '1');
        return in_array($val, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Ambil seluruh daftar fitur beserta status aktivasinya.
     */
    public static function getFeatureList(): array
    {
        $result = [];
        foreach (self::AVAILABLE_FEATURES as $key => $meta) {
            $val = self::getValue('feature_' . $key, '1');
            $isEnabled = in_array($val, ['1', 'true', 'yes', 'on'], true);

            $result[$key] = array_merge($meta, [
                'key' => $key,
                'setting_key' => 'feature_' . $key,
                'is_enabled' => $isEnabled,
            ]);
        }

        return $result;
    }

    /**
     * Aktifkan / Nonaktifkan fitur tertentu.
     */
    public static function setFeature(string $featureKey, bool $enabled)
    {
        $meta = self::AVAILABLE_FEATURES[$featureKey] ?? null;
        $name = $meta ? $meta['name'] : $featureKey;

        self::setValue(
            'feature_' . $featureKey,
            $enabled ? '1' : '0',
            'features',
            'Status aktivasi fitur ' . $name
        );
    }

    /**
     * Aktifkan atau Nonaktifkan SEMUA fitur sekaligus.
     */
    public static function setAllFeatures(bool $enabled)
    {
        foreach (array_keys(self::AVAILABLE_FEATURES) as $key) {
            self::setFeature($key, $enabled);
        }
    }

    // ==========================================================
    // FEATURE FLAGS PER CAKUPAN (Fitur F22)
    // ==========================================================

    /**
     * Status fitur untuk pengguna tertentu:
     *
     *  1. Sakelar global NONAKTIF mengalahkan SEMUA penugasan → fitur mati
     *     untuk semua non-superadmin, tanpa terkecuali.
     *  2. Bila global aktif, cek penugasan per cakupan (role / divisi / karyawan).
     *     Assignment `enabled=false` memblokir fitur untuk cakupan itu;
     *     tanpa penugasan yang cocok → fitur terbuka.
     *  3. Bila beberapa penugasan cocok, prioritas: karyawan > divisi > role.
     *
     * Super Admin selalu lolos (dicek di middleware).
     */
    public static function isFeatureEnabledFor(?object $user, string $featureKey): bool
    {
        if (!array_key_exists($featureKey, self::AVAILABLE_FEATURES)) {
            return true;
        }

        // Sakelar global mati = fitur mati untuk semua non-superadmin.
        if (!self::isFeatureEnabled($featureKey)) {
            return false;
        }

        if (!$user) {
            return true;
        }

        $assignments = self::featureAssignmentsFor($featureKey);

        if ($assignments->isEmpty()) {
            return true;
        }

        $matching = $assignments->filter(fn ($row) => self::assignmentMatches($row, $user));

        if ($matching->isEmpty()) {
            return true;
        }

        // Bobot: karyawan individual > divisi > role.
        $priority = ['karyawan' => 3, 'divisi' => 2, 'role' => 1];
        $top = $matching->sortByDesc(fn ($row) => $priority[$row->scope_type] ?? 0)->first();

        return (bool) $top->enabled;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\FeatureAssignment>
     */
    public static function featureAssignmentsFor(string $featureKey)
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'sys_feature_assignments_' . $featureKey,
            60,
            fn () => \App\Models\FeatureAssignment::where('feature_key', $featureKey)->get()
        );
    }

    private static function assignmentMatches(object $assignment, object $user): bool
    {
        return match ($assignment->scope_type) {
            'role' => ($user->posisi ?? null) === $assignment->scope_value,
            'divisi' => (string) ($user->divisi ?? '') !== ''
                && strcasecmp((string) $user->divisi, (string) $assignment->scope_value) === 0,
            'karyawan' => (string) $user->id === (string) $assignment->scope_value,
            default => false,
        };
    }

    /**
     * Buat/ubah satu penugasan fitur per cakupan.
     */
    public static function assignFeature(
        string $featureKey,
        string $scopeType,
        string $scopeValue,
        bool $enabled,
        ?string $notes = null,
        ?int $createdBy = null
    ): \App\Models\FeatureAssignment {
        $assignment = \App\Models\FeatureAssignment::updateOrCreate(
            [
                'feature_key' => $featureKey,
                'scope_type' => $scopeType,
                'scope_value' => $scopeValue,
            ],
            [
                'enabled' => $enabled,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]
        );

        self::forgetFeatureAssignmentCache($featureKey);

        return $assignment;
    }

    public static function forgetFeatureAssignmentCache(?string $featureKey = null): void
    {
        if ($featureKey !== null) {
            Cache::forget('sys_feature_assignments_' . $featureKey);

            return;
        }

        foreach (array_keys(self::AVAILABLE_FEATURES) as $key) {
            Cache::forget('sys_feature_assignments_' . $key);
        }
    }

    /**
     * Nomor WhatsApp Admin SIKEKAR dalam format internasional (contoh: 6282123439604).
     * Disimpan pada system_settings dengan key "admin_whatsapp",
     * sehingga nomor bisa diubah lewat database tanpa menyentuh file view.
     */
    public static function adminWhatsapp(): string
    {
        $number = (string) self::getValue('admin_whatsapp', '082123439604');

        return self::normalizeWaNumber($number) ?? '6282123439604';
    }

    /**
     * Normalisasi nomor WhatsApp ke format internasional tanpa tanda "+".
     * "082123439604" -> "6282123439604", "+62 821-2343-9604" -> "6282123439604".
     * Mengembalikan null jika nomor kosong / tidak valid.
     */
    public static function normalizeWaNumber(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === null || $digits === '') {
            return null;
        }

        // Sudah format internasional Indonesia (62...) -> biarkan
        // Nomor lokal (08xx... / 8xx...) -> tambahkan kode negara 62
        if (!str_starts_with($digits, '62')) {
            $digits = str_starts_with($digits, '0')
                ? '62' . substr($digits, 1)
                : '62' . $digits;
        }

        return strlen($digits) >= 10 ? $digits : null;
    }
}
