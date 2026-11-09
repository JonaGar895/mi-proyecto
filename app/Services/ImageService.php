<?php

namespace App\Services;

use App\Models\Painting;
use App\Models\Publication;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageService
{
    /**
     * Guarda la imagen subida en el disco público y devuelve su ruta y sus hashes.
     *
     * El nombre del archivo se deriva del hash de su contenido, de modo que
     * dos subidas idénticas siempre apuntan al mismo archivo físico.
     *
     * @return array{
     *     image_path: string,
     *     image_hash: string,
     *     image_phash: string|null,
     *     image_dhash: string|null
     * }
     */
    public static function store(UploadedFile $file, string $directory): array
    {
        $hash = self::hash($file);
        $fingerprints = self::perceptualHashes($file->getRealPath());
        $extension = self::extension($file);
        $path = trim($directory, '/').'/'.$hash.'.'.$extension;
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->put($path, (string) file_get_contents($file->getRealPath()));
        }

        return [
            'image_path' => $path,
            'image_hash' => $hash,
            'image_phash' => $fingerprints['phash'],
            'image_dhash' => $fingerprints['dhash'],
        ];
    }

    /** Calcula el hash del archivo antes de guardarlo (para detectar duplicados). */
    public static function hash(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

    /**
     * Calcula las huellas perceptuales pHash y dHash de una imagen.
     *
     * Ambas son cadenas hexadecimales de 64 bits. Se comparan con la distancia
     * de Hamming para detectar imágenes redimensionadas o recomprimidas que no
     * coinciden byte a byte con el SHA-256.
     *
     * @return array{phash: string|null, dhash: string|null}
     */
    public static function perceptualHashes(string $path): array
    {
        $empty = ['phash' => null, 'dhash' => null];

        if (! is_file($path) || ! is_readable($path)) {
            return $empty;
        }

        $contents = @file_get_contents($path);

        if ($contents === false || $contents === '') {
            return $empty;
        }

        $image = self::createImageFromString($contents);

        if (! $image instanceof GdImage) {
            return $empty;
        }

        try {
            return [
                'phash' => self::phash($image),
                'dhash' => self::dhash($image),
            ];
        } catch (Throwable) {
            return $empty;
        } finally {
            imagedestroy($image);
        }
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

    /**
     * Indica si ya existe una imagen perceptual muy parecida en obras o publicaciones.
     *
     * Se compara la distancia de Hamming de pHash y dHash contra las imágenes
     * ya registradas. Los umbrales se configuran en config/images.php.
     */
    public static function nearDuplicateExists(
        ?string $phash,
        ?string $dhash,
        ?Painting $ignorePainting = null,
        ?Publication $ignorePublication = null,
    ): bool {
        if (self::isBlankHash($phash) && self::isBlankHash($dhash)) {
            return false;
        }

        // Las imágenes uniformes comparten la misma huella perceptual; se
        // comparan solo por SHA-256 para no bloquear colores planos distintos.
        if (self::isDegenerateFingerprint($phash, $dhash)) {
            return false;
        }

        $phashLimit = max(0, (int) config('images.phash_max_distance', 5));
        $dhashLimit = max(0, (int) config('images.dhash_max_distance', 5));

        $queries = [
            Painting::query()
                ->when($ignorePainting, fn ($query) => $query->whereKeyNot($ignorePainting->id)),
            Publication::query()
                ->when($ignorePublication, fn ($query) => $query->whereKeyNot($ignorePublication->id)),
        ];

        foreach ($queries as $query) {
            $candidates = $query
                ->where(function ($query) {
                    $query->whereNotNull('image_phash')
                        ->orWhereNotNull('image_dhash');
                })
                ->cursor(['id', 'image_phash', 'image_dhash']);

            foreach ($candidates as $candidate) {
                if (self::matchesFingerprint(
                    $phash,
                    $dhash,
                    $candidate->image_phash,
                    $candidate->image_dhash,
                    $phashLimit,
                    $dhashLimit,
                )) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Calcula la distancia de Hamming entre dos hashes hexadecimales. */
    public static function hammingDistance(?string $left, ?string $right): ?int
    {
        if (
            $left === null || $right === null
            || $left === '' || $right === ''
            || strlen($left) !== strlen($right)
        ) {
            return null;
        }

        $distance = 0;
        $length = strlen($left);

        for ($i = 0; $i < $length; $i++) {
            $nibble = hexdec($left[$i]) ^ hexdec($right[$i]);
            $distance += substr_count(decbin($nibble), '1');
        }

        return $distance;
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

    private static function isBlankHash(?string $hash): bool
    {
        return $hash === null || $hash === '';
    }

    private static function isDegenerateFingerprint(?string $phash, ?string $dhash): bool
    {
        // Un color plano produce un dHash sin cambios y un pHash con, como
        // mucho, el coeficiente DC. Esas imágenes no se comparan por similitud.
        return self::isAllZero($dhash) && self::bitCount($phash) <= 1;
    }

    private static function isAllZero(?string $hash): bool
    {
        return $hash === null || $hash === '' || trim($hash, '0') === '';
    }

    private static function bitCount(?string $hash): int
    {
        if ($hash === null || $hash === '') {
            return 0;
        }

        $count = 0;

        for ($i = 0; $i < strlen($hash); $i++) {
            $count += substr_count(decbin(hexdec($hash[$i])), '1');
        }

        return $count;
    }

    /**
     * Crea una imagen GD sin emitir warnings cuando el archivo no es válido.
     */
    private static function createImageFromString(string $contents): ?GdImage
    {
        set_error_handler(static fn (): bool => true);

        try {
            $image = imagecreatefromstring($contents);
        } finally {
            restore_error_handler();
        }

        return $image instanceof GdImage ? $image : null;
    }

    private static function matchesFingerprint(
        ?string $phash,
        ?string $dhash,
        ?string $candidatePhash,
        ?string $candidateDhash,
        int $phashLimit,
        int $dhashLimit,
    ): bool {
        $phashDistance = self::hammingDistance($phash, $candidatePhash);

        if ($phashDistance !== null && $phashDistance <= $phashLimit) {
            return true;
        }

        $dhashDistance = self::hammingDistance($dhash, $candidateDhash);

        return $dhashDistance !== null && $dhashDistance <= $dhashLimit;
    }

    /**
     * pHash: DCT de una versión reducida en escala de grises.
     *
     * Se conservan las 8x8 frecuencias más bajas y se genera un bit por
     * coeficiente comparándolo con la mediana de los coeficientes AC.
     */
    private static function phash(GdImage $source): string
    {
        $size = 32;
        $grid = self::grayscaleGrid($source, $size, $size);
        $coefficients = self::dct($grid, $size);
        $values = [];

        for ($v = 0; $v < 8; $v++) {
            for ($u = 0; $u < 8; $u++) {
                // Redondeamos para eliminar el ruido de coma flotante de la DCT.
                $values[] = round($coefficients[$v][$u], 9);
            }
        }

        // La mediana se calcula sin el coeficiente DC para que no domine el resultado.
        $ac = array_slice($values, 1);
        sort($ac);
        $median = $ac[intdiv(count($ac), 2)];

        $bits = '';

        foreach ($values as $value) {
            $bits .= $value > $median ? '1' : '0';
        }

        return self::bitsToHex($bits);
    }

    /**
     * dHash: compara píxeles adyacentes de una versión reducida a 9x8.
     */
    private static function dhash(GdImage $source): string
    {
        $grid = self::grayscaleGrid($source, 9, 8);
        $bits = '';

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $bits .= $grid[$y][$x] > $grid[$y][$x + 1] ? '1' : '0';
            }
        }

        return self::bitsToHex($bits);
    }

    /**
     * Reduce la imagen y la convierte a una matriz de valores de gris.
     *
     * @return list<list<int>>
     */
    private static function grayscaleGrid(GdImage $source, int $width, int $height): array
    {
        $resized = imagecreatetruecolor($width, $height);

        if (! $resized instanceof GdImage) {
            throw new \RuntimeException('No se pudo crear la imagen reducida.');
        }

        try {
            // Fondo blanco para que las zonas transparentes no se vuelvan negras.
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefilledrectangle($resized, 0, 0, $width - 1, $height - 1, $white);

            imagecopyresampled(
                $resized,
                $source,
                0,
                0,
                0,
                0,
                $width,
                $height,
                imagesx($source),
                imagesy($source),
            );

            $grid = [];

            for ($y = 0; $y < $height; $y++) {
                $row = [];

                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($resized, $x, $y);
                    $red = ($rgb >> 16) & 0xFF;
                    $green = ($rgb >> 8) & 0xFF;
                    $blue = $rgb & 0xFF;
                    $row[] = (int) round(0.299 * $red + 0.587 * $green + 0.114 * $blue);
                }

                $grid[] = $row;
            }

            return $grid;
        } finally {
            imagedestroy($resized);
        }
    }

    /**
     * DCT 2D separable. Solo se calculan las 8x8 frecuencias inferiores.
     *
     * @param  list<list<int>>  $grid
     * @return array<int, array<int, float>>
     */
    private static function dct(array $grid, int $size): array
    {
        $cosine = [];

        for ($u = 0; $u < 8; $u++) {
            for ($x = 0; $x < $size; $x++) {
                $cosine[$u][$x] = cos((2 * $x + 1) * $u * M_PI / (2 * $size));
            }
        }

        $horizontal = array_fill(0, $size, array_fill(0, 8, 0.0));

        for ($y = 0; $y < $size; $y++) {
            for ($u = 0; $u < 8; $u++) {
                $sum = 0.0;

                for ($x = 0; $x < $size; $x++) {
                    $sum += $grid[$y][$x] * $cosine[$u][$x];
                }

                $horizontal[$y][$u] = $sum * ($u === 0 ? sqrt(1 / $size) : sqrt(2 / $size));
            }
        }

        $result = array_fill(0, 8, array_fill(0, 8, 0.0));

        for ($u = 0; $u < 8; $u++) {
            for ($v = 0; $v < 8; $v++) {
                $sum = 0.0;

                for ($y = 0; $y < $size; $y++) {
                    $sum += $horizontal[$y][$u] * $cosine[$v][$y];
                }

                $result[$v][$u] = $sum * ($v === 0 ? sqrt(1 / $size) : sqrt(2 / $size));
            }
        }

        return $result;
    }

    private static function bitsToHex(string $bits): string
    {
        $hex = '';

        foreach (str_split($bits, 4) as $nibble) {
            $hex .= dechex((int) bindec($nibble));
        }

        return $hex;
    }
}
