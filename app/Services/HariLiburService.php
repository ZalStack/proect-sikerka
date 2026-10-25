<?php

namespace App\Services;

use App\Models\HariLibur;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HariLiburService
{
    /**
     * Fallback data libur nasional 2026 jika offline / API down
     */
    public static function defaultHolidays2026(): array
    {
        return [
            ['tanggal' => '2026-01-01', 'keterangan' => 'Tahun Baru 2026 Masehi', 'jenis' => 'nasional'],
            ['tanggal' => '2026-01-16', 'keterangan' => 'Isra Mikraj Nabi Muhammad SAW', 'jenis' => 'nasional'],
            ['tanggal' => '2026-02-17', 'keterangan' => 'Tahun Baru Imlek 2577 Kongzili', 'jenis' => 'nasional'],
            ['tanggal' => '2026-03-20', 'keterangan' => 'Hari Suci Nyepi Tahun Baru Saka 1948', 'jenis' => 'nasional'],
            ['tanggal' => '2026-03-21', 'keterangan' => 'Hari Raya Idul Fitri 1447 H (Hari ke-1)', 'jenis' => 'nasional'],
            ['tanggal' => '2026-03-22', 'keterangan' => 'Hari Raya Idul Fitri 1447 H (Hari ke-2)', 'jenis' => 'nasional'],
            ['tanggal' => '2026-03-23', 'keterangan' => 'Cuti Bersama Idul Fitri 1447 H', 'jenis' => 'cuti_bersama'],
            ['tanggal' => '2026-03-24', 'keterangan' => 'Cuti Bersama Idul Fitri 1447 H', 'jenis' => 'cuti_bersama'],
            ['tanggal' => '2026-04-03', 'keterangan' => 'Wafat Yesus Kristus', 'jenis' => 'nasional'],
            ['tanggal' => '2026-04-05', 'keterangan' => 'Kebangkitan Yesus Kristus (Paskah)', 'jenis' => 'nasional'],
            ['tanggal' => '2026-05-01', 'keterangan' => 'Hari Buruh Internasional', 'jenis' => 'nasional'],
            ['tanggal' => '2026-05-14', 'keterangan' => 'Kenaikan Yesus Kristus', 'jenis' => 'nasional'],
            ['tanggal' => '2026-05-27', 'keterangan' => 'Hari Raya Idul Adha 1447 H', 'jenis' => 'nasional'],
            ['tanggal' => '2026-05-31', 'keterangan' => 'Hari Raya Waisak 2570 BE', 'jenis' => 'nasional'],
            ['tanggal' => '2026-06-01', 'keterangan' => 'Hari Lahir Pancasila', 'jenis' => 'nasional'],
            ['tanggal' => '2026-06-16', 'keterangan' => 'Tahun Baru Islam 1448 Hijriah', 'jenis' => 'nasional'],
            ['tanggal' => '2026-08-17', 'keterangan' => 'Hari Proklamasi Kemerdekaan RI ke-81', 'jenis' => 'nasional'],
            ['tanggal' => '2026-08-25', 'keterangan' => 'Maulid Nabi Muhammad SAW', 'jenis' => 'nasional'],
            ['tanggal' => '2026-12-25', 'keterangan' => 'Kelahiran Yesus Kristus (Hari Raya Natal)', 'jenis' => 'nasional'],
            ['tanggal' => '2026-12-26', 'keterangan' => 'Cuti Bersama Hari Raya Natal', 'jenis' => 'cuti_bersama'],
        ];
    }

    /**
     * Sinkronisasi data libur nasional dari API Publik (DayOff API / Libur Nasional)
     * dengan fallback ke default data lokal jika API gagal/offline.
     */
    public function syncFromApi(int $tahun): array
    {
        $count = 0;
        $items = [];
        $source = 'api';

        try {
            // Sumber 1: Nager.Date API (Layanan publik global yang sangat stabil)
            $response = Http::timeout(8)->get("https://date.nager.at/api/v3/PublicHolidays/{$tahun}/ID");

            if ($response->successful() && is_array($response->json())) {
                $raw = $response->json();
                foreach ($raw as $item) {
                    $tanggal = $item['date'] ?? null;
                    $keterangan = $item['localName'] ?? $item['name'] ?? 'Hari Libur Nasional';

                    if ($tanggal) {
                        $items[] = [
                            'tanggal' => Carbon::parse($tanggal)->format('Y-m-d'),
                            'keterangan' => $keterangan,
                            'jenis' => 'nasional',
                        ];
                    }
                }
                $source = 'Nager.Date API';
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal fetch Nager.Date API hari libur tahun {$tahun}: " . $e->getMessage());
        }

        // Sumber 2 (Cadangan): DayOff API
        if (empty($items)) {
            try {
                $response = Http::timeout(8)->get("https://dayoffapi.vercel.app/api?year={$tahun}");
                if ($response->successful() && is_array($response->json())) {
                    $raw = $response->json();
                    foreach ($raw as $item) {
                        $tanggal = $item['tanggal'] ?? null;
                        $keterangan = $item['keterangan'] ?? 'Hari Libur Nasional';
                        $isCuti = $item['is_cuti'] ?? false;

                        if ($tanggal) {
                            $items[] = [
                                'tanggal' => Carbon::parse($tanggal)->format('Y-m-d'),
                                'keterangan' => $keterangan,
                                'jenis' => $isCuti ? 'cuti_bersama' : 'nasional',
                            ];
                        }
                    }
                    $source = 'DayOff API';
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal fetch DayOff API hari libur tahun {$tahun}: " . $e->getMessage());
            }
        }

        // Sumber 3: Fallback data SKB 3 Menteri resmi 2026 jika tahun 2026
        if ($tahun === 2026) {
            $default2026 = self::defaultHolidays2026();
            // Gabungkan agar hari libur lengkap termasuk cuti bersama
            $existingDates = array_column($items, 'tanggal');
            foreach ($default2026 as $def) {
                if (!in_array($def['tanggal'], $existingDates, true)) {
                    $items[] = $def;
                }
            }
            if ($source === 'api') {
                $source = 'SKB Resmi 2026';
            }
        }

        if (empty($items)) {
            return [
                'success' => false,
                'message' => "Tidak ada data hari libur yang ditemukan untuk tahun {$tahun}. Anda dapat menambahkan tanggal libur secara manual.",
                'count' => 0,
            ];
        }

        foreach ($items as $item) {
            HariLibur::updateOrCreate(
                ['tanggal' => $item['tanggal']],
                [
                    'keterangan' => $item['keterangan'],
                    'jenis' => $item['jenis'] ?? 'nasional',
                    'tahun' => $tahun,
                ]
            );
            $count++;
        }

        return [
            'success' => true,
            'message' => "Berhasil menyinkronkan {$count} hari libur nasional untuk tahun {$tahun} (Sumber: {$source}).",
            'count' => $count,
            'source' => $source,
        ];
    }
}
