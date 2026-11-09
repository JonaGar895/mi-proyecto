<?php

namespace Tests\Concerns;

use GdImage;
use Illuminate\Http\UploadedFile;

trait CreatesUploadedImages
{
    protected function patternedImage(string $name, int $width = 320, int $height = 240): UploadedFile
    {
        return $this->uploadFromImage($this->makePattern($width, $height), $name);
    }

    protected function resizedPatternedImage(string $name, int $width = 160, int $height = 120): UploadedFile
    {
        $source = $this->makePattern(320, 240);
        $resized = imagecreatetruecolor($width, $height);

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

        imagedestroy($source);

        return $this->uploadFromImage($resized, $name);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    protected function solidColorImage(string $name, int $width, int $height, array $rgb): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);

        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $color);

        return $this->uploadFromImage($image, $name);
    }

    private function makePattern(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorallocate(
                    $image,
                    (int) (127 + 127 * sin(2 * M_PI * $x / $width)),
                    (int) (127 + 127 * sin(2 * M_PI * $y / $height)),
                    (int) (127 + 127 * sin(2 * M_PI * ($x + $y) / ($width + $height))),
                );
                imagesetpixel($image, $x, $y, $color);
            }
        }

        imagefilledellipse(
            $image,
            intdiv($width, 2),
            intdiv($height, 2),
            intdiv($width, 2),
            intdiv($height, 3),
            imagecolorallocate($image, 240, 80, 40),
        );

        imagefilledrectangle(
            $image,
            10,
            10,
            intdiv($width, 3),
            intdiv($height, 3),
            imagecolorallocate($image, 30, 120, 220),
        );

        return $image;
    }

    private function uploadFromImage(GdImage $image, string $name): UploadedFile
    {
        ob_start();
        imagepng($image);
        $contents = (string) ob_get_clean();
        imagedestroy($image);

        return UploadedFile::fake()->createWithContent($name, $contents);
    }
}
