<?php

namespace App\Http\Controllers;

use App\Models\Painting;
use App\Models\Rating;
use App\Notifications\PaintingRated;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Painting $painting)
    {
        $user = $request->user();
        abort_unless(
            $painting->is_public || $painting->user_id === $user->id || $user->isAdmin(),
            404,
        );
        abort_if($painting->user_id === $user->id, 403, 'No puedes valorar tu propia obra.');

        $attributes = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $rating = Rating::updateOrCreate(
            [
                'user_id' => $user->id,
                'painting_id' => $painting->id,
            ],
            [
                'rating' => $attributes['rating'],
            ],
        );

        // Solo se avisa de la primera valoración: actualizar la puntuación
        // de una obra ya valorada no vuelve a notificar al dueño.
        if (
            $rating->wasRecentlyCreated
            && $painting->user_id !== null
            && $painting->user_id !== $user->id
        ) {
            $painting->user->notify(new PaintingRated($painting, $user, $attributes['rating']));
        }

        return back()->with('success', 'Valoración guardada. ¡Gracias por puntuar la obra!');
    }
}
