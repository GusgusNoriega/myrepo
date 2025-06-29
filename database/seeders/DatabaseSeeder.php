<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       // 1) Limpiar archivos
       $this->call(MediaCleanupSeeder::class);
       // 2) actualiza token de passport
       $this->call(PassportSeeder::class);
       // 3) crea super usuario
       $this->call(AdminUserSeeder::class);
      
    }
}
