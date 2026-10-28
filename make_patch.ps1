$content = Get-Content 'C:\Projects\mi-proyecto\app\Http\Controllers\PaintingController.php' -Raw
$old = @"
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
        $attributes['emoji'] = $attributes['emoji'] ?? '�Y-���?';
        $attributes['is_public'] = $request->has('is_public')
            ? $request->boolean('is_public')
            : true;

        Painting::create($attributes);

        return redirect()
            ->route('paintings.index')
            ->with('success', 'Obra publicada correctamente.');
    }
"@
$new = @"
    public function create(Request $request)
    {
        $user = $request->user();
        abort_unless($user->canSell(), 403);

        return Inertia::render('Paintings/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->canSell(), 403);

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

        $attributes['user_id'] = $user->id;
        $attributes['emoji'] = $attributes['emoji'] ?? '🖼️';
        $attributes['is_public'] = $request->has('is_public')
            ? $request->boolean('is_public')
            : true;

        Painting::create($attributes);

        return redirect()
            ->route('paintings.index')
            ->with('success', 'Obra publicada correctamente.');
    }
"@
if ($content -notmatch [regex]::Escape($old)) { Write-Host 'NOT FOUND'; exit 1 }
$content = $content.Replace($old, $new)
Set-Content 'C:\Projects\mi-proyecto\app\Http\Controllers\PaintingController.php' $content -Encoding UTF8
Write-Host 'OK'
