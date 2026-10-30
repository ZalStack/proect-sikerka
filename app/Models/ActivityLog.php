<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Label ramah pengguna untuk aksi yang sering dipakai.
     */
    public const ACTION_LABELS = [
        'auth.login' => 'Login Berhasil',
        'auth.logout' => 'Logout',
        'auth.failed' => 'Login Gagal',
        'backup.create' => 'Buat Backup',
        'backup.download' => 'Unduh Backup',
        'backup.delete' => 'Hapus Backup',
        'maintenance.toggle' => 'Ubah Mode Maintenance',
        'feature.toggle' => 'Ubah Status Fitur',
        'feature.toggle_all' => 'Ubah Semua Fitur',
        'absensi.create' => 'Tambah Presensi',
        'absensi.update' => 'Koreksi Presensi',
        'absensi.delete' => 'Hapus Presensi',
        'karyawan.role_update' => 'Ubah Role Pengguna',
        'access.suspend' => 'Suspend Akun',
        'access.unsuspend' => 'Angkat Suspend',
        'access.menus' => 'Ubah Hak Akses Menu',
        'violation.confirm' => 'Konfirmasi Pelanggaran',
        'violation.dismiss' => 'Tolak Pelanggaran',
        'karyawan.unban' => 'Buka Blokir Presensi',
        'karyawan.ban_message' => 'Ubah Pesan Blokir',
        'hari_libur.create' => 'Tambah Hari Libur',
        'hari_libur.delete' => 'Hapus Hari Libur',
        'hari_libur.sync' => 'Sinkronisasi Hari Libur',
        'system.cache_clear' => 'Bersihkan Cache',
        'system.tool' => 'Jalankan Tool Sistem',
        'logs.export' => 'Ekspor Log Aktivitas',
        'http.post' => 'Aksi POST',
        'http.put' => 'Aksi PUT',
        'http.patch' => 'Aksi PATCH',
        'http.delete' => 'Aksi DELETE',
    ];

    public const MODULE_LABELS = [
        'auth' => 'Autentikasi',
        'backup' => 'Backup Database',
        'maintenance' => 'Maintenance',
        'features' => 'Fitur Sistem',
        'absensi' => 'Presensi',
        'users' => 'Pengguna',
        'access' => 'Manajemen Akses',
        'violations' => 'Pelanggaran Presensi',
        'holidays' => 'Hari Libur',
        'system' => 'Sistem',
        'http' => 'Aktivitas HTTP',
    ];

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? Str::headline(str_replace(['.', '_'], ' ', (string) $this->action));
    }

    public function getModuleLabelAttribute(): string
    {
        return self::MODULE_LABELS[$this->module] ?? Str::headline(str_replace(['.', '_'], ' ', (string) $this->module));
    }

    /**
     * Simpan satu catatan audit. Tidak pernah melempar exception agar
     * aktivitas utama pengguna tidak ikut gagal jika logging bermasalah.
     */
    public static function record(string $action, ?string $description = null, array $options = []): ?self
    {
        try {
            $user = $options['user'] ?? (Auth::check() ? Auth::user() : null);
            $request = app()->bound('request') ? request() : null;

            return self::create([
                'user_id' => $options['user_id'] ?? ($user->id ?? null),
                'user_name' => $options['user_name'] ?? ($user->nama_lengkap ?? $user->name ?? null),
                'user_role' => $options['user_role'] ?? ($user->posisi ?? null),
                'action' => $action,
                'module' => $options['module'] ?? null,
                'description' => $description,
                'subject_type' => $options['subject_type'] ?? null,
                'subject_id' => $options['subject_id'] ?? null,
                'ip_address' => $options['ip_address'] ?? ($request?->ip()),
                'user_agent' => $options['user_agent'] ?? ($request?->userAgent()),
                'metadata' => $options['metadata'] ?? null,
                'created_at' => $options['created_at'] ?? now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan activity log: ' . $e->getMessage());

            return null;
        }
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when(!empty($filters['q']), function ($q) use ($filters) {
                $search = trim((string) $filters['q']);
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['user_id']), fn ($q) => $q->where('user_id', $filters['user_id']))
            ->when(!empty($filters['role']), fn ($q) => $q->where('user_role', $filters['role']))
            ->when(!empty($filters['module']), fn ($q) => $q->where('module', $filters['module']))
            ->when(!empty($filters['action']), fn ($q) => $q->where('action', $filters['action']))
            ->when(!empty($filters['from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['to']));
    }
}
