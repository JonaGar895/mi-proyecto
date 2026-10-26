<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:backup')]
class BackupDatabase extends Command
{
    protected $signature = 'db:backup
                            {--keep=10 : Número de copias recientes a conservar (0 = conservar todas)}';

    protected $description = 'Crea una copia de seguridad de la base de datos SQLite en database/backups';

    public function handle(): int
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver !== 'sqlite') {
            $this->error("La conexión [{$connection}] no es SQLite (driver: {$driver}).");
            $this->line('Este comando solo copia archivos SQLite. Para MySQL/Postgres usa mysqldump/pg_dump.');

            return self::FAILURE;
        }

        $database = config("database.connections.{$connection}.database");

        if (! $database || $database === ':memory:' || ! File::exists($database)) {
            $this->error("No se encontró el archivo de la base de datos: {$database}");

            return self::FAILURE;
        }

        $directory = database_path('backups');
        File::ensureDirectoryExists($directory);

        $destination = $directory.DIRECTORY_SEPARATOR.'database-'.now()->format('Y-m-d_H-i-s').'.sqlite';

        $this->copyDatabase($database, $destination);

        $size = number_format(File::size($destination) / 1024, 1);

        $this->info("Copia creada: {$destination} ({$size} KB)");

        $this->prune((int) $this->option('keep'));

        return self::SUCCESS;
    }

    /**
     * VACUUM INTO genera una copia consistente aunque la base esté en modo WAL.
     * Si no está disponible, se copia el archivo directamente.
     */
    private function copyDatabase(string $database, string $destination): void
    {
        try {
            $quoted = "'".str_replace("'", "''", $destination)."'";
            DB::statement("VACUUM INTO {$quoted}");
        } catch (\Throwable) {
            File::copy($database, $destination);
        }
    }

    private function prune(int $keep): void
    {
        if ($keep <= 0) {
            return;
        }

        $backups = collect(File::files(database_path('backups')))
            ->filter(fn ($file) => $file->getExtension() === 'sqlite')
            ->sortByDesc(fn ($file) => $file->getFilename())
            ->values();

        $backups->slice($keep)->each(function ($file) {
            File::delete($file->getPathname());
            $this->line('Copia antigua eliminada: '.$file->getFilename());
        });
    }
}
