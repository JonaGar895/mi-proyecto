
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PaintingController;
use App\Models\Painting;
use App\Models\User;
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

    // Listado de usuarios
    Route::get('/users', function (Request $request) {
        $search = $request->input('search', '');

        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]),

            'filters' => [
                'search' => $search,
            ],
        ]);
    })
        ->can('viewAny', User::class)
        ->name('users.index');

    // Formulario de creación
    Route::get('/users/create', function () {
        return Inertia::render('Users/Create');
    })
        ->can('create', User::class)
        ->name('users.create');

    // Guardar usuario
    Route::post('/users', function (Request $request) {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // El cast "hashed" del modelo User procesa la contraseña.
        User::create($attributes);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    })
        ->can('create', User::class)
        ->name('users.store');

    // Editar usuario
    Route::get('/users/{user}/edit', function (User $user) {
        return Inertia::render('Users/Edit', [
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    })
        ->can('update', 'user')
        ->name('users.edit');

    Route::put('/users/{user}', function (Request $request, User $user) {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($attributes['password'] ?? null)) {
            unset($attributes['password']);
        }

        $user->update($attributes);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    })
        ->can('update', 'user')
        ->name('users.update');

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
