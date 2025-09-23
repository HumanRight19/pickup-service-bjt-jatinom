<?php
/*
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlokPasar;

class BlokSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range('A', 'Z') as $char) {
            BlokPasar::create([
                'nama_blok' => "Blok $char",
            ]);
        }
    }
}
*/
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlokPasar;

class BlokSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range('A', 'D') as $char) {
            BlokPasar::create([
                'nama_blok' => "Blok $char",
            ]);
        }
    }
}