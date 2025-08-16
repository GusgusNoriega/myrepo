<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Admin - ACL", description="Roles y permisos (guard: web)")
 */
class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/admin/permissions",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
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
     *           @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *           @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *         )
     *       ),
     *       @OA\Property(
     *         property="meta", type="object",
     *         @OA\Property(property="current_page", type="integer", example=1),
     *         @OA\Property(property="last_page", type="integer", example=1),
     *         @OA\Property(property="per_page", type="integer", example=0),
     *         @OA\Property(property="total", type="integer", example=0)
     *       )
     *     )
     *   )
     * )
     */
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

    /**
     * @OA\Post(
     *   path="/api/admin/permissions",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name"},
     *       @OA\Property(property="name", type="string", example="orders.update")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201, description="Creado",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="guard_name", type="string", example="web"),
     *       @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *     )
     *   )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255', Rule::unique(config('permission.table_names.permissions'),'name')->where('guard_name','web')],
        ]);

        $perm = Permission::create(['name'=>$data['name'],'guard_name'=>'web']);
        return response()->json($perm, 201);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/permissions/{permission}",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="permission", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="guard_name", type="string", example="web"),
     *       @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *     )
     *   )
     * )
     */
    public function show(Permission $permission)
    {
        abort_if($permission->guard_name !== 'web', 404);
        return response()->json($permission);
    }

   /**
     * @OA\Patch(
     *   path="/api/admin/permissions/{permission}",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="permission", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name"},
     *       @OA\Property(property="name", type="string", example="orders.manage")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="guard_name", type="string", example="web"),
     *       @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *     )
     *   )
     * )
     */
    public function update(Request $request, Permission $permission)
    {
        abort_if($permission->guard_name !== 'web', 404);

        $data = $request->validate([
            'name' => ['required','string','max:255', Rule::unique(config('permission.table_names.permissions'),'name')->ignore($permission->id)->where('guard_name','web')],
        ]);

        $permission->name = $data['name'];
        $permission->save();

        return response()->json($permission);
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/permissions/{permission}",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="permission", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Eliminado")
     * )
     */
    public function destroy(Permission $permission)
    {
        abort_if($permission->guard_name !== 'web', 404);
        $permission->delete();
        return response()->json(null, 204);
    }
}