<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SunnahDaily extends Model
{
    use HasFactory;

    protected $table = 'sunnah_daily';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'sholat_tahajud',
        'sholat_subuh',
        'sholat_subuh_berjamaah',
        'sholat_zuhur',
        'sholat_zuhur_berjamaah',
        'sholat_asar',
        'sholat_asar_berjamaah',
        'sholat_maghrib',
        'sholat_maghrib_berjamaah',
        'sholat_isya',
        'sholat_isya_berjamaah',
        'infaq_sedekah',
        'dzikir_sholawat',
        'tilawah_quran',
        'sholat_dhuha',
        'menjaga_wudhu',
        'puasa_sunnah',
        'total_poin',
        'status_approval',
        'catatan_hr',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sholat_tahajud' => 'boolean',
        'sholat_subuh' => 'boolean',
        'sholat_subuh_berjamaah' => 'boolean',
        'sholat_zuhur' => 'boolean',
        'sholat_zuhur_berjamaah' => 'boolean',
        'sholat_asar' => 'boolean',
        'sholat_asar_berjamaah' => 'boolean',
        'sholat_maghrib' => 'boolean',
        'sholat_maghrib_berjamaah' => 'boolean',
        'sholat_isya' => 'boolean',
        'sholat_isya_berjamaah' => 'boolean',
        'infaq_sedekah' => 'boolean',
        'dzikir_sholawat' => 'boolean',
        'tilawah_quran' => 'boolean',
        'sholat_dhuha' => 'boolean',
        'menjaga_wudhu' => 'boolean',
        'puasa_sunnah' => 'boolean',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // Konfigurasi poin per kegiatan
    public static function getPoinConfig()
    {
        return [
            'sholat_tahajud' => ['label' => 'Sholat Tahajud', 'poin' => 35, 'icon' => '🌙', 'has_jamaah' => false],
            'sholat_subuh' => ['label' => 'Sholat Subuh', 'poin' => 1, 'poin_jamaah' => 4, 'icon' => '🌅', 'has_jamaah' => true],
            'sholat_zuhur' => ['label' => 'Sholat Zuhur', 'poin' => 1, 'poin_jamaah' => 4, 'icon' => '☀️', 'has_jamaah' => true],
            'sholat_asar' => ['label' => 'Sholat Asar', 'poin' => 1, 'poin_jamaah' => 4, 'icon' => '🌤️', 'has_jamaah' => true],
            'sholat_maghrib' => ['label' => 'Sholat Maghrib', 'poin' => 1, 'poin_jamaah' => 4, 'icon' => '🌆', 'has_jamaah' => true],
            'sholat_isya' => ['label' => 'Sholat Isya', 'poin' => 1, 'poin_jamaah' => 4, 'icon' => '🌙', 'has_jamaah' => true],
            'infaq_sedekah' => ['label' => 'Infaq/Sedekah', 'poin' => 5, 'icon' => '🤲', 'has_jamaah' => false],
            'dzikir_sholawat' => ['label' => 'Dzikir/Sholawat', 'poin' => 5, 'icon' => '📿', 'has_jamaah' => false],
            'tilawah_quran' => ['label' => 'Tilawah Quran', 'poin' => 5, 'icon' => '📖', 'has_jamaah' => false],
            'sholat_dhuha' => ['label' => 'Sholat Dhuha', 'poin' => 5, 'icon' => '🌄', 'has_jamaah' => false],
            'menjaga_wudhu' => ['label' => 'Menjaga Wudhu', 'poin' => 10, 'icon' => '💧', 'has_jamaah' => false],
            'puasa_sunnah' => ['label' => 'Puasa Sunnah', 'poin' => 15, 'icon' => '🌙', 'has_jamaah' => false],
        ];
    }

    // Hitung total poin berdasarkan checklist dengan mempertimbangkan berjamaah
    public static function calculateTotalPoin($data)
    {
        $config = self::getPoinConfig();
        $total = 0;

        foreach ($config as $key => $value) {
            if (isset($data[$key]) && $data[$key]) {
                // Cek apakah ini sholat wajib dengan berjamaah
                if ($value['has_jamaah'] ?? false) {
                    $jamaahKey = $key . '_berjamaah';
                    if (isset($data[$jamaahKey]) && $data[$jamaahKey]) {
                        $total += $value['poin_jamaah'] ?? $value['poin'] * 4;
                    } else {
                        $total += $value['poin'];
                    }
                } else {
                    $total += $value['poin'];
                }
            }
        }
        return $total;
    }

    // Dapatkan label status approval
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];
        return $labels[$this->status_approval] ?? $this->status_approval;
    }

    // Dapatkan badge status approval
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'bg-[#FCC626] text-[#1B1B1B]',
            'approved' => 'bg-[#2E7D3E] text-white',
            'rejected' => 'bg-[#ec1d1d] text-white'
        ];
        return $colors[$this->status_approval] ?? 'bg-gray-500 text-white';
    }

    // Scope untuk filter bulan dan tahun
    public function scopeFilterByMonthYear($query, $month, $year)
    {
        if ($month && $year) {
            return $query->whereMonth('tanggal', $month)
                        ->whereYear('tanggal', $year);
        }
        return $query;
    }

    /**
     * Rekap total poin 7SPS per karyawan untuk bulan & tahun tertentu.
     */
    public static function rekapPerKaryawan($month, $year)
    {
        $karyawans = Karyawan::all();

        $dataBulanIni = self::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get()
            ->groupBy('karyawan_id');

        return $karyawans->map(function ($karyawan) use ($dataBulanIni) {
            $items = $dataBulanIni->get($karyawan->id, collect());

            $totalHari = $items->count();
            $totalPoin = $items->sum('total_poin');

            return [
                'karyawan_id' => $karyawan->id,
                'nama_lengkap' => $karyawan->nama_lengkap,
                'kode_pegawai' => $karyawan->kode_pegawai ?? '-',
                'divisi' => $karyawan->divisi ?? '-',
                'total_hari' => $totalHari,
                'total_poin' => $totalPoin,
                'rata_rata' => $totalHari > 0 ? round($totalPoin / $totalHari, 1) : 0,
                'approved' => $items->where('status_approval', 'approved')->count(),
                'pending' => $items->where('status_approval', 'pending')->count(),
                'rejected' => $items->where('status_approval', 'rejected')->count(),
            ];
        })
        ->sortByDesc('total_poin')
        ->values();
    }
}
