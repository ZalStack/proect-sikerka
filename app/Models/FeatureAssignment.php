<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Penugasan fitur per cakupan (role / divisi / karyawan individual).
 * Dipakai oleh SystemSetting::isFeatureEnabledFor() (Fitur F22).
 */
class FeatureAssignment extends Model
{
    use HasFactory;

    protected $table = 'feature_assignments';

    protected $fillable = [
        'feature_key',
        'scope_type',
        'scope_value',
        'enabled',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public const SCOPE_TYPES = ['role', 'divisi', 'karyawan'];

    /** Label cakupan untuk tampilan UI. */
    public function scopeTypeLabel(): string
    {
        return match ($this->scope_type) {
            'role' => 'Role: ' . $this->scope_value,
            'divisi' => 'Divisi: ' . $this->scope_value,
            'karyawan' => 'Karyawan: ' . ($this->targetKaryawan()?->nama_lengkap ?? '#' . $this->scope_value),
            default => $this->scope_type . ': ' . $this->scope_value,
        };
    }

    /** Karyawan target — hanya relevan bila scope_type = 'karyawan'. */
    public function targetKaryawan(): ?Karyawan
    {
        if ($this->scope_type !== 'karyawan') {
            return null;
        }

        return Karyawan::find((int) $this->scope_value);
    }

    public function creator()
    {
        return $this->belongsTo(Karyawan::class, 'created_by');
    }
}
