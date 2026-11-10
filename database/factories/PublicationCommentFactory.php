<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\PublicationComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublicationComment>
 */
class PublicationCommentFactory extends Factory
{
    protected $model = PublicationComment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'publication_id' => Publication::factory(),
            'body' => fake()->sentence(12),
        ];
    }
}
