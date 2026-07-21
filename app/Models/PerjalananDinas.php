<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerjalananDinas extends Model
{
    protected $table = 'perjalanan_dinas';

    protected $fillable = [
        'karyawan_id',
        'judul',
        'agenda',
        'tanggal_mulai',
        'tanggal_selesai',
        'surat_tugas',
        'status',
        'catatan_hr',
        'tanggal_pengajuan',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_pengajuan' => 'date',
        'approved_at' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'approved_by');
    }

    // Scope untuk filter status
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Cek apakah pengajuan masih pending
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Cek apakah sudah disetujui
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // Cek apakah ditolak
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
