<?php

use App\Http\Middleware\HrMiddleware;
use App\Http\Middleware\KaryawanMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckFeatureEnabled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            CheckMaintenanceMode::class,
        ]);

        $middleware->alias([
            'superadmin' => SuperAdminMiddleware::class,
            'hr' => HrMiddleware::class,
            'karyawan' => KaryawanMiddleware::class,
            'maintenance' => CheckMaintenanceMode::class,
            'feature' => CheckFeatureEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();