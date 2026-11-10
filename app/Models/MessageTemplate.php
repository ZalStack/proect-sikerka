<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Template pesan siap pakai untuk broadcast, ban, dan pelanggaran (Fitur F23).
 */
class MessageTemplate extends Model
{
    use HasFactory;

    protected $table = 'message_templates';

    protected $fillable = [
        'type',
        'title',
        'body',
        'is_default',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'ban' => 'Pesan Ban',
        'violation' => 'Pesan Pelanggaran',
        'broadcast' => 'Broadcast Pengumuman',
    ];

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /** Isi template dengan placeholder yang sudah diganti. */
    public function render(array $replacements = []): string
    {
        $body = (string) $this->body;

        foreach ($replacements as $key => $value) {
            $body = str_replace('{' . $key . '}', (string) $value, $body);
        }

        return $body;
    }

    public function creator()
    {
        return $this->belongsTo(Karyawan::class, 'created_by');
    }
}
