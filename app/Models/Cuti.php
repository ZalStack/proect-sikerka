<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    use HasFactory;

    protected $table = 'cuti';

    protected $fillable = [
        'karyawan_id',
        'jenis_cuti',
        'jatah_cuti',
        'sisa_cuti',
        'cuti_digunakan',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'status',
        'tanggal_pengajuan',
        'catatan_hr',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_pengajuan' => 'date',
        'approved_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Baris "kuota" adalah baris tracker jatah/sisa cuti tahunan,
     * dibedakan dari pengajuan cuti asli lewat tanggal_mulai yang kosong.
     */
    public function scopeKuota($query)
    {
        return $query->whereNull('tanggal_mulai');
    }

    /**
     * Baris "pengajuan" adalah pengajuan cuti asli yang punya rentang tanggal.
     */
    public function scopePengajuan($query)
    {
        return $query->whereNotNull('tanggal_mulai');
    }

    /**
     * Cari atau buat baris kuota cuti untuk karyawan dan jenis cuti tertentu.
     */
    public static function getOrCreateKuota(int $karyawanId, string $jenisCuti = 'Cuti Tahunan'): self
    {
        $kuota = self::kuota()
            ->where('karyawan_id', $karyawanId)
            ->where('jenis_cuti', $jenisCuti)
            ->first();

        if (!$kuota) {
            $karyawan = Karyawan::find($karyawanId);
            $jatah = $karyawan
                ? \App\Models\CutiPolicy::kuotaFor($karyawan, now()->year, $jenisCuti)
                : (\App\Models\CutiType::where('nama', $jenisCuti)->value('kuota_hari') ?? 12);

            $kuota = self::create([
                'karyawan_id' => $karyawanId,
                'jenis_cuti' => $jenisCuti,
                'jatah_cuti' => $jatah,
                'sisa_cuti' => $jatah,
                'cuti_digunakan' => 0,
                'status' => 'approved',
                'tanggal_pengajuan' => \Carbon\Carbon::now(),
            ]);
        }

        return $kuota;
    }

    /**
     * Cari baris kuota tanpa membuat baru jika belum ada.
     */
    public static function findKuota(int $karyawanId, string $jenisCuti = 'Cuti Tahunan'): ?self
    {
        return self::kuota()
            ->where('karyawan_id', $karyawanId)
            ->where('jenis_cuti', $jenisCuti)
            ->first();
    }

    // Hitung durasi cuti dalam hari
    public function getDurasiAttribute(): int
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            return (int) $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
        }
        return 0;
    }

    // Status badge
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'bg-[#FCC626] text-[#1B1B1B]',
            'approved' => 'bg-[#2E7D3E] text-white',
            'rejected' => 'bg-[#ec1d1d] text-white',
        ];
        return $colors[$this->status] ?? 'bg-gray-500 text-white';
    }

    // Status label
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
        return $labels[$this->status] ?? $this->status;
    }
}
