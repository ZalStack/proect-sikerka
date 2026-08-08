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
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(Karyawan::class, 'created_by');
    }

    public function getTargetLabelAttribute()
    {
        $labels = [
            'semua' => 'Semua Karyawan',
            'hr' => 'HR',
            'karyawan' => 'Karyawan',
        ];

        return $labels[$this->target] ?? $this->target;
    }
}
