<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\RemoteAccessViolation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ==========================================================
 * ANTI REMOTE ACCESS (TeamViewer / AnyDesk / RustDesk / RDP / VNC)
 * ==========================================================
 * Browser TIDAK bisa membaca daftar proses komputer, sehingga deteksi dilakukan
 * secara heuristik dari 3 lapis sinyal:
 *
 *  1. Client-side flags  : sinyal eksplisit yang dilaporkan JavaScript di halaman absensi
 *                          (driver display virtual, automation, headless, dll).
 *  2. Device fingerprint : string driver display WebGL (UNMASKED_RENDERER/VENDOR) yang
 *                          sering memuat nama aplikasi remote access, plus resolusi layar.
 *  3. Server-side        : konsistensi perangkat (user-agent & fingerprint antara
 *                          check-in/check-out, satu perangkat untuk banyak akun) dan
 *                          pola user-agent browser otomatis/headless.
 *
 * Risiko TINGGI (1 sinyal kuat ATAU ≥2 sinyal lemah) → check-in/out ditolak,
 * pelanggaran tercatat KONFIRMASI, ban otomatis diterapkan sesuai tangga.
 * Risiko RENDAH (tepat 1 sinyal lemah) → absensi tetap jalan, pelanggaran masuk
 * antrean review Superadmin.
 */
class RemoteAccessDetectionService
{
    private const OFFICE_TIMEZONE = 'Asia/Jakarta';

    /** Kata kunci nama aplikasi remote desktop / remote access. */
    private const REMOTE_TOOL_KEYWORDS = [
        'teamviewer', 'anydesk', 'rustdesk', 'parsec', 'moonlight', 'sunshine',
        'splashtop', 'logmein', 'screenconnect', 'connectwise', 'dameware',
        'ultravnc', 'realvnc', 'tightvnc', 'remotepc', 'ammyy', 'vnc',
        'remote desktop', 'rdp', 'mstsc',
    ];

    /** Pola user-agent browser automation / headless (sinyal kuat). */
    private const HEADLESS_UA_PATTERNS = ['headlesschrome', 'phantomjs'];

    /** Daftar flag client yang dianggap beresiko tinggi (exact atau prefix 'kode:detail'). */
    private const HIGH_CONFIDENCE_FLAGS = [
        'driver_remote_desktop_terdeteksi',
        'user_agent_remote_tool_terdeteksi',
        'headless_browser_terdeteksi',
        'automasi_browser_terdeteksi',
        'geolocation_api_dimodifikasi',
    ];

    /** Bobot sinyal: satu sinyal kuat sudah cukup untuk risiko TINGGI. */
    private const WEIGHT_HIGH = 100;
    private const WEIGHT_LOW = 40;

    /** Alasan singkat yang ditulis ke kolom suspicious_reason pada data absensi. */
    public const REASON_LABEL = 'Indikasi akses remote - review superadmin';

    /**
     * Analisa request absensi dan tentukan level risiko (none / low / high).
     *
     * @return array{risk_level: string, risk_score: int, signals: array, fingerprint: ?array}
     */
    public function assess(Request $request, Karyawan $user, string $context): array
    {
        $signals = [];
        $clientFlags = array_values(array_unique(array_filter(array_map('strval', (array) $request->input('client_flags', [])))));
        $fingerprint = $this->normalizeFingerprint($request->input('device_fingerprint'));
        $todayAbsensi = $this->todayAbsensi($user->id);

        // ----------------------------------------------------------
        // 1. Sinyal dari flag client (browser)
        // ----------------------------------------------------------
        foreach ($clientFlags as $flag) {
            if ($this->isHighConfidenceFlag($flag)) {
                $signals[] = $this->signal('client_flag', $flag, self::WEIGHT_HIGH, 'Sinyal kecurangan beresiko tinggi dilaporkan browser');
            } else {
                $signals[] = $this->signal('client_flag', $flag, self::WEIGHT_LOW, 'Sinyal anomali beresiko rendah dilaporkan browser');
            }
        }

        // ----------------------------------------------------------
        // 2. Device fingerprint (driver display WebGL + resolusi layar)
        // ----------------------------------------------------------
        if ($fingerprint !== null) {
            $rendererHaystack = strtolower(trim(($fingerprint['renderer'] . ' ' . $fingerprint['vendor'])));

            foreach (self::REMOTE_TOOL_KEYWORDS as $keyword) {
                if ($rendererHaystack !== '' && str_contains($rendererHaystack, $keyword)) {
                    $signals[] = $this->signal(
                        'webgl_driver',
                        'driver_remote_desktop_terdeteksi:' . $keyword,
                        self::WEIGHT_HIGH,
                        'Driver display virtual remote access terdeteksi di WebGL: ' . trim($fingerprint['renderer'] . ' ' . $fingerprint['vendor'])
                    );
                    break;
                }
            }

            $uaLower = strtolower($fingerprint['ua']);
            foreach (self::REMOTE_TOOL_KEYWORDS as $keyword) {
                if ($uaLower !== '' && str_contains($uaLower, $keyword)) {
                    $signals[] = $this->signal(
                        'user_agent',
                        'user_agent_remote_tool_terdeteksi:' . $keyword,
                        self::WEIGHT_HIGH,
                        'User-agent browser memuat nama aplikasi remote access'
                    );
                    break;
                }
            }

            // Resolusi layar sangat kecil → umum pada sesi remote control / virtual display
            $screenParts = array_map('intval', explode('x', $fingerprint['screen']));
            if (count($screenParts) === 2 && ($screenParts[0] > 0 && $screenParts[0] < 400 || $screenParts[1] > 0 && $screenParts[1] < 400)) {
                $signals[] = $this->signal('screen', 'resolusi_layar_tidak_wajar', self::WEIGHT_LOW, 'Resolusi layar sangat kecil (' . $fingerprint['screen'] . '), umum pada sesi remote control');
            }
        }

        // ----------------------------------------------------------
        // 3. Pemeriksaan konsistensi server-side
        // ----------------------------------------------------------
        $userAgent = (string) $request->userAgent();

        foreach (self::HEADLESS_UA_PATTERNS as $pattern) {
            if (str_contains(strtolower($userAgent), $pattern)) {
                $signals[] = $this->signal('server_ua', 'headless_browser_terdeteksi', self::WEIGHT_HIGH, 'User-agent terindikasi browser otomatis/headless');
                break;
            }
        }

        // Check-out dari perangkat berbeda daripada saat check-in (di hari yang sama)
        if ($context === 'checkout' && $todayAbsensi && $todayAbsensi->user_agent && $todayAbsensi->user_agent !== $userAgent) {
            $signals[] = $this->signal('server_consistency', 'perangkat_berubah_disesi', self::WEIGHT_LOW, 'User-agent check-out berbeda dari saat check-in');
        }

        // Fingerprint perangkat berubah di hari yang sama
        if ($context === 'checkout' && $todayAbsensi && $todayAbsensi->device_fingerprint_hash
            && $fingerprint !== null && $todayAbsensi->device_fingerprint_hash !== $fingerprint['hash']) {
            $signals[] = $this->signal('server_consistency', 'fingerprint_perangkat_berubah', self::WEIGHT_LOW, 'Fingerprint perangkat check-out berbeda dari saat check-in');
        }

        // Satu fingerprint perangkat dipakai beberapa akun di hari yang sama
        // (indikasi akun dikerjakan pihak lain dari jarak jauh)
        if ($fingerprint !== null) {
            $shared = Absensi::where('device_fingerprint_hash', $fingerprint['hash'])
                ->where('karyawan_id', '!=', $user->id)
                ->whereDate('tanggal', Carbon::today(self::OFFICE_TIMEZONE))
                ->exists();

            if ($shared) {
                $signals[] = $this->signal('server_consistency', 'fingerprint_dipakai_banyak_akun', self::WEIGHT_LOW, 'Fingerprint perangkat yang sama tercatat pada akun lain hari ini');
            }
        }

        // ----------------------------------------------------------
        // 4. Hitung skor & level risiko
        // ----------------------------------------------------------
        $score = (int) array_sum(array_column($signals, 'weight'));
        $hasHigh = collect($signals)->contains(fn ($s) => $s['weight'] >= self::WEIGHT_HIGH);
        $lowCount = collect($signals)->filter(fn ($s) => $s['weight'] < self::WEIGHT_HIGH)->count();

        if ($hasHigh || $lowCount >= 2) {
            $riskLevel = RemoteAccessViolation::RISK_HIGH;
        } elseif ($lowCount === 1) {
            $riskLevel = RemoteAccessViolation::RISK_LOW;
        } else {
            $riskLevel = 'none';
        }

        return [
            'risk_level' => $riskLevel,
            'risk_score' => min(255, $score),
            'signals' => $signals,
            'fingerprint' => $fingerprint,
        ];
    }

    /**
     * Catat pelanggaran presensi untuk review Superadmin.
     *
     * - $autoConfirm = true (risiko tinggi) → status confirmed + ban otomatis diterapkan.
     * - $autoConfirm = false (risiko rendah) → status pending_review, karyawan TIDAK diblokir.
     *
     * Satu record per karyawan per hari: deteksi berulang di hari yang sama digabung
     * (bukti ditambahkan) dan TIDAK menambah strike untuk kedua kalinya.
     */
    public function recordViolation(Request $request, Karyawan $user, string $context, array $assessment, bool $autoConfirm = false): RemoteAccessViolation
    {
        $signals = $assessment['signals'];
        $fingerprint = $assessment['fingerprint'] ?? null;
        $isHigh = $assessment['risk_level'] === RemoteAccessViolation::RISK_HIGH;
        $riskLevel = $isHigh ? RemoteAccessViolation::RISK_HIGH : RemoteAccessViolation::RISK_LOW;

        $todayAbsensi = $this->todayAbsensi($user->id);

        $violation = RemoteAccessViolation::where('karyawan_id', $user->id)
            ->whereDate('tanggal', Carbon::today(self::OFFICE_TIMEZONE))
            ->first();

        if ($violation) {
            // Gabungkan bukti baru tanpa duplikasi kode sinyal yang sudah ada
            $merged = $violation->signals ?? [];
            $existingCodes = array_column($merged, 'code');

            foreach ($signals as $signal) {
                if (!in_array($signal['code'], $existingCodes, true)) {
                    $merged[] = $signal;
                    $existingCodes[] = $signal['code'];
                }
            }

            $violation->signals = $merged;
            $violation->risk_score = min(255, max((int) $violation->risk_score, (int) $assessment['risk_score']));

            if (!$violation->absensi_id && $todayAbsensi) {
                $violation->absensi_id = $todayAbsensi->id;
            }

            // Deteksi baru berisiko tinggi & belum pernah dikonfirmasi
            // (termasuk yang sebelumnya ditandai false positive) → konfirmasi + ban.
            if ($isHigh && in_array($violation->status, [RemoteAccessViolation::STATUS_PENDING, RemoteAccessViolation::STATUS_DISMISSED], true)) {
                $violation->risk_level = RemoteAccessViolation::RISK_HIGH;
                $violation->status = RemoteAccessViolation::STATUS_CONFIRMED;
                $violation->strike_number = $user->fresh()->applyBan();
            }

            $violation->save();

            // Badge antrean review di sidebar superadmin ikut ter-update langsung
            Cache::forget('superadmin_pending_violations');

            return $violation;
        }

        $status = ($autoConfirm && $isHigh) ? RemoteAccessViolation::STATUS_CONFIRMED : RemoteAccessViolation::STATUS_PENDING;

        $violation = RemoteAccessViolation::create([
            'karyawan_id' => $user->id,
            'absensi_id' => $todayAbsensi?->id,
            'tanggal' => Carbon::today(self::OFFICE_TIMEZONE),
            'konteks' => $context,
            'signals' => $signals,
            'risk_score' => min(255, (int) $assessment['risk_score']),
            'risk_level' => $riskLevel,
            'status' => $status,
            'notes' => $this->buildEvidenceNote($fingerprint, $request),
        ]);

        if ($status === RemoteAccessViolation::STATUS_CONFIRMED) {
            $violation->strike_number = $user->fresh()->applyBan();
            $violation->save();
        }

        // Badge antrean review di sidebar superadmin ikut ter-update langsung
        Cache::forget('superadmin_pending_violations');

        Log::warning('Pelanggaran presensi (indikasi akses remote) tercatat', [
            'karyawan_id' => $user->id,
            'violation_id' => $violation->id,
            'konteks' => $context,
            'risk_level' => $riskLevel,
            'risk_score' => $violation->risk_score,
            'status' => $violation->status,
            'strike_number' => $violation->strike_number,
            'signals' => array_column($signals, 'code'),
            'ip' => $request->ip(),
        ]);

        return $violation;
    }

    /**
     * Ambil & bersihkan fingerprint perangkat yang dikirim browser (null kalau kosong).
     * Hash dihitung ulang di server supaya konsisten untuk deteksi silang.
     */
    private function normalizeFingerprint($input): ?array
    {
        if (!is_array($input) || empty($input)) {
            return null;
        }

        $pick = fn ($key, $max) => isset($input[$key]) ? mb_substr(trim((string) $input[$key]), 0, $max) : '';

        $fingerprint = [
            'renderer' => $pick('renderer', 190),
            'vendor' => $pick('vendor', 190),
            'screen' => $pick('screen', 21),
            'avail' => $pick('avail', 21),
            'dpr' => $pick('dpr', 10),
            'color_depth' => $pick('colorDepth', 10),
            'tz' => $pick('tz', 64),
            'lang' => $pick('lang', 35),
            'platform' => $pick('platform', 50),
            'ua' => $pick('ua', 255),
        ];

        if ($fingerprint['renderer'] === '' && $fingerprint['vendor'] === '' && $fingerprint['screen'] === '' && $fingerprint['ua'] === '') {
            return null;
        }

        $fingerprint['hash'] = hash('sha256', json_encode($fingerprint));

        return $fingerprint;
    }

    private function todayAbsensi(int $karyawanId): ?Absensi
    {
        return Absensi::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', Carbon::today(self::OFFICE_TIMEZONE))
            ->first();
    }

    private function isHighConfidenceFlag(string $flag): bool
    {
        foreach (self::HIGH_CONFIDENCE_FLAGS as $prefix) {
            if ($flag === $prefix || str_starts_with($flag, $prefix . ':')) {
                return true;
            }
        }

        return false;
    }

    private function signal(string $source, string $code, int $weight, string $detail): array
    {
        return [
            'source' => $source,
            'code' => mb_substr($code, 0, 100),
            'weight' => $weight,
            'detail' => mb_substr($detail, 0, 255),
        ];
    }

    /** Ringkasan bukti teknis (IP, user-agent, WebGL, layar, fingerprint) untuk review Superadmin. */
    private function buildEvidenceNote(?array $fingerprint, Request $request): string
    {
        $lines = [
            'IP: ' . $request->ip(),
            'User-Agent: ' . mb_substr((string) $request->userAgent(), 0, 255),
        ];

        if ($fingerprint !== null) {
            $lines[] = 'WebGL: ' . trim($fingerprint['renderer'] . ' ' . $fingerprint['vendor']);
            $lines[] = 'Layar: ' . ($fingerprint['screen'] ?: '-') . ' @' . ($fingerprint['dpr'] ?: '-') . 'x';
            $lines[] = 'Zona: ' . ($fingerprint['tz'] ?: '-') . ' | Bahasa: ' . ($fingerprint['lang'] ?: '-');
            $lines[] = 'Fingerprint: ' . $fingerprint['hash'];
        }

        return implode("\n", $lines);
    }
}
