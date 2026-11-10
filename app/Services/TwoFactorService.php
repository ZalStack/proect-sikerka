<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

/**
 * Implementasi TOTP (RFC 6238 / HOTP RFC 4226) tanpa dependensi eksternal.
 *
 * Dipakai untuk Two-Factor Authentication akun Super Admin (Fitur A2).
 * Secret disimpan ter-encrypt di database, kode 6 digit divalidasi dengan
 * jendela ±1 periode (30 detik) untuk mengakomodasi selisih jam perangkat.
 */
class TwoFactorService
{
    public const PERIOD = 30;

    public const DIGITS = 6;

    /** Alfanumerik Base32 tanpa angka 0/1/8/9 — menghindari salah ketik. */
    private const B32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Buat secret Base32 baru (20 byte → 32 karakter), format standar
     * authenticator app (Google Authenticator, Authy, dll).
     */
    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    /**
     * String `otpauth://totp/...` yang dipindai QR oleh aplikasi authenticator.
     */
    public function provisioningUri(string $secret, string $accountName, string $issuer = 'SIKEKAR'): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            $label,
            $secret,
            rawurlencode($issuer),
            self::DIGITS,
            self::PERIOD
        );
    }

    /**
     * Kode TOTP 6 digit untuk periode tertentu (default: sekarang).
     * $backstep dipakai saat uji/verifikasi kode historis.
     */
    public function codeAt(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $counter = intdiv($timestamp, self::PERIOD);

        return $this->hotp($secret, $counter);
    }

    /**
     * Validasi kode yang dimasukkan pengguna, dengan toleransi jendela
     * ±$window periode (default 1 → ±30 detik).
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', (string) $code) ?? '';

        if (!preg_match('/^\d{' . self::DIGITS . '}$/', $code)) {
            return false;
        }

        $now = intdiv(time(), self::PERIOD);

        for ($offset = -$window; $offset <= $window; $offset++) {
            // hash_equals → constant-time, mencegah timing attack.
            if (hash_equals($this->hotp($secret, $now + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Buat beberapa recovery code acak. Disimpan dalam bentuk hash (bcrypt),
     * sehingga database yang bocor tidak langsung memberi akses cadangan.
     *
     * @return string[] daftar code mentah (hanya diksekali saat generate)
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            // 10 karakter acak, format XXXXX-XXXXX agar mudah diketik.
            $raw = strtoupper(substr(str_replace(['0', '1', '8', '9'], '', bin2hex(random_bytes(8))), 0, 10));
            $codes[] = substr($raw, 0, 5) . '-' . substr($raw, 5, 5);
        }

        return $codes;
    }

    /**
     * @param string[] $plainCodes
     * @return string[] hash bcrypt untuk disimpan ke database
     */
    public function hashRecoveryCodes(array $plainCodes): array
    {
        return array_map(fn ($code) => \Illuminate\Support\Facades\Hash::make($code), $plainCodes);
    }

    /**
     * Konsumsi (hapus) satu recovery code bila cocok.
     *
     * @param string[] $hashedCodes
     * @return string[] daftar hash tersisa
     */
    public function consumeRecoveryCode(array $hashedCodes, string $input): ?array
    {
        $input = strtoupper(trim(str_replace(' ', '', (string) $input)));

        if ($input === '') {
            return null;
        }

        foreach ($hashedCodes as $index => $hash) {
            if (\Illuminate\Support\Facades\Hash::check($input, $hash)) {
                unset($hashedCodes[$index]);

                return array_values($hashedCodes);
            }
        }

        return null;
    }

    // ==========================================================
    // CRYPTO INTERNAL
    // ==========================================================

    private function hotp(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);

        if ($key === '') {
            return str_repeat('0', self::DIGITS);
        }

        // 8 byte big-endian counter
        $binaryCounter = pack('N2', ($counter >> 32) & 0xFFFFFFFF, $counter & 0xFFFFFFFF);

        $hash = hash_hmac('sha1', $binaryCounter, $key, true);

        // Dynamic truncation (RFC 4226 §5.3)
        $offset = ord($hash[19]) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        );

        $otp = $binary % (10 ** self::DIGITS);

        return str_pad((string) $otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    public function base32Encode(string $data): string
    {
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $binary = str_split($binary, 5);
        $output = '';

        foreach ($binary as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $output .= self::B32_ALPHABET[bindec($chunk)];
        }

        // Padding standar Base32 (kelipatan 8)
        $remainder = strlen($data) % 3;
        if ($remainder === 1) {
            $output .= '======';
        } elseif ($remainder === 2) {
            $output .= '====';
        }

        return $output;
    }

    public function base32Decode(string $data): string
    {
        $data = strtoupper(preg_replace('/[^A-Z2-7]/', '', (string) $data) ?? '');

        if ($data === '') {
            return '';
        }

        $binary = '';
        foreach (str_split($data) as $char) {
            $index = strpos(self::B32_ALPHABET, $char);
            if ($index === false) {
                continue;
            }
            $binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) < 8) {
                break;
            }
            $output .= chr(bindec($byte));
        }

        return $output;
    }

    // ==========================================================
    // PENYIMPANAN SECRET (ENCRYPTED)
    // ==========================================================

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(?string $encrypted): ?string
    {
        if ($encrypted === null || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable $e) {
            // Secret tidak lagi terbaca (APP_KEY berubah) → anggap belum setup.
            return null;
        }
    }
}
