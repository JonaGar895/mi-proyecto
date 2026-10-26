<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use App\Models\Painting;
use Illuminate\Database\Seeder;

class ExhibitionSeeder extends Seeder
{
    public function run(): void
    {
        $paintings = Painting::all();

        if ($paintings->isEmpty()) {
            return;
        }

        $exhibitions = [
            ['Luz y color', 'Un recorrido por las obras que cambiaron la forma de mirar el color.', -15, 20],
            ['Retratos del alma', 'Rostros, miradas y emociones a lo largo de la historia del arte.', 5, 45],
            ['Formas en movimiento', 'Vanguardias, abstracción y nuevas geometrías.', -40, -5],
            ['Clásicos revisitados', 'Grandes obras y su influencia en el arte contemporáneo.', 30, 90],
        ];

        foreach ($exhibitions as [$title, $description, $startsIn, $endsIn]) {
            $exhibition = Exhibition::firstOrCreate(
                ['title' => $title],
                [
                    'description' => $description,
                    'starts_at' => now()->addDays($startsIn)->toDateString(),
                    'ends_at' => now()->addDays($endsIn)->toDateString(),
                ],
            );

            $exhibition->paintings()->syncWithoutDetaching(
                $paintings->random(min(6, $paintings->count()))->pluck('id')->all(),
            );
        }
    }
}
