<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Auth;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia caché de Spatie por si hay residuos
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Crea/recupera el usuario admin
        $admin = User::updateOrCreate(
            ['email' => 'gusgusnoriega@gmail.com'],
            ['name' => 'gusgus', 'password' => Hash::make('2535570Panda')]
        );

       $roleWeb = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleApi = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $admin->assignRole($roleWeb);
        $admin->assignRole($roleApi); // ahora sí no dará GuardDoesNotMatch

        // --- Si aún vieras GuardDoesNotMatch con el de 'api', usa la Opción B:
        /*
        $prev = Auth::getDefaultDriver();
        Auth::shouldUse('api');
        try {
            $admin->assignRole($roleApi);
        } finally {
            Auth::shouldUse($prev);
        }
        */

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}