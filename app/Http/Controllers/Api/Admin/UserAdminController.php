<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Membership;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Admin - Users",
 *   description="Gestión de usuarios (CRUD) y lectura agregada de relaciones. Para roles/permisos usar AclUserController; para membresías usar MembershipController."
 * )
 */
class UserAdminController extends Controller
{
    /**
     * Listado de usuarios (paginado + filtros).
     *
     * Filtros:
     * - search: coincide en name o email
     * - role: nombre del rol (guard web)
     * - business_id: lista usuarios que pertenezcan (via memberships) a ese negocio
     * - per_page, page
     *
     * @OA\Get(
     *   path="/api/admin/users",
     *   tags={"Admin - Users"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="role", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="business_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=20)),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "data": {{
     *           "id": 7, "name": "Jane Doe", "email": "jane@example.com",
     *           "active_business_id": 1,
     *           "roles": {"admin","editor"},
     *           "permissions": {"posts.view"},
     *           "memberships": {{
     *             "id": 12, "business": {"id":1,"name":"Mi Negocio"}, "role":"editor", "state":"active"
     *           }}
     *         }},
     *         "meta": {"current_page":1,"last_page":3,"per_page":20,"total":41}
     *       }
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $q = User::query();

        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $role = $request->get('role');
            // No asumimos scope role(); usamos whereHas por compatibilidad
            $q->whereHas('roles', function ($r) use ($role) {
                $r->where('name', $role)->where('guard_name', 'web');
            });
        }

        if ($request->filled('business_id')) {
            $businessId = $request->integer('business_id');
            $q->whereExists(function ($sub) use ($businessId) {
                $sub->select('id')
                    ->from('memberships')
                    ->whereColumn('memberships.user_id', 'users.id')
                    ->where('memberships.business_id', $businessId);
            });
        }

        $users = $q->orderBy('name')->paginate($request->integer('per_page', 20))
                   ->appends($request->all());

        // Prefetch memberships + business para evitar N+1
        $byUser = Membership::whereIn('user_id', $users->pluck('id'))
            ->with(['business:id,name'])
            ->get()
            ->groupBy('user_id');

        $data = $users->getCollection()->map(function (User $u) use ($byUser) {
            $memberships = ($byUser[$u->id] ?? collect())->map(function ($m) {
                return [
                    'id' => $m->id,
                    'business' => ['id'=>$m->business->id, 'name'=>$m->business->name],
                    'role' => $m->role,
                    'state' => $m->state,
                ];
            })->values();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'active_business_id' => $u->active_business_id,
                'roles' => $u->roles()->where('guard_name','web')->pluck('name')->values(),
                'permissions' => $u->permissions()->where('guard_name','web')->pluck('name')->values(),
                'memberships' => $memberships,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    /**
     * Mostrar un usuario con relaciones agregadas (roles/permisos web, memberships, negocio activo).
     *
     * @OA\Get(
     *   path="/api/admin/users/{user}",
     *   tags={"Admin - Users"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $memberships = Membership::where('user_id', $user->id)
            ->with('business:id,name')->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'business' => ['id'=>$m->business->id, 'name'=>$m->business->name],
                'role' => $m->role,
                'state' => $m->state,
            ])->values();
        
            

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'active_business_id' => $user->active_business_id,
            'roles' => $user->roles()->where('guard_name','web')->pluck('name')->values(),
            'permissions' => $user->permissions()->where('guard_name','web')->pluck('name')->values(),
            'memberships' => $memberships,
        ]);
    }

    /**
     * Crear usuario (solo datos del usuario). Para roles/permisos y membresías usar los endpoints dedicados.
     *
     * @OA\Post(
     *   path="/api/admin/users",
     *   tags={"Admin - Users"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email","password"},
     *       @OA\Property(property="name", type="string", example="Jane Doe"),
     *       @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="secret123"),
     *       @OA\Property(property="active_business_id", type="integer", nullable=true, example=1)
     *     )
     *   ),
     *   @OA\Response(response=201, description="Creado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(Request $request)
    {
        // Solo admin crea usuarios
        $this->authorize('create', User::class);

        // ¿puede el autenticado setear active_business_id en creación?
        $canSetActiveBiz = $request->user()->can('setActiveBusinessOnCreate', User::class);

        $payload = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:6'],
            // si NO es admin => 'prohibited' (rechaza si viene en el payload)
            'active_business_id' => $canSetActiveBiz
                ? ['nullable','integer','exists:businesses,id']
                : ['prohibited'],
        ]);

        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'active_business_id' => $payload['active_business_id'] ?? null,
        ]);

        return response()->json([
            'id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'active_business_id'=>$user->active_business_id
        ], 201);
    }

    /**
     * Actualizar usuario (datos básicos + active_business_id).
     *
     * @OA\Patch(
     *   path="/api/admin/users/{user}",
     *   tags={"Admin - Users"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string", format="password"),
     *       @OA\Property(property="active_business_id", type="integer", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(Request $request, User $user)
    {
        // admin puede actualizar cualquiera; usuarios normales solo su propio perfil
        $this->authorize('update', $user);
        // ¿puede el autenticado cambiar active_business_id de ESTE usuario?
        $canSetActiveBiz = $request->user()->can('setActiveBusinessOnCreate', User::class);

        $payload = $request->validate([
            'name'  => ['sometimes','string','max:255'],
            'email' => ['sometimes','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['sometimes','string','min:6'],
            'active_business_id' => $canSetActiveBiz
                ? ['nullable','integer','exists:businesses,id']
                : ['prohibited'],
        ]);

        if (array_key_exists('name', $payload))    $user->name = $payload['name'];
        if (array_key_exists('email', $payload))   $user->email = $payload['email'];
        if (array_key_exists('password', $payload)) $user->password = Hash::make($payload['password']);
        if (array_key_exists('active_business_id', $payload)) $user->active_business_id = $payload['active_business_id'];

        $user->save();

        return response()->json([
            'id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'active_business_id'=>$user->active_business_id
        ]);
    }

    /**
     * Eliminar usuario.
     *
     * @OA\Delete(
     *   path="/api/admin/users/{user}",
     *   tags={"Admin - Users"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Eliminado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(User $user)
    {
         // Solo admin elimina
        $this->authorize('delete', $user);

        $user->delete();
        return response()->json(null, 204);
    }

    /**
     * Cambiar el negocio activo de un usuario validando pertenencia.
     *
     * @OA\Patch(
     *   path="/api/admin/users/{user}/active-business",
     *   tags={"Admin - Users"},
     *   security={{"bearerAuth":{}}},
     *   summary="Establece el active_business_id del usuario (debe pertenecer al negocio o ser owner).",
     *   @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"business_id"},
     *       @OA\Property(property="business_id", type="integer", example=1)
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=422, description="Validación o pertenencia fallida")
     * )
     */
    public function setActiveBusiness(Request $request, User $user)
    {
        // Solo admin puede usar este endpoint
        $this->authorize('updateActiveBusiness', $user);

        $data = $request->validate([
            'business_id' => ['required','integer','exists:businesses,id'],
        ]);

        $businessId = $data['business_id'];

        $belongs = Membership::where('user_id', $user->id)
            ->where('business_id', $businessId)->exists();

        $isOwner = Business::where('id', $businessId)
            ->where('owner_user_id', $user->id)->exists();

        if (!$belongs && !$isOwner) {
            return response()->json([
                'message' => 'El usuario no pertenece a ese negocio.'
            ], 422);
        }

        $user->active_business_id = $businessId;
        $user->save();

        return response()->json([
            'id'=>$user->id,'active_business_id'=>$user->active_business_id
        ]);
    }
}