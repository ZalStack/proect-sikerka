<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi API eksternal (aplikasi SUDOKU) via header X-API-Key
 * atau Authorization: Bearer.
 *
 * Perilaku KEAMANAN yang sengaja dibalik dari versi lama:
 *   - Kunci tidak dikonfigurasi  -> SEMUA request DITOLAK (fail-closed),
 *     bukan dilewati (fail-open) seperti sebelumnya.
 *   - Perbandingan memakai hash_equals (constant-time) untuk mencegah
 *     timing attack.
 */
class AuthenticateSudokuApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = (string) config('services.sudoku.api_key', '');

        if ($expectedKey === '') {
            Log::warning('API SUDOKU ditolak: SUDOKU_API_KEY belum dikonfigurasi.', [
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API key belum dikonfigurasi pada server.',
            ], 503);
        }

        $providedKey = (string) ($request->header('X-API-Key') ?: $request->bearerToken());

        if ($providedKey === '' || !hash_equals($expectedKey, $providedKey)) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
