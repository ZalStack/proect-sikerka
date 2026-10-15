<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Perizinan extends Model
{
    use HasFactory;

    const JENIS = ['Izin', 'Sakit'];
    const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'karyawan_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'status',
        'catatan_hr',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'approved_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /** HRD yang memproses (approve/reject) pengajuan ini. */
    public function approver()
    {
        return $this->belongsTo(Karyawan::class, 'approved_by');
    }

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

    public function getJumlahHariAttribute(): int
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return 0;
        }

        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Menunggu Persetujuan',
        };
    }

    /**
     * Cek apakah karyawan sudah punya pengajuan (pending ATAU approved) yang
     * tanggalnya bertabrakan dengan rentang tanggal yang mau diajukan.
     * Dipakai untuk mencegah pengajuan ganda pada tanggal yang sama.
     */
    public static function hasOverlap(int $karyawanId, string $tanggalMulai, string $tanggalSelesai, ?int $excludeId = null): bool
    {
        $query = self::where('karyawan_id', $karyawanId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('tanggal_mulai', '<=', $tanggalSelesai)
            ->where('tanggal_selesai', '>=', $tanggalMulai);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Terapkan pengajuan yang SUDAH disetujui ke tabel absensi: setiap tanggal
     * dalam rentang tanggal_mulai s/d tanggal_selesai akan dibuat/diupdate
     * menjadi baris absensi berstatus Izin/Sakit dengan keterangan dari
     * pengajuan ini -- supaya langsung tercermin di rekap absensi karyawan
     * (baik di halaman karyawan sendiri maupun di halaman HR).
     *
     * Hanya menyentuh kolom status & keterangan; tidak menimpa check_in/
     * check_out/lokasi kalau baris absensi hari itu kebetulan sudah ada
     * (mis. karyawan sempat check-in sebelum izinnya disetujui).
     */
    public function syncToAbsensi(): void
    {
        app(\App\Services\AbsensiSyncService::class)->syncPerizinan($this);
    }

    /**
     * Kebalikan dari syncToAbsensi(): dipakai kalau pengajuan yang SUDAH
     * disetujui dibatalkan/direset oleh HR.
     */
    public function revertAbsensi(): void
    {
        app(\App\Services\AbsensiSyncService::class)->removePerizinan($this);
    }
}
