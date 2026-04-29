<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Faker\Factory;
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
        $faker = Factory::create('id_ID');

        DB::transaction(function () use ($faker) {
            // Akun Admin (hanya di tabel users)
            User::create([
                'username' => 'admin',
                'email' => 'admin@protrack.test',
                'password' => Hash::make('Qwerty123*'),
                'role'     => 'admin',
            ]);

            $pms = [
                ['username' => 'pm1', 'email' => 'pm1@protrack.test', 'employee_name' => 'Andi Pratama'],
                ['username' => 'pm2', 'email' => 'pm2@protrack.test', 'employee_name' => 'Bima Saputra'],
                ['username' => 'pm3', 'email' => 'pm3@protrack.test', 'employee_name' => 'Citra Wulandari'],
                ['username' => 'pm4', 'email' => 'pm4@protrack.test', 'employee_name' => 'Deni Kurniawan'],
                ['username' => 'pm5', 'email' => 'pm5@protrack.test', 'employee_name' => 'Eka Lestari'],
            ];

            foreach ($pms as $pm) {
                $pmUser = User::create([
                    'username' => $pm['username'],
                    'email' => $pm['email'],
                    'password' => Hash::make('Qwerty123*'),
                    'role'     => 'pm',
                ]);

                Employee::create([
                    'user_id'       => $pmUser->id,
                    'employee_name' => $pm['employee_name'],
                    'position'      => 'Project Manager',
                    'nik' => $faker->numerify('################'),
                    'phone_number' => $faker->numerify('08##########'),
                    'birth_date' => $faker->dateTimeBetween('-45 years', '-28 years')->format('Y-m-d'),
                    'gender' => $faker->randomElement(['L', 'P']),
                    'address' => $faker->address,
                ]);
            }
        });

        $this->command->info('Seeder berhasil: Admin dan 5 PM telah dibuat!');
    }
}