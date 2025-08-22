<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AclUserController extends Controller
{
    /**
     * PUT /api/admin/users/{user}/roles
     * Body: { "items": ["admin","editor"] }
     * Sincroniza roles en ambos guards (web y api) sin tocar uno cuando actualizas el otro.
     */
    public function syncRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'items'   => ['required','array'],
            'items.*' => ['string'],
        ]);
        $names = array_values(array_unique($data['items']));

        return DB::transaction(function () use ($user, $names) {
            // Asegura existencia de roles espejo en ambos guards
            foreach ($names as $n) {
                Role::firstOrCreate(['name' => $n, 'guard_name' => 'web']);
                Role::firstOrCreate(['name' => $n, 'guard_name' => 'api']);
            }

            // Sincroniza SOLO el guard web (no toca api)
            $this->syncRolesForGuard($user, $names, 'web');

            // Sincroniza SOLO el guard api (no toca web)
            $this->syncRolesForGuard($user, $names, 'api');

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // La UI solo necesita el estado WEB para marcar checkboxes
            return response()->json($this->payloadForUi($user));
        });
    }

    /**
     * PUT /api/admin/users/{user}/permissions
     * Body: { "items": ["users.manage","posts.publish"] }
     * Sincroniza permisos directos en ambos guards (web y api) sin interferir el otro.
     */
    public function syncPermissions(Request $request, User $user)
    {
        $data = $request->validate([
            'items'   => ['required','array'],
            'items.*' => ['string'],
        ]);
        $names = array_values(array_unique($data['items']));

        return DB::transaction(function () use ($user, $names) {
            // Asegura existencia de permisos espejo en ambos guards
            foreach ($names as $n) {
                Permission::firstOrCreate(['name' => $n, 'guard_name' => 'web']);
                Permission::firstOrCreate(['name' => $n, 'guard_name' => 'api']);
            }

            // Sincroniza SOLO el guard web
            $this->syncPermsForGuard($user, $names, 'web');

            // Sincroniza SOLO el guard api
            $this->syncPermsForGuard($user, $names, 'api');

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // Devolvemos estado WEB
            return response()->json($this->payloadForUi($user));
        });
    }

    /** ---------- Helpers: sincronizan por guard sin tocar el otro ---------- */

    private function syncRolesForGuard(User $user, array $names, string $guard): void
    {
        // IDs destino para ese guard
        $targetIds = Role::where('guard_name', $guard)
            ->whereIn('name', $names)
            ->pluck('id')
            ->all();

        // IDs actuales del usuario en ese guard
        $currentIds = $user->roles()
            ->where('guard_name', $guard)
            ->pluck('id')
            ->all();

        $toAttach = array_diff($targetIds, $currentIds);
        $toDetach = array_diff($currentIds, $targetIds);

        if ($toDetach) {
            // Detacha solo los roles de ese guard
            $user->roles()->detach($toDetach);
        }
        if ($toAttach) {
            // Adjunta solo los roles de ese guard
            $user->roles()->attach($toAttach);
        }
    }

    private function syncPermsForGuard(User $user, array $names, string $guard): void
    {
        $targetIds = Permission::where('guard_name', $guard)
            ->whereIn('name', $names)
            ->pluck('id')
            ->all();

        $currentIds = $user->permissions()
            ->where('guard_name', $guard)
            ->pluck('id')
            ->all();

        $toAttach = array_diff($targetIds, $currentIds);
        $toDetach = array_diff($currentIds, $targetIds);

        if ($toDetach) {
            $user->permissions()->detach($toDetach);
        }
        if ($toAttach) {
            $user->permissions()->attach($toAttach);
        }
    }

    /** Solo lo que tu UI necesita (guard web) */
    private function payloadForUi(User $user): array
    {
        $user->refresh();

        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'roles'       => $user->roles()->where('guard_name', 'web')->pluck('name')->values(),
            'permissions' => $user->permissions()->where('guard_name', 'web')->pluck('name')->values(),
        ];
    }
}
