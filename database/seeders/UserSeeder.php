<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Akun Admin (hanya di tabel users)
            User::create([
                'username' => 'admin',
                'password' => Hash::make('Qwerty123*'),
                'role'     => 'admin',
            ]);

            // Akun PM #1
            $pmUser1 = User::create([
                'username' => 'pm1',
                'password' => Hash::make('Qwerty123*'),
                'role'     => 'pm',
            ]);

            Employee::create([
                'user_id'       => $pmUser1->id,
                'employee_name' => 'John Doe',
                'position'      => 'Project Manager',
            ]);

            // Akun PM #2 (untuk demo multi-PM)
            $pmUser2 = User::create([
                'username' => 'pm2',
                'password' => Hash::make('Qwerty123*'),
                'role'     => 'pm',
            ]);

            Employee::create([
                'user_id'       => $pmUser2->id,
                'employee_name' => 'Jane Doe',
                'position'      => 'Project Manager',
            ]);
        });

        $this->command->info('Seeder berhasil: Admin dan 2 PM telah dibuat!');
    }
}