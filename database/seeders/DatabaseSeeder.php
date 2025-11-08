<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PerbaikiSupervisorIdSeeder::class,
            BlokSeeder::class,
            // NasabahSeeder::class,
            // SeederGabunganSetoranTitipan::class,
            // RealisticSetoranSeeder::class,
            // TitipSetoranSeeder::class, // 👈 ini harus ada
        ]);

        // Optional
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

}
