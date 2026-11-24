<?php

namespace App\Auth;

use App\Models\Karyawan;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * Custom User Provider yang memungkinkan autentikasi Root Super Admin
 * berbasis kredensial .env (in-memory / virtual) tanpa perlu record di database.
 *
 * Mendukung pembacaan kredensial langsung dari file .env milik aplikasi
 * Pamersuku (jika berada dalam satu server hosting yang sama).
 */
class VirtualSuperAdminProvider extends EloquentUserProvider
{
    public const VIRTUAL_ID = 'virtual-root-superadmin';

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        if ($identifier === self::VIRTUAL_ID && $this->isRootEnabled()) {
            return $this->getVirtualSuperAdmin();
        }

        return parent::retrieveById($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        if ($identifier === self::VIRTUAL_ID && $this->isRootEnabled()) {
            return $this->getVirtualSuperAdmin();
        }

        return parent::retrieveByToken($identifier, $token);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token)
    {
        if (method_exists($user, 'isVirtual') && $user->isVirtual()) {
            return;
        }

        parent::updateRememberToken($user, $token);
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        if ($this->isRootEnabled() && !empty($credentials['email'])) {
            $rootConfig = $this->getRootConfig();
            $rootEmail = (string) ($rootConfig['email'] ?? '');
            if ($rootEmail !== '' && strtolower(trim($credentials['email'])) === strtolower(trim($rootEmail))) {
                return $this->getVirtualSuperAdmin();
            }
        }

        return parent::retrieveByCredentials($credentials);
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials)
    {
        if (method_exists($user, 'isVirtual') && $user->isVirtual()) {
            $rootConfig = $this->getRootConfig();
            $rootPassword = (string) ($rootConfig['password'] ?? '');
            $inputPassword = (string) ($credentials['password'] ?? '');

            if ($rootPassword === '' || $inputPassword === '') {
                return false;
            }

            // Mendukung pencocokan plain text atau hash Bcrypt/Argon
            if (hash_equals($rootPassword, $inputPassword)) {
                return true;
            }

            // Jika kata sandi berupa hash Bcrypt / Argon
            $info = password_get_info($rootPassword);
            if (!empty($info['algo'])) {
                try {
                    return Hash::check($inputPassword, $rootPassword);
                } catch (\Throwable) {
                    return false;
                }
            }

            return false;
        }

        return parent::validateCredentials($user, $credentials);
    }

    /**
     * Rehash the user's password if required and supported.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false)
    {
        if (method_exists($user, 'isVirtual') && $user->isVirtual()) {
            return;
        }

        parent::rehashPasswordIfRequired($user, $credentials, $force);
    }

    /**
     * Cek apakah akun Root Super Admin diaktifkan.
     */
    public function isRootEnabled(): bool
    {
        $config = $this->getRootConfig();
        return (bool) ($config['enabled'] ?? false);
    }

    /**
     * Mengambil konfigurasi Root Superadmin terpadu.
     * Prioritas:
     * 1. Nilai dari file .env milik Pamersuku (jika ada dan terisi)
     * 2. Nilai dari config/auth.php (dari .env SIKEKAR)
     */
    public function getRootConfig(): array
    {
        $enabled = (bool) config('auth.root_superadmin.enabled', false);
        $email = (string) config('auth.root_superadmin.email', '');
        $password = (string) config('auth.root_superadmin.password', '');
        $name = (string) config('auth.root_superadmin.name', 'Root Super Admin');

        $pamersukuPath = $this->resolvePamersukuEnvPath();
        if ($pamersukuPath && file_exists($pamersukuPath) && is_readable($pamersukuPath)) {
            $parsed = $this->parseEnvFile($pamersukuPath);

            if (isset($parsed['SUPERADMIN_ROOT_ENABLED'])) {
                $enabled = filter_var($parsed['SUPERADMIN_ROOT_ENABLED'], FILTER_VALIDATE_BOOLEAN);
            }
            if (!empty($parsed['SUPERADMIN_ROOT_EMAIL'])) {
                $email = $parsed['SUPERADMIN_ROOT_EMAIL'];
            }
            if (!empty($parsed['SUPERADMIN_ROOT_PASSWORD'])) {
                $password = $parsed['SUPERADMIN_ROOT_PASSWORD'];
            }
            if (!empty($parsed['SUPERADMIN_ROOT_NAME'])) {
                $name = $parsed['SUPERADMIN_ROOT_NAME'];
            }
        }

        return [
            'enabled' => $enabled,
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'pamersuku_path' => $pamersukuPath,
        ];
    }

    /**
     * Cari lokasi file .env milik Pamersuku di server hosting / lokal.
     */
    public function resolvePamersukuEnvPath(): ?string
    {
        // 1. Cek jika di-set eksplisit di .env SIKEKAR via PAMERSUKU_ENV_PATH
        $custom = config('auth.root_superadmin.pamersuku_env_path');
        if (!empty($custom) && file_exists($custom)) {
            return $custom;
        }

        // 2. Cek lokasi relatif folder yang umum di hosting (cPanel/Hostinger) atau lokal
        $candidates = [
            base_path('../pamersuku/.env'),
            base_path('../pamersuku/backend/.env'),
            base_path('../pamersuku-karywan-optimize/backend/.env'),
            base_path('../kpm-sudoku/.env'),
            base_path('../../pamersuku/.env'),
            base_path('../../pamersuku/backend/.env'),
            base_path('../../pamersuku-karywan-optimize/backend/.env'),
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Parser sederhana file .env tanpa polusi environment global.
     */
    public function parseEnvFile(string $filePath): array
    {
        $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        $values = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $val] = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);
                // Buang tanda kutip pembungkus
                $val = preg_replace('/^([\'"])(.*)\1$/', '$2', $val);
                $values[$key] = $val;
            }
        }

        return $values;
    }

    /**
     * Bangun objek Karyawan virtual di memori (in-memory Authenticatable).
     */
    public function getVirtualSuperAdmin(): ?Karyawan
    {
        if (!$this->isRootEnabled()) {
            return null;
        }

        $rootConfig = $this->getRootConfig();
        $email = $rootConfig['email'];
        if ($email === '') {
            return null;
        }

        $user = new Karyawan();
        $user->exists = false;
        $user->is_virtual = true;

        // Properti standar user SIKEKAR
        $user->email = $email;
        $user->nama_lengkap = $rootConfig['name'];
        $user->posisi = 'superadmin';
        $user->jabatan = 'System Administrator (Root)';
        $user->divisi = 'IT / System';
        $user->status = 'Karyawan Tetap';
        $user->kode_pegawai = 'ROOT-001';
        $user->nomor_telepon = '-';
        $user->no_wa = '-';
        $user->alamat = 'System Environment';
        $user->is_resigned = false;
        $user->violation_count = 0;
        $user->is_banned_permanent = false;

        return $user;
    }
}
