<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Si ya usas Spatie Laravel Permission:
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        /* 1) Crea (o recupera) el rol “admin”  */
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        /* 2) Crea (o actualiza) al usuario admin  */
        $admin = User::updateOrCreate(
            ['email' => 'gusgusnoriega@gmail.com'],              // clave de búsqueda
            [
                'name'     => 'gusgus',
                'password' => Hash::make('2535570Panda'),   // cámbiala luego
            ]
        );

        /* 3) Asigna el rol si aún no lo tiene */
        if (! $admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }
    }
}
