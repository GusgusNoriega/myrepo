<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia la caché de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guards = ['web', 'api']; // <- ambos guards

        // Permisos base
        $permissions = [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'posts.view', 'posts.create', 'posts.update', 'posts.publish', 'posts.delete',
            'orders.view', 'orders.create', 'orders.update', 'orders.delete',
            'admin.access',
        ];

        foreach ($guards as $guard) {
            // Crea permisos por guard
            foreach ($permissions as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }

            // Crea roles por guard
            $admin  = Role::firstOrCreate(['name' => 'admin',  'guard_name' => $guard]);
            $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => $guard]);
            $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => $guard]);

            // Admin = todos los permisos de ese guard
            $admin->syncPermissions(Permission::where('guard_name', $guard)->get());

            // Editor / Viewer con subsets
            $editor->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', ['posts.view','posts.create','posts.update','posts.publish'])
                    ->get()
            );

            $viewer->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', ['posts.view'])
                    ->get()
            );
        }

        // Asigna admin al usuario conocido en AMBOS guards
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        if ($user = User::where('email', $adminEmail)->first()) {
            // Usa Role::findByName con guard explícito para evitar ambigüedad
            $user->assignRole(Role::findByName('admin', 'web'));
            $user->assignRole(Role::findByName('admin', 'api'));
        }

        // Refresca la caché al final
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}