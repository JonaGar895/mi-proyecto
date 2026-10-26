<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaintingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $categoryIds = $categories->pluck('id', 'slug');

        $works = [
            ['La noche estrellada', 1889, 'Óleo sobre lienzo', '🌌', 'impresionismo'],
            ['El grito', 1893, 'Temple y pastel sobre cartón', '😱', 'expresionismo'],
            ['Las meninas', 1656, 'Óleo sobre lienzo', '👑', 'barroco'],
            ['La persistencia de la memoria', 1931, 'Óleo sobre lienzo', '🕰️', 'surrealismo'],
            ['El jardín de las delicias', 1510, 'Óleo sobre tabla', '🌳', 'renacimiento'],
            ['Los girasoles', 1888, 'Óleo sobre lienzo', '🌻', 'impresionismo'],
            ['La joven de la perla', 1665, 'Óleo sobre lienzo', '💎', 'barroco'],
            ['El nacimiento de Venus', 1486, 'Temple sobre lienzo', '🐚', 'renacimiento'],
            ['Guernica', 1937, 'Óleo sobre lienzo', '🕊️', 'cubismo'],
            ['Composición VIII', 1923, 'Óleo sobre lienzo', '🔶', 'arte-abstracto'],
            ['La gran ola de Kanagawa', 1831, 'Grabado en madera', '🌊', 'realismo'],
            ['El beso', 1908, 'Óleo y pan de oro', '💋', 'modernismo'],
        ];

        foreach ($works as [$title, $year, $technique, $emoji, $categorySlug]) {
            $painting = Painting::firstOrNew(['title' => $title]);

            if (! $painting->exists) {
                $painting->user_id = $users->random()->id;
            }

            $painting->fill([
                'category_id' => $categoryIds[$categorySlug] ?? $categories->random()->id,
                'description' => $painting->description ?? fake()->paragraph(3),
                'year' => $year,
                'technique' => $technique,
                'emoji' => $emoji,
                'price' => $painting->price ?? fake()->randomFloat(2, 100, 5000),
                'is_public' => true,
            ]);

            $painting->save();

            if (! $painting->comments()->exists()) {
                $commenters = $users->count() >= 2 ? $users->random(2) : $users;

                $painting->comments()->createMany(
                    $commenters->map(fn (User $user) => [
                        'user_id' => $user->id,
                        'body' => fake()->sentence(12),
                    ])->all(),
                );
            }

            if (! $painting->favorites()->exists()) {
                $painting->favoritedByUsers()->syncWithoutDetaching(
                    $users->random(min(5, $users->count()))->pluck('id')->all(),
                );
            }
        }
    }
}
