<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Publication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->input('search', ''));
        $category = $request->input('category');

        $publications = Publication::query()
            ->with(['user:id,name', 'category:id,name,slug', 'painting:id,title,emoji,image_url'])
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
            'painting:id,title,emoji,image_url',
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
                'is_published' => $publication->is_published,
            ],
        ]));
    }

    public function update(Request $request, Publication $publication)
    {
        $user = $request->user();
        abort_unless($publication->user_id === $user->id || $user->isAdmin(), 403);

        $attributes = $this->validatePublication($request);
        $isPublished = $request->has('is_published') ? $request->boolean('is_published') : true;

        $attributes['is_published'] = $isPublished;

        if (! $isPublished) {
            $attributes['published_at'] = null;
        } elseif (! $publication->published_at) {
            $attributes['published_at'] = now();
        }

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

    private function validatePublication(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:20000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'painting_id' => ['nullable', 'integer', 'exists:paintings,id'],
            'is_published' => ['boolean'],
        ]);
    }

    private function formData(Request $request): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'paintings' => $request->user()->paintings()->orderBy('title')->get(['id', 'title']),
        ];
    }

    private function publicationData(Publication $publication): array
    {
        return [
            'id' => $publication->id,
            'title' => $publication->title,
            'summary' => $publication->summary,
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
                'image_url' => $publication->painting->image_url,
            ] : null,
            'comments_count' => $publication->comments_count ?? 0,
        ];
    }
}
