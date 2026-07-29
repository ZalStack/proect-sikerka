<?php
// app/Services/EnglishTodayService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnglishTodayService
{
    protected string $baseUrl;

    public function __construct()
    {
        // Set ENGLISH_TODAY_API_URL di .env kalau mau override
        $this->baseUrl = rtrim(
            config('services.english_today.base_url', 'https://englishtoday.read1kpmseikhlasnya.com/api'),
            '/'
        );
    }

    /**
     * Ambil semua data karyawan/employee dari sistem English Today.
     * GET /hr/employees
     */
    public function getEmployees(): array
    {
        return Cache::remember('english_today_employees', now()->addMinutes(10), function () {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/hr/employees");

                if ($response->failed()) {
                    Log::warning('EnglishToday: gagal fetch employees', ['status' => $response->status()]);
                    return [];
                }

                return $response->json('data', []);
            } catch (\Throwable $e) {
                Log::warning('EnglishToday: exception fetch employees - ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Ambil daftar SEMUA quiz yang ada (bukan cuma quiz id tertentu).
     * GET /hr/quizzes
     *
     * Return: [ ['id' => 2, 'title' => '...', 'status' => 'active', ...], ... ]
     */
    public function getAllQuizzes(): array
    {
        return Cache::remember('english_today_quizzes', now()->addMinutes(15), function () {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/hr/quizzes");

                if ($response->failed()) {
                    Log::warning('EnglishToday: gagal fetch quizzes', ['status' => $response->status()]);
                    return [];
                }

                // apiResource biasanya balikin {data: [...]} atau {data: {data: [...]}} kalau dipaginate.
                // Kita coba dua-duanya biar aman.
                $data = $response->json('data', []);

                if (isset($data['data']) && is_array($data['data'])) {
                    return $data['data'];
                }

                return is_array($data) ? $data : [];
            } catch (\Throwable $e) {
                Log::warning('EnglishToday: exception fetch quizzes - ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Ambil hanya ID dari semua quiz yang ada.
     */
    public function getAllQuizIds(): array
    {
        return collect($this->getAllQuizzes())
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Ambil detail report/attempt untuk satu quiz.
     * GET /hr/reports/{quizId}
     */
    public function getQuizReport(int $quizId): ?array
    {
        return Cache::remember("english_today_report_{$quizId}", now()->addMinutes(5), function () use ($quizId) {
            try {
                $response = Http::timeout(15)->get("{$this->baseUrl}/hr/reports/{$quizId}");

                if ($response->failed()) {
                    Log::warning('EnglishToday: gagal fetch report', [
                        'quiz_id' => $quizId,
                        'status' => $response->status(),
                    ]);
                    return null;
                }

                return $response->json('data');
            } catch (\Throwable $e) {
                Log::warning('EnglishToday: exception fetch report - ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Mapping hasil SEMUA quiz English Today per email, supaya gampang dicocokkan
     * dengan data karyawan lokal (karyawans.email). Otomatis mengikuti quiz baru
     * yang muncul (quiz 3, 4, dst) tanpa perlu ganti kode — tidak di-hardcode ke
     * satu quiz_id saja.
     *
     * Kalau $quizIds tidak diisi, otomatis ambil SEMUA quiz yang ada lewat
     * getAllQuizIds().
     *
     * Return format per email:
     * [
     *   'ridwan.hasan@gmail.com' => [
     *       'average_score'        => 79.5,
     *       'best_score'           => 86,
     *       'total_quizzes_taken'  => 2,
     *       'latest_attempt'       => [
     *           'quiz_id' => 2, 'quiz_title' => '...', 'score' => 86,
     *           'total_correct' => 13, 'total_wrong' => 2, 'completed_at' => '...',
     *       ],
     *       'attempts' => [ [...], [...] ], // semua attempt, terbaru duluan
     *   ],
     *   ...
     * ]
     */
    public function getScoresByEmail(?array $quizIds = null): array
    {
        $quizIds = $quizIds ?? $this->getAllQuizIds();

        $scores = [];

        foreach ($quizIds as $quizId) {
            $report = $this->getQuizReport($quizId);

            if (!$report || empty($report['quiz']['attempts'])) {
                continue;
            }

            $quizTitle = $report['quiz']['title'] ?? "Quiz #{$quizId}";

            foreach ($report['quiz']['attempts'] as $attempt) {
                // Lewati attempt yang belum selesai (skor masih null)
                if (($attempt['status'] ?? null) !== 'completed') {
                    continue;
                }

                $email = strtolower(trim($attempt['user']['email'] ?? ''));
                if (!$email) {
                    continue;
                }

                $scores[$email]['attempts'][] = [
                    'quiz_id'       => $quizId,
                    'quiz_title'    => $quizTitle,
                    'score'         => $attempt['score'] ?? 0,
                    'total_correct' => $attempt['total_correct'] ?? 0,
                    'total_wrong'   => $attempt['total_wrong'] ?? 0,
                    'completed_at'  => $attempt['completed_at'] ?? null,
                ];
            }
        }

        foreach ($scores as $email => &$entry) {
            $attempts = $entry['attempts'];

            // Attempt terbaru di paling atas
            usort($attempts, function ($a, $b) {
                return strcmp($b['completed_at'] ?? '', $a['completed_at'] ?? '');
            });

            $scoreValues = array_column($attempts, 'score');

            $entry['attempts'] = $attempts;
            $entry['latest_attempt'] = $attempts[0] ?? null;
            $entry['total_quizzes_taken'] = count($attempts);
            $entry['best_score'] = count($scoreValues) ? max($scoreValues) : 0;
            $entry['average_score'] = count($scoreValues)
                ? round(array_sum($scoreValues) / count($scoreValues), 1)
                : 0;
        }
        unset($entry);

        return $scores;
    }

    /**
     * Ringkasan keseluruhan lintas SEMUA quiz (bukan cuma satu quiz).
     * Dipakai untuk banner ringkasan di halaman achievement HR.
     */
    public function getOverallSummary(): array
    {
        $quizzes = $this->getAllQuizzes();
        $quizIds = collect($quizzes)->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $scoresByEmail = $this->getScoresByEmail($quizIds);

        $allAttemptScores = [];
        foreach ($scoresByEmail as $entry) {
            foreach ($entry['attempts'] as $attempt) {
                $allAttemptScores[] = $attempt['score'];
            }
        }

        return [
            'total_quizzes'      => count($quizzes),
            'total_participants' => count($scoresByEmail),
            'total_attempts'     => count($allAttemptScores),
            'average_score'      => count($allAttemptScores)
                ? round(array_sum($allAttemptScores) / count($allAttemptScores), 1)
                : 0,
        ];
    }
}
