<?php

namespace App\Services;

use App\Models\Painting;
use App\Models\Publication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Guarda la imagen subida en el disco público y devuelve la ruta y su hash.
     *
     * El nombre del archivo se deriva del hash de su contenido, de modo que
     * dos subidas idénticas siempre apuntan al mismo archivo físico.
     *
     * @return array{image_path: string, image_hash: string}
     */
    public static function store(UploadedFile $file, string $directory): array
    {
        $hash = self::hash($file);
        $extension = self::extension($file);
        $path = trim($directory, '/').'/'.$hash.'.'.$extension;
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->put($path, (string) file_get_contents($file->getRealPath()));
        }

        return [
            'image_path' => $path,
            'image_hash' => $hash,
        ];
    }

    /** Calcula el hash del archivo antes de guardarlo (para detectar duplicados). */
    public static function hash(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

    /** Indica si ya existe una imagen con ese hash en obras o publicaciones. */
    public static function hashExists(
        string $hash,
        ?Painting $ignorePainting = null,
        ?Publication $ignorePublication = null,
    ): bool {
        $inPaintings = Painting::query()
            ->where('image_hash', $hash)
            ->when($ignorePainting, fn ($query) => $query->whereKeyNot($ignorePainting->id))
            ->exists();

        if ($inPaintings) {
            return true;
        }

        return Publication::query()
            ->where('image_hash', $hash)
            ->when($ignorePublication, fn ($query) => $query->whereKeyNot($ignorePublication->id))
            ->exists();
    }

    /** Elimina una imagen del disco público si existe. */
    public static function delete(?string $path): void
    {
        if (! empty($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Devuelve la URL pública de una imagen guardada o el valor alternativo. */
    public static function url(?string $path, ?string $fallback = null): ?string
    {
        if (! $path) {
            return $fallback;
        }

        // asset() respeta el host, el puerto y el subdirectorio actuales.
        return asset('storage/'.ltrim($path, '/'));
    }

    /** Extensión normalizada a partir del tipo MIME real del archivo. */
    private static function extension(UploadedFile $file): string
    {
        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension()));

        return match ($extension) {
            'jpeg' => 'jpg',
            'jpg', 'png', 'webp', 'gif' => $extension,
            default => 'jpg',
        };
    }
}
