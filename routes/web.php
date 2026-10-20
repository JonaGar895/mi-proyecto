<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User; 

Route::get('/', function () {
    return Inertia::render('Home', [
        'username' => 'JohnDoe',
        'frameworks' => ['Laravel', 'Vue', 'Inertia'],
    ]);
});

Route::get('/users', function (Request $request) {
    $search = $request->input('search', '');

    return Inertia::render('Users', [
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
});

Route::get('/cuadros', function () {
    return Inertia::render('Paintings');
});

Route::get('/setting', function () {
    return Inertia::render('Setting', ['time' => now()->toTimeString(),
        'foo' => request('foo'),
        'baz' => request('baz'),
    ]);
});

Route::post('/logout', function () {
    dd(request('foo'));
});