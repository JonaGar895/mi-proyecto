<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PaintingController;
use App\Http\Controllers\UserController;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Login: solo para usuarios que no han iniciado sesión
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

// Logout
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {

    // Página principal
    Route::get('/', function () {
        return Inertia::render('Home', [
            'featuredPaintings' => Painting::query()
                ->with('user:id,name')
                ->where('is_public', true)
                ->latest()
                ->take(3)
                ->get()
                ->map(fn (Painting $painting) => [
                    'id' => $painting->id,
                    'title' => $painting->title,
                    'emoji' => $painting->emoji,
                    'image_url' => $painting->image_url,
                    'artist' => $painting->user?->name,
                ]),
        ]);
    })->name('home');

    // Galería de cuadros
    Route::get('/cuadros', [PaintingController::class, 'index'])->name('paintings.index');
    Route::get('/cuadros/create', [PaintingController::class, 'create'])->name('paintings.create');
    Route::post('/cuadros', [PaintingController::class, 'store'])->name('paintings.store');
    Route::get('/cuadros/{painting}', [PaintingController::class, 'show'])->name('paintings.show');
    Route::delete('/cuadros/{painting}', [PaintingController::class, 'destroy'])->name('paintings.destroy');
    Route::post('/cuadros/{painting}/favorite', [PaintingController::class, 'favorite'])->name('paintings.favorite');
    Route::post('/cuadros/{painting}/comments', [PaintingController::class, 'comment'])->name('paintings.comments.store');

    // Usuarios
    Route::redirect('/users', '/users/list')->name('users.index');

    Route::get('/users/list', [UserController::class, 'list'])
        ->can('viewAny', User::class)
        ->name('users.list');

    Route::get('/users/create', [UserController::class, 'createView'])
        ->can('create', User::class)
        ->name('users.create');

    Route::post('/users/create', [UserController::class, 'create'])
        ->can('create', User::class)
        ->name('users.store');

    Route::get('/users/view/{user}', [UserController::class, 'view'])
        ->can('view', 'user')
        ->name('users.view');

    Route::get('/users/edit/{user}', [UserController::class, 'editView'])
        ->can('update', 'user')
        ->name('users.edit');

    Route::post('/users/edit/{user}', [UserController::class, 'update'])
        ->can('update', 'user')
        ->name('users.update');

    Route::post('/users/delete/{user}', [UserController::class, 'delete'])
        ->can('delete', 'user')
        ->name('users.delete');

    // Ajustes
    Route::redirect('/setting', '/settings');

    Route::get('/settings', function (Request $request) {
        $user = $request->user();

        return Inertia::render('Settings', [
            'stats' => [
                'paintings' => $user->paintings()->count(),
                'favorites' => $user->favoritePaintings()->count(),
                'comments' => $user->comments()->count(),
            ],
            'favoritePaintings' => $user->favoritePaintings()
                ->with('user:id,name')
                ->orderByDesc('favorites.created_at')
                ->take(6)
                ->get()
                ->map(fn (Painting $painting) => [
                    'id' => $painting->id,
                    'title' => $painting->title,
                    'emoji' => $painting->emoji,
                    'artist' => $painting->user?->name,
                ]),
        ]);
    })->name('settings');
});
