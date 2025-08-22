<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    /** Listar SOLO permisos del guard web */
    public function index(Request $request)
    {
        $q = Permission::where('guard_name','web');

        if ($s = $request->query('search')) {
            $q->where('name','like',"%{$s}%");
        }

        return response()->json([
            'data' => $q->orderBy('name')->get(),
            'meta' => ['current_page'=>1,'last_page'=>1,'per_page'=>0,'total'=>0],
        ]);
    }

    /** Crear permiso en web y api */
    public function store(Request $request)
    {
        $permsTable = config('permission.table_names.permissions', 'permissions');

        $data = $request->validate([
            'name' => ['required','string','max:255', Rule::unique($permsTable, 'name')->where('guard_name','web')],
        ]);

        return DB::transaction(function () use ($data) {
            $web = Permission::create(['name'=>$data['name'],'guard_name'=>'web']);
            Permission::firstOrCreate(['name'=>$data['name'],'guard_name'=>'api']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return response()->json($web, 201);
        });
    }

    /** Mostrar SOLO permiso web */
    public function show(Permission $permission)
    {
        abort_if($permission->guard_name !== 'web', 404);
        return response()->json($permission);
    }

    /** Renombrar en web y api */
    public function update(Request $request, Permission $permission)
    {
        abort_if($permission->guard_name !== 'web', 404);

        $permsTable = config('permission.table_names.permissions', 'permissions');

        $data = $request->validate([
            'name' => ['required','string','max:255', Rule::unique($permsTable,'name')->ignore($permission->id)->where('guard_name','web')],
        ]);

        return DB::transaction(function () use ($permission, $data) {
            $old = $permission->name;
            $permission->name = $data['name'];
            $permission->save();

            $api = Permission::firstOrCreate(['name'=>$old,'guard_name'=>'api']);
            // validar conflicto en api
            $conflict = Permission::where('guard_name','api')->where('name',$data['name'])->where('id','<>',$api->id)->exists();
            if ($conflict) {
                abort(422, "Ya existe un permiso API con el nombre '{$data['name']}'");
            }
            $api->name = $data['name'];
            $api->save();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return response()->json($permission);
        });
    }

    /** Borrar en web y api */
    public function destroy(Permission $permission)
    {
        abort_if($permission->guard_name !== 'web', 404);

        return DB::transaction(function () use ($permission) {
            $name = $permission->name;
            $permission->delete();
            $api = Permission::where('guard_name','api')->where('name',$name)->first();
            if ($api) { $api->delete(); }
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return response()->json(null, 204);
        });
    }
}