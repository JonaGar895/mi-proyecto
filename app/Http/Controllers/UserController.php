<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
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
            'user' => $user->only(['id', 'name', 'email', 'is_admin']),
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
        ]);

        if (! $request->user()->isAdmin()) {
            unset($attributes['is_admin']);
        } else {
            $attributes['is_admin'] = $request->boolean('is_admin');
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
            'user' => $user->only(['id', 'name', 'email', 'is_admin']),
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
        ]);

        if (blank($attributes['password'] ?? null)) {
            unset($attributes['password']);
        }

        if (! $request->user()->isAdmin()) {
            unset($attributes['is_admin']);
        } else {
            $attributes['is_admin'] = $request->boolean('is_admin');
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
