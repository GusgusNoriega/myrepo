<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Admin - Categories",
 *   description="Gestión de categorías por negocio (requiere rol admin con guard api)"
 * )
 */
class CategoryController extends Controller
{
    /**
     * Listado de categorías (paginado y filtrado).
     *
     * Filtros:
     * - business_id (recomendado)
     * - parent_id (para listar subcategorías)
     * - search (coincide en name o slug)
     * - order_by: position|name|created_at (default: position)
     * - sort: asc|desc (default: asc)
     * - per_page, page
     *
     * @OA\Get(
     *   path="/api/admin/categories",
     *   tags={"Admin - Categories"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="business_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="parent_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string", enum={"position","name","created_at"})),
     *   @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=20)),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "data": {{
     *           "id": 10, "business_id": 1, "parent_id": null,
     *           "name": "Ropa", "slug": "ropa", "position": 1,
     *           "created_at": "2025-08-01T12:00:00Z", "updated_at": "2025-08-01T12:00:00Z",
     *           "children_count": 2, "products_count": 14,
     *           "parent": null, "business": {"id":1,"name":"Mi Negocio"}
     *         }},
     *         "meta": {"current_page":1,"last_page":1,"per_page":20,"total":1}
     *       }
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado")
     * )
     */
    public function index(Request $request)
    {
        $q = Category::query()
            ->with(['parent:id,name', 'business:id,name'])
            ->withCount(['children', 'products']);

        if ($request->filled('business_id')) {
            $q->where('business_id', $request->integer('business_id'));
        }
        if ($request->filled('parent_id')) {
            $q->where('parent_id', $request->integer('parent_id'));
        }
        if ($s = $request->query('search')) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('slug', 'like', "%{$s}%");
            });
        }

        $orderBy = in_array($request->query('order_by'), ['position','name','created_at'])
            ? $request->query('order_by') : 'position';
        $sort = $request->query('sort') === 'desc' ? 'desc' : 'asc';

        $cats = $q->orderBy($orderBy, $sort)->paginate(
            $request->integer('per_page', 20)
        )->appends($request->all());

        return response()->json([
            'data' => $cats->items(),
            'meta' => [
                'current_page' => $cats->currentPage(),
                'last_page'    => $cats->lastPage(),
                'per_page'     => $cats->perPage(),
                'total'        => $cats->total(),
            ],
        ]);
    }

    /**
     * Mostrar una categoría por ID.
     *
     * @OA\Get(
     *   path="/api/admin/categories/{category}",
     *   tags={"Admin - Categories"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "id": 10, "business_id": 1, "parent_id": null,
     *         "name": "Ropa", "slug":"ropa","position":1,
     *         "created_at":"2025-08-01T12:00:00Z","updated_at":"2025-08-01T12:00:00Z",
     *         "children_count":2,"products_count":14,
     *         "parent": null, "business":{"id":1,"name":"Mi Negocio"}
     *       }
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Category $category)
    {
        return response()->json(
            $category->load(['parent:id,name', 'business:id,name'])
                     ->loadCount(['children','products'])
        );
    }

    /**
     * Crear una categoría.
     *
     * Reglas:
     * - `slug` único por (business_id, slug). Si no se envía, se genera desde `name`.
     * - `parent_id` (si se envía) debe pertenecer al mismo `business_id`.
     * - Se valida **límite de categorías** según la suscripción activa del negocio.
     *
     * @OA\Post(
     *   path="/api/admin/categories",
     *   tags={"Admin - Categories"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"business_id","name"},
     *       @OA\Property(property="business_id", type="integer", example=1),
     *       @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
     *       @OA\Property(property="name", type="string", example="Ropa"),
     *       @OA\Property(property="slug", type="string", nullable=true, example="ropa"),
     *       @OA\Property(property="position", type="integer", example=1)
     *     )
     *   ),
     *   @OA\Response(
     *     response=201, description="Creado",
     *     @OA\JsonContent(type="object",
     *       example={"id":10,"business_id":1,"parent_id":null,"name":"Ropa","slug":"ropa","position":1}
     *     )
     *   ),
     *   @OA\Response(
     *     response=422, description="Validación / Límite alcanzado",
     *     @OA\JsonContent(type="object", example={"message":"Has alcanzado el límite de categorías para tu plan."})
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado")
     * )
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            'business_id' => ['required','integer','exists:businesses,id'],
            'parent_id'   => ['nullable','integer','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'slug'        => ['nullable','string','max:255'],
            'position'    => ['nullable','integer','min:0'],
        ]);

        // Validar parent pertenece al mismo negocio
        if (!empty($payload['parent_id'])) {
            $parent = Category::find($payload['parent_id']);
            if (!$parent || $parent->business_id !== (int) $payload['business_id']) {
                return response()->json(['message' => 'La categoría padre debe pertenecer al mismo negocio.'], 422);
            }
        }

        // Límite de categorías por suscripción activa
        if (!$this->checkCategoryLimit($payload['business_id'])) {
            return response()->json(['message' => 'Has alcanzado el límite de categorías para tu plan.'], 422);
        }

        // Slug único por negocio
        $slug = $payload['slug'] ?: $payload['name'];
        $slug = $this->uniqueSlug($payload['business_id'], $slug);

        $cat = Category::create([
            'business_id' => $payload['business_id'],
            'parent_id'   => $payload['parent_id'] ?? null,
            'name'        => $payload['name'],
            'slug'        => $slug,
            'position'    => $payload['position'] ?? 0,
        ]);

        return response()->json($cat->fresh(), 201);
    }

    /**
     * Actualizar una categoría.
     *
     * Notas:
     * - No se permite cambiar `business_id`.
     * - `parent_id`, si se envía, debe ser del mismo negocio y no puede ser el propio id
     *   ni un descendiente (ciclo). Se valida de forma simple subiendo por ancestros.
     *
     * @OA\Patch(
     *   path="/api/admin/categories/{category}",
     *   tags={"Admin - Categories"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
     *       @OA\Property(property="name", type="string", example="Ropa y Accesorios"),
     *       @OA\Property(property="slug", type="string", example="ropa-accesorios"),
     *       @OA\Property(property="position", type="integer", example=2)
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={"id":10,"business_id":1,"parent_id":null,"name":"Ropa y Accesorios","slug":"ropa-accesorios","position":2}
     *     )
     *   ),
     *   @OA\Response(response=422, description="Validación fallida"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function update(Request $request, Category $category)
    {
        $payload = $request->validate([
            'parent_id' => ['sometimes','nullable','integer','exists:categories,id'],
            'name'      => ['sometimes','string','max:255'],
            'slug'      => ['sometimes','nullable','string','max:255'],
            'position'  => ['sometimes','integer','min:0'],
        ]);

        // Validar parent (mismo negocio y sin ciclos)
        if (array_key_exists('parent_id', $payload)) {
            $parentId = $payload['parent_id'];
            if (!is_null($parentId)) {
                if ($parentId == $category->id) {
                    return response()->json(['message' => 'Una categoría no puede ser su propio padre.'], 422);
                }
                $parent = Category::find($parentId);
                if (!$parent || $parent->business_id !== $category->business_id) {
                    return response()->json(['message' => 'La categoría padre debe pertenecer al mismo negocio.'], 422);
                }
                // Chequeo simple de ciclo: subimos por ancestros del nuevo padre
                $cursor = $parent;
                while ($cursor) {
                    if ($cursor->id === $category->id) {
                        return response()->json(['message' => 'Asignación inválida: crearías un ciclo en la jerarquía.'], 422);
                    }
                    $cursor = $cursor->parent_id ? Category::find($cursor->parent_id) : null;
                }
            }
            $category->parent_id = $parentId;
        }

        if (array_key_exists('name', $payload)) {
            $category->name = $payload['name'];
        }

        if (array_key_exists('slug', $payload)) {
            $newSlug = $payload['slug'];
            if ($newSlug === null || $newSlug === '') {
                // regenerar desde name si se envía vacío/null
                $newSlug = $this->uniqueSlug($category->business_id, $category->name);
            } else {
                $newSlug = $this->uniqueSlug($category->business_id, $newSlug, $category->id);
            }
            $category->slug = $newSlug;
        }

        if (array_key_exists('position', $payload)) {
            $category->position = (int) $payload['position'];
        }

        $category->save();

        return response()->json($category->fresh());
    }

    /**
     * Eliminar una categoría.
     *
     * Efectos por FKs:
     * - `parent_id` de los hijos se pone en NULL (SET NULL)
     * - Relaciones en `product_category` se eliminan (CASCADE)
     *
     * @OA\Delete(
     *   path="/api/admin/categories/{category}",
     *   tags={"Admin - Categories"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Eliminado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }

    // ========================
    // Helpers
    // ========================

    /**
     * Genera un slug único por negocio. Si $excludeId se pasa, lo ignora en la comprobación (para update).
     */
    private function uniqueSlug(int $businessId, string $base, ?int $excludeId = null): string
    {
        $slug = Str::slug(Str::limit($base, 255, ''));
        $slug = $slug !== '' ? $slug : Str::random(8);

        $exists = function ($candidate) use ($businessId, $excludeId) {
            return Category::where('business_id', $businessId)
                ->where('slug', $candidate)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();
        };

        if (!$exists($slug)) {
            return $slug;
        }

        // añade sufijos -2, -3, ...
        for ($i = 2; $i <= 5000; $i++) {
            $candidate = Str::limit($slug, 240, '') . '-' . $i;
            if (!$exists($candidate)) {
                return $candidate;
            }
        }
        // Fallback improbable
        return $slug . '-' . Str::random(5);
    }

    /**
     * Verifica si el negocio puede crear otra categoría según la suscripción activa.
     * Si no hay suscripción activa/trial, se asume sin límite (o puedes cambiar a false según tu negocio).
     */
    private function checkCategoryLimit(int $businessId): bool
    {
        $now = now();

        $sub = Subscription::with('plan.features')
            ->where('business_id', $businessId)
            ->whereIn('status', ['trialing','active'])
            ->where('current_period_start', '<=', $now)
            ->where('current_period_end',   '>=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('canceled_at')->orWhere('canceled_at', '>', $now);
            })
            ->orderByDesc('current_period_end')
            ->first();

        $limit = optional(optional($sub)->plan->features)->category_limit;

        if (is_null($limit)) {
            // Sin límite configurado → permitir
            return true;
        }

        $count = Category::where('business_id', $businessId)->count();
        return $count < (int) $limit;
    }
}