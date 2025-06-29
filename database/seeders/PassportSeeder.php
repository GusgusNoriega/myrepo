<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PassportSeeder extends Seeder
{
    public function run(): void
    {
        // Si no existe el personal access client, créalo
        if (DB::table('oauth_personal_access_clients')->count() === 0) {
            Artisan::call('passport:install', [
                '--force'          => true,
                '--no-interaction' => true,
            ]);

            $this->command->info('Passport keys & clients generated (no-interaction).');
        }
    }
}
