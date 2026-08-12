<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SunnahDaily extends Model
{
    use HasFactory;

    protected $table = 'sunnah_daily';

    const POIN_WAJIB_KOSONG   = 0;
    const POIN_WAJIB_PER_ITEM = 1;
    const POIN_WAJIB_LENGKAP  = 20;

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

    public static function getPoinConfig()
    {
        return [
            'sholat_tahajud' => ['label' => 'Sholat Tahajud', 'poin' => 35, 'icon' => '🌙', 'has_jamaah' => false],
            'sholat_subuh' => ['label' => 'Sholat Subuh', 'icon' => '🌅', 'has_jamaah' => true],
            'sholat_zuhur' => ['label' => 'Sholat Zuhur', 'icon' => '☀️', 'has_jamaah' => true],
            'sholat_asar' => ['label' => 'Sholat Asar', 'icon' => '🌤️', 'has_jamaah' => true],
            'sholat_maghrib' => ['label' => 'Sholat Maghrib', 'icon' => '🌆', 'has_jamaah' => true],
            'sholat_isya' => ['label' => 'Sholat Isya', 'icon' => '🌙', 'has_jamaah' => true],
            'infaq_sedekah' => ['label' => 'Infaq/Sedekah', 'poin' => 5, 'icon' => '🤲', 'has_jamaah' => false],
            'dzikir_sholawat' => ['label' => 'Dzikir/Sholawat', 'poin' => 5, 'icon' => '📿', 'has_jamaah' => false],
            'tilawah_quran' => ['label' => 'Tilawah Quran', 'poin' => 5, 'icon' => '📖', 'has_jamaah' => false],
            'sholat_dhuha' => ['label' => 'Sholat Dhuha', 'poin' => 5, 'icon' => '🌄', 'has_jamaah' => false],
            'menjaga_wudhu' => ['label' => 'Menjaga Wudhu', 'poin' => 10, 'icon' => '💧', 'has_jamaah' => false],
            'puasa_sunnah' => ['label' => 'Puasa Sunnah', 'poin' => 15, 'icon' => '🌙', 'has_jamaah' => false],
        ];
    }

    public static function getSholatWajibKeys()
    {
        return [
            'sholat_subuh',
            'sholat_zuhur',
            'sholat_asar',
            'sholat_maghrib',
            'sholat_isya',
        ];
    }

    public static function hitungPoinWajib(array $data): array
    {
        $wajibKeys = self::getSholatWajibKeys();
        $jumlahBerjamaah = 0;

        foreach ($wajibKeys as $key) {
            $jamaahKey = $key . '_berjamaah';
            if (!empty($data[$key]) && !empty($data[$jamaahKey])) {
                $jumlahBerjamaah++;
            }
        }

        if ($jumlahBerjamaah >= 5) {
            $poin = self::POIN_WAJIB_LENGKAP;
        } elseif ($jumlahBerjamaah >= 1) {
            $poin = $jumlahBerjamaah * self::POIN_WAJIB_PER_ITEM;
        } else {
            $poin = self::POIN_WAJIB_KOSONG;
        }

        return [
            'jumlah_berjamaah' => $jumlahBerjamaah,
            'poin' => $poin,
        ];
    }

    public static function calculateTotalPoin($data)
    {
        $config = self::getPoinConfig();
        $wajibKeys = self::getSholatWajibKeys();

        $total = self::hitungPoinWajib($data)['poin'];

        foreach ($config as $key => $value) {
            if (in_array($key, $wajibKeys, true)) {
                continue;
            }
            if (!empty($data[$key])) {
                $total += $value['poin'];
            }
        }

        return $total;
    }

    public function getJumlahSholatBerjamaahAttribute()
    {
        return self::hitungPoinWajib($this->attributesToArray())['jumlah_berjamaah'];
    }

    public function getPoinSholatWajibAttribute()
    {
        return self::hitungPoinWajib($this->attributesToArray())['poin'];
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];
        return $labels[$this->status_approval] ?? $this->status_approval;
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'bg-[#FCC626] text-[#1B1B1B]',
            'approved' => 'bg-[#2E7D3E] text-white',
            'rejected' => 'bg-[#ec1d1d] text-white'
        ];
        return $colors[$this->status_approval] ?? 'bg-gray-500 text-white';
    }

    public function scopeFilterByMonthYear($query, $month, $year)
    {
        if ($month && $year) {
            return $query->whereMonth('tanggal', $month)
                        ->whereYear('tanggal', $year);
        }
        return $query;
    }

    public function isWithinApprovalPeriod()
    {
        $tanggalData = Carbon::parse($this->tanggal);
        // Diubah dari 6 hari menjadi 29 hari (1 bulan)
        $batasWaktu = Carbon::today()->subDays(29)->startOfDay();
        return $tanggalData->greaterThanOrEqualTo($batasWaktu);
    }

    /**
     * Rekap per Divisi berdasarkan rentang tanggal
     */
    public static function rekapPerDivisiByDateRange($startDate, $endDate)
    {
        $query = self::query()
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        $poinPerKaryawan = $query->selectRaw('karyawan_id, SUM(total_poin) as total_poin')
            ->groupBy('karyawan_id')
            ->pluck('total_poin', 'karyawan_id');

        $karyawans = Karyawan::whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->where('is_resigned', false)
            ->get();

        return $karyawans
            ->groupBy('divisi')
            ->map(function ($anggota, $divisi) use ($poinPerKaryawan) {
                $jumlahAnggota = $anggota->count();
                $totalPoinDivisi = $anggota->sum(function ($k) use ($poinPerKaryawan) {
                    return $poinPerKaryawan->get($k->id, 0);
                });

                return [
                    'divisi' => $divisi,
                    'jumlah_anggota' => $jumlahAnggota,
                    'total_poin' => $totalPoinDivisi,
                    'rata_rata_poin' => $jumlahAnggota > 0 ? round($totalPoinDivisi / $jumlahAnggota, 1) : 0,
                ];
            })
            ->sortByDesc('rata_rata_poin')
            ->values();
    }

    /**
     * Rekap total poin 7SPS per karyawan untuk bulan & tahun tertentu.
     */
    public static function rekapPerKaryawan($month, $year)
    {
        $karyawans = Karyawan::where('is_resigned', false)->get();

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