<?php

namespace App\Console\Commands;

use App\Services\FileManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'gallery:clean')]
class CleanGallery extends Command
{
    protected $signature = 'gallery:clean
                            {--dry-run : Muestra el informe sin modificar archivos ni registros}
                            {--strict : Fusiona también los títulos repetidos aunque el contenido sea distinto}';

    protected $description = 'Elimina archivos huérfanos y duplicados, y fusiona registros duplicados exactos';

    public function handle(FileManager $manager): int
    {
        if ($this->option('dry-run')) {
            $report = $manager->overview();

            $this->info('Informe de archivos y registros');
            $this->table(['Métrica', 'Valor'], [
                ['Archivos totales', $report['files']['total']],
                ['Tamaño total', $report['files']['size_human']],
                ['Archivos referenciados', $report['files']['referenced']],
                ['Archivos huérfanos', $report['files']['orphans']],
                ['Archivos duplicados', $report['files']['duplicates']],
                ['Referencias sin archivo', $report['files']['missing']],
                ['Grupos de registros duplicados', $report['records']['duplicate_groups']],
                ['Registros duplicados sobrantes', $report['records']['duplicate_entries']],
                ['Títulos en conflicto', $report['records']['conflicts']],
            ]);

            if ($report['duplicate_records'] !== []) {
                $this->newLine();
                $this->warn('Registros duplicados exactos:');
                $this->table(
                    ['Tipo', 'Título', 'Cantidad', 'Se conserva'],
                    collect($report['duplicate_records'])
                        ->map(fn (array $group) => [
                            $group['type_label'],
                            $group['label'],
                            $group['count'],
                            $group['keep'],
                        ])
                        ->all(),
                );
            }

            return self::SUCCESS;
        }

        $result = $manager->clean((bool) $this->option('strict'));

        $this->info('Limpieza completada');
        $this->table(['Acción', 'Cantidad'], [
            ['Publicaciones duplicadas fusionadas', $result['merged_publications']],
            ['Obras duplicadas fusionadas', $result['merged_paintings']],
            ['Archivos duplicados eliminados', $result['deleted_duplicate_files']],
            ['Archivos huérfanos eliminados', $result['deleted_orphans']],
            ['Hashes corregidos', $result['synced_hashes']],
            ['Referencias sin archivo', count($result['missing'])],
            ['Títulos en conflicto', count($result['conflicts'])],
        ]);

        if ($result['missing'] !== []) {
            $this->newLine();
            $this->warn('Estos registros apuntan a archivos que no existen:');
            $this->line(implode(PHP_EOL, $result['missing']));
        }

        return self::SUCCESS;
    }
}
