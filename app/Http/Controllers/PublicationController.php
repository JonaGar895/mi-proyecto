<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Painting;
use App\Models\Publication;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

        return Inertia::render('Publicaciones/Show', [
            'publication' => array_merge($this->publicationData($publication), [
                'content' => $publication->content,
                'can_edit' => $publication->user_id === $user->id || $user->isAdmin(),
                'can_delete' => $publication->user_id === $user->id || $user->isAdmin(),
            ]),
            'comments' => $publication->comments()
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
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $publication->comments()->create([
            'user_id' => $user->id,
            'body' => $attributes['body'],
        ]);

        return back()->with('success', 'Comentario añadido.');
    }

    private function validatePublication(Request $request, ?Publication $publication = null): array
    {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                $this->uniqueTitleRule($publication),
            ],
            'summary' => [
                'nullable',
                'string',
                'max:500',
                $this->uniqueSummaryRule($publication),
            ],
            'content' => [
                'required',
                'string',
                'max:20000',
                $this->uniqueContentRule($publication),
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
    }

    /** Rechaza publicaciones con un título ya existente. */
    private function uniqueTitleRule(?Publication $publication): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($publication): void {
            $exists = Publication::query()
                ->whereRaw('LOWER(title) = ?', [mb_strtolower(trim((string) $value))])
                ->when($publication, fn ($query) => $query->whereKeyNot($publication->id))
                ->exists();

            if ($exists) {
                $fail('Ya existe una publicación con ese título. No se permiten publicaciones duplicadas.');
            }
        };
    }

    /** Rechaza publicaciones con un resumen ya existente. */
    private function uniqueSummaryRule(?Publication $publication): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($publication): void {
            $summary = trim((string) $value);

            if ($summary === '') {
                return;
            }

            $exists = Publication::query()
                ->whereRaw('LOWER(summary) = ?', [mb_strtolower($summary)])
                ->when($publication, fn ($query) => $query->whereKeyNot($publication->id))
                ->exists();

            if ($exists) {
                $fail('Ya existe una publicación con ese resumen. No se permiten publicaciones duplicadas.');
            }
        };
    }

    /** Rechaza publicaciones con un contenido ya existente. */
    private function uniqueContentRule(?Publication $publication): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($publication): void {
            $content = trim((string) $value);

            if ($content === '') {
                return;
            }

            $exists = Publication::query()
                ->whereRaw('LOWER(content) = ?', [mb_strtolower($content)])
                ->when($publication, fn ($query) => $query->whereKeyNot($publication->id))
                ->exists();

            if ($exists) {
                $fail('Ya existe una publicación con ese contenido. No se permiten publicaciones duplicadas.');
            }
        };
    }

    /** Rechaza imágenes cuyo contenido ya esté registrado en obras o publicaciones. */
    private function uniqueImageRule(?Publication $publication): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($publication): void {
            if (! $value instanceof UploadedFile) {
                return;
            }

            if (ImageService::hashExists(ImageService::hash($value), null, $publication)) {
                $fail('Ya existe un registro con esta misma imagen. No se permiten imágenes duplicadas.');
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
