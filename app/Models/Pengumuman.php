<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengumuman extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengumuman';

    protected $fillable = [
        'created_by',
        'judul',
        'isi',
        'gambar',
        'target',
        'target_karyawan_ids',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'target_karyawan_ids' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(Karyawan::class, 'created_by');
    }

    public function getTargetLabelAttribute()
    {
        $labels = [
            'semua' => 'Semua Karyawan',
            'spesifik' => 'Karyawan Spesifik',
        ];

        return $labels[$this->target] ?? $this->target;
    }

    public function getTargetBadgeColorAttribute()
    {
        $colors = [
            'semua' => 'bg-[#00a2e9] text-white',
            'spesifik' => 'bg-[#8B5CF6] text-white',
        ];

        return $colors[$this->target] ?? 'bg-gray-500 text-white';
    }

    /**
     * Get target karyawan collection
     */
    public function getTargetKaryawanListAttribute()
    {
        if ($this->target !== 'spesifik' || empty($this->target_karyawan_ids)) {
            return collect();
        }

        $ids = is_array($this->target_karyawan_ids)
            ? $this->target_karyawan_ids
            : json_decode($this->target_karyawan_ids, true);

        if (empty($ids)) {
            return collect();
        }

        return Karyawan::whereIn('id', $ids)
            ->where('is_resigned', false)
            ->get(['id', 'nama_lengkap', 'kode_pegawai', 'divisi', 'posisi']);
    }

    /**
     * Get target karyawan names as string for display
     */
    public function getTargetKaryawanNamesAttribute()
    {
        $list = $this->target_karyawan_list;
        if ($list->isEmpty()) {
            return '-';
        }
        return $list->pluck('nama_lengkap')->implode(', ');
    }

    /**
     * Check if a karyawan can see this announcement
     */
    public function canBeSeenBy($karyawanId)
    {
        if ($this->target === 'semua') {
            return true;
        }

        if ($this->target === 'spesifik') {
            if (empty($this->target_karyawan_ids)) {
                return false;
            }
            $ids = is_array($this->target_karyawan_ids)
                ? $this->target_karyawan_ids
                : json_decode($this->target_karyawan_ids, true);
            return in_array($karyawanId, $ids);
        }

        return false;
    }
}
