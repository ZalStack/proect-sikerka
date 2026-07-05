<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'created_by',
        'judul',
        'isi',
        'gambar',
        'target',
        'is_sent_to_whatsapp',
        'sent_at',
        'whatsapp_status',
    ];

    protected $casts = [
        'is_sent_to_whatsapp' => 'boolean',
        'sent_at' => 'datetime',
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
            'karyawan' => 'Karyawan'
        ];
        return $labels[$this->target] ?? $this->target;
    }

    public function getWhatsappStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu',
            'sent' => 'Terkirim',
            'failed' => 'Gagal'
        ];
        return $labels[$this->whatsapp_status] ?? $this->whatsapp_status;
    }
}
