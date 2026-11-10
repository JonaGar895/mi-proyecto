<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\PublicationComment;
use App\Models\User;
use App\Notifications\CommentReplied;
use App\Notifications\PublicationCommented;
use App\Rules\UniqueNormalized;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PublicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->input('search', ''));
        $category = $request->input('category');

        $publications = Publication::query()
            ->with(['user:id,name', 'category:id,name,slug', 'painting:id,title,emoji,image_url,image_path'])
            ->withCount('comments')
            ->where(function ($query) use ($user) {
                $query->where('is_published', true)
                    ->orWhere('user_id', $user->id);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($category, fn ($query, $category) => $query->whereHas(
                'category',
                fn ($query) => $query->where('slug', $category),
            ))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Publication $publication) => $this->publicationData($publication));

        return Inertia::render('Publicaciones/Index', [
            'publications' => $publications,
            'categories' => Category::orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Publicaciones/Create', $this->formData($request));
    }

    public function store(Request $request)
    {
        $attributes = $this->validatePublication($request);
        $isPublished = $request->has('is_published') ? $request->boolean('is_published') : true;

        $attributes['user_id'] = $request->user()->id;
        $attributes['is_published'] = $isPublished;
        $attributes['published_at'] = $isPublished ? now() : null;

        if ($request->hasFile('image')) {
            $attributes = array_merge(
                $attributes,
                ImageService::store($request->file('image'), 'publications'),
            );
        }

        unset($attributes['image']);

        $publication = Publication::create($attributes);

        return redirect()
            ->route('publications.show', $publication)
            ->with('success', 'Publicación creada correctamente.');
    }

    public function show(Request $request, Publication $publication)
    {
        $user = $request->user();
        abort_unless(
            $publication->is_published || $publication->user_id === $user->id || $user->isAdmin(),
            404,
        );

        $publication->load([
            'user:id,name',
            'category:id,name,slug',
            'painting:id,title,emoji,image_url,image_path',
        ]);
        $publication->loadCount('comments');

        $comments = $publication->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name', 'replies.user:id,name'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Publicaciones/Show', [
            'publication' => array_merge($this->publicationData($publication), [
                'content' => $publication->content,
                'can_edit' => $publication->user_id === $user->id || $user->isAdmin(),
                'can_delete' => $publication->user_id === $user->id || $user->isAdmin(),
            ]),
            'comments' => [
                'data' => $comments->getCollection()
                    ->map(fn (PublicationComment $comment) => $this->commentData($comment, $user))
                    ->all(),
                'links' => $comments->links(),
                'has_pages' => $comments->hasPages(),
            ],
        ]);
    }

    public function edit(Request $request, Publication $publication)
    {
        $user = $request->user();
        abort_unless($publication->user_id === $user->id || $user->isAdmin(), 403);

        return Inertia::render('Publicaciones/Edit', array_merge($this->formData($request), [
            'publication' => [
                'id' => $publication->id,
                'title' => $publication->title,
                'summary' => $publication->summary,
                'content' => $publication->content,
                'category_id' => $publication->category_id,
                'painting_id' => $publication->painting_id,
                'image_url' => ImageService::url($publication->image_path),
                'is_published' => $publication->is_published,
            ],
        ]));
    }

    public function update(Request $request, Publication $publication)
    {
        $user = $request->user();
        abort_unless($publication->user_id === $user->id || $user->isAdmin(), 403);

        $attributes = $this->validatePublication($request, $publication);
        $isPublished = $request->has('is_published') ? $request->boolean('is_published') : true;

        $attributes['is_published'] = $isPublished;

        if (! $isPublished) {
            $attributes['published_at'] = null;
        } elseif (! $publication->published_at) {
            $attributes['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            $attributes = array_merge(
                $attributes,
                ImageService::store($request->file('image'), 'publications'),
            );
        }

        unset($attributes['image']);

        $publication->update($attributes);

        return redirect()
            ->route('publications.show', $publication)
            ->with('success', 'Publicación actualizada correctamente.');
    }

    public function destroy(Request $request, Publication $publication)
    {
        $user = $request->user();
        abort_unless($publication->user_id === $user->id || $user->isAdmin(), 403);

        $publication->delete();

        return redirect()
            ->route('publications.index')
            ->with('success', 'Publicación eliminada correctamente.');
    }

    public function comment(Request $request, Publication $publication)
    {
        $user = $request->user();
        abort_unless(
            $publication->is_published || $publication->user_id === $user->id || $user->isAdmin(),
            404,
        );

        $attributes = $request->validate([
            'body' => [
                'required',
                'string',
                'max:1000',
                new UniqueNormalized(
                    PublicationComment::class,
                    'body',
                    scopes: [
                        'user_id' => $user->id,
                        'publication_id' => $publication->id,
                    ],
                    message: 'Ya has publicado este mismo comentario en esta publicación.',
                ),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('publication_comments', 'id')
                    ->where('publication_id', $publication->id)
                    ->whereNull('parent_id'),
            ],
        ]);

        $comment = $publication->comments()->create([
            'user_id' => $user->id,
            'parent_id' => filled($attributes['parent_id'] ?? null) ? (int) $attributes['parent_id'] : null,
            'body' => trim($attributes['body']),
        ]);

        $owner = $publication->user;

        if ($owner !== null && $owner->id !== $user->id) {
            $owner->notify(new PublicationCommented($comment, $publication));
        }

        if ($comment->parent_id !== null) {
            $parentAuthor = $comment->parent?->user;

            if ($parentAuthor !== null && $parentAuthor->id !== $user->id && $parentAuthor->id !== $owner?->id) {
                $parentAuthor->notify(new CommentReplied($comment, $comment->parent));
            }
        }

        return back()->with('success', 'Comentario añadido.');
    }

    public function updateComment(Request $request, Publication $publication, PublicationComment $comment)
    {
        $user = $request->user();
        abort_unless($comment->publication_id === $publication->id, 404);
        abort_unless($comment->user_id === $user->id || $user->isAdmin(), 403);

        $attributes = $request->validate([
            'body' => [
                'required',
                'string',
                'max:1000',
                new UniqueNormalized(
                    PublicationComment::class,
                    'body',
                    $comment,
                    scopes: [
                        'user_id' => $comment->user_id,
                        'publication_id' => $publication->id,
                    ],
                    message: 'Ya has publicado este mismo comentario en esta publicación.',
                ),
            ],
        ]);

        $comment->update(['body' => trim($attributes['body'])]);

        return back()->with('success', 'Comentario actualizado.');
    }

    public function destroyComment(Request $request, Publication $publication, PublicationComment $comment)
    {
        $user = $request->user();
        abort_unless($comment->publication_id === $publication->id, 404);
        abort_unless($comment->user_id === $user->id || $user->isAdmin(), 403);

        $comment->delete();

        return back()->with('success', 'Comentario eliminado.');
    }

    private function commentData(PublicationComment $comment, User $user): array
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
            'replies' => $comment->replies->map(fn (PublicationComment $reply) => [
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

    private function validatePublication(Request $request, ?Publication $publication = null): array
    {
        $attributes = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                new UniqueNormalized(
                    Publication::class,
                    'title',
                    $publication,
                    message: 'Ya existe una publicación con ese título. No se permiten publicaciones duplicadas.',
                ),
            ],
            'summary' => [
                'nullable',
                'string',
                'max:500',
                new UniqueNormalized(
                    Publication::class,
                    'summary',
                    $publication,
                    message: 'Ya existe una publicación con ese resumen. No se permiten publicaciones duplicadas.',
                ),
            ],
            'content' => [
                'required',
                'string',
                'max:20000',
                new UniqueNormalized(
                    Publication::class,
                    'content',
                    $publication,
                    message: 'Ya existe una publicación con ese contenido. No se permiten publicaciones duplicadas.',
                ),
            ],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'painting_id' => ['nullable', 'integer', 'exists:paintings,id'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
                $this->uniqueImageRule($publication),
            ],
            'is_published' => ['boolean'],
        ]);

        $attributes['title'] = trim($attributes['title']);
        $attributes['content'] = trim($attributes['content']);

        if (array_key_exists('summary', $attributes)) {
            $attributes['summary'] = filled($attributes['summary'])
                ? trim($attributes['summary'])
                : null;
        }

        return $attributes;
    }

    /** Rechaza imágenes idénticas o muy parecidas ya registradas en obras o publicaciones. */
    private function uniqueImageRule(?Publication $publication): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($publication): void {
            if (! $value instanceof UploadedFile) {
                return;
            }

            if (ImageService::hashExists(ImageService::hash($value), null, $publication)) {
                $fail('Ya existe un registro con esta misma imagen. No se permiten imágenes duplicadas.');

                return;
            }

            $fingerprints = ImageService::perceptualHashes($value->getRealPath());

            if (ImageService::nearDuplicateExists($fingerprints['phash'], $fingerprints['dhash'], null, $publication)) {
                $fail('Ya existe una imagen muy similar registrada. No se permiten imágenes duplicadas.');
            }
        };
    }

    private function formData(Request $request): array
    {
        $user = $request->user();

        $paintings = Painting::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhere('is_public', true);
            })
            ->with('user:id,name')
            ->orderBy('title')
            ->get(['id', 'title', 'user_id', 'emoji', 'image_url', 'image_path']);

        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'paintings' => $paintings,
        ];
    }

    private function publicationData(Publication $publication): array
    {
        return [
            'id' => $publication->id,
            'title' => $publication->title,
            'summary' => $publication->summary,
            'image_url' => ImageService::url($publication->image_path),
            'date' => ($publication->published_at ?? $publication->created_at)
                ?->translatedFormat('d M Y'),
            'is_published' => $publication->is_published,
            'author' => $publication->user ? [
                'id' => $publication->user->id,
                'name' => $publication->user->name,
            ] : null,
            'category' => $publication->category ? [
                'id' => $publication->category->id,
                'name' => $publication->category->name,
                'slug' => $publication->category->slug,
            ] : null,
            'painting' => $publication->painting ? [
                'id' => $publication->painting->id,
                'title' => $publication->painting->title,
                'emoji' => $publication->painting->emoji,
                'image_url' => ImageService::url(
                    $publication->painting->image_path,
                    $publication->painting->image_url,
                ),
            ] : null,
            'comments_count' => $publication->comments_count ?? 0,
        ];
    }
}
