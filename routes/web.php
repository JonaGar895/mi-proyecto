<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'username' => 'JohnDoe',
        'frameworks' => ['Laravel', 'Vue', 'Inertia'],
    ]);
});

Route::get('/users', function () {
    return Inertia::render('Users', [
        'time' => now()->toDateTimeString(),
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