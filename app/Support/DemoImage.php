<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Genera imágenes abstractas deterministas para los datos de demostración.
 *
 * El objetivo es que el proyecto tenga cuadros y publicaciones con imágenes
 * reales sin depender de servicios externos ni de archivos binarios en el repo.
 */
class DemoImage
{
    /**
     * Crea (o reutiliza) una imagen PNG/JPG y devuelve su ruta y hash.
     *
     * @param  list<string>  $palette
     * @return array{image_path: string, image_hash: string}
     */
    public static function make(string $key, string $directory, array $palette = []): array
    {
        $slug = Str::slug($key) ?: 'imagen';
        $filename = $slug.'-'.substr(sha1($key), 0, 10).'.jpg';
        $path = trim($directory, '/').'/'.$filename;
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->put($path, self::render($key, $palette));
        }

        return [
            'image_path' => $path,
            'image_hash' => hash('sha256', $disk->get($path)),
        ];
    }

    /**
     * @param  list<string>  $palette
     */
    private static function render(string $key, array $palette): string
    {
        $width = 900;
        $height = 650;
        $image = imagecreatetruecolor($width, $height);

        if (function_exists('imageantialias')) {
            imageantialias($image, true);
        }

        $palette = $palette ?: self::paletteFor($key);

        mt_srand(crc32($key));

        // Fondo con degradado vertical.
        [$topR, $topG, $topB] = self::hexToRgb($palette[0]);
        [$bottomR, $bottomG, $bottomB] = self::hexToRgb($palette[1]);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $color = imagecolorallocate(
                $image,
                (int) round($topR + ($bottomR - $topR) * $ratio),
                (int) round($topG + ($bottomG - $topG) * $ratio),
                (int) round($topB + ($bottomB - $topB) * $ratio),
            );
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Manchas de color translúcidas.
        for ($i = 0; $i < 24; $i++) {
            [$r, $g, $b] = self::hexToRgb($palette[mt_rand(2, count($palette) - 1)]);
            $color = imagecolorallocatealpha($image, $r, $g, $b, mt_rand(15, 85));
            $radius = mt_rand(50, 280);

            imagefilledellipse(
                $image,
                mt_rand(0, $width),
                mt_rand(0, $height),
                $radius * 2,
                (int) ($radius * mt_rand(5, 16) / 10),
                $color,
            );
        }

        // Trazos y planos geométricos para dar textura.
        for ($i = 0; $i < 18; $i++) {
            [$r, $g, $b] = self::hexToRgb($palette[mt_rand(1, count($palette) - 1)]);
            $color = imagecolorallocatealpha($image, $r, $g, $b, mt_rand(20, 95));

            if (mt_rand(0, 1) === 0) {
                $x1 = mt_rand(-100, $width);
                $y1 = mt_rand(-100, $height);
                $x2 = mt_rand(-100, $width);
                $y2 = mt_rand(-100, $height);

                imagesetthickness($image, mt_rand(5, 28));
                imageline($image, $x1, $y1, $x2, $y2, $color);
            } else {
                $size = mt_rand(80, 340);
                imagefilledrectangle(
                    $image,
                    mt_rand(-50, $width),
                    mt_rand(-50, $height),
                    $size,
                    $size,
                    $color,
                );
            }
        }

        imagesetthickness($image, 1);

        // Marco interior sutil.
        $frame = imagecolorallocatealpha($image, 255, 255, 255, 105);
        for ($i = 0; $i < 3; $i++) {
            imagerectangle($image, 24 + $i, 24 + $i, $width - 25 - $i, $height - 25 - $i, $frame);
        }

        ob_start();
        imagejpeg($image, null, 82);
        $contents = (string) ob_get_clean();

        imagedestroy($image);

        return $contents;
    }

    /**
     * Paletas estables por contenido para que la demo se vea variada.
     *
     * @return list<string>
     */
    private static function paletteFor(string $key): array
    {
        $palettes = [
            ['#0f172a', '#1e3a8a', '#818cf8', '#f0abfc', '#fef3c7'],
            ['#1c1917', '#7c2d12', '#ea580c', '#fbbf24', '#fef9c3'],
            ['#052e16', '#166534', '#22c55e', '#a3e635', '#ecfccb'],
            ['#3b0764', '#6d28d9', '#c084fc', '#f0abfc', '#fdf4ff'],
            ['#0c4a6e', '#0369a1', '#38bdf8', '#a5f3fc', '#ecfeff'],
            ['#450a0a', '#991b1b', '#ef4444', '#fca5a5', '#fff1f2'],
            ['#422006', '#854d0e', '#eab308', '#fde68a', '#fefce8'],
            ['#111827', '#374151', '#9ca3af', '#e5e7eb', '#f9fafb'],
        ];

        return $palettes[crc32($key) % count($palettes)];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
