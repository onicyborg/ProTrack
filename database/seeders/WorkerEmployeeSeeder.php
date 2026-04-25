<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkerEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            for ($i = 1; $i <= 50; $i++) {
                Employee::create([
                    'user_id' => null,
                    'employee_name' => 'Worker ' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'position' => 'Worker',
                ]);
            }
        });

        $this->command->info('Seeder berhasil: 50 Worker (tanpa akun) telah dibuat!');
    }
}
