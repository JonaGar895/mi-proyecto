<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Painting>
 */
class PaintingFactory extends Factory
{
    protected $model = Painting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => ucfirst(fake()->words(3, true)),
            'description' => fake()->paragraph(),
            'year' => fake()->numberBetween(1900, (int) date('Y')),
            'technique' => fake()->randomElement([
                'Óleo sobre lienzo',
                'Acrílico sobre lienzo',
                'Acuarela sobre papel',
                'Temple sobre tabla',
                'Grabado',
            ]),
            'emoji' => fake()->randomElement(['🖼️', '🎨', '🌅', '🌄', '🏞️', '🖌️']),
            'image_url' => null,
            'price' => fake()->randomFloat(2, 100, 5000),
            'is_public' => true,
        ];
    }
}
