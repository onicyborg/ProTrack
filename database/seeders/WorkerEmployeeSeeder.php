<?php

namespace Database\Seeders;

use App\Models\Employee;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkerEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        DB::transaction(function () use ($faker) {
            for ($i = 1; $i <= 60; $i++) {
                Employee::create([
                    'user_id' => null,
                    'employee_name' => $faker->name,
                    'position' => $faker->randomElement(['Worker', 'Tukang', 'Mandor', 'Operator', 'Surveyor', 'Helper']),
                    'nik' => $faker->numerify('################'),
                    'phone_number' => $faker->numerify('08##########'),
                    'birth_date' => $faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
                    'gender' => $faker->randomElement(['L', 'P']),
                    'address' => $faker->address,
                ]);
            }
        });

        $this->command->info('Seeder berhasil: 60 Karyawan lapangan (tanpa akun) telah dibuat!');
    }
}
