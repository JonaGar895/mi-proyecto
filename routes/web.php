<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaintingController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        $topRated = Painting::query()
            ->with('user:id,name')
            ->where('is_public', true)
            ->whereHas('ratings')
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating')
            ->orderByDesc('ratings_count')
            ->first();

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
                    'image_url' => ImageService::url($painting->image_path, $painting->image_url),
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
                    'image_url' => ImageService::url($publication->image_path),
                    'author' => $publication->user?->name,
                    'category' => $publication->category?->name,
                ]),
            'topRatedPainting' => $topRated ? [
                'id' => $topRated->id,
                'title' => $topRated->title,
                'emoji' => $topRated->emoji,
                'image_url' => ImageService::url($topRated->image_path, $topRated->image_url),
                'artist' => $topRated->user?->name,
                'ratings_avg' => round((float) $topRated->ratings_avg_rating, 1),
                'ratings_count' => $topRated->ratings_count,
            ] : null,
        ]);
    })->name('home');

    Route::get('/cuadros', [PaintingController::class, 'index'])->name('paintings.index');
    Route::get('/cuadros/create', [PaintingController::class, 'create'])->name('paintings.create');
    Route::post('/cuadros', [PaintingController::class, 'store'])->name('paintings.store');
    Route::get('/cuadros/{painting}', [PaintingController::class, 'show'])->name('paintings.show');
    Route::get('/cuadros/{painting}/edit', [PaintingController::class, 'edit'])->name('paintings.edit');
    Route::match(['put', 'post'], '/cuadros/{painting}', [PaintingController::class, 'update'])->name('paintings.update');
    Route::delete('/cuadros/{painting}', [PaintingController::class, 'destroy'])->name('paintings.destroy');
    Route::post('/cuadros/{painting}/favorite', [PaintingController::class, 'favorite'])->name('paintings.favorite');
    Route::post('/cuadros/{painting}/comments', [PaintingController::class, 'comment'])->name('paintings.comments.store');
    Route::match(['put', 'post'], '/cuadros/{painting}/comments/{comment}', [PaintingController::class, 'updateComment'])->name('paintings.comments.update');
    Route::delete('/cuadros/{painting}/comments/{comment}', [PaintingController::class, 'destroyComment'])->name('paintings.comments.destroy');
    Route::post('/cuadros/{painting}/rating', [RatingController::class, 'store'])->name('paintings.ratings.store');
    Route::get('/mis-obras', [PaintingController::class, 'mine'])->name('paintings.mine');

    Route::get('/exposiciones', [ExhibitionController::class, 'index'])->name('exhibitions.index');
    Route::get('/exposiciones/create', [ExhibitionController::class, 'create'])->name('exhibitions.create');
    Route::post('/exposiciones', [ExhibitionController::class, 'store'])->name('exhibitions.store');
    Route::get('/exposiciones/{exhibition}', [ExhibitionController::class, 'show'])->name('exhibitions.show');
    Route::get('/exposiciones/{exhibition}/edit', [ExhibitionController::class, 'edit'])->name('exhibitions.edit');
    Route::match(['put', 'post'], '/exposiciones/{exhibition}', [ExhibitionController::class, 'update'])->name('exhibitions.update');
    Route::delete('/exposiciones/{exhibition}', [ExhibitionController::class, 'destroy'])->name('exhibitions.destroy');

    Route::get('/publicaciones', [PublicationController::class, 'index'])->name('publications.index');
    Route::get('/publicaciones/create', [PublicationController::class, 'create'])->name('publications.create');
    Route::post('/publicaciones', [PublicationController::class, 'store'])->name('publications.store');
    Route::get('/publicaciones/{publication}', [PublicationController::class, 'show'])->name('publications.show');
    Route::get('/publicaciones/{publication}/edit', [PublicationController::class, 'edit'])->name('publications.edit');
    Route::match(['put', 'post'], '/publicaciones/{publication}', [PublicationController::class, 'update'])->name('publications.update');
    Route::delete('/publicaciones/{publication}', [PublicationController::class, 'destroy'])->name('publications.destroy');
    Route::post('/publicaciones/{publication}/comments', [PublicationController::class, 'comment'])->name('publications.comments.store');
    Route::match(['put', 'post'], '/publicaciones/{publication}/comments/{comment}', [PublicationController::class, 'updateComment'])->name('publications.comments.update');
    Route::delete('/publicaciones/{publication}/comments/{comment}', [PublicationController::class, 'destroyComment'])->name('publications.comments.destroy');

    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notificaciones/leer-todas', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notificaciones/{id}/leer', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');

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

    Route::redirect('/setting', '/mi-cuenta');
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');
    Route::get('/mi-cuenta', [UserController::class, 'settings'])->name('account');
    Route::post('/mi-cuenta/tema', [UserController::class, 'updateTheme'])->name('theme.update');
    Route::post('/mi-cuenta/saldo', [WalletController::class, 'deposit'])->name('wallet.deposit');

    Route::get('/cuenta-super-admin', [AccountController::class, 'show'])->name('super-admin.account');

    Route::post('/cuadros/{painting}/inquiries', [InquiryController::class, 'store'])->name('paintings.inquiries.store');
    Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/history', [InquiryController::class, 'history'])->name('inquiries.history');
    Route::post('/inquiries/{inquiry}/status', [InquiryController::class, 'update'])->name('inquiries.update');
    Route::post('/inquiries/{inquiry}/approve', [InquiryController::class, 'approve'])->name('inquiries.approve');
    Route::post('/inquiries/{inquiry}/reject', [InquiryController::class, 'reject'])->name('inquiries.reject');
    Route::post('/inquiries/{inquiry}/mark-sold', [InquiryController::class, 'markSold'])->name('inquiries.markSold');
});
