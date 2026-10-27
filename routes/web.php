<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PaintingController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\UserController;
use App\Models\Painting;
use App\Models\Publication;
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
            'latestPublications' => Publication::query()
                ->published()
                ->with(['user:id,name', 'category:id,name'])
                ->latest('published_at')
                ->take(3)
                ->get()
                ->map(fn (Publication $publication) => [
                    'id' => $publication->id,
                    'title' => $publication->title,
                    'summary' => $publication->summary,
                    'author' => $publication->user?->name,
                    'category' => $publication->category?->name,
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

    // Publicaciones (blog de la galería)
    Route::get('/publicaciones', [PublicationController::class, 'index'])->name('publications.index');
    Route::get('/publicaciones/create', [PublicationController::class, 'create'])->name('publications.create');
    Route::post('/publicaciones', [PublicationController::class, 'store'])->name('publications.store');
    Route::get('/publicaciones/{publication}', [PublicationController::class, 'show'])->name('publications.show');
    Route::get('/publicaciones/{publication}/edit', [PublicationController::class, 'edit'])->name('publications.edit');
    Route::match(['put', 'post'], '/publicaciones/{publication}', [PublicationController::class, 'update'])->name('publications.update');
    Route::delete('/publicaciones/{publication}', [PublicationController::class, 'destroy'])->name('publications.destroy');
    Route::post('/publicaciones/{publication}/comments', [PublicationController::class, 'comment'])->name('publications.comments.store');

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
                'publications' => $user->publications()->count(),
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
            'myPublications' => $user->publications()
                ->with('category:id,name')
                ->withCount('comments')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn (Publication $publication) => [
                    'id' => $publication->id,
                    'title' => $publication->title,
                    'summary' => $publication->summary,
                    'category' => $publication->category?->name,
                    'is_published' => $publication->is_published,
                    'date' => ($publication->published_at ?? $publication->created_at)
                        ?->translatedFormat('d M Y'),
                    'comments_count' => $publication->comments_count,
                ]),
        ]);
    })->name('settings');
});
