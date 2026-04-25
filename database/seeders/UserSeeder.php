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

            $pms = [
                ['username' => 'pm1', 'employee_name' => 'PM Andi'],
                ['username' => 'pm2', 'employee_name' => 'PM Bima'],
                ['username' => 'pm3', 'employee_name' => 'PM Citra'],
                ['username' => 'pm4', 'employee_name' => 'PM Deni'],
                ['username' => 'pm5', 'employee_name' => 'PM Eka'],
            ];

            foreach ($pms as $pm) {
                $pmUser = User::create([
                    'username' => $pm['username'],
                    'password' => Hash::make('Qwerty123*'),
                    'role'     => 'pm',
                ]);

                Employee::create([
                    'user_id'       => $pmUser->id,
                    'employee_name' => $pm['employee_name'],
                    'position'      => 'Project Manager',
                ]);
            }
        });

        $this->command->info('Seeder berhasil: Admin dan 5 PM telah dibuat!');
    }
}