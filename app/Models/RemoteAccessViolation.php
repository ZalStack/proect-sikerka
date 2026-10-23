<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan pelanggaran presensi karena indikasi penggunaan akses remote
 * (TeamViewer / AnyDesk / RustDesk / RDP / VNC dll.).
 *
 * Status:
 * - pending_review : semua hasil deteksi (risiko rendah maupun tinggi) masuk ke
 *                    sini — menunggu review Superadmin, karyawan BELUM diblokir.
 * - confirmed      : pelanggaran ditinjau & dikonfirmasi Superadmin — strike &
 *                    ban (tangga 1 hari / 2 hari / permanen) baru diterapkan.
 * - dismissed      : ditandai false positive oleh Superadmin.
 */
class RemoteAccessViolation extends Model
{
    protected $table = 'remote_access_violations';

    protected $fillable = [
        'karyawan_id',
        'absensi_id',
        'tanggal',
        'konteks',
        'signals',
        'risk_score',
        'risk_level',
        'status',
        'strike_number',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'signals' => 'array',
        'risk_score' => 'integer',
        'strike_number' => 'integer',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending_review';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DISMISSED = 'dismissed';

    public const RISK_LOW = 'low';
    public const RISK_HIGH = 'high';

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class, 'absensi_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Review',
            self::STATUS_CONFIRMED => 'Dikonfirmasi',
            self::STATUS_DISMISSED => 'Ditolak (False Positive)',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800 border border-amber-200',
            self::STATUS_CONFIRMED => 'bg-rose-100 text-rose-800 border border-rose-200',
            self::STATUS_DISMISSED => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            default => 'bg-gray-100 text-gray-700 border border-gray-200',
        };
    }

    public function getRiskBadgeAttribute(): string
    {
        return $this->risk_level === self::RISK_HIGH
            ? 'bg-rose-100 text-rose-800 border border-rose-200'
            : 'bg-amber-100 text-amber-800 border border-amber-200';
    }

    public function getRiskLabelAttribute(): string
    {
        return $this->risk_level === self::RISK_HIGH ? 'Risiko Tinggi' : 'Risiko Rendah';
    }
}
