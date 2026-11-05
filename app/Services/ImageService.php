<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Guarda la imagen subida en el disco público y devuelve la ruta y su hash.
     *
     * @return array{image_path: string, image_hash: string}
     */
    public static function store(UploadedFile $file, string $directory): array
    {
        $path = $file->store($directory, 'public');

        return [
            'image_path' => $path,
            'image_hash' => hash_file('sha256', Storage::disk('public')->path($path)),
        ];
    }

    /** Calcula el hash del archivo antes de guardarlo (para detectar duplicados). */
    public static function hash(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
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
}
