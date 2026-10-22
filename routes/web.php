<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\LoginController;
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
        return Inertia::render('Home');
    })->name('home');

    // Listado de usuarios, búsqueda y paginación
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
                ]),

            'filters' => [
                'search' => $search,
            ],
        ]);
    })->name('users.index');

    // Formulario de creación
    Route::get('/users/create', function () {
        return Inertia::render('Users/Create');
    })->name('users.create');

    // Guardar usuario
    Route::post('/users', function (Request $request) {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $attributes['password'] = Hash::make($attributes['password']);

        User::create($attributes);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    })->name('users.store');

    // Ajustes
    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');
});