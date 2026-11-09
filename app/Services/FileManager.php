<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Inquiry;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\PublicationComment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Analiza y limpia los archivos de imagen y los registros duplicados.
 *
 * Solo se eliminan registros cuando son duplicados exactos (mismo título,
 * resumen/descripción y contenido). Los títulos repetidos con contenido
 * distinto se muestran como conflictos para revisión manual.
 */
class FileManager
{
    /** Directorios del disco público gestionados por la aplicación. */
    private const DIRECTORIES = ['paintings', 'publications', 'demo'];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Informe del estado actual, sin modificar nada.
     *
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $disk = Storage::disk('public');
        $files = $this->scanFiles();
        $references = $this->referencedPaths();

        $duplicateFileGroups = $files
            ->groupBy('hash')
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->map(function (Collection $group, string $hash) use ($references) {
                $paths = $group->pluck('path')->values();
                $keep = $paths->first(fn (string $path) => $references->contains($path))
                    ?? $paths->first();

                return [
                    'hash' => $hash,
                    'count' => $group->count(),
                    'keep' => $keep,
                    'paths' => $paths->all(),
                ];
            })
            ->values();

        $orphans = $files
            ->reject(fn (array $file) => $references->contains($file['path']))
            ->pluck('path')
            ->values();

        $missing = $references
            ->reject(fn (string $path) => $disk->exists($path))
            ->values();

        $publicationDuplicates = $this->duplicatePublicationGroups();
        $paintingDuplicates = $this->duplicatePaintingGroups();
        $conflicts = $this->conflictGroups();

        $duplicateRecords = count($publicationDuplicates) + count($paintingDuplicates);
        $duplicateRecordEntries = collect($publicationDuplicates)->sum(fn (array $group) => $group['count'] - 1)
            + collect($paintingDuplicates)->sum(fn (array $group) => $group['count'] - 1);

        return [
            'files' => [
                'total' => $files->count(),
                'size' => $files->sum('size'),
                'size_human' => $this->humanSize((int) $files->sum('size')),
                'referenced' => $references->filter(fn (string $path) => $disk->exists($path))->count(),
                'orphans' => $orphans->count(),
                'duplicates' => $duplicateFileGroups->sum(fn (array $group) => $group['count'] - 1),
                'missing' => $missing->count(),
            ],
            'records' => [
                'duplicate_groups' => $duplicateRecords,
                'duplicate_entries' => $duplicateRecordEntries,
                'conflicts' => count($conflicts),
            ],
            'duplicate_files' => $duplicateFileGroups->all(),
            'orphans' => $orphans->all(),
            'missing' => $missing->all(),
            'duplicate_records' => array_merge($publicationDuplicates, $paintingDuplicates),
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Limpia duplicados exactos, archivos duplicados y archivos huérfanos.
     *
     * Con $strict también fusiona los registros que comparten título aunque
     * su contenido sea distinto, conservando siempre el más antiguo.
     *
     * @return array<string, mixed>
     */
    public function clean(bool $strict = false): array
    {
        $result = [
            'merged_publications' => 0,
            'merged_paintings' => 0,
            'deleted_duplicate_files' => 0,
            'deleted_orphans' => 0,
            'synced_hashes' => 0,
            'missing' => [],
            'conflicts' => [],
        ];

        DB::transaction(function () use (&$result, $strict): void {
            $result['merged_publications'] = $this->mergeDuplicatePublications($strict);
            $result['merged_paintings'] = $this->mergeDuplicatePaintings($strict);
        });

        // Recalculamos después de fusionar: los registros borrados ya no cuentan.
        $files = $this->scanFiles();
        $references = $this->referencedPaths();
        $toDelete = [];

        $files->groupBy('hash')
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->each(function (Collection $group) use ($references, &$toDelete): void {
                $paths = $group->pluck('path');
                $keep = $paths->first(fn (string $path) => $references->contains($path))
                    ?? $paths->first();

                foreach ($paths as $path) {
                    // Nunca borramos un archivo que sigue referenciado.
                    if ($path !== $keep && ! $references->contains($path)) {
                        $toDelete[$path] = 'duplicate';
                    }
                }
            });

        foreach ($files as $file) {
            if (! $references->contains($file['path']) && ! isset($toDelete[$file['path']])) {
                $toDelete[$file['path']] = 'orphan';
            }
        }

        foreach ($toDelete as $path => $reason) {
            if (Storage::disk('public')->delete($path)) {
                $result[$reason === 'duplicate'
                    ? 'deleted_duplicate_files'
                    : 'deleted_orphans']++;
            }
        }

        $result['synced_hashes'] = $this->syncHashes();
        $result['missing'] = $this->referencedPaths()
            ->reject(fn (string $path) => Storage::disk('public')->exists($path))
            ->values()
            ->all();
        $result['conflicts'] = $this->conflictGroups();

        return $result;
    }

    /** @return Collection<int, array{path: string, size: int, hash: string}> */
    private function scanFiles(): Collection
    {
        $disk = Storage::disk('public');

        return collect($disk->allFiles())
            ->filter(fn (string $path) => $this->isManagedPath($path))
            ->map(function (string $path) use ($disk): array {
                return [
                    'path' => $path,
                    'size' => $disk->size($path),
                    'hash' => hash_file('sha256', $disk->path($path)),
                ];
            })
            ->values();
    }

    private function isManagedPath(string $path): bool
    {
        $path = str_replace('\\', '/', $path);

        foreach (self::DIRECTORIES as $directory) {
            if (str_starts_with($path, $directory.'/')) {
                return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true);
            }
        }

        return false;
    }

