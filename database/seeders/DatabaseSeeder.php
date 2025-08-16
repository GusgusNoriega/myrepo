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
       // 3) crea roles y permisos
       $this->call(PermissionRoleSeeder::class);
       // 4) crea super usuario
       $this->call(AdminUserSeeder::class);
       // 5) crea PLANES Y MEMBRESIAS
       $this->call(PlanSeeder::class);
       // 6) crea negocios de prueba
       $this->call(DemoBusinessSeeder::class);
      
    }
}
