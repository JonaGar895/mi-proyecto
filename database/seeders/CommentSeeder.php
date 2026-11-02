<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /** @var list<string> */
    private const BODIES = [
        'Me encanta cómo la luz construye toda la escena.',
        'Es una obra que gana muchísimo al verla de cerca.',
        'El color transmite una calma difícil de explicar.',
        'La composición me parece absolutamente moderna.',
        'Volvería a verla mil veces y siempre encontraría algo nuevo.',
        'Hay un detalle en la pincelada que me tiene fascinado.',
        'Una pieza ideal para empezar una colección.',
        'La tensión entre las figuras está muy bien resuelta.',
    ];

    public function run(): void
    {
        $users = UserSeeder::demoUsers();

        if ($users->isEmpty()) {
            return;
        }

        $paintings = Painting::query()->with('user:id,name')->orderBy('id')->get();

        foreach ($paintings as $index => $painting) {
            $commenters = $users
                ->reject(fn (User $user) => $user->id === $painting->user_id)
                ->values();

            if ($commenters->isEmpty()) {
                continue;
            }

            foreach ([0, 1] as $offset) {
                $user = $commenters->get(($index + $offset) % $commenters->count());

                if (! $user) {
                    continue;
                }

                Comment::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'painting_id' => $painting->id,
                    ],
                    [
                        'body' => self::BODIES[($index + $offset) % count(self::BODIES)],
                    ],
                );
            }
        }
    }
}
