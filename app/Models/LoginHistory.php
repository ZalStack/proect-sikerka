<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Riwayat login/logout & percobaan login gagal (Fitur C11).
 * Tabel: login_histories.
 */
class LoginHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'login_histories';

    protected $fillable = [
        'karyawan_id',
        'email',
        'ip_address',
        'user_agent',
        'success',
        'failure_reason',
        'guard',
        'created_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

    /** Catat satu peristiwa login. */
    public static function record(array $attributes): void
    {
        try {
            static::create(array_merge(
                ['created_at' => now()],
                $attributes
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mencatat riwayat login: ' . $e->getMessage());
        }
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
