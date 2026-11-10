<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\LoginHistory;
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

        // Pengaturan SMTP dari UI (Fitur B9) menimpa .env agar berlaku
        // tanpa restart. Aman bila tabel belum ada (sebelum migrate).
        try {
            \App\Models\SystemSetting::applyMailConfig();
        } catch (\Throwable $e) {
            report($e);
        }

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

            // Riwayat login terstruktur (Fitur C11) — untuk halaman
            // "Riwayat Login" & deteksi login mencurigakan.
            LoginHistory::record([
                'karyawan_id' => $event->user->id ?? null,
                'email' => $event->user->email ?? request()->input('email'),
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
                'success' => true,
                'guard' => $event->guard,
            ]);
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

            LoginHistory::record([
                'karyawan_id' => $event->user->id ?? null,
                'email' => $event->user->email ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
                'success' => true,
                'failure_reason' => 'logout',
                'guard' => $event->guard,
            ]);
        });

        Event::listen(Failed::class, function (Failed $event) {
            // Event Failed di Laravel tidak membawa exception — gunakan
            // kredensial yang dicoba sebagai konteks penyebab kegagalan.
            $reason = 'Kredensial tidak cocok';

            if ($event->user) {
                ActivityLog::record(
                    'auth.failed',
                    'Percobaan login gagal.',
                    [
                        'user' => $event->user,
                        'module' => 'auth',
                        'metadata' => ['guard' => $event->guard, 'reason' => $reason],
                    ]
                );
            }

            $triedEmail = is_array($event->credentials)
                ? ($event->credentials['email'] ?? null)
                : null;

            LoginHistory::record([
                'karyawan_id' => $event->user->id ?? null,
                'email' => $event->user->email ?? $triedEmail ?? request()->input('email'),
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
                'success' => false,
                'failure_reason' => $reason,
                'guard' => $event->guard,
            ]);
        });
    }
}
