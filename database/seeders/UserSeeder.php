<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Membuat Akun Admin (Hanya di tabel Users)
        User::create([
            'username' => 'admin',
            'password' => Hash::make('Qwerty123*'), // Default password: password
            'role'     => 'admin',
        ]);

        // 2. Membuat Akun Project Manager (PM)
        $pmUser = User::create([
            'username' => 'manager',
            'password' => Hash::make('Qwerty123*'),
            'role'     => 'pm',
        ]);

        // 3. Menghubungkan Akun PM ke tabel Employees
        // Menggunakan instance $pmUser->id (UUID) yang baru saja dibuat
        Employee::create([
            'user_id'       => $pmUser->id,
            'employee_name' => 'John Doe', // Mengambil salah satu relasi rekan kerjamu nih!
            'position'      => 'Project Manager',
        ]);
        
        $this->command->info('Seeder berhasil: Akun Admin dan PM (John Doe) telah dibuat!');
    }
}