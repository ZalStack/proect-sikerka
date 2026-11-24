<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AbsensiCorrectionLog extends Model
{
    public $timestamps = false;

    protected $table = 'absensi_correction_logs';

    protected $fillable = [
        'operator_id',
        'operator_name',
        'operator_role',
        'karyawan_id',
        'karyawan_nama',
        'karyawan_kode',
        'tanggal_presensi',
        'action',
        'old_data',
        'new_data',
        'keterangan',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'tanggal_presensi' => 'date',
        'created_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id')->withTrashed();
    }

    /**
     * Catat log koreksi presensi secara aman tanpa mengganggu alur utama jika gagal.
     */
    public static function record(
        Absensi $absensi,
        string $action,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $keterangan = null
    ): ?self {
        try {
            $user = Auth::check() ? Auth::user() : null;
            $request = app()->bound('request') ? request() : null;

            $isVirtual = $user && method_exists($user, 'isVirtual') && $user->isVirtual();
            $operatorId = ($isVirtual || !$user) ? null : ($user->id ?? null);
            $operatorName = $user ? ($user->nama_lengkap ?? $user->email ?? 'Sistem') : 'Sistem';
            $operatorRole = $user ? ($user->posisi ?? 'superadmin') : 'system';

            $karyawan = $absensi->karyawan ?? Karyawan::withTrashed()->find($absensi->karyawan_id);

            return self::create([
                'operator_id' => $operatorId,
                'operator_name' => $operatorName,
                'operator_role' => $operatorRole,
                'karyawan_id' => $absensi->karyawan_id,
                'karyawan_nama' => $karyawan->nama_lengkap ?? 'Karyawan #' . $absensi->karyawan_id,
                'karyawan_kode' => $karyawan->kode_pegawai ?? null,
                'tanggal_presensi' => $absensi->tanggal,
                'action' => $action,
                'old_data' => $oldData,
                'new_data' => $newData,
                'keterangan' => $keterangan ?: ($absensi->keterangan ?? null),
                'ip_address' => $request?->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat AbsensiCorrectionLog: ' . $e->getMessage());
            return null;
        }
    }
}
