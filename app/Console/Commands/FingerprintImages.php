<?php

namespace App\Console\Commands;

use App\Models\Painting;
use App\Models\Publication;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'images:fingerprint')]
class FingerprintImages extends Command
{
    protected $signature = 'images:fingerprint
                            {--force : Recalcula también las huellas que ya existen}';

    protected $description = 'Calcula los hashes perceptuales (pHash y dHash) de las imágenes existentes';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $updated = 0;
        $missing = 0;

        foreach ([Painting::class, Publication::class] as $model) {
            $model::query()
                ->whereNotNull('image_path')
                ->orderBy('id')
                ->chunkById(100, function ($records) use ($disk, &$updated, &$missing): void {
                    foreach ($records as $record) {
                        if (! $this->option('force') && $record->image_phash && $record->image_dhash) {
                            continue;
                        }

                        if (! $disk->exists($record->image_path)) {
                            $missing++;

                            continue;
                        }

                        $fingerprints = ImageService::perceptualHashes($disk->path($record->image_path));

                        $record->forceFill([
                            'image_phash' => $fingerprints['phash'],
                            'image_dhash' => $fingerprints['dhash'],
                        ])->saveQuietly();

                        $updated++;
                    }
                });
        }

        $this->info("Huellas perceptuales actualizadas: {$updated}");

        if ($missing > 0) {
            $this->warn("Referencias sin archivo: {$missing}");
        }

        return self::SUCCESS;
    }
}
