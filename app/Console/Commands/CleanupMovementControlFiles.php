<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupMovementControlFiles extends Command
{
    protected $signature = 'movement-control:cleanup';

    protected $description = 'Elimina extractos bancarios temporales antiguos del control de movimientos';

    public function handle(): int
    {
        $disk = Storage::disk('local');

        $directory = 'movement-control';

        if (!$disk->exists($directory)) {
            return self::SUCCESS;
        }


        $files = $disk->allFiles($directory);

        $limit = now()->subHours(24)->timestamp;

        $deleted = 0;


        foreach ($files as $file) {

            try {

                $lastModified = $disk->lastModified($file);

                if ($lastModified < $limit) {

                    $disk->delete($file);

                    $deleted++;
                }

            } catch (\Throwable $e) {

                report($e);

            }

        }


        $this->info(
            "Limpieza finalizada. {$deleted} archivo(s) eliminado(s)."
        );


        return self::SUCCESS;
    }
}