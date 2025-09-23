<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'petugas1@example.com'],
            [
                'name' => 'Petugas 1',
                'password' => Hash::make('password'),
                'role' => 'petugas', // Pastikan kolom role ada
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas2@example.com'],
            [
                'name' => 'Petugas 2',
                'password' => Hash::make('password'),
                'role' => 'petugas', // Pastikan kolom role ada
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor@example.com'],
            [
                'name' => 'Supervisor 1',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor2@example.com'],
            [
                'name' => 'Supervisor 2',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
            ]
        );
    }
}
