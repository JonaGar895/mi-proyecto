<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function list(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->when($search !== '', function ($query) use ($search) {
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
                ]),

            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function view(User $user)
    {
        return Inertia::render('Users/Show', [
            'user' => $user->only(['id', 'name', 'email']),
            'stats' => [
                'paintings' => $user->paintings()->count(),
                'favorites' => $user->favoritePaintings()->count(),
                'comments' => $user->comments()->count(),
            ],
        ]);
    }

    public function createView()
    {
        return Inertia::render('Users/Create');
    }

    public function create(Request $request)
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // El cast "hashed" del modelo User procesa la contraseña.
        $user = User::create($attributes);

        return redirect()
            ->route('users.view', $user->id)
            ->with('success', 'Usuario creado correctamente.');
    }

    public function editView(Request $request, User $user)
    {
        $backUrl = $request->query('from') === 'list'
            ? route('users.list')
            : route('users.view', $user->id);

        return Inertia::render('Users/Edit', [
            'user' => $user->only(['id', 'name', 'email']),
            'backUrl' => $backUrl,
        ]);
    }

    public function update(Request $request, User $user)
    {
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
            ->route('users.view', $user->id)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function delete(User $user)
    {
        $user->delete();

        return redirect()
            ->route('users.list')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
