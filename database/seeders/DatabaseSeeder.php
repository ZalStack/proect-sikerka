<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Division;
use App\Models\Employee;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate tables first (untuk fresh seed)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::table('users')->truncate();
        DB::table('divisions')->truncate();
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

        // Get division IDs
        $itDivision = Division::where('name', 'IT')->first();
        $hrDivision = Division::where('name', 'HR')->first();
        $financeDivision = Division::where('name', 'Finance')->first();

        // Create Admin
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@elearning.com',
            'enroll_number' => 'ADMIN001',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'division_id' => null,
            'is_active' => true,
            'last_login_at' => Carbon::now(),
            'email_verified_at' => Carbon::now(),
        ]);

        // Create Sample Employees
        $employeeData = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@company.com',
                'enroll_number' => 'EMP001',
                'division_id' => $itDivision->id,
                'password' => Hash::make('password123'),
                'full_name' => 'Budi Santoso',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 1, Jakarta',
                'join_date' => '2024-01-01',
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@company.com',
                'enroll_number' => 'EMP002',
                'division_id' => $hrDivision->id,
                'password' => Hash::make('password123'),
                'full_name' => 'Siti Rahayu',
                'phone' => '081234567891',
                'address' => 'Jl. Sudirman No. 2, Jakarta',
                'join_date' => '2024-01-15',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@company.com',
                'enroll_number' => 'EMP003',
                'division_id' => $financeDivision->id,
                'password' => Hash::make('password123'),
                'full_name' => 'Ahmad Fauzi',
                'phone' => '081234567892',
                'address' => 'Jl. Thamrin No. 3, Jakarta',
                'join_date' => '2024-02-01',
            ],
        ];

        foreach ($employeeData as $emp) {
            $user = User::create([
                'name' => $emp['name'],
                'email' => $emp['email'],
                'enroll_number' => $emp['enroll_number'],
                'division_id' => $emp['division_id'],
                'password' => $emp['password'],
                'role' => 'employee',
                'is_active' => true,
                'last_login_at' => Carbon::now(),
                'email_verified_at' => Carbon::now(),
            ]);

            Employee::create([
                'user_id' => $user->id,
                'full_name' => $emp['full_name'],
                'phone' => $emp['phone'],
                'address' => $emp['address'],
                'join_date' => $emp['join_date'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: ADMIN001 / password123');
        $this->command->info('Employee: EMP001, EMP002, EMP003 / password123');
    }
}
