<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
    ];

    /**
     * Ambil nilai pengaturan berdasarkan key.
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember('sys_setting_' . $key, 60, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
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
            return [
                'is_active' => self::isMaintenance(),
                'title' => self::getValue('maintenance_title', 'Sistem Sedang Dalam Pemeliharaan'),
                'message' => self::getValue(
                    'maintenance_message',
                    'Mohon maaf atas ketidaknyamanannya. Saat ini kami sedang melakukan pemeliharaan dan pembaruan sistem untuk meningkatkan performa dan layanan. Silakan coba kembali beberapa saat lagi.'
                ),
                'end_time' => self::getValue('maintenance_end_time', 'Segera Kembali'),
                'updated_at' => self::where('key', 'maintenance_mode')->value('updated_at'),
            ];
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
}
