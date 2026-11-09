<?php

namespace Tests\Unit;

use App\Services\ImageService;
use PHPUnit\Framework\TestCase;

class ImageServiceTest extends TestCase
{
    /** @var list<string> */
    private array $paths = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_hamming_distance_counts_different_bits(): void
    {
        $this->assertSame(0, ImageService::hammingDistance('0000000000000000', '0000000000000000'));
        $this->assertSame(64, ImageService::hammingDistance('0000000000000000', 'ffffffffffffffff'));
        $this->assertSame(1, ImageService::hammingDistance('0000000000000000', '0000000000000001'));
        $this->assertNull(ImageService::hammingDistance(null, '0000000000000000'));
        $this->assertNull(ImageService::hammingDistance('abc', 'abcd'));
    }

    public function test_the_same_image_produces_stable_fingerprints(): void
    {
        $path = $this->createPatternImage(320, 240);

        $first = ImageService::perceptualHashes($path);
        $second = ImageService::perceptualHashes($path);

        $this->assertSame($first, $second);
        $this->assertSame(16, strlen((string) $first['phash']));
        $this->assertSame(16, strlen((string) $first['dhash']));
    }

    public function test_a_resized_and_recompressed_image_is_a_near_duplicate(): void
    {
        $original = $this->createPatternImage(320, 240);
        $copy = $this->resizeAndCompress($original, 160, 120);

        $originalHashes = ImageService::perceptualHashes($original);
        $copyHashes = ImageService::perceptualHashes($copy);

        $this->assertNotNull($originalHashes['phash']);
        $this->assertNotNull($originalHashes['dhash']);
        $this->assertNotNull($copyHashes['phash']);
        $this->assertNotNull($copyHashes['dhash']);

        $this->assertLessThanOrEqual(
            5,
            ImageService::hammingDistance($originalHashes['phash'], $copyHashes['phash']),
        );

        $this->assertLessThanOrEqual(
            5,
            ImageService::hammingDistance($originalHashes['dhash'], $copyHashes['dhash']),
        );
    }

    public function test_a_file_that_is_not_an_image_has_no_fingerprints(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'not_image_');
        file_put_contents($path, 'esto no es una imagen');
        $this->paths[] = $path;

        $hashes = ImageService::perceptualHashes($path);

        $this->assertNull($hashes['phash']);
        $this->assertNull($hashes['dhash']);
    }

    private function createPatternImage(int $width, int $height): string
    {
        $path = tempnam(sys_get_temp_dir(), 'image_');
        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorallocate(
                    $image,
                    (int) (255 * $x / $width),
                    (int) (255 * $y / $height),
                    (int) (255 * (($x + $y) % 100) / 100),
                );
                imagesetpixel($image, $x, $y, $color);
            }
        }

        imagefilledellipse(
            $image,
            (int) ($width / 2),
            (int) ($height / 2),
            (int) ($width / 2),
            (int) ($height / 3),
            imagecolorallocate($image, 240, 80, 40),
        );

        imagefilledrectangle(
            $image,
            10,
            10,
            (int) ($width / 3),
            (int) ($height / 3),
            imagecolorallocate($image, 30, 120, 220),
        );

        imagestring(
            $image,
            5,
            20,
            $height - 30,
            'GALERIA',
            imagecolorallocate($image, 255, 255, 255),
        );

        imagepng($image, $path);
        imagedestroy($image);

        $this->paths[] = $path;

        return $path;
    }

    private function resizeAndCompress(string $source, int $width, int $height): string
    {
        $image = imagecreatefromstring((string) file_get_contents($source));
        $resized = imagecreatetruecolor($width, $height);

        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $width,
            $height,
            imagesx($image),
            imagesy($image),
        );

        $path = tempnam(sys_get_temp_dir(), 'image_');
        imagejpeg($resized, $path, 70);

        imagedestroy($resized);
        imagedestroy($image);

        $this->paths[] = $path;

        return $path;
    }
}
