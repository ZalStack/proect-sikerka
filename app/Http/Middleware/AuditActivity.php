<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditActivity
{
    /**
     * Catat aksi perubahan data yang dilakukan Superadmin / HR.
     * Login & logout dicatat terpisah melalui event listener.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $user = Auth::user();

            if (!$user || !in_array($user->posisi, ['superadmin', 'hr'], true)) {
                return $response;
            }

            if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return $response;
            }

            $routeName = $request->route()?->getName();

            if (!$routeName || in_array($routeName, ['logout', 'login', 'superadmin.login.store'], true)) {
                return $response;
            }

            // Aksi penting sudah dicatat eksplisit oleh controller dengan
            // deskripsi yang lebih kaya. Middleware ini menjadi jaring pengaman
            // untuk route administratif lain yang belum memiliki log khusus.
            if (str_starts_with((string) $routeName, 'superadmin.backup.')
                || str_starts_with((string) $routeName, 'superadmin.health.')) {
                return $response;
            }

            ActivityLog::record(
                'http.' . strtolower($request->method()),
                'Aktivitas ' . strtoupper($request->method()) . ' pada ' . $routeName,
                [
                    'module' => 'http',
                    'metadata' => [
                        'route' => $routeName,
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'status' => $response->getStatusCode(),
                    ],
                ]
            );
        } catch (\Throwable $e) {
            // Audit log tidak boleh mengganggu request utama.
        }

        return $response;
    }
}
