<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MediaCleanupSeeder extends Seeder
{
    public function run(): void
    {
        /* Disco PUBLIC ────────────────────────────────────────────── */
        $publicRoot = storage_path('app/public');

        // Borra todas las subcarpetas (ej. /8, /9, /10…)
        foreach (File::directories($publicRoot) as $dir) {
            File::deleteDirectory($dir);
            $this->command->info('Deleted ' . $dir);
        }

        /* Disco PRIVATE (si lo llegaras a usar) ───────────────────── */
        $privateRoot = storage_path('app/private');

        if (File::exists($privateRoot)) {
            foreach (File::directories($privateRoot) as $dir) {
                File::deleteDirectory($dir);
                $this->command->info('Deleted ' . $dir);
            }
        }
    }
}