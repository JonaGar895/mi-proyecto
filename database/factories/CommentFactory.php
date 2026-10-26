<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'painting_id' => Painting::factory(),
            'body' => fake()->sentence(12),
        ];
    }
}
