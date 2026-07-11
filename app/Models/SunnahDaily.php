<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SunnahDaily extends Model
{
    use HasFactory;

    protected $table = 'sunnah_daily';

    // Poin kelompok sholat wajib (subuh, zuhur, asar, maghrib, isya)
    const POIN_WAJIB_KOSONG   = 0;  // tidak ada satupun sholat wajib yang berjamaah
    const POIN_WAJIB_PER_ITEM = 1;  // poin per sholat wajib yang berjamaah (jika tidak lengkap)
    const POIN_WAJIB_LENGKAP  = 20; // seluruh (5 dari 5) sholat wajib berjamaah (5 × 4)

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

    // Konfigurasi poin per kegiatan (untuk kegiatan sunnah non-wajib, dihitung per item seperti biasa)
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

    // 5 field sholat wajib yang dihitung sebagai satu kelompok (bukan per item)
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

    /**
     * Hitung poin kelompok sholat wajib berdasarkan jumlah sholat yang dikerjakan berjamaah.
     * - Jika semua 5 sholat berjamaah (lengkap) → 20 poin (5 × 4)
     * - Jika sebagian (1-4) sholat berjamaah → jumlah × 1 poin
     * - Jika tidak ada yang berjamaah → 0 poin
     */
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

        // Hitung poin berdasarkan jumlah sholat yang berjamaah
        if ($jumlahBerjamaah >= 5) {
            // Semua 5 sholat berjamaah → 5 × 4 = 20 poin
            $poin = self::POIN_WAJIB_LENGKAP;
        } elseif ($jumlahBerjamaah >= 1) {
            // Sebagian sholat berjamaah (1-4) → jumlah × 1 poin
            $poin = $jumlahBerjamaah * self::POIN_WAJIB_PER_ITEM;
        } else {
            // Tidak ada yang berjamaah → 0 poin
            $poin = self::POIN_WAJIB_KOSONG;
        }

        return [
            'jumlah_berjamaah' => $jumlahBerjamaah,
            'poin' => $poin,
        ];
    }

    // Hitung total poin berdasarkan checklist (kelompok wajib + kegiatan sunnah lain)
    public static function calculateTotalPoin($data)
    {
        $config = self::getPoinConfig();
        $wajibKeys = self::getSholatWajibKeys();

        // Poin kelompok sholat wajib (subuh, zuhur, asar, maghrib, isya)
        $total = self::hitungPoinWajib($data)['poin'];

        // Poin kegiatan sunnah lainnya (dihitung per item seperti biasa)
        foreach ($config as $key => $value) {
            if (in_array($key, $wajibKeys, true)) {
                continue; // sudah dihitung di atas sebagai kelompok
            }
            if (!empty($data[$key])) {
                $total += $value['poin'];
            }
        }

        return $total;
    }

    // Jumlah sholat wajib yang dikerjakan secara berjamaah pada record ini
    public function getJumlahSholatBerjamaahAttribute()
    {
        return self::hitungPoinWajib($this->attributesToArray())['jumlah_berjamaah'];
    }

    // Poin kelompok sholat wajib pada record ini
    public function getPoinSholatWajibAttribute()
    {
        return self::hitungPoinWajib($this->attributesToArray())['poin'];
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
     * Scope untuk filter berdasarkan periode cepat: 3_hari, 1_minggu, 1_bulan.
     * Rentang dihitung mundur dari hari ini (inklusif).
     */
    public function scopeFilterByPeriode($query, $periode)
    {
        $end = Carbon::today()->endOfDay();

        switch ($periode) {
            case '3_hari':
                $start = Carbon::today()->subDays(2)->startOfDay();
                break;
            case '1_minggu':
                $start = Carbon::today()->subDays(6)->startOfDay();
                break;
            case '1_bulan':
                $start = Carbon::today()->subDays(29)->startOfDay();
                break;
            default:
                return $query;
        }

        return $query->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
    }

    // Label periode untuk tampilan
    public static function getPeriodeOptions()
    {
        return [
            '3_hari' => '3 Hari Terakhir',
            '1_minggu' => '1 Minggu Terakhir',
            '1_bulan' => '1 Bulan Terakhir',
        ];
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
