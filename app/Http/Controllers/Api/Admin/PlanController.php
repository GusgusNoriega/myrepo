<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Admin - Plans",
 *   description="Gestión de planes y límites (requiere rol admin con guard api)"
 * )
 *
 * =========================
 *  Component Schemas
 * =========================
 *
 * @OA\Schema(
 *   schema="PlanFeature",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=10),
 *   @OA\Property(property="plan_id", type="integer", example=3),
 *   @OA\Property(property="product_limit", type="integer", example=100),
 *   @OA\Property(property="storage_limit_bytes", type="integer", example=1073741824),
 *   @OA\Property(property="staff_limit", type="integer", example=3),
 *   @OA\Property(property="asset_limit", type="integer", nullable=true, example=1000),
 *   @OA\Property(property="category_limit", type="integer", nullable=true, example=50),
 *   @OA\Property(property="other", type="object", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="Plan",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=3),
 *   @OA\Property(property="code", type="string", example="starter"),
 *   @OA\Property(property="name", type="string", example="Starter"),
 *   @OA\Property(property="price_usd", type="number", format="float", example=9.0),
 *   @OA\Property(property="billing_interval", type="string", enum={"month","year"}, example="month"),
 *   @OA\Property(property="is_active", type="boolean", example=true),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="features", ref="#/components/schemas/PlanFeature")
 * )
 *
 * @OA\Schema(
 *   schema="PlanStoreRequest",
 *   type="object",
 *   required={"code","name","price_usd","billing_interval","features"},
 *   @OA\Property(property="code", type="string", example="starter"),
 *   @OA\Property(property="name", type="string", example="Starter"),
 *   @OA\Property(property="price_usd", type="number", format="float", example=9.00),
 *   @OA\Property(property="billing_interval", type="string", enum={"month","year"}, example="month"),
 *   @OA\Property(property="is_active", type="boolean", example=true),
 *   @OA\Property(
 *     property="features",
 *     type="object",
 *     required={"product_limit","storage_limit_bytes","staff_limit"},
 *     @OA\Property(property="product_limit", type="integer", example=100),
 *     @OA\Property(property="storage_limit_bytes", type="integer", example=1073741824),
 *     @OA\Property(property="staff_limit", type="integer", example=3),
 *     @OA\Property(property="asset_limit", type="integer", nullable=true, example=1000),
 *     @OA\Property(property="category_limit", type="integer", nullable=true, example=50),
 *     @OA\Property(property="other", type="object", nullable=true, example={"support":"email"})
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="PlanUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string", example="starter"),
 *   @OA\Property(property="name", type="string", example="Starter Plus"),
 *   @OA\Property(property="price_usd", type="number", format="float", example=12.0),
 *   @OA\Property(property="billing_interval", type="string", enum={"month","year"}),
 *   @OA\Property(property="is_active", type="boolean"),
 *   @OA\Property(
 *     property="features",
 *     type="object",
 *     @OA\Property(property="product_limit", type="integer", example=200),
 *     @OA\Property(property="storage_limit_bytes", type="integer", example=2147483648),
 *     @OA\Property(property="staff_limit", type="integer", example=5),
 *     @OA\Property(property="asset_limit", type="integer", nullable=true),
 *     @OA\Property(property="category_limit", type="integer", nullable=true),
 *     @OA\Property(property="other", type="object", nullable=true)
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="MetaPagination",
 *   type="object",
 *   @OA\Property(property="current_page", type="integer", example=1),
 *   @OA\Property(property="last_page", type="integer", example=3),
 *   @OA\Property(property="per_page", type="integer", example=20),
 *   @OA\Property(property="total", type="integer", example=45)
 * )
 *
 * @OA\Schema(
 *   schema="PlanIndexResponse",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Plan")),
 *   @OA\Property(property="meta", ref="#/components/schemas/MetaPagination")
 * )
 */
class PlanController extends Controller
{
    /**
     * Listado de planes (paginado).
     *
     * @OA\Get(
     *   path="/api/admin/plans",
     *   operationId="AdminPlansIndex",
     *   tags={"Admin - Plans"},
     *   security={{"bearerAuth":{}}}, 
     *   @OA\Parameter(
     *     name="active", in="query", required=false, description="Filtrar por estado activo (1/0)",
     *     @OA\Schema(type="boolean")
     *   ),
     *   @OA\Parameter(
     *     name="search", in="query", required=false, description="Buscar por code/name (like)",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="per_page", in="query", required=false, description="Tamaño de página (default 20)",
     *     @OA\Schema(type="integer", example=20)
     *   ),
     *   @OA\Parameter(
     *     name="page", in="query", required=false, description="Página a consultar",
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PlanIndexResponse")),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado (requiere rol admin)")
     * )
     */
    public function index(Request $request)
    {
        $q = Plan::query();

        // filtros opcionales
        if ($request->filled('active')) {
            $q->where('is_active', (int) (bool) $request->boolean('active'));
        }
        if ($s = $request->query('search')) {
            $q->where(function($qq) use ($s){
                $qq->where('code','like',"%$s%")
                   ->orWhere('name','like',"%$s%");
            });
        }

        $plans = $q->orderBy('created_at','desc')->paginate(
            $request->integer('per_page', 20)
        )->appends($request->all());

        return response()->json([
            'data' => $plans->items(),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'last_page'    => $plans->lastPage(),
                'per_page'     => $plans->perPage(),
                'total'        => $plans->total(),
            ],
        ]);
    }

    /**
     * Mostrar un plan por ID.
     *
     * @OA\Get(
     *   path="/api/admin/plans/{plan}",
     *   operationId="AdminPlansShow",
     *   tags={"Admin - Plans"},
     *   security={{"bearerAuth":{}}}, 
     *   @OA\Parameter(
     *     name="plan", in="path", required=true, description="ID del plan",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Plan")),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Plan $plan)
    {
        return response()->json($plan);
    }

    /**
     * Crear un nuevo plan.
     *
     * @OA\Post(
     *   path="/api/admin/plans",
     *   operationId="AdminPlansStore",
     *   tags={"Admin - Plans"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PlanStoreRequest")),
     *   @OA\Response(response=201, description="Creado", @OA\JsonContent(ref="#/components/schemas/Plan")),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(StorePlanRequest $request)
    {
        $payload = $request->validated();

        return DB::transaction(function() use ($payload) {

            $plan = Plan::create([
                'code'             => $payload['code'],
                'name'             => $payload['name'],
                'price_usd'        => $payload['price_usd'],
                'billing_interval' => $payload['billing_interval'],
                'is_active'        => (int) ($payload['is_active'] ?? 1),
            ]);

            $f = $payload['features'];
            $plan->features()->create([
                'product_limit'        => $f['product_limit'],
                'storage_limit_bytes'  => $f['storage_limit_bytes'],
                'staff_limit'          => $f['staff_limit'],
                'asset_limit'          => $f['asset_limit'] ?? null,
                'category_limit'       => $f['category_limit'] ?? null,
                'other'                => $f['other'] ?? null,
            ]);

            return response()->json($plan->fresh(), 201);
        });
    }

    /**
     * Actualizar un plan existente.
     *
     * @OA\Patch(
     *   path="/api/admin/plans/{plan}",
 *   operationId="AdminPlansUpdate",
     *   tags={"Admin - Plans"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="plan", in="path", required=true, description="ID del plan",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PlanUpdateRequest")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Plan")),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $payload = $request->validated();

        return DB::transaction(function() use ($payload, $plan) {

            $plan->fill(collect($payload)->only([
                'code','name','price_usd','billing_interval','is_active'
            ])->toArray());
            $plan->save();

            if (isset($payload['features'])) {
                $f = $payload['features'];
                $features = $plan->features ?: new PlanFeature(['plan_id' => $plan->id]);

                $features->fill([
                    'product_limit'        => $f['product_limit']        ?? $features->product_limit,
                    'storage_limit_bytes'  => $f['storage_limit_bytes']  ?? $features->storage_limit_bytes,
                    'staff_limit'          => $f['staff_limit']          ?? $features->staff_limit,
                    'asset_limit'          => array_key_exists('asset_limit', $f) ? $f['asset_limit'] : $features->asset_limit,
                    'category_limit'       => array_key_exists('category_limit', $f) ? $f['category_limit'] : $features->category_limit,
                    'other'                => array_key_exists('other', $f) ? $f['other'] : $features->other,
                ]);

                $plan->features()->save($features);
            }

            return response()->json($plan->fresh());
        });
    }

    /**
     * Eliminar un plan.
     *
     * @OA\Delete(
     *   path="/api/admin/plans/{plan}",
     *   operationId="AdminPlansDestroy",
     *   tags={"Admin - Plans"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="plan", in="path", required=true, description="ID del plan",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=204, description="Eliminado"),
     *   @OA\Response(
     *     response=409,
     *     description="Conflicto: hay suscripciones asociadas",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="message", type="string", example="No se puede eliminar el plan porque tiene suscripciones asociadas.")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Plan $plan)
    {
        try {
            $plan->features()?->delete();
            $plan->delete();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se puede eliminar el plan porque tiene suscripciones asociadas.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}