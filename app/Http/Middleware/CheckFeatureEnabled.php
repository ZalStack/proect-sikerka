<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Super Admin memiliki akses penuh ke seluruh fitur walau dimatikan untuk user umum
        if (Auth::check() && Auth::user()->posisi === 'superadmin') {
            return $next($request);
        }

        // Cek status fitur
        if (!SystemSetting::isFeatureEnabled($feature)) {
            $featureMeta = SystemSetting::AVAILABLE_FEATURES[$feature] ?? [
                'name' => ucfirst($feature),
                'category' => 'Fitur Sistem',
                'icon' => 'fa-ban',
                'color' => '#ec1d1d',
            ];

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'feature_disabled' => true,
                    'message' => 'Fitur ' . $featureMeta['name'] . ' sedang dinonaktifkan sementara oleh Administrator.',
                ], 403);
            }

            return response()->view('errors.feature-disabled', [
                'feature' => $feature,
                'meta' => $featureMeta,
            ], 403);
        }

        return $next($request);
    }
}
