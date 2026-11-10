<?php

namespace Database\Seeders;

use App\Models\CutiType;
use Illuminate\Database\Seeder;

class CutiTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CutiType::seedDefaults();
    }
}
