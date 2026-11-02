<?php

namespace Database\Seeders;

use App\Models\Painting;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = UserSeeder::demoUsers();

        if ($users->isEmpty()) {
            return;
        }

        $paintings = Painting::query()->orderBy('id')->get();

        if ($paintings->isEmpty()) {
            return;
        }

        // Cada obra recibe al menos un favorito de un usuario que no sea su autor.
        foreach ($paintings as $index => $painting) {
            $candidates = $users
                ->reject(fn (User $user) => $user->id === $painting->user_id)
                ->values();

            if ($candidates->isEmpty()) {
                continue;
            }

            $candidates
                ->get($index % $candidates->count())
                ?->favoritePaintings()
                ->syncWithoutDetaching([$painting->id]);
        }

        // Cada usuario demo guarda además varias obras en favoritos.
        foreach ($users as $index => $user) {
            $favorites = $paintings
                ->reject(fn (Painting $painting) => $painting->user_id === $user->id)
                ->slice($index, 4)
                ->pluck('id')
                ->all();

            if ($favorites !== []) {
                $user->favoritePaintings()->syncWithoutDetaching($favorites);
            }
        }
    }
}