    /** @return Collection<int, string> */
    private function referencedPaths(): Collection
    {
        return Painting::query()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->merge(Publication::query()->whereNotNull('image_path')->pluck('image_path'))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Grupos de publicaciones duplicadas.
     *
     * Por defecto exige título, resumen y contenido idénticos. Con $byTitle
     * agrupa solo por título.
     *
     * @return list<array<string, mixed>>
     */
    private function duplicatePublicationGroups(bool $byTitle = false): array
    {
        if ($byTitle) {
            $groups = DB::table('publications')
                ->selectRaw('LOWER(TRIM(title)) as normalized_title')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('normalized_title')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('total')
                ->get();
        } else {
            $groups = DB::table('publications')
                ->selectRaw('LOWER(TRIM(title)) as normalized_title')
                ->selectRaw("LOWER(COALESCE(TRIM(summary), '')) as normalized_summary")
                ->selectRaw('LOWER(TRIM(content)) as normalized_content')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('normalized_title', 'normalized_summary', 'normalized_content')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('total')
                ->get();
        }

        return $groups->map(function (object $group) use ($byTitle): array {
            $query = Publication::query()
                ->whereRaw('LOWER(TRIM(title)) = ?', [$group->normalized_title]);

            if (! $byTitle) {
                $query->whereRaw("LOWER(COALESCE(TRIM(summary), '')) = ?", [$group->normalized_summary])
                    ->whereRaw('LOWER(TRIM(content)) = ?', [$group->normalized_content]);
            }

            $records = $query->orderBy('id')->get(['id', 'title', 'image_path']);

            return [
                'type' => 'publication',
                'type_label' => 'Publicación',
                'label' => $records->first()?->title ?? '(sin título)',
                'count' => $records->count(),
                'keep' => $records->first()?->id,
                'ids' => $records->pluck('id')->all(),
                'paths' => $records->pluck('image_path')->filter()->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Grupos de obras duplicadas.
     *
     * Por defecto exige título y descripción idénticos. Con $byTitle agrupa
     * solo por título.
     *
     * @return list<array<string, mixed>>
     */
    private function duplicatePaintingGroups(bool $byTitle = false): array
    {
        if ($byTitle) {
            $groups = DB::table('paintings')
                ->selectRaw('LOWER(TRIM(title)) as normalized_title')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('normalized_title')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('total')
                ->get();
        } else {
            $groups = DB::table('paintings')
                ->selectRaw('LOWER(TRIM(title)) as normalized_title')
                ->selectRaw("LOWER(COALESCE(TRIM(description), '')) as normalized_description")
                ->selectRaw('COUNT(*) as total')
                ->groupBy('normalized_title', 'normalized_description')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('total')
                ->get();
        }

        return $groups->map(function (object $group) use ($byTitle): array {
            $query = Painting::query()
                ->whereRaw('LOWER(TRIM(title)) = ?', [$group->normalized_title]);

            if (! $byTitle) {
                $query->whereRaw("LOWER(COALESCE(TRIM(description), '')) = ?", [$group->normalized_description]);
            }

            $records = $query->orderBy('id')->get(['id', 'title', 'image_path']);

            return [
                'type' => 'painting',
                'type_label' => 'Obra',
                'label' => $records->first()?->title ?? '(sin título)',
                'count' => $records->count(),
                'keep' => $records->first()?->id,
                'ids' => $records->pluck('id')->all(),
                'paths' => $records->pluck('image_path')->filter()->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Títulos repetidos cuyo contenido no es idéntico y requieren revisión.
     *
     * @return list<array<string, mixed>>
     */
    private function conflictGroups(): array
    {
        $publications = DB::table('publications')
            ->selectRaw('LOWER(TRIM(title)) as normalized_title')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(DISTINCT LOWER(COALESCE(TRIM(summary), ''))) as distinct_summaries")
            ->selectRaw('COUNT(DISTINCT LOWER(TRIM(content))) as distinct_contents')
            ->groupBy('normalized_title')
            ->havingRaw('COUNT(*) > 1')
            ->havingRaw(
                "COUNT(DISTINCT LOWER(COALESCE(TRIM(summary), ''))) > 1
                 OR COUNT(DISTINCT LOWER(TRIM(content))) > 1"
            )
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $group) => [
                'type' => 'publication',
                'type_label' => 'Publicación',
                'label' => Publication::query()
                    ->whereRaw('LOWER(TRIM(title)) = ?', [$group->normalized_title])
                    ->value('title') ?? '(sin título)',
                'count' => (int) $group->total,
                'detail' => "{$group->distinct_summaries} resúmenes y {$group->distinct_contents} contenidos distintos",
            ]);

        $paintings = DB::table('paintings')
            ->selectRaw('LOWER(TRIM(title)) as normalized_title')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(DISTINCT LOWER(COALESCE(TRIM(description), ''))) as distinct_descriptions")
            ->groupBy('normalized_title')
            ->havingRaw('COUNT(*) > 1')
            ->havingRaw("COUNT(DISTINCT LOWER(COALESCE(TRIM(description), ''))) > 1")
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $group) => [
                'type' => 'painting',
                'type_label' => 'Obra',
                'label' => Painting::query()
                    ->whereRaw('LOWER(TRIM(title)) = ?', [$group->normalized_title])
                    ->value('title') ?? '(sin título)',
                'count' => (int) $group->total,
                'detail' => "{$group->distinct_descriptions} descripciones distintas",
            ]);

        return $publications->concat($paintings)->values()->all();
    }

    private function mergeDuplicatePublications(bool $byTitle = false): int
    {
        $merged = 0;

        foreach ($this->duplicatePublicationGroups($byTitle) as $group) {
            $records = Publication::query()
                ->whereIn('id', $group['ids'])
                ->orderBy('id')
                ->get();

            $keep = $records->shift();

            foreach ($records as $duplicate) {
                // Conservamos los comentarios trasladándolos a la publicación que queda.
                PublicationComment::query()
                    ->where('publication_id', $duplicate->id)
                    ->update(['publication_id' => $keep->id]);

                $duplicate->delete();
                $merged++;
            }
        }

        return $merged;
    }

    private function mergeDuplicatePaintings(bool $byTitle = false): int
    {
        $merged = 0;

        foreach ($this->duplicatePaintingGroups($byTitle) as $group) {
            $records = Painting::query()
                ->whereIn('id', $group['ids'])
                ->orderBy('id')
                ->get();

            $keep = $records->shift();

            foreach ($records as $duplicate) {
                // Si hay solicitudes que chocarían con las de la obra conservada,
                // no borramos para no perder información.
                $buyerIds = Inquiry::query()
                    ->where('painting_id', $keep->id)
                    ->pluck('buyer_id');

                $hasInquiryConflict = $buyerIds->isNotEmpty()
                    && Inquiry::query()
                        ->where('painting_id', $duplicate->id)
                        ->whereIn('buyer_id', $buyerIds)
                        ->exists();

                if ($hasInquiryConflict) {
                    continue;
                }

                Comment::query()
                    ->where('painting_id', $duplicate->id)
                    ->update(['painting_id' => $keep->id]);

                Publication::query()
                    ->where('painting_id', $duplicate->id)
                    ->update(['painting_id' => $keep->id]);

                Inquiry::query()
                    ->where('painting_id', $duplicate->id)
                    ->update(['painting_id' => $keep->id]);

                DB::table('balance_transactions')
                    ->where('painting_id', $duplicate->id)
                    ->update(['painting_id' => $keep->id]);

                DB::table('account_transactions')
                    ->where('painting_id', $duplicate->id)
                    ->update(['painting_id' => $keep->id]);

                $this->moveFavorites($duplicate->id, $keep->id);
                $this->moveExhibitions($duplicate->id, $keep->id);

                $duplicate->delete();
                $merged++;
            }
        }

        return $merged;
    }

    private function moveFavorites(int $from, int $to): void
    {
        $favorites = DB::table('favorites')->where('painting_id', $from)->get();

        foreach ($favorites as $favorite) {
            $exists = DB::table('favorites')
                ->where('user_id', $favorite->user_id)
                ->where('painting_id', $to)
                ->exists();

            if (! $exists) {
                DB::table('favorites')->insert([
                    'user_id' => $favorite->user_id,
                    'painting_id' => $to,
                    'created_at' => $favorite->created_at,
                    'updated_at' => $favorite->updated_at,
                ]);
            }
        }

        DB::table('favorites')->where('painting_id', $from)->delete();
    }

    private function moveExhibitions(int $from, int $to): void
    {
        $links = DB::table('exhibition_painting')->where('painting_id', $from)->get();

        foreach ($links as $link) {
            $exists = DB::table('exhibition_painting')
                ->where('exhibition_id', $link->exhibition_id)
                ->where('painting_id', $to)
                ->exists();

            if (! $exists) {
                DB::table('exhibition_painting')->insert([
                    'exhibition_id' => $link->exhibition_id,
                    'painting_id' => $to,
                    'created_at' => $link->created_at,
                    'updated_at' => $link->updated_at,
                ]);
            }
        }

        DB::table('exhibition_painting')->where('painting_id', $from)->delete();
    }

    private function syncHashes(): int
    {
        $disk = Storage::disk('public');
        $synced = 0;

        foreach ([Painting::class, Publication::class] as $model) {
            $model::query()
                ->whereNotNull('image_path')
                ->orderBy('id')
                ->chunkById(100, function (Collection $records) use ($disk, &$synced): void {
                    foreach ($records as $record) {
                        if (! $disk->exists($record->image_path)) {
                            continue;
                        }

                        $path = $disk->path($record->image_path);
                        $hash = hash_file('sha256', $path);
                        $fingerprints = ImageService::perceptualHashes($path);

                        $changed = $record->image_hash !== $hash
                            || $record->image_phash !== $fingerprints['phash']
                            || $record->image_dhash !== $fingerprints['dhash'];

                        if (! $changed) {
                            continue;
                        }

                        $ignorePainting = $record instanceof Painting ? $record : null;
                        $ignorePublication = $record instanceof Publication ? $record : null;

                        if (ImageService::hashExists($hash, $ignorePainting, $ignorePublication)) {
                            continue;
                        }

                        $record->forceFill([
                            'image_hash' => $hash,
                            'image_phash' => $fingerprints['phash'],
                            'image_dhash' => $fingerprints['dhash'],
                        ])->saveQuietly();
                        $synced++;
                    }
                });
        }

        return $synced;
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1, ',', '.').' KB';
        }

        return number_format($bytes / (1024 * 1024), 1, ',', '.').' MB';
    }
}
