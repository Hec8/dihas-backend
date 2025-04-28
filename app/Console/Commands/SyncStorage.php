<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncStorage extends Command
{
    protected $signature = 'storage:sync';
    protected $description = 'Synchronize storage files with the frontend public directory';

    public function handle()
    {
        $source = storage_path('app/public');
        $destination = base_path('../dihas/public/storage');

        // Créer le dossier de destination s'il n'existe pas
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        // Copier les fichiers
        File::copyDirectory($source, $destination);

        $this->info('Storage files synchronized successfully!');
    }
}
