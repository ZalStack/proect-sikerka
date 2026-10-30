<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        spl_autoload_register(function ($class) {
            $prefixes = [
                'PhpOffice\\PhpSpreadsheet\\' => base_path('vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/'),
                'ZipStream\\' => base_path('vendor/maennchen/zipstream-php/src/'),
                'Composer\\Pcre\\' => base_path('vendor/composer/pcre/src/'),
            ];

            foreach ($prefixes as $prefix => $baseDir) {
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    continue;
                }

                $relativeClass = substr($class, $len);
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'id', 'Indonesian');

        Event::listen(Login::class, function (Login $event) {
            ActivityLog::record(
                'auth.login',
                'Login berhasil ke sistem.',
                [
                    'user' => $event->user,
                    'module' => 'auth',
                    'metadata' => [
                        'guard' => $event->guard,
                        'remember' => $event->remember,
                    ],
                ]
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (!$event->user) {
                return;
            }

            ActivityLog::record(
                'auth.logout',
                'Logout dari sistem.',
                [
                    'user' => $event->user,
                    'module' => 'auth',
                    'metadata' => ['guard' => $event->guard],
                ]
            );
        });

        Event::listen(Failed::class, function (Failed $event) {
            if (!$event->user) {
                return;
            }

            ActivityLog::record(
                'auth.failed',
                'Percobaan login gagal.',
                [
                    'user' => $event->user,
                    'module' => 'auth',
                    'metadata' => ['guard' => $event->guard],
                ]
            );
        });
    }
}
