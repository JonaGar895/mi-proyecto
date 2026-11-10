<?php

namespace App\Http\Controllers;

use App\Models\Painting;
use App\Models\Rating;
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

        Rating::updateOrCreate(
            [
                'user_id' => $user->id,
                'painting_id' => $painting->id,
            ],
            [
                'rating' => $attributes['rating'],
            ],
        );

        return back()->with('success', 'Valoración guardada. ¡Gracias por puntuar la obra!');
    }
}
