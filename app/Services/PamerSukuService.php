<?php
// app/Services/PamerSukuService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PamerSukuService
{
    protected string $baseUrl;

    public function __construct()
    {
        // Set PAMER_SUKU_API_URL di .env kalau mau override
        $this->baseUrl = rtrim(config('services.pamer_suku.base_url', 'https://pamersuku.read1kpmseikhlasnya.com/api/external'), '/');
    }

    /**
     * Ambil daftar semua volume yang ada.
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

    /**
     * Ambil volume dengan volume_number tertinggi (volume terbaru).
     */
    public function getLatestVolume(): ?array
    {
        $volumes = $this->getVolumes();

        if (empty($volumes)) {
            return null;
        }

        return collect($volumes)->sortByDesc('volume_number')->first();
    }

    /**
     * Ambil leaderboard global untuk satu volume.
     * GET /leaderboard/global?volume_id={id}
     */
    public function getLeaderboardGlobal(int $volumeId): array
    {
        return Cache::remember("pamer_suku_leaderboard_global_{$volumeId}", now()->addMinutes(10), function () use ($volumeId) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/leaderboard/global", [
                    'volume_id' => $volumeId,
                ]);

                if ($response->failed()) {
                    Log::warning('PamerSuku: gagal fetch leaderboard global', [
                        'volume_id' => $volumeId,
                        'status' => $response->status(),
                    ]);
                    return [];
                }

                return $response->json() ?? [];
            } catch (\Throwable $e) {
                Log::warning('PamerSuku: exception fetch leaderboard global - ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Ambil leaderboard PKA untuk satu volume.
     * GET /leaderboard/pka?volume_id={id}
     */
    public function getLeaderboardPka(int $volumeId): array
    {
        return Cache::remember("pamer_suku_leaderboard_pka_{$volumeId}", now()->addMinutes(10), function () use ($volumeId) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/leaderboard/pka", [
                    'volume_id' => $volumeId,
                ]);

                if ($response->failed()) {
                    Log::warning('PamerSuku: gagal fetch leaderboard pka', [
                        'volume_id' => $volumeId,
                        'status' => $response->status(),
                    ]);
                    return [];
                }

                return $response->json() ?? [];
            } catch (\Throwable $e) {
                Log::warning('PamerSuku: exception fetch leaderboard pka - ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Ambil daftar kepala suku (per bulan) untuk board tertentu.
     * GET /kepala-suku?board=global|pka
     */
    public function getKepalaSuku(string $board = 'global'): array
    {
        $board = in_array($board, ['global', 'pka']) ? $board : 'global';

        return Cache::remember("pamer_suku_kepala_suku_{$board}", now()->addMinutes(10), function () use ($board) {
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
     * Cari peserta berdasarkan nama.
     * GET /leaderboard/search?q={query}&type=all|global|pka
     */
    public function searchLeaderboard(string $query, string $type = 'all'): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $type = in_array($type, ['all', 'global', 'pka']) ? $type : 'all';

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/leaderboard/search", [
                'q' => $query,
                'type' => $type,
            ]);

            if ($response->failed()) {
                Log::warning('PamerSuku: gagal search leaderboard', [
                    'q' => $query,
                    'type' => $type,
                    'status' => $response->status(),
                ]);
                return [];
            }

            return $response->json('data', []);
        } catch (\Throwable $e) {
            Log::warning('PamerSuku: exception search leaderboard - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Petakan leaderboard (global + PKA) volume terbaru per kode_pegawai, supaya
     * gampang dicocokkan dengan data karyawan lokal (karyawans.kode_pegawai).
     * Kalau $volumeId tidak diisi, otomatis pakai volume terbaru.
     *
     * Return format per kode_pegawai:
     * [
     *   '3102021' => [
     *       'player_name' => 'Nanda Lindawati',
     *       'division'    => 'LPS',
     *       'global'      => ['rank' => 1, 'completion_time' => '0:01', ...] | null,
     *       'pka'         => [...] | null,
     *   ],
     *   ...
     * ]
     */
    public function getLeaderboardByKodePegawai(?int $volumeId = null): array
    {
        $volumeId = $volumeId ?? ($this->getLatestVolume()['id'] ?? null);

        if (!$volumeId) {
            return [];
        }

        $boards = [
            'global' => $this->getLeaderboardGlobal($volumeId),
            'pka' => $this->getLeaderboardPka($volumeId),
        ];

        $mapped = [];

        foreach ($boards as $boardName => $data) {
            $entries = array_merge($data['top_3'] ?? [], $data['top_4_to_10'] ?? []);

            foreach ($entries as $entry) {
                $kode = $entry['kode_pegawai'] ?? null;
                if (!$kode) {
                    continue;
                }

                $mapped[$kode]['player_name'] = $entry['player_name'] ?? $entry['display_name'] ?? null;
                $mapped[$kode]['division'] = $entry['division'] ?? null;
                $mapped[$kode][$boardName] = $entry;
            }
        }

        return [
            'volume_id' => $volumeId,
            'volume_number' => $boards['global']['volume_number'] ?? $boards['pka']['volume_number'] ?? null,
            'entries' => $mapped,
        ];
    }

    /**
     * Petakan status "kepala suku" (global & PKA) per kode_pegawai, lengkap dengan
     * daftar periode (bulan/tahun) yang pernah dijabat. Karyawan yang kode_pegawai-nya
     * masih kosong di sisi API (belum sinkron) otomatis dilewati.
     *
     * Return format per kode_pegawai:
     * [
     *   '3102021' => [
     *       'global' => [ ['period' => 'Juli 2026', 'month' => 7, 'year' => 2026, 'flyer_path' => '...'], ... ],
     *       'pka'    => [ ... ],
     *   ],
     *   ...
     * ]
     */
    public function getKepalaSukuByKodePegawai(): array
    {
        $mapped = [];

        foreach (['global', 'pka'] as $board) {
            foreach ($this->getKepalaSuku($board) as $entry) {
                $kode = $entry['kode_pegawai'] ?? null;
                if (!$kode) {
                    continue;
                }

                $mapped[$kode][$board][] = [
                    'period' => $entry['period'] ?? null,
                    'month' => $entry['month'] ?? null,
                    'year' => $entry['year'] ?? null,
                    'player_name' => $entry['player_name'] ?? null,
                    'division' => $entry['division'] ?? null,
                    'photo_path' => $entry['photo_path'] ?? null,
                    'flyer_path' => $entry['flyer_path'] ?? null,
                ];
            }
        }

        return $mapped;
    }

    /**
     * Ringkasan keseluruhan Pamer Suku lintas semua volume (dipakai untuk banner
     * ringkasan di halaman achievement HR).
     */
    /**
     * Ambil leaderboard (global & PKA) untuk SEMUA volume yang ada, dipetakan
     * per volume. Dipakai untuk rekap lengkap per volume di halaman HR.
     * Diurutkan dari volume terbaru ke terlama.
     *
     * Return:
     * [
     *   [
     *       'volume_id' => 1, 'volume_number' => 1,
     *       'global' => [ ['rank' => 1, 'kode_pegawai' => '...', 'player_name' => '...', 'total_time' => ..., ...], ... ],
     *       'pka'    => [ ... ],
     *   ],
     *   ...
     * ]
     */
    public function getAllLeaderboards(): array
    {
        return Cache::remember('pamer_suku_all_leaderboards', now()->addMinutes(15), function () {
            $volumes = collect($this->getVolumes())->sortByDesc('volume_number')->values();

            return $volumes->map(function ($volume) {
                $global = $this->getLeaderboardGlobal($volume['id']);
                $pka = $this->getLeaderboardPka($volume['id']);

                return [
                    'volume_id' => $volume['id'],
                    'volume_number' => $volume['volume_number'],
                    'global' => array_merge($global['top_3'] ?? [], $global['top_4_to_10'] ?? []),
                    'pka' => array_merge($pka['top_3'] ?? [], $pka['top_4_to_10'] ?? []),
                ];
            })->values()->all();
        });
    }

    /**
     * Rekap keseluruhan: rata-rata waktu pengerjaan tiap peserta digabung dari
     * SEMUA volume yang ada (bukan cuma volume terbaru), dipetakan per
     * kode_pegawai, per board (global & pka). Diurutkan dari rata-rata waktu
     * tercepat. Dipakai untuk tabel rekap keseluruhan Pamer Suku di halaman HR.
     *
     * Return:
     * [
     *   'global' => [
     *       '3102021' => [
     *           'player_name' => 'Nanda Lindawati', 'division' => 'LPS',
     *           'total_volumes_followed' => 3,
     *           'average_time' => 12.4,
     *           'best_time' => 8, 'best_rank' => 1,
     *       ],
     *       ...
     *   ],
     *   'pka' => [ ... ],
     * ]
     */
    public function getAggregatedLeaderboardByKodePegawai(): array
    {
        return Cache::remember('pamer_suku_aggregated_leaderboard', now()->addMinutes(15), function () {
            $allLeaderboards = $this->getAllLeaderboards();

            $aggregated = ['global' => [], 'pka' => []];

            foreach ($allLeaderboards as $volume) {
                foreach (['global', 'pka'] as $board) {
                    foreach ($volume[$board] as $entry) {
                        $kode = $entry['kode_pegawai'] ?? null;
                        if (!$kode) {
                            continue;
                        }

                        if (!isset($aggregated[$board][$kode])) {
                            $aggregated[$board][$kode] = [
                                'player_name' => $entry['player_name'] ?? $entry['display_name'] ?? null,
                                'division' => $entry['division'] ?? null,
                                'times' => [],
                                'best_time' => null,
                                'best_rank' => null,
                            ];
                        }

                        $time = $entry['total_time'] ?? null;
                        if ($time !== null) {
                            $aggregated[$board][$kode]['times'][] = $time;

                            if ($aggregated[$board][$kode]['best_time'] === null || $time < $aggregated[$board][$kode]['best_time']) {
                                $aggregated[$board][$kode]['best_time'] = $time;
                                $aggregated[$board][$kode]['best_rank'] = $entry['rank'] ?? null;
                            }
                        }
                    }
                }
            }

            foreach ($aggregated as $board => &$entries) {
                foreach ($entries as $kode => &$entry) {
                    $times = $entry['times'];
                    $entry['total_volumes_followed'] = count($times);
                    $entry['average_time'] = count($times) ? round(array_sum($times) / count($times), 1) : null;
                    unset($entry['times']);
                }
                unset($entry);

                // Urutkan dari rata-rata waktu pengerjaan tercepat
                $entries = collect($entries)
                    ->sortBy('average_time')
                    ->all();
            }
            unset($entries);

            return $aggregated;
        });
    }

    public function getOverallSummary(): array
    {
        $volumes = $this->getVolumes();
        $latestVolume = $this->getLatestVolume();

        $totalKepalaSukuGlobal = collect($this->getKepalaSuku('global'))
            ->filter(fn($entry) => !empty($entry['kode_pegawai']))
            ->count();

        $totalKepalaSukuPka = collect($this->getKepalaSuku('pka'))
            ->filter(fn($entry) => !empty($entry['kode_pegawai']))
            ->count();

        $latestParticipants = 0;
        if ($latestVolume) {
            $global = $this->getLeaderboardGlobal($latestVolume['id']);
            $latestParticipants = count($global['top_3'] ?? []) + count($global['top_4_to_10'] ?? []);
        }

        return [
            'total_volumes' => count($volumes),
            'latest_volume_number' => $latestVolume['volume_number'] ?? null,
            'latest_participants' => $latestParticipants,
            'total_kepala_suku_global' => $totalKepalaSukuGlobal,
            'total_kepala_suku_pka' => $totalKepalaSukuPka,
        ];
    }
}
