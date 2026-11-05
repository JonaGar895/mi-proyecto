<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Painting;
use App\Models\User;
use App\Support\DemoImage;
use Illuminate\Database\Seeder;

class PaintingSeeder extends Seeder
{
    public function run(): void
    {
        $users = UserSeeder::vendors();

        if ($users->isEmpty()) {
            $users = User::query()->orderBy('id')->get();
        }

        $allUsers = User::query()->orderBy('id')->get();
        $categories = Category::query()->orderBy('id')->get();

        if ($users->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $categoryIds = $categories->pluck('id', 'slug');

        $works = [
            ['La noche estrellada', 1889, 'Óleo sobre lienzo', '🌌', 'impresionismo', 4500.00],
            ['El grito', 1893, 'Temple y pastel sobre cartón', '😱', 'expresionismo', 3800.00],
            ['Las meninas', 1656, 'Óleo sobre lienzo', '👑', 'barroco', 5000.00],
            ['La persistencia de la memoria', 1931, 'Óleo sobre lienzo', '🕰️', 'surrealismo', 4200.00],
            ['El jardín de las delicias', 1510, 'Óleo sobre tabla', '🌳', 'renacimiento', 4900.00],
            ['Los girasoles', 1888, 'Óleo sobre lienzo', '🌻', 'impresionismo', 3600.00],
            ['La joven de la perla', 1665, 'Óleo sobre lienzo', '💎', 'barroco', 4100.00],
            ['El nacimiento de Venus', 1486, 'Temple sobre lienzo', '🐚', 'renacimiento', 4700.00],
            ['Guernica', 1937, 'Óleo sobre lienzo', '🕊️', 'cubismo', 4600.00],
            ['Composición VIII', 1923, 'Óleo sobre lienzo', '🔶', 'arte-abstracto', 2900.00],
            ['La gran ola de Kanagawa', 1831, 'Grabado en madera', '🌊', 'realismo', 3200.00],
            ['El beso', 1908, 'Óleo y pan de oro', '💋', 'modernismo', 3400.00],
            ['Impresión, sol naciente', 1872, 'Óleo sobre lienzo', '🌅', 'impresionismo', 2800.00],
            ['La libertad guiando al pueblo', 1830, 'Óleo sobre lienzo', '🗽', 'realismo', 3500.00],
            ['La tentación de San Antonio', 1946, 'Óleo sobre lienzo', '👹', 'surrealismo', 2750.00],
            ['Retrato de Adele Bloch-Bauer I', 1907, 'Óleo, plata y oro sobre lienzo', '✨', 'modernismo', 5000.00],
        ];

        foreach ($works as $index => [$title, $year, $technique, $emoji, $categorySlug, $price]) {
            $painting = Painting::firstOrNew(['title' => $title]);

            if (! $painting->exists) {
                $painting->user_id = $users->get($index % $users->count())->id;
                $painting->status = 'available';
            } else {
                // Aseguramos que las obras demo pertenezcan a un vendedor/admin para poder usarlas en ventas.
                if ($painting->user_id && ! $painting->user?->isVendor() && ! $painting->user?->isAdmin()) {
                    $painting->user_id = $users->first()->id;
                }
            }

            $image = $painting->image_path
                ? null
                : DemoImage::make($title, 'demo/paintings');

            $painting->fill([
                'category_id' => $categoryIds[$categorySlug] ?? $categories->random()->id,
                'description' => $painting->description ?? fake()->paragraph(3),
                'year' => $year,
                'technique' => $technique,
                'emoji' => $emoji,
                'price' => $painting->price ?? $price,
                'is_public' => true,
            ]);

            if ($image) {
                $painting->image_path = $image['image_path'];
                $painting->image_hash = $image['image_hash'];
            }

            $painting->save();

            if (! $painting->comments()->exists() && $allUsers->isNotEmpty()) {
                $commenters = $allUsers->random(min(3, $allUsers->count()));
                $commenters = $commenters instanceof User ? collect([$commenters]) : $commenters;

                $painting->comments()->createMany(
                    $commenters->map(fn (User $user) => [
                        'user_id' => $user->id,
                        'body' => fake()->sentence(12),
                    ])->all(),
                );
            }

            if (! $painting->favorites()->exists() && $allUsers->isNotEmpty()) {
                $painting->favoritedByUsers()->syncWithoutDetaching(
                    $allUsers->random(min(6, $allUsers->count()))->pluck('id')->all(),
                );
            }
        }

        // Cualquier obra que ya existiera sin imagen recibe también una demo.
        Painting::query()
            ->whereNull('image_path')
            ->get()
            ->each(function (Painting $painting) {
                $image = DemoImage::make(
                    $painting->title.'-'.$painting->id,
                    'demo/paintings',
                );

                $painting->forceFill($image)->save();
            });
    }
}
