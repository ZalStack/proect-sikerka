<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Employee::truncate();
        User::truncate();
        Division::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Divisions
        $divisions = [
            ['name' => 'IT', 'description' => 'Teknologi Informasi', 'is_active' => true],
            ['name' => 'HR', 'description' => 'Human Resources', 'is_active' => true],
            ['name' => 'Finance', 'description' => 'Keuangan', 'is_active' => true],
            ['name' => 'Marketing', 'description' => 'Pemasaran', 'is_active' => true],
            ['name' => 'Operations', 'description' => 'Operasional', 'is_active' => true],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }

        // Create Admin
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@elearning.com',
            'enroll_number' => 'ADMIN001',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Sample Employees
        $employees = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@company.com',
                'enroll_number' => 'EMP001',
                'division_id' => 1, // IT
                'password' => Hash::make('password123'),
                'full_name' => 'Budi Santoso',
                'phone' => '08123456789',
                'address' => 'Jl. Merdeka No. 123, Jakarta',
                'join_date' => now(),
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@company.com',
                'enroll_number' => 'EMP002',
                'division_id' => 2, // HR
                'password' => Hash::make('password123'),
                'full_name' => 'Siti Rahayu',
                'phone' => '08123456790',
                'address' => 'Jl. Sudirman No. 45, Jakarta',
                'join_date' => now(),
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@company.com',
                'enroll_number' => 'EMP003',
                'division_id' => 3, // Finance
                'password' => Hash::make('password123'),
                'full_name' => 'Ahmad Fauzi',
                'phone' => '08123456791',
                'address' => 'Jl. Gatot Subroto No. 78, Jakarta',
                'join_date' => now(),
            ],
        ];

        foreach ($employees as $emp) {
            $user = User::create([
                'name' => $emp['name'],
                'email' => $emp['email'],
                'enroll_number' => $emp['enroll_number'],
                'division_id' => $emp['division_id'],
                'password' => $emp['password'],
                'role' => 'employee',
                'is_active' => true,
            ]);

            Employee::create([
                'user_id' => $user->id,
                'division_id' => $emp['division_id'],
                'full_name' => $emp['full_name'],
                'phone' => $emp['phone'],
                'address' => $emp['address'],
                'join_date' => $emp['join_date'],
                'is_active' => true,
            ]);
        }
    }
}
