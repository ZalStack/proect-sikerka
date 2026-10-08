<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika maintenance tidak aktif, lanjutkan request
        if (!SystemSetting::isMaintenance()) {
            return $next($request);
        }

        // Super Admin selalu memiliki akses penuh (bypass maintenance)
        if (Auth::check() && Auth::user()->posisi === 'superadmin') {
            return $next($request);
        }

        // Izinkan route auth/login/logout dan superadmin agar Super Admin bisa login saat sistem maintenance
        $allowedRoutes = [
            'login',
            'logout',
            'logout.get',
            'password.request',
            'password.verify',
            'password.reset.form',
            'password.reset',
            'refresh.captcha',
        ];

        $currentRouteName = $request->route() ? $request->route()->getName() : '';
        if (in_array($currentRouteName, $allowedRoutes, true) || str_starts_with((string)$currentRouteName, 'superadmin.')) {
            return $next($request);
        }

        // Izinkan path superadmin atau login secara eksplisit
        $path = $request->path();
        if (str_starts_with($path, 'superadmin') || $path === 'login' || $path === 'logout') {
            return $next($request);
        }

        $info = SystemSetting::getMaintenanceInfo();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'maintenance' => true,
                'status' => 'service_unavailable',
                'title' => $info['title'],
                'message' => $info['message'],
                'end_time' => $info['end_time'],
            ], 503);
        }

        return response()->view('maintenance', [
            'info' => $info,
        ], 503);
    }
}
