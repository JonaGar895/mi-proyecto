<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\Painting;
use App\Models\User;
use App\Rules\UniqueNormalized;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ExhibitionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->input('search', ''));
        $state = (string) $request->input('state', '');
        $today = today();

        $exhibitions = Exhibition::query()
            ->withCount('paintings')
            ->with(['paintings' => fn ($query) => $query
                ->select('paintings.id', 'paintings.title', 'paintings.emoji', 'paintings.image_path', 'paintings.image_url')
                ->orderBy('title')
                ->take(1),
            ])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($state === 'active', fn ($query) => $query
                ->where(fn ($query) => $query
                    ->whereNull('starts_at')
                    ->orWhereDate('starts_at', '<=', $today))
                ->where(fn ($query) => $query
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', $today)))
            ->when($state === 'upcoming', fn ($query) => $query
                ->whereDate('starts_at', '>', $today))
            ->when($state === 'finished', fn ($query) => $query
                ->whereDate('ends_at', '<', $today))
            ->orderByRaw(
                'CASE
                    WHEN starts_at IS NULL THEN 1
                    WHEN starts_at > ? THEN 0
                    ELSE 1
                END',
                [$today],
            )
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Exhibition $exhibition) => $this->exhibitionData($exhibition, $user));

        return Inertia::render('Exposiciones/Index', [
            'exhibitions' => $exhibitions,
            'filters' => [
                'search' => $search,
                'state' => $state,
            ],
            'canManage' => $user->canSell() || $user->isAdmin(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeManagement($request->user());

        return Inertia::render('Exposiciones/Create', $this->formData($request));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $this->authorizeManagement($user);

        $attributes = $this->validateExhibition($request);

        $exhibition = Exhibition::create([
            ...$attributes,
            'user_id' => $user->id,
        ]);

        $exhibition->paintings()->sync($this->attachablePaintingIds($request, $user));

        return redirect()
            ->route('exhibitions.show', $exhibition)
            ->with('success', 'Exposición creada correctamente.');
    }

    public function show(Request $request, Exhibition $exhibition)
    {
        $user = $request->user();

        $exhibition->loadCount('paintings');
        $exhibition->setRelation(
            'paintings',
            $exhibition->paintings()
                ->with(['user:id,name', 'category:id,name'])
                ->withCount(['favorites', 'comments'])
                ->withAvg('ratings as ratings_avg_rating', 'rating')
                ->orderBy('title')
                ->paginate(9)
                ->withQueryString(),
        );

        return Inertia::render('Exposiciones/Show', [
            'exhibition' => $this->exhibitionData($exhibition, $user),
            'paintings' => $exhibition->paintings->through(fn (Painting $painting) => [
                'id' => $painting->id,
                'title' => $painting->title,
                'emoji' => $painting->emoji,
                'image_url' => ImageService::url($painting->image_path, $painting->image_url),
                'artist' => $painting->user?->name,
                'category' => $painting->category?->name,
                'year' => $painting->year,
                'price' => $painting->price,
                'status' => $painting->status ?? 'available',
                'favorites_count' => $painting->favorites_count,
                'comments_count' => $painting->comments_count,
                'ratings_avg' => round((float) $painting->ratings_avg_rating, 1),
            ]),
            'canManage' => $exhibition->canManage($user),
        ]);
    }

    public function edit(Request $request, Exhibition $exhibition)
    {
        $user = $request->user();
        abort_unless($exhibition->canManage($user), 403);

        return Inertia::render('Exposiciones/Edit', array_merge($this->formData($request), [
            'exhibition' => [
                'id' => $exhibition->id,
                'title' => $exhibition->title,
                'description' => $exhibition->description,
                'starts_at' => $exhibition->starts_at?->toDateString(),
                'ends_at' => $exhibition->ends_at?->toDateString(),
                'paintings' => $exhibition->paintings()->pluck('paintings.id')->map(fn ($id) => (string) $id)->all(),
            ],
        ]));
    }

    public function update(Request $request, Exhibition $exhibition)
    {
        $user = $request->user();
        abort_unless($exhibition->canManage($user), 403);

        $attributes = $this->validateExhibition($request, $exhibition);

        $exhibition->update($attributes);
        $exhibition->paintings()->sync($this->attachablePaintingIds($request, $user));

        return redirect()
            ->route('exhibitions.show', $exhibition)
            ->with('success', 'Exposición actualizada correctamente.');
    }

    public function destroy(Request $request, Exhibition $exhibition)
    {
        $user = $request->user();
        abort_unless($exhibition->canManage($user), 403);

        $exhibition->delete();

        return redirect()
            ->route('exhibitions.index')
            ->with('success', 'Exposición eliminada correctamente.');
    }

    /** Solo administradores o vendedores pueden organizar exposiciones. */
    private function authorizeManagement(User $user): void
    {
        abort_unless($user->canSell() || $user->isAdmin(), 403, 'No tienes permisos para gestionar exposiciones.');
    }

    private function validateExhibition(Request $request, ?Exhibition $exhibition = null): array
    {
        $attributes = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                new UniqueNormalized(
                    Exhibition::class,
                    'title',
                    $exhibition,
                    message: 'Ya existe una exposición con ese título. No se permiten exposiciones duplicadas.',
                ),
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
                new UniqueNormalized(
                    Exhibition::class,
                    'description',
                    $exhibition,
                    message: 'Ya existe una exposición con esa descripción. No se permiten exposiciones duplicadas.',
                ),
            ],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => [
                'nullable',
                'date',
                Rule::when($request->filled('starts_at'), ['after_or_equal:starts_at']),
            ],
            'paintings' => ['nullable', 'array'],
            'paintings.*' => ['integer', 'exists:paintings,id'],
        ]);

        $attributes['title'] = trim($attributes['title']);
        $attributes['description'] = filled($attributes['description'] ?? null)
            ? trim($attributes['description'])
            : null;
        $attributes['starts_at'] = filled($attributes['starts_at'] ?? null) ? $attributes['starts_at'] : null;
        $attributes['ends_at'] = filled($attributes['ends_at'] ?? null) ? $attributes['ends_at'] : null;

        unset($attributes['paintings']);

        return $attributes;
    }

    /**
     * Devuelve los ids de obras válidos para la exposición: públicas o
     * propias del usuario (los administradores pueden elegir cualquiera).
     *
     * @return list<int>
     */
    private function attachablePaintingIds(Request $request, User $user): array
    {
        $requested = array_map('intval', (array) ($request->input('paintings') ?? []));

        if ($requested === []) {
            return [];
        }

        $query = Painting::whereIn('id', $requested);

        if (! $user->isAdmin()) {
            $query->where(function ($query) use ($user) {
                $query->where('is_public', true)->orWhere('user_id', $user->id);
            });
        }

        return $query->pluck('id')->all();
    }

    private function formData(Request $request): array
    {
        $user = $request->user();

        $paintings = Painting::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where(function ($query) use ($user) {
                $query->where('is_public', true)->orWhere('user_id', $user->id);
            }))
            ->with('user:id,name')
            ->orderBy('title')
            ->get(['id', 'title', 'user_id', 'emoji', 'image_url', 'image_path', 'is_public'])
            ->map(fn (Painting $painting) => [
                'id' => (string) $painting->id,
                'title' => $painting->title,
                'artist' => $painting->user?->name,
                'emoji' => $painting->emoji,
                'image_url' => ImageService::url($painting->image_path, $painting->image_url),
            ]);

        return ['paintings' => $paintings];
    }

    private function exhibitionData(Exhibition $exhibition, User $user): array
    {
        return [
            'id' => $exhibition->id,
            'title' => $exhibition->title,
            'description' => $exhibition->description,
            'starts_at' => $exhibition->starts_at?->toDateString(),
            'ends_at' => $exhibition->ends_at?->toDateString(),
            'state' => $exhibition->state(),
            'paintings_count' => $exhibition->paintings_count,
            'cover' => $exhibition->paintings->isNotEmpty()
                ? ImageService::url(
                    $exhibition->paintings[0]->image_path,
                    $exhibition->paintings[0]->image_url,
                )
                : null,
            'organizer' => $exhibition->user ? [
                'id' => $exhibition->user->id,
                'name' => $exhibition->user->name,
            ] : null,
            'can_manage' => $exhibition->canManage($user),
        ];
    }
}
