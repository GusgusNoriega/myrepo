<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /** Listar SOLO roles del guard web (para evitar duplicados en UI) */
    public function index(Request $request)
    {
        $q = Role::query()->where('guard_name', 'web')->with('permissions');

        if ($s = $request->query('search')) {
            $q->where('name', 'like', "%{$s}%");
        }

        $p = $q->orderBy('name')->paginate($request->integer('per_page', 25))->appends($request->all());

        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'per_page'     => $p->perPage(),
                'total'        => $p->total(),
            ],
        ]);
    }

    /** Crear role (web) y su gemelo (api). Opcionalmente asigna permisos (a ambos). */
    public function store(Request $request)
    {
        $rolesTable = config('permission.table_names.roles', 'roles');

        $data = $request->validate([
            'name'              => ['required','string','max:255', Rule::unique($rolesTable, 'name')->where('guard_name', 'web')],
            'permissions'       => ['array'],
            'permissions.*'     => ['string'],
        ]);

        return DB::transaction(function () use ($data) {
            // Crear/asegurar en ambos guards
            $roleWeb = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
            $roleApi = Role::firstOrCreate(['name' => $data['name'], 'guard_name' => 'api']);

            // Sincronizar permisos si vinieron
            if (!empty($data['permissions'])) {
                $webPerms = Permission::where('guard_name','web')->whereIn('name', $data['permissions'])->get();

                // Asegurar que existan también en api con los mismos nombres
                foreach ($data['permissions'] as $permName) {
                    Permission::firstOrCreate(['name'=>$permName, 'guard_name'=>'api']);
                }
                $apiPerms = Permission::where('guard_name','api')->whereIn('name', $data['permissions'])->get();

                $roleWeb->syncPermissions($webPerms);
                $roleApi->syncPermissions($apiPerms);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return response()->json($roleWeb->load('permissions'), 201);
        });
    }

    /** Mostrar SOLO roles del guard web */
    public function show(Role $role)
    {
        abort_if($role->guard_name !== 'web', 404);
        return response()->json($role->load('permissions'));
    }

    /** Renombrar role (web) y su gemelo (api). También puede re-sincronizar permisos si se envían. */
    public function update(Request $request, Role $role)
    {
        abort_if($role->guard_name !== 'web', 404);

        $rolesTable = config('permission.table_names.roles', 'roles');

        $data = $request->validate([
            'name'          => ['required','string','max:255', Rule::unique($rolesTable, 'name')->ignore($role->id)->where('guard_name','web')],
            'permissions'   => ['array'],
            'permissions.*' => ['string'],
        ]);

        return DB::transaction(function () use ($role, $data) {
            // Renombrar WEB
            $oldName = $role->name;
            $role->name = $data['name'];
            $role->save();

            // Renombrar/generar API gemelo
            $apiRole = Role::firstOrCreate(['name' => $oldName, 'guard_name' => 'api']);
            // Validar que no haya conflicto en api con el nuevo nombre
            $conflict = Role::where('guard_name','api')->where('name',$data['name'])->where('id','<>',$apiRole->id)->exists();
            if ($conflict) {
                abort(422, "Ya existe un rol API con el nombre '{$data['name']}'");
            }
            $apiRole->name = $data['name'];
            $apiRole->save();

            // Si se mandó lista de permisos, sincronizar ambos
            if (array_key_exists('permissions', $data)) {
                $names = $data['permissions'] ?? [];

                // Asegurar existencia en ambos guards
                foreach ($names as $permName) {
                    Permission::firstOrCreate(['name'=>$permName, 'guard_name'=>'web']);
                    Permission::firstOrCreate(['name'=>$permName, 'guard_name'=>'api']);
                }

                $webPerms = Permission::where('guard_name','web')->whereIn('name', $names)->get();
                $apiPerms = Permission::where('guard_name','api')->whereIn('name', $names)->get();

                $role->syncPermissions($webPerms);
                $apiRole->syncPermissions($apiPerms);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return response()->json($role->fresh()->load('permissions'));
        });
    }

    /** Borrar role (web) y su gemelo (api) */
    public function destroy(Role $role)
    {
        abort_if($role->guard_name !== 'web', 404);

        return DB::transaction(function () use ($role) {
            $name = $role->name;
            $role->delete();

            $api = Role::where('guard_name','api')->where('name',$name)->first();
            if ($api) { $api->delete(); }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return response()->json(null, 204);
        });
    }

    /** Sincronizar permisos a un rol (web) y espejo (api) */
    public function syncPermissions(Request $request, Role $role)
    {
        abort_if($role->guard_name !== 'web', 404);

        $data = $request->validate([
            'items'   => ['required','array'],
            'items.*' => ['string'],
        ]);

        return DB::transaction(function () use ($role, $data) {
            // Asegurar permisos en ambos guards
            foreach ($data['items'] as $permName) {
                Permission::firstOrCreate(['name'=>$permName, 'guard_name'=>'web']);
                Permission::firstOrCreate(['name'=>$permName, 'guard_name'=>'api']);
            }

            $webPerms = Permission::where('guard_name','web')->whereIn('name', $data['items'])->get();
            $role->syncPermissions($webPerms);

            $apiRole = Role::firstOrCreate(['name'=>$role->name, 'guard_name'=>'api']);
            $apiPerms = Permission::where('guard_name','api')->whereIn('name', $data['items'])->get();
            $apiRole->syncPermissions($apiPerms);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return response()->json($role->fresh()->load('permissions'));
        });
    }
}