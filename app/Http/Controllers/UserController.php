<?php

namespace App\Http\Controllers;

use App\Models\Painting;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function settings(Request $request)
    {
        $user = $request->user();

        $purchased = $user->soldPaintings()
            ->with(['user:id,name', 'category:id,name'])
            ->latest('sold_at')
            ->get()
            ->map(fn (Painting $painting) => [
                'id' => $painting->id,
                'title' => $painting->title,
                'emoji' => $painting->emoji,
                'image_url' => $painting->image_url,
                'price' => $painting->price,
                'sold_at' => $painting->sold_at?->format('d/m/Y H:i'),
                'artist' => $painting->user?->name,
            ]);

        $inquiries = $user->inquiriesSent()
            ->with(['painting', 'seller'])
            ->latest()
            ->get()
            ->map(fn (\App\Models\Inquiry $inquiry) => [
                'id' => $inquiry->id,
                'status' => $inquiry->status,
                'created_at' => $inquiry->created_at?->format('d/m/Y H:i'),
                'painting' => [
                    'id' => $inquiry->painting?->id,
                    'title' => $inquiry->painting?->title,
                    'price' => $inquiry->painting?->price,
                ],
                'seller' => [
                    'name' => $inquiry->seller?->name,
                ],
            ]);

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
            'purchasedPaintings' => $purchased,
            'myInquiries' => $inquiries,
            'balance' => $user->balance,
        ]);
    }
    public function list(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $authUser = $request->user();
        $search = trim((string) $request->input('search', ''));

        $query = User::query();

        if (! $authUser->isAdmin()) {
            $query->where('id', $authUser->id);
        }

        return Inertia::render('Users/Index', [
            'users' => $query
                ->when($search !== '', function ($query) use ($search, $authUser) {
                    if (! $authUser->isAdmin()) {
                        return;
                    }

                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('id')
                ->paginate(10)
                ->withQueryString()
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'is_vendor' => $user->is_vendor,
                ]),

            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function view(Request $request, User $user)
    {
        Gate::authorize('view', $user);

        return Inertia::render('Users/Show', [
            'user' => $user->only(['id', 'name', 'email', 'is_admin', 'is_vendor']),
            'stats' => [
                'paintings' => $user->paintings()->count(),
                'favorites' => $user->favoritePaintings()->count(),
                'comments' => $user->comments()->count(),
            ],
        ]);
    }

    public function createView(Request $request)
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Users/Create');
    }

    public function create(Request $request)
    {
        Gate::authorize('create', User::class);

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8'],
            'is_admin' => ['nullable', 'boolean'],
            'is_vendor' => ['nullable', 'boolean'],
        ]);

        if (! $request->user()->isAdmin()) {
            unset($attributes['is_admin']);
            unset($attributes['is_vendor']);
        } else {
            $attributes['is_admin'] = $request->boolean('is_admin');
            $attributes['is_vendor'] = $request->boolean('is_vendor');
        }

        // El cast "hashed" del modelo User procesa la contraseña.
        $user = User::create($attributes);

        return redirect()
            ->route('users.view', $user->id)
            ->with('success', 'Usuario creado correctamente.');
    }

    public function editView(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $backUrl = $request->query('from') === 'list'
            ? route('users.list')
            : route('users.view', $user->id);

        return Inertia::render('Users/Edit', [
            'user' => $user->only(['id', 'name', 'email', 'is_admin', 'is_vendor']),
            'backUrl' => $backUrl,
        ]);
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['nullable', 'boolean'],
            'is_vendor' => ['nullable', 'boolean'],
        ]);

        if (blank($attributes['password'] ?? null)) {
            unset($attributes['password']);
        }

        if (! $request->user()->isAdmin()) {
            unset($attributes['is_admin']);
            unset($attributes['is_vendor']);
        } else {
            $attributes['is_admin'] = $request->boolean('is_admin');
            $attributes['is_vendor'] = $request->boolean('is_vendor');
        }

        $user->update($attributes);

        return redirect()
            ->route('users.view', $user->id)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function delete(Request $request, User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.list')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
