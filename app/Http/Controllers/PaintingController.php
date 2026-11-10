<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Painting;
use App\Models\User;
use App\Notifications\CommentReplied;
use App\Notifications\PaintingCommented;
use App\Notifications\PaintingFavorited;
use App\Rules\UniqueNormalized;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaintingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->input('search', ''));
        $category = $request->input('category');
        $technique = trim((string) $request->input('technique', ''));
        $status = (string) $request->input('status', '');
        $sort = (string) $request->input('sort', 'recent');
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');
        $year = $request->input('year');

        $paintings = Painting::query()
            ->with(['user:id,name', 'category:id,name,slug'])
            ->withCount(['favorites', 'comments', 'ratings'])
            ->withAvg('ratings as ratings_avg_rating', 'rating')
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
            ->when($technique !== '', fn ($query) => $query->where('technique', 'like', "%{$technique}%"))
            ->when($status !== '' && in_array($status, ['available', 'sold', 'reserved'], true), function ($query) use ($status) {
                $query->when(
                    $status === 'available',
                    fn ($query) => $query->where(fn ($query) => $query->whereNull('status')->orWhere('status', 'available')),
                    fn ($query) => $query->where('status', $status),
                );
            })
            ->when(filled($priceMin), fn ($query) => $query->where('price', '>=', $priceMin))
            ->when(filled($priceMax), fn ($query) => $query->where('price', '<=', $priceMax))
            ->when(filled($year), fn ($query) => $query->where('year', $year))
            ->when($sort === 'favorites', fn ($query) => $query->orderByDesc('favorites_count'))
            ->when($sort === 'comments', fn ($query) => $query->orderByDesc('comments_count'))
            ->when($sort === 'rating', fn ($query) => $query->orderByDesc('ratings_avg_rating'))
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('created_at'))
            ->when(! in_array($sort, ['favorites', 'comments', 'rating', 'price_asc', 'price_desc', 'oldest'], true), function ($query) {
                $query->latest();
            })
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Painting $painting) => $this->paintingData($painting));

        return Inertia::render('Paintings', [
            'paintings' => $paintings,
            'categories' => Category::orderBy('name')->get(['id', 'name', 'slug']),
            'techniques' => Painting::query()
                ->where('is_public', true)
                ->whereNotNull('technique')
                ->distinct()
                ->orderBy('technique')
                ->pluck('technique'),
            'filters' => [
                'search' => $search,
                'category' => $category,
                'technique' => $technique,
                'status' => $status,
                'sort' => $sort,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'year' => $year,
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
        $attributes = $this->validatePainting($request);

        $attributes['title'] = trim($attributes['title']);
        $attributes['description'] = filled($attributes['description'] ?? null)
            ? trim($attributes['description'])
            : null;
        $attributes['user_id'] = $request->user()->id;
        $attributes['emoji'] = $attributes['emoji'] ?? '🖼️';
        $attributes['is_public'] = $request->has('is_public')
            ? $request->boolean('is_public')
            : true;

        if ($request->hasFile('image')) {
            $attributes = array_merge(
                $attributes,
                ImageService::store($request->file('image'), 'paintings'),
            );
        }

        unset($attributes['image']);

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
        $painting->loadCount(['favorites', 'comments', 'ratings']);
        $painting->loadAvg('ratings', 'rating');
        $painting->setAttribute(
            'is_favorited',
            $painting->favorites()->where('user_id', $user->id)->exists(),
        );

        $hasInquired = $painting->inquiries()->where('buyer_id', $user->id)->exists();
        $buyCheck = $user->canBuy($painting);

        $comments = $painting->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name', 'replies.user:id,name'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

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
                'can_edit' => $painting->user_id === $user->id || $user->isAdmin(),
                'is_owner' => $painting->user_id === $user->id,
                'my_rating' => $painting->ratings()->where('user_id', $user->id)->value('rating'),
            ]),
            'comments' => [
                'data' => $comments->getCollection()->map(fn (Comment $comment) => $this->commentData($comment, $user))->all(),
                'links' => $comments->links(),
                'has_pages' => $comments->hasPages(),
            ],
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

    public function edit(Request $request, Painting $painting)
    {
        $user = $request->user();
        abort_unless($painting->user_id === $user->id || $user->isAdmin(), 403);

        return Inertia::render('Paintings/Edit', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'painting' => [
                'id' => $painting->id,
                'title' => $painting->title,
                'description' => $painting->description,
                'category_id' => $painting->category_id,
                'year' => $painting->year,
                'technique' => $painting->technique,
                'emoji' => $painting->emoji,
                'image_url' => ImageService::url($painting->image_path, $painting->image_url),
                'price' => $painting->price,
                'is_public' => $painting->is_public,
            ],
        ]);
    }

    public function update(Request $request, Painting $painting)
    {
        $user = $request->user();
        abort_unless($painting->user_id === $user->id || $user->isAdmin(), 403);
        abort_if($painting->status === 'sold', 403, 'No se puede editar una obra ya vendida.');

        $attributes = $this->validatePainting($request, $painting);

        $attributes['title'] = trim($attributes['title']);
        $attributes['description'] = filled($attributes['description'] ?? null)
            ? trim($attributes['description'])
            : null;
        $attributes['emoji'] = $attributes['emoji'] ?? $painting->emoji;
        $attributes['is_public'] = $request->has('is_public')
            ? $request->boolean('is_public')
            : $painting->is_public;

        if ($request->hasFile('image')) {
            $attributes = array_merge(
                $attributes,
                ImageService::store($request->file('image'), 'paintings'),
            );
        }

        unset($attributes['image']);

        $painting->update($attributes);

        return redirect()
            ->route('paintings.show', $painting)
            ->with('success', 'Obra actualizada correctamente.');
    }

    /** Listado de las obras propias del usuario. */
    public function mine(Request $request)
    {
        $user = $request->user();

        $paintings = $user->paintings()
            ->with(['category:id,name'])
            ->withCount(['favorites', 'comments'])
            ->withAvg('ratings as ratings_avg_rating', 'rating')
            ->latest()
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Painting $painting) => [
                'id' => $painting->id,
                'title' => $painting->title,
                'emoji' => $painting->emoji,
                'image_url' => ImageService::url($painting->image_path, $painting->image_url),
                'category' => $painting->category?->name,
                'year' => $painting->year,
                'price' => $painting->price,
                'status' => $painting->status ?? 'available',
                'is_public' => $painting->is_public,
                'favorites_count' => $painting->favorites_count,
                'comments_count' => $painting->comments_count,
                'ratings_avg' => round((float) $painting->ratings_avg_rating, 1),
            ]);

        return Inertia::render('Paintings/Mine', [
            'paintings' => $paintings,
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

        $wasFavorited = $user->favoritePaintings()
            ->where('painting_id', $painting->id)
            ->exists();

        $user->favoritePaintings()->toggle($painting->id);

        if (! $wasFavorited && $painting->user_id !== null && $painting->user_id !== $user->id) {
            $painting->user->notify(new PaintingFavorited($painting, $user));
        }

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
            'body' => [
                'required',
                'string',
                'max:1000',
                new UniqueNormalized(
                    Comment::class,
                    'body',
                    scopes: [
                        'user_id' => $user->id,
                        'painting_id' => $painting->id,
                    ],
                    message: 'Ya has publicado este mismo comentario en esta obra.',
                ),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')
                    ->where('painting_id', $painting->id)
                    ->whereNull('parent_id'),
            ],
        ]);

        $comment = $painting->comments()->create([
            'user_id' => $user->id,
            'parent_id' => filled($attributes['parent_id'] ?? null) ? (int) $attributes['parent_id'] : null,
            'body' => trim($attributes['body']),
        ]);

        $this->notifyCommentAuthors($comment, $painting, $user);

        return back()->with('success', 'Comentario añadido.');
    }

    public function updateComment(Request $request, Painting $painting, Comment $comment)
    {
        $user = $request->user();
        abort_unless($comment->painting_id === $painting->id, 404);
        abort_unless($comment->user_id === $user->id || $user->isAdmin(), 403);

        $attributes = $request->validate([
            'body' => [
                'required',
                'string',
                'max:1000',
                new UniqueNormalized(
                    Comment::class,
                    'body',
                    $comment,
                    scopes: [
                        'user_id' => $comment->user_id,
                        'painting_id' => $painting->id,
                    ],
                    message: 'Ya has publicado este mismo comentario en esta obra.',
                ),
            ],
        ]);

        $comment->update(['body' => trim($attributes['body'])]);

        return back()->with('success', 'Comentario actualizado.');
    }

    public function destroyComment(Request $request, Painting $painting, Comment $comment)
    {
        $user = $request->user();
        abort_unless($comment->painting_id === $painting->id, 404);
        abort_unless($comment->user_id === $user->id || $user->isAdmin(), 403);

        $comment->delete();

        return back()->with('success', 'Comentario eliminado.');
    }

    /** Notifica al autor de la obra y al autor del comentario respondido. */
    private function notifyCommentAuthors(Comment $comment, Painting $painting, User $user): void
    {
        $owner = $painting->user;

        if ($owner !== null && $owner->id !== $user->id) {
            $owner->notify(new PaintingCommented($comment, $painting));
        }

        if ($comment->parent_id !== null) {
            $parentAuthor = $comment->parent?->user;

            if ($parentAuthor !== null && $parentAuthor->id !== $user->id && $parentAuthor->id !== $owner?->id) {
                $parentAuthor->notify(new CommentReplied($comment, $comment->parent));
            }
        }
    }

    private function validatePainting(Request $request, ?Painting $painting = null): array
    {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                new UniqueNormalized(
                    Painting::class,
                    'title',
                    $painting,
                    message: 'Ya existe una obra con ese título. No se permiten obras duplicadas.',
                ),
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
                new UniqueNormalized(
                    Painting::class,
                    'description',
                    $painting,
                    message: 'Ya existe una obra con esa descripción. No se permiten obras duplicadas.',
                ),
            ],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'year' => ['nullable', 'integer', 'min:1000', 'max:'.date('Y')],
            'technique' => ['nullable', 'string', 'max:255'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
                $this->uniqueImageRule($painting),
            ],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'is_public' => ['boolean'],
        ]);
    }

    /** Rechaza imágenes idénticas o muy parecidas ya registradas en obras o publicaciones. */
    private function uniqueImageRule(?Painting $painting = null): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($painting): void {
            if (! $value instanceof UploadedFile) {
                return;
            }

            $publication = null;

            if (ImageService::hashExists(ImageService::hash($value), $painting)) {
                $fail('Ya existe un registro con esta misma imagen. No se permiten imágenes duplicadas.');

                return;
            }

            $fingerprints = ImageService::perceptualHashes($value->getRealPath());

            if (ImageService::nearDuplicateExists($fingerprints['phash'], $fingerprints['dhash'], $painting, $publication)) {
                $fail('Ya existe una imagen muy similar registrada. No se permiten imágenes duplicadas.');
            }
        };
    }

    private function commentData(Comment $comment, User $user): array
    {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'created_at' => $comment->created_at->diffForHumans(),
            'can_edit' => $comment->user_id === $user->id || $user->isAdmin(),
            'can_delete' => $comment->user_id === $user->id || $user->isAdmin(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
            ],
            'replies' => $comment->replies->map(fn (Comment $reply) => [
                'id' => $reply->id,
                'body' => $reply->body,
                'created_at' => $reply->created_at->diffForHumans(),
                'can_edit' => $reply->user_id === $user->id || $user->isAdmin(),
                'can_delete' => $reply->user_id === $user->id || $user->isAdmin(),
                'user' => [
                    'id' => $reply->user->id,
                    'name' => $reply->user->name,
                ],
            ])->all(),
        ];
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
            'image_url' => ImageService::url($painting->image_path, $painting->image_url),
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
            'ratings_avg' => round((float) ($painting->ratings_avg_rating ?? 0), 1),
            'ratings_count' => $painting->ratings_count ?? 0,
        ];
    }
}
