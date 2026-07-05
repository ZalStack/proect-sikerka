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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function getWhatsappStatusColorAttribute()
    {
        $colors = [
            'pending' => 'bg-[#FCC626] text-[#1B1B1B]',
            'sent' => 'bg-[#2E7D3E] text-white',
            'failed' => 'bg-[#ec1d1d] text-white'
        ];
        return $colors[$this->whatsapp_status] ?? 'bg-gray-300 text-gray-600';
    }
}
