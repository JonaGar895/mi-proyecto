<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Impresionismo',
            'Cubismo',
            'Surrealismo',
            'Realismo',
            'Arte abstracto',
            'Barroco',
            'Renacimiento',
            'Expresionismo',
            'Modernismo',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
