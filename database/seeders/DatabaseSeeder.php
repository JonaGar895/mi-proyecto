<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(100)->create();

        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            PaintingSeeder::class,
            ExhibitionSeeder::class,
            PublicationSeeder::class,
        ]);
    }
}