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

    // Mendapatkan pesan WhatsApp
    public function getWhatsAppMessageAttribute()
    {
        $message = "📢 *PENGUMUMAN*\n\n";
        $message .= "*{$this->judul}*\n\n";
        $message .= "{$this->isi}\n\n";
        $message .= "📅 *Tanggal:* " . $this->created_at->format('d-m-Y H:i') . "\n";
        $message .= "👤 *Dibuat oleh:* " . ($this->creator ? $this->creator->nama_lengkap : 'HR') . "\n\n";
        $message .= "---\n";
        $message .= "📌 *Target:* " . $this->target_label . "\n";
        $message .= "🆔 *ID:* #" . str_pad($this->id, 4, '0', STR_PAD_LEFT);

        return $message;
    }

    // Mendapatkan URL WhatsApp
    public function getWhatsAppUrlAttribute()
    {
        return "https://wa.me/?text=" . urlencode($this->whatsapp_message);
    }

    // Mendapatkan URL WhatsApp dengan nomor
    public function getWhatsAppUrlToNumberAttribute($phone)
    {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        if (strpos($cleanPhone, '0') === 0) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        $cleanPhone = str_replace('+', '', $cleanPhone);
        return "https://wa.me/{$cleanPhone}?text=" . urlencode($this->whatsapp_message);
    }
}
