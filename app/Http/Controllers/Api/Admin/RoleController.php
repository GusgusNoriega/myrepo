<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Admin - ACL",
 *   description="Roles y permisos (guard único: web). Requiere token y rol admin."
 * )
 */
class RoleController extends Controller
{
   /**
     * @OA\Get(
     *   path="/api/admin/roles",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="data", type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="guard_name", type="string", example="web"),
     *           @OA\Property(
     *             property="permissions", type="array",
     *             @OA\Items(
     *               type="object",
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="name", type="string"),
     *               @OA\Property(property="guard_name", type="string", example="web")
     *             )
     *           ),
     *           @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *           @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *         )
     *       ),
     *       @OA\Property(
     *         property="meta", type="object",
     *         @OA\Property(property="current_page", type="integer"),
     *         @OA\Property(property="last_page", type="integer"),
     *         @OA\Property(property="per_page", type="integer"),
     *         @OA\Property(property="total", type="integer")
     *       )
     *     )
     *   )
     * )
     */
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

    /**
     * @OA\Post(
     *   path="/api/admin/roles",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name"},
     *       @OA\Property(property="name", type="string", example="editor"),
     *       @OA\Property(
     *         property="permissions", type="array",
     *         @OA\Items(type="string"), example={"posts.view","posts.create"}
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=201, description="Creado",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="guard_name", type="string", example="web"),
     *       @OA\Property(
     *         property="permissions", type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="guard_name", type="string", example="web")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required','string','max:255', Rule::unique(config('permission.table_names.roles'),'name')->where('guard_name','web')],
            'permissions'       => ['array'],
            'permissions.*'     => ['string'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        if (!empty($data['permissions'])) {
            $perms = Permission::where('guard_name','web')->whereIn('name', $data['permissions'])->get();
            $role->syncPermissions($perms);
        }

        return response()->json($role->load('permissions'), 201);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/roles/{role}",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="role", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="guard_name", type="string", example="web"),
     *       @OA\Property(
     *         property="permissions", type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="guard_name", type="string", example="web")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    public function show(Role $role)
    {
        abort_if($role->guard_name !== 'web', 404);
        return response()->json($role->load('permissions'));
    }

    /**
     * @OA\Patch(
     *   path="/api/admin/roles/{role}",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="role", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="name", type="string", example="editor-plus"),
     *       @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="guard_name", type="string", example="web"),
     *       @OA\Property(
     *         property="permissions", type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="guard_name", type="string", example="web")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    public function update(Request $request, Role $role)
    {
        abort_if($role->guard_name !== 'web', 404);

        $data = $request->validate([
            'name'          => ['sometimes','string','max:255', Rule::unique(config('permission.table_names.roles'),'name')->ignore($role->id)->where('guard_name','web')],
            'permissions'   => ['sometimes','array'],
            'permissions.*' => ['string'],
        ]);

        if (array_key_exists('name', $data)) {
            $role->name = $data['name'];
            $role->save();
        }

        if (array_key_exists('permissions', $data)) {
            $perms = Permission::where('guard_name','web')->whereIn('name', $data['permissions'])->get();
            $role->syncPermissions($perms);
        }

        return response()->json($role->fresh()->load('permissions'));
    }

   /**
     * @OA\Delete(
     *   path="/api/admin/roles/{role}",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="role", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Eliminado")
     * )
     */
    public function destroy(Role $role)
    {
        abort_if($role->guard_name !== 'web', 404);
        $role->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Put(
     *   path="/api/admin/roles/{role}/permissions",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="role", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"items"},
     *       @OA\Property(property="items", type="array", @OA\Items(type="string"), example={"posts.view","posts.create"})
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="guard_name", type="string", example="web"),
     *       @OA\Property(
     *         property="permissions", type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="guard_name", type="string", example="web")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    public function syncPermissions(Request $request, Role $role)
    {
        abort_if($role->guard_name !== 'web', 404);

        $data = $request->validate([
            'items'   => ['required','array'],
            'items.*' => ['string'],
        ]);

        $perms = Permission::where('guard_name','web')->whereIn('name', $data['items'])->get();
        $role->syncPermissions($perms);

        return response()->json($role->fresh()->load('permissions'));
    }
}