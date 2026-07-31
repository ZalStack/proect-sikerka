<?php
// app/Services/PamerSukuService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PamerSukuService
{
    protected string $baseUrl;
    protected string $siteUrl;

    public function __construct()
    {
        // .../api/external
        $this->baseUrl = rtrim(
            config('services.pamer_suku.base_url', 'https://pamersuku.read1kpmseikhlasnya.com/api/external'),
            '/'
        );

        // Dipakai buat prefix flyer_path yang formatnya path relatif,
        // contoh: "/storage/chief-flyers/xxx.jpg"
        $this->siteUrl = rtrim(
            config('services.pamer_suku.site_url', 'https://pamersuku.read1kpmseikhlasnya.com'),
            '/'
        );
    }

    /**
     * GET /volumes
     */
    public function getVolumes(): array
    {
        return Cache::remember('pamer_suku_volumes', now()->addMinutes(15), function () {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/volumes");

                if ($response->failed()) {
                    Log::warning('PamerSuku: gagal fetch volumes', ['status' => $response->status()]);
                    return [];
                }

                return $response->json('data', []);
            } catch (\Throwable $e) {
                Log::warning('PamerSuku: exception fetch volumes - ' . $e->getMessage());
                return [];
            }
        });
    }

    public function getLatestVolume(): ?array
    {
        return collect($this->getVolumes())->sortByDesc('volume_number')->first();
    }

    /**
     * GET /leaderboard/global?volume_id=X
     * Return leaderboard gabungan (top_3 + top_4_to_10) untuk satu volume.
     */
    public function getGlobalLeaderboard(int $volumeId): array
    {
        return $this->fetchLeaderboard('global', $volumeId);
    }

    /**
     * GET /leaderboard/pka?volume_id=X
     */
    public function getPkaLeaderboard(int $volumeId): array
    {
        return $this->fetchLeaderboard('pka', $volumeId);
    }

    private function fetchLeaderboard(string $board, int $volumeId): array
    {
        return Cache::remember("pamer_suku_leaderboard_{$board}_{$volumeId}", now()->addMinutes(15), function () use ($board, $volumeId) {
            try {
                $response = Http::timeout(15)->get("{$this->baseUrl}/leaderboard/{$board}", [
                    'volume_id' => $volumeId,
                ]);

                if ($response->failed()) {
                    Log::warning('PamerSuku: gagal fetch leaderboard', [
                        'board' => $board,
                        'volume_id' => $volumeId,
                        'status' => $response->status(),
                    ]);
                    return [];
                }

                $json = $response->json();

                return array_merge($json['top_3'] ?? [], $json['top_4_to_10'] ?? []);
            } catch (\Throwable $e) {
                Log::warning('PamerSuku: exception fetch leaderboard - ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * GET /kepala-suku?board=global|pka
     * Riwayat kepala suku per periode (bulan-tahun).
     */
    public function getKepalaSukuHistory(string $board = 'global'): array
    {
        return Cache::remember("pamer_suku_kepala_suku_{$board}", now()->addMinutes(15), function () use ($board) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/kepala-suku", [
                    'board' => $board,
                ]);

                if ($response->failed()) {
                    Log::warning('PamerSuku: gagal fetch kepala suku', [
                        'board' => $board,
                        'status' => $response->status(),
                    ]);
                    return [];
                }

                return $response->json('data', []);
            } catch (\Throwable $e) {
                Log::warning('PamerSuku: exception fetch kepala suku - ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Ambil entri kepala suku PALING BARU (berdasarkan year + month) untuk satu board.
     * Tetap dikembalikan walau kode_pegawai/player_name null (misal cuma ada flyer-nya),
     * supaya minimal flyer-nya masih bisa ditampilkan di halaman achievement.
     */
    public function getCurrentKepalaSuku(string $board = 'global'): ?array
    {
        $history = $this->getKepalaSukuHistory($board);

        if (empty($history)) {
            return null;
        }

        $sorted = collect($history)->sortByDesc(function ($item) {
            return sprintf('%04d%02d', $item['year'] ?? 0, $item['month'] ?? 0);
        })->values();

        $entry = $sorted->first();

        if ($entry && !empty($entry['flyer_path'])) {
            $entry['flyer_url'] = $this->siteUrl . $entry['flyer_path'];
        }

        return $entry;
    }

    /**
     * GET /leaderboard/search?q=...&type=all|global|pka
     */
    public function search(string $query, string $type = 'all'): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/leaderboard/search", [
                'q' => $query,
                'type' => $type,
            ]);

            if ($response->failed()) {
                return [];
            }

            return $response->json('data', []);
        } catch (\Throwable $e) {
            Log::warning('PamerSuku: exception search - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Rekap performa tiap peserta (dikelompokkan per kode_pegawai) LINTAS SEMUA
     * volume yang ada, untuk board global maupun pka. Otomatis mengikuti volume
     * baru yang ditambahkan (volume 24, 25, dst) — tidak di-hardcode ke satu
     * volume_id saja.
     *
     * Return format per kode_pegawai:
     * [
     *   '3102021' => [
     *       'player_name' => 'Nanda Lindawati',
     *       'division' => 'LPS',
     *       'global' => [ [...rincian per volume, terbaru duluan...], ... ],
     *       'pka' => [ ... ],
     *       'global_best_rank' => 1,
     *       'global_appearances' => 3,
     *       'pka_best_rank' => null,
     *       'pka_appearances' => 0,
     *   ],
     *   ...
     * ]
     */
    public function getLeaderboardSummaryByKodePegawai(): array
    {
        return Cache::remember('pamer_suku_summary_by_kode', now()->addMinutes(20), function () {
            $volumes = $this->getVolumes();
            $summary = [];

            foreach ($volumes as $volume) {
                $volumeId = $volume['id'] ?? null;
                $volumeNumber = $volume['volume_number'] ?? null;
                if (!$volumeId) {
                    continue;
                }

                foreach (['global', 'pka'] as $board) {
                    $entries = $board === 'global'
                        ? $this->getGlobalLeaderboard((int) $volumeId)
                        : $this->getPkaLeaderboard((int) $volumeId);

                    foreach ($entries as $entry) {
                        $kode = $entry['kode_pegawai'] ?? null;
                        if (!$kode) {
                            continue;
                        }

                        $summary[$kode]['player_name'] = $summary[$kode]['player_name'] ?? ($entry['player_name'] ?? null);
                        $summary[$kode]['division'] = $summary[$kode]['division'] ?? ($entry['division'] ?? null);

                        $summary[$kode][$board][] = array_merge($entry, [
                            'volume_id'     => $volumeId,
                            'volume_number' => $volumeNumber,
                        ]);
                    }
                }
            }

            foreach ($summary as $kode => &$data) {
                foreach (['global', 'pka'] as $board) {
                    $list = $data[$board] ?? [];

                    if (!empty($list)) {
                        $ranks = array_column($list, 'rank');
                        $data["{$board}_best_rank"] = min($ranks);
                        $data["{$board}_appearances"] = count($list);

                        usort($list, fn ($a, $b) => ($b['volume_number'] ?? 0) <=> ($a['volume_number'] ?? 0));
                        $data[$board] = $list;
                    } else {
                        $data[$board] = [];
                        $data["{$board}_best_rank"] = null;
                        $data["{$board}_appearances"] = 0;
                    }
                }
            }
            unset($data);

            return $summary;
        });
    }
}
