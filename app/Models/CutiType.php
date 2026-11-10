<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CutiType extends Model
{
    use HasFactory;

    protected $table = 'cuti_types';

    protected $fillable = [
        'nama',
        'kuota_hari',
        'scope_golongan',
        'sort_order',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'kuota_hari' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('nama');
    }

    /**
     * Cek apakah jenis cuti ini sudah pernah digunakan dalam tabel cuti (pengajuan atau kuota).
     */
    public function isUsed(): bool
    {
        return Cuti::where('jenis_cuti', $this->nama)->exists();
    }

    /**
     * Seeder idempoten 4 jenis cuti bawaan sistem.
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            [
                'nama' => 'Cuti Tahunan',
                'kuota_hari' => 12,
                'scope_golongan' => null,
                'sort_order' => 1,
                'is_active' => true,
                'keterangan' => 'Hak cuti tahunan reguler karyawan',
            ],
            [
                'nama' => 'Cuti Besar',
                'kuota_hari' => 12,
                'scope_golongan' => null,
                'sort_order' => 2,
                'is_active' => true,
                'keterangan' => 'Cuti besar untuk karyawan dengan masa kerja tertentu',
            ],
            [
                'nama' => 'Cuti Melahirkan',
                'kuota_hari' => 12,
                'scope_golongan' => null,
                'sort_order' => 3,
                'is_active' => true,
                'keterangan' => 'Cuti melahirkan bagi karyawan wanita',
            ],
            [
                'nama' => 'Cuti Penting',
                'kuota_hari' => 12,
                'scope_golongan' => null,
                'sort_order' => 4,
                'is_active' => true,
                'keterangan' => 'Cuti untuk keperluan mendesak atau keluarga',
            ],
        ];

        foreach ($defaults as $item) {
            static::updateOrCreate(['nama' => $item['nama']], $item);
        }
    }
}
