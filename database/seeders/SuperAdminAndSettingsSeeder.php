<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Kompatibilitas untuk nama seeder lama.
 * Logika sekarang tinggal di SuperAdminSettingsSeeder.
 */
class SuperAdminAndSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SuperAdminSettingsSeeder::class);
    }
}
