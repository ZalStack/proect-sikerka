<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuditActivity
{
    /**
     * Catat aksi perubahan data yang dilakukan Superadmin / HR / Karyawan.
     * Login & logout dicatat terpisah melalui event listener.
     *
     * Kegagalan pencatatan audit TIDAK boleh dibungkam diam-diam: tetap
     * dilaporkan ke log sistem (report) tanpa menggagalkan request utama.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $user = Auth::user();

            if (!$user) {
                return $response;
            }

            if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return $response;
            }

            $routeName = (string) $request->route()?->getName();

            if ($routeName === '' || in_array($routeName, ['logout', 'login', 'superadmin.login.store'], true)) {
                return $response;
            }

            // Aksi penting sudah dicatat eksplisit oleh controller dengan
            // deskripsi yang lebih kaya. Middleware ini menjadi jaring pengaman
            // untuk route administratif lain yang belum memiliki log khusus.
            if (str_starts_with($routeName, 'superadmin.backup.')
                || str_starts_with($routeName, 'superadmin.health.')
                || str_starts_with($routeName, 'superadmin.features.assignments.')
                || str_starts_with($routeName, 'superadmin.message-templates.')) {
                return $response;
            }

            // Notifikasi & aksi ringan karyawan sehari-hari tidak perlu
            // dicatat (hanya membanjiri log tanpa nilai audit).
            if (in_array($routeName, ['notifications.mark-read', 'profile.update'], true)) {
                return $response;
            }

            $module = $this->moduleForRoute($routeName, $user->posisi);

            ActivityLog::record(
                'http.' . strtolower($request->method()),
                'Aktivitas ' . strtoupper($request->method()) . ' pada ' . $routeName,
                [
                    'user' => $user,
                    'module' => $module,
                    'metadata' => [
                        'route' => $routeName,
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'status' => $response->getStatusCode(),
                        'actor_posisi' => $user->posisi,
                    ],
                ]
            );
        } catch (\Throwable $e) {
            // Audit log tidak boleh mengganggu request utama, tetapi
            // kegagalannya wajib terlihat di log sistem (jangan dibungkam).
            report($e);
        }

        return $response;
    }

    /** Tebak modul audit dari nama route agar tampilan log lebih rapi. */
    private function moduleForRoute(string $routeName, ?string $posisi): string
    {
        $prefix = Str::before($routeName, '.');

        $map = [
            'superadmin' => 'users',
            'absensi' => 'absensi',
            'cuti' => 'cuti',
            'karyawan' => 'users',
            'pengumuman' => 'system',
            'khataman' => 'system',
            'sunnah' => 'system',
            'fhl' => 'system',
            'perjalanan' => 'absensi',
            'profile' => 'users',
        ];

        return $map[$prefix] ?? ($posisi === 'karyawan' ? 'absensi' : 'system');
    }
}
