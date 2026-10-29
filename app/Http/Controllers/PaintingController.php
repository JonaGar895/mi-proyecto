<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Painting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaintingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->input('search', ''));
        $category = $request->input('category');

        $paintings = Painting::query()
            ->with(['user:id,name', 'category:id,name,slug'])
            ->withCount(['favorites', 'comments'])
            ->withExists(['favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id)])
            ->where('is_public', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($category, fn ($query, $category) => $query->whereHas(
                'category',
                fn ($query) => $query->where('slug', $category),
            ))
            ->latest()
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Painting $painting) => $this->paintingData($painting));

        return Inertia::render('Paintings', [
            'paintings' => $paintings,
            'categories' => Category::orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Paintings/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'year' => ['nullable', 'integer', 'min:1000', 'max:'.date('Y')],
            'technique' => ['nullable', 'string', 'max:255'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'is_public' => ['boolean'],
        ]);

        $attributes['user_id'] = $request->user()->id;
        $attributes['emoji'] = $attributes['emoji'] ?? '🖼️';
        $attributes['is_public'] = $request->has('is_public')
            ? $request->boolean('is_public')
            : true;

        Painting::create($attributes);

        return redirect()
            ->route('paintings.index')
            ->with('success', 'Obra publicada correctamente.');
    }

    public function show(Request $request, Painting $painting)
    {
        $user = $request->user();
        abort_unless(
            $painting->is_public || $painting->user_id === $user->id || $user->isAdmin(),
            404,
        );

        $painting->load([
            'user:id,name,email,is_vendor,is_admin',
            'category:id,name,slug',
            'exhibitions:id,title,starts_at,ends_at',
        ]);
        $painting->loadCount(['favorites', 'comments']);
        $painting->setAttribute(
            'is_favorited',
            $painting->favorites()->where('user_id', $user->id)->exists(),
        );

        $hasInquired = $painting->inquiries()->where('buyer_id', $user->id)->exists();
        $buyCheck = $user->canBuy($painting);

        return Inertia::render('Paintings/Show', [
            'painting' => array_merge($this->paintingData($painting), [
                'status' => $painting->status ?? 'available',
                'sold_at' => $painting->sold_at?->toDateString(),
                'exhibitions' => $painting->exhibitions->map(fn ($exhibition) => [
                    'id' => $exhibition->id,
                    'title' => $exhibition->title,
                    'starts_at' => $exhibition->starts_at?->toDateString(),
                    'ends_at' => $exhibition->ends_at?->toDateString(),
                ]),
                'can_delete' => $painting->user_id === $user->id || $user->isAdmin(),
                'is_owner' => $painting->user_id === $user->id,
            ]),
            'comments' => $painting->comments()
                ->with('user:id,name')
                ->latest()
                ->get()
                ->map(fn ($comment) => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ],
                ]),
            'hasInquired' => $hasInquired,
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'balance' => $user->balance,
                    'is_admin' => $user->isAdmin(),
                    'is_super_admin' => $user->isSuperAdmin(),
                ],
                'can_buy' => $buyCheck['can_buy'],
                'buy_reason' => $buyCheck['reason'],
            ],
        ]);
    }

    public function destroy(Request $request, Painting $painting)
    {
        $user = $request->user();
        abort_unless($painting->user_id === $user->id || $user->isAdmin(), 403);

        $painting->delete();

        return redirect()
            ->route('paintings.index')
            ->with('success', 'Obra eliminada correctamente.');
    }

    public function favorite(Request $request, Painting $painting)
    {
        $user = $request->user();
        abort_unless(
            $painting->is_public || $painting->user_id === $user->id || $user->isAdmin(),
            404,
        );

        $user->favoritePaintings()->toggle($painting->id);

        return back();
    }

    public function comment(Request $request, Painting $painting)
    {
        $user = $request->user();
        abort_unless(
            $painting->is_public || $painting->user_id === $user->id || $user->isAdmin(),
            404,
        );

        $attributes = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $painting->comments()->create([
            'user_id' => $user->id,
            'body' => $attributes['body'],
        ]);

        return back()->with('success', 'Comentario añadido.');
    }

    private function paintingData(Painting $painting): array
    {
        return [
            'id' => $painting->id,
            'title' => $painting->title,
            'description' => $painting->description,
            'year' => $painting->year,
            'technique' => $painting->technique,
            'status' => $painting->status ?? 'available',
            'sold_at' => $painting->sold_at?->toDateString(),
            'emoji' => $painting->emoji,
            'image_url' => $painting->image_url,
            'price' => $painting->price,
            'is_public' => $painting->is_public,
            'artist' => $painting->user ? [
                'id' => $painting->user->id,
                'name' => $painting->user->name,
            ] : null,
            'category' => $painting->category ? [
                'id' => $painting->category->id,
                'name' => $painting->category->name,
                'slug' => $painting->category->slug,
            ] : null,
            'favorites_count' => $painting->favorites_count ?? 0,
            'comments_count' => $painting->comments_count ?? 0,
            'is_favorited' => (bool) ($painting->is_favorited ?? false),
        ];
    }
}
