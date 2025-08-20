<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Admin - Memberships",
 *   description="Gestión de membresías (requiere rol admin con guard api)"
 * )
 */
class MembershipController extends Controller
{
    /**
     * Listado de membresías (paginado y filtrado).
     *
     * @OA\Get(
     *   path="/api/admin/memberships",
     *   tags={"Admin - Memberships"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="business_id", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="user_id", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="role", in="query", required=false, @OA\Schema(type="string", enum={"owner","admin","editor","viewer"})),
     *   @OA\Parameter(name="state", in="query", required=false, @OA\Schema(type="string", enum={"invited","active","suspended"})),
     *   @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=20)),
     *   @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       example={
     *         "data": {
     *           {
     *             "id": 12,
     *             "user_id": 3,
     *             "business_id": 1,
     *             "role": "editor",
     *             "state": "active",
     *             "accepted_at": "2025-08-10T14:15:22Z",
     *             "invited_by": 2,
     *             "created_at": "2025-08-01T12:34:56Z",
     *             "updated_at": "2025-08-10T14:15:22Z",
     *             "user": { "id":3, "name":"Ana Pérez", "email":"ana@example.com" },
     *             "business": { "id":1, "name":"Mi Negocio" },
     *             "invited_by_user": { "id":2, "name":"Admin", "email":"admin@example.com" }
     *           }
     *         },
     *         "meta": { "current_page":1, "last_page":3, "per_page":20, "total":41 }
     *       }
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado")
     * )
     */
    public function index(Request $request)
    {
        $q = Membership::query()
            ->with([
                'user:id,name,email',
                'business:id,name',
                'invitedBy:id,name,email',
            ]);

        if ($request->filled('business_id')) {
            $q->where('business_id', $request->integer('business_id'));
        }
        if ($request->filled('user_id')) {
            $q->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('role')) {
            $q->where('role', $request->get('role'));
        }
        if ($request->filled('state')) {
            $q->where('state', $request->get('state'));
        }
        if ($s = $request->query('search')) {
            $q->whereHas('user', function ($u) use ($s) {
                $u->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $memberships = $q->orderBy('created_at', 'desc')->paginate(
            $request->integer('per_page', 20)
        )->appends($request->all());

        // Renombramos invitedBy a invited_by_user en la respuesta de ejemplo; la salida real mantiene la relación invitedBy
        return response()->json([
            'data' => $memberships->items(),
            'meta' => [
                'current_page' => $memberships->currentPage(),
                'last_page'    => $memberships->lastPage(),
                'per_page'     => $memberships->perPage(),
                'total'        => $memberships->total(),
            ],
        ]);
    }

    /**
     * Mostrar una membresía por ID.
     *
     * @OA\Get(
     *   path="/api/admin/memberships/{membership}",
     *   tags={"Admin - Memberships"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="membership", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       example={
     *         "id": 12,
     *         "user_id": 3,
     *         "business_id": 1,
     *         "role": "editor",
     *         "state": "active",
     *         "accepted_at": "2025-08-10T14:15:22Z",
     *         "invited_by": 2,
     *         "created_at": "2025-08-01T12:34:56Z",
     *         "updated_at": "2025-08-10T14:15:22Z",
     *         "user": { "id":3, "name":"Ana Pérez", "email":"ana@example.com" },
     *         "business": { "id":1, "name":"Mi Negocio" },
     *         "invitedBy": { "id":2, "name":"Admin", "email":"admin@example.com" }
     *       }
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Membership $membership)
    {
        return response()->json(
            $membership->load(['user:id,name,email', 'business:id,name', 'invitedBy:id,name,email'])
        );
    }

    /**
     * Crear una nueva membresía.
     *
     * @OA\Post(
     *   path="/api/admin/memberships",
     *   tags={"Admin - Memberships"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"user_id","business_id","role"},
     *       @OA\Property(property="user_id", type="integer", example=3),
     *       @OA\Property(property="business_id", type="integer", example=1),
     *       @OA\Property(property="role", type="string", enum={"owner","admin","editor","viewer"}, example="editor"),
     *       @OA\Property(property="state", type="string", enum={"invited","active","suspended"}, example="invited"),
     *       @OA\Property(property="accepted_at", type="string", format="date-time", example="2025-08-19T15:00:00Z"),
     *       @OA\Property(property="invited_by", type="integer", example=2)
     *     ),
     *     description="Cuerpo mínimo: user_id, business_id y role"
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Creado",
     *     @OA\JsonContent(
     *       type="object",
     *       example={
     *         "id": 25,
     *         "user_id": 3,
     *         "business_id": 1,
     *         "role": "editor",
     *         "state": "invited",
     *         "accepted_at": null,
     *         "invited_by": 2,
     *         "created_at": "2025-08-19T15:01:00Z",
     *         "updated_at": "2025-08-19T15:01:00Z",
     *         "user": { "id":3, "name":"Ana Pérez", "email":"ana@example.com" },
     *         "business": { "id":1, "name":"Mi Negocio" }
     *       }
     *     )
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(
     *     response=409,
     *     description="Conflicto: membresía duplicada",
     *     @OA\JsonContent(type="object", example={"message": "Ya existe una membresía para este usuario en este negocio."})
     *   ),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            'user_id'     => 'required|integer|exists:users,id',
            'business_id' => 'required|integer|exists:businesses,id',
            'role'        => 'required|string|in:owner,admin,editor,viewer',
            'state'       => 'nullable|string|in:invited,active,suspended',
            'accepted_at' => 'nullable|date',
            'invited_by'  => 'nullable|integer|exists:users,id',
        ]);

        try {
            $m = Membership::create($payload);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ya existe una membresía para este usuario en este negocio.',
            ], 409);
        }

        return response()->json($m->fresh()->load(['user:id,name,email', 'business:id,name']), 201);
    }

    /**
     * Actualizar una membresía.
     *
     * @OA\Patch(
     *   path="/api/admin/memberships/{membership}",
     *   tags={"Admin - Memberships"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="membership", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="role", type="string", enum={"owner","admin","editor","viewer"}, example="admin"),
     *       @OA\Property(property="state", type="string", enum={"invited","active","suspended"}, example="active"),
     *       @OA\Property(property="accepted_at", type="string", format="date-time", example="2025-08-19T15:30:00Z"),
     *       @OA\Property(property="invited_by", type="integer", nullable=true, example=2)
     *     ),
     *     description="Envíe solo los campos que desee actualizar."
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       example={
     *         "id": 12,
     *         "user_id": 3,
     *         "business_id": 1,
     *         "role": "admin",
     *         "state": "active",
     *         "accepted_at": "2025-08-19T15:30:00Z",
     *         "invited_by": 2,
     *         "created_at": "2025-08-01T12:34:56Z",
     *         "updated_at": "2025-08-19T15:30:00Z",
     *         "user": { "id":3, "name":"Ana Pérez", "email":"ana@example.com" },
     *         "business": { "id":1, "name":"Mi Negocio" },
     *         "invitedBy": { "id":2, "name":"Admin", "email":"admin@example.com" }
     *       }
     *     )
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(Request $request, Membership $membership)
    {
        $payload = $request->validate([
            'role'        => 'sometimes|string|in:owner,admin,editor,viewer',
            'state'       => 'sometimes|string|in:invited,active,suspended',
            'accepted_at' => 'sometimes|nullable|date',
            'invited_by'  => 'sometimes|nullable|integer|exists:users,id',
        ]);

        $membership->fill($payload);

        if (array_key_exists('state', $payload)
            && $payload['state'] === 'active'
            && is_null($membership->accepted_at)
            && !$request->has('accepted_at')) {
            $membership->accepted_at = now();
        }

        $membership->save();

        return response()->json($membership->fresh()->load(['user:id,name,email', 'business:id,name', 'invitedBy:id,name,email']));
    }

    /**
     * Eliminar una membresía.
     *
     * @OA\Delete(
     *   path="/api/admin/memberships/{membership}",
     *   tags={"Admin - Memberships"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="membership", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Eliminado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Membership $membership)
    {
        $membership->delete();
        return response()->json(null, 204);
    }
}