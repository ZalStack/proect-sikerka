<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Whitelist IP untuk akses halaman Super Admin (Fitur A5).
 *
 * Daftar IP disimpan pada system_settings key `superadmin_ip_whitelist`
 * (dipisah koma / baris baru). Bila daftar kosong → semua IP diizinkan
 * (perilaku default aman, tidak merusak instalasi yang sudah berjalan).
 *
 * Mendukung CIDR sederhana (contoh: 192.168.1.0/24) dan wildcard (10.0.0.*).
 */
class SuperAdminIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya berlaku untuk route ber-prefix superadmin dan user superadmin.
        if (!Auth::check() || Auth::user()->posisi !== 'superadmin') {
            return $next($request);
        }

        $raw = (string) SystemSetting::getValue('superadmin_ip_whitelist', '');
        $allowed = $this->parseList($raw);

        // Whitelist kosong = tidak ada pembatasan.
        if ($allowed === []) {
            return $next($request);
        }

        $ip = (string) $request->ip();

        foreach ($allowed as $entry) {
            if ($this->matches($entry, $ip)) {
                return $next($request);
            }
        }

        \Illuminate\Support\Facades\Log::warning('Akses Super Admin ditolak oleh whitelist IP.', [
            'ip' => $ip,
            'path' => $request->path(),
            'user' => Auth::id(),
        ]);

        ActivityLog::record(
            'access.ip_blocked',
            'Akses Superadmin ditolak: IP ' . $ip . ' tidak ada di whitelist.',
            ['module' => 'access', 'metadata' => ['ip' => $ip, 'path' => $request->path()]]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'IP Anda tidak diizinkan mengakses panel Super Admin.',
            ], 403);
        }

        return response()->view('errors.feature-disabled', [
            'feature' => 'superadmin_ip_whitelist',
            'meta' => [
                'name' => 'Akses Super Admin (Whitelist IP)',
                'category' => 'Keamanan',
                'icon' => 'fa-shield-halved',
                'color' => '#dc2626',
            ],
            'restricted' => true,
            'restriction_reason' => 'IP ' . $ip . ' tidak termasuk daftar IP yang diizinkan. Tambahkan IP ini di menu Pengaturan Keamanan.',
            'restriction_period' => null,
        ], 403);
    }

    /** @return string[] */
    private function parseList(string $raw): array
    {
        $parts = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $parts))));
    }

    private function matches(string $entry, string $ip): bool
    {
        $entry = trim($entry);

        if ($entry === '') {
            return false;
        }

        if ($entry === '*') {
            return true;
        }

        if ($entry === $ip) {
            return true;
        }

        // Wildcard sederhana: 10.0.0.*
        if (str_contains($entry, '*')) {
            $pattern = '/^' . str_replace('\*', '\d+', preg_quote($entry, '/')) . '$/';
            return (bool) preg_match($pattern, $ip);
        }

        // CIDR: 192.168.1.0/24
        if (str_contains($entry, '/')) {
            return $this->inCidr($entry, $ip);
        }

        return false;
    }

    private function inCidr(string $cidr, string $ip): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2) + [1 => '32'];

        if (!is_numeric($bits)) {
            return false;
        }

        $bits = (int) $bits;
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false || $bits < 0 || $bits > 32) {
            return false;
        }

        $mask = $bits === 0 ? 0 : (-1 << (32 - $bits)) & 0xFFFFFFFF;

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
