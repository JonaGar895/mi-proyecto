<?php

namespace Database\Factories;

use App\Models\Exhibition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exhibition>
 */
class ExhibitionFactory extends Factory
{
    protected $model = Exhibition::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-2 months', '+1 month');

        return [
            'title' => 'Exposición '.ucfirst(fake()->unique()->words(2, true)),
            'description' => fake()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+2 months'),
        ];
    }
}
