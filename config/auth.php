<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'karyawans',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'karyawans',
        ],
    ],

    'providers' => [
        'karyawans' => [
            'driver' => 'virtual_karyawan',
            'model' => App\Models\Karyawan::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Root / Emergency Super Admin (.env In-Memory)
    |--------------------------------------------------------------------------
    |
    | Memungkinkan akun Super Admin diakses langsung menggunakan kredensial
    | environment tanpa harus mendaftarkan / menyimpan user ke database.
    |
    */
    'root_superadmin' => [
        'enabled' => env('SUPERADMIN_ROOT_ENABLED', true),
        'email' => env('SUPERADMIN_ROOT_EMAIL', 'root@sikekar.local'),
        'password' => env('SUPERADMIN_ROOT_PASSWORD', 'RootAdmin2026!'),
        'name' => env('SUPERADMIN_ROOT_NAME', 'Root Super Admin'),
        'pamersuku_env_path' => env('PAMERSUKU_ENV_PATH'),
    ],

    'passwords' => [
        'karyawans' => [
            'provider' => 'karyawans',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
