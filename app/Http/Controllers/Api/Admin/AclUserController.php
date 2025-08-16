<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Admin - ACL", description="Asignaciones a usuarios (guard web)")
 */
class AclUserController extends Controller
{
    /**
     * Asigna/sincroniza roles (guard web) a un usuario.
     *
     * @OA\Put(
     *   path="/api/admin/users/{user}/roles",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   summary="Sincronizar roles de un usuario (reemplaza todos)",
     *   @OA\Parameter(
     *     name="user", in="path", required=true,
     *     description="ID del usuario objetivo",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     description="Lista completa de nombres de roles que el usuario debe tener (guard web).",
     *     @OA\JsonContent(
     *       required={"items"},
     *       type="object",
     *       @OA\Property(
     *         property="items",
     *         type="array",
     *         @OA\Items(type="string"),
     *         example={"admin","editor"}
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer", example=7),
     *       @OA\Property(property="name", type="string", example="Jane Doe"),
     *       @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *       @OA\Property(
     *         property="roles", type="array",
     *         @OA\Items(type="string"), example={"admin","editor"}
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="Usuario no encontrado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function syncRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'items'   => ['required','array'],
            'items.*' => ['string'],
        ]);

        $targetNames = Role::where('guard_name','web')->whereIn('name', $data['items'])->pluck('name')->all();
        $current     = $user->roles()->where('guard_name','web')->pluck('name')->all();

        foreach (array_diff($current, $targetNames) as $toRemove) {
            $user->removeRole(Role::where('name',$toRemove)->where('guard_name','web')->first());
        }
        foreach (array_diff($targetNames, $current) as $toAdd) {
            $user->assignRole(Role::where('name',$toAdd)->where('guard_name','web')->first());
        }

        return response()->json([
            'id'=>$user->id,'name'=>$user->name,'email'=>$user->email,
            'roles' => $user->roles()->where('guard_name','web')->pluck('name')->values(),
        ]);
    }

    /**
     * Asigna/sincroniza permisos (guard web) directos a un usuario.
     *
     * @OA\Put(
     *   path="/api/admin/users/{user}/permissions",
     *   tags={"Admin - ACL"},
     *   security={{"bearerAuth":{}}},
     *   summary="Sincronizar permisos directos de un usuario (reemplaza todos)",
     *   @OA\Parameter(
     *     name="user", in="path", required=true,
     *     description="ID del usuario objetivo",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     description="Lista completa de nombres de permisos que el usuario debe tener (guard web).",
     *     @OA\JsonContent(
     *       required={"items"},
     *       type="object",
     *       @OA\Property(
     *         property="items",
     *         type="array",
     *         @OA\Items(type="string"),
     *         example={"posts.view","posts.create"}
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer", example=7),
     *       @OA\Property(property="name", type="string", example="Jane Doe"),
     *       @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *       @OA\Property(
     *         property="permissions", type="array",
     *         @OA\Items(type="string"), example={"posts.view","posts.create"}
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="Usuario no encontrado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function syncPermissions(Request $request, User $user)
    {
        $data = $request->validate([
            'items'   => ['required','array'],
            'items.*' => ['string'],
        ]);

        $targetNames = Permission::where('guard_name','web')->whereIn('name', $data['items'])->pluck('name')->all();
        $current     = $user->permissions()->where('guard_name','web')->pluck('name')->all();

        foreach (array_diff($current, $targetNames) as $toRemove) {
            $user->revokePermissionTo(Permission::where('name',$toRemove)->where('guard_name','web')->first());
        }
        foreach (array_diff($targetNames, $current) as $toAdd) {
            $user->givePermissionTo(Permission::where('name',$toAdd)->where('guard_name','web')->first());
        }

        return response()->json([
            'id'=>$user->id,'name'=>$user->name,'email'=>$user->email,
            'permissions' => $user->permissions()->where('guard_name','web')->pluck('name')->values(),
        ]);
    }
}