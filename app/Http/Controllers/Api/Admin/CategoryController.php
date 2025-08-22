<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Aplica CategoryPolicy automáticamente (viewAny, view, create, update, delete)
        $this->authorizeResource(Category::class, 'category');
    }

    /**
     * @OA\Get( path="/api/admin/categories", tags={"Admin - Categories"}, security={{"bearerAuth":{}}}, ...)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $q = Category::query()
            ->with(['parent:id,name', 'business:id,name'])
            ->withCount(['children', 'products']);

        // ---- Scoping para "administrador" (NO super admin) ----
        if ($user->hasRole('administrador') && !$user->hasRole('admin')) {
            $activeId = $user->active_business_id;
            if (!$activeId) {
                return response()->json([
                    'data' => [],
                    'meta' => ['current_page'=>1,'last_page'=>1,'per_page'=>$request->integer('per_page',20),'total'=>0],
                ]);
            }
            $q->where('business_id', $activeId);
            // Si envían parent_id, lo respetamos (pero ya filtrado por su business)
            if ($request->filled('parent_id')) {
                $q->where('parent_id', $request->integer('parent_id'));
            }
        } else {
            // ---- Super admin: filtros completos ----
            if ($request->filled('business_id')) {
                $q->where('business_id', $request->integer('business_id'));
            }
            if ($request->filled('parent_id')) {
                $q->where('parent_id', $request->integer('parent_id'));
            }
        }

        if ($s = $request->query('search')) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('slug', 'like', "%{$s}%");
            });
        }

        $orderBy = in_array($request->query('order_by'), ['position','name','created_at']) ? $request->query('order_by') : 'position';
        $sort    = $request->query('sort') === 'desc' ? 'desc' : 'asc';

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
     * @OA\Get( path="/api/admin/categories/{category}", ... )
     */
    public function show(Category $category)
    {
        // authorizeResource ya validó policy->view()
        return response()->json(
            $category->load(['parent:id,name', 'business:id,name'])
                     ->loadCount(['children','products'])
        );
    }

    /**
     * @OA\Post( path="/api/admin/categories", ... )
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // ---- Super admin: mantiene tu validación original ----
        if ($user->hasRole('admin')) {
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

            // Límite por plan
            if (!$this->checkCategoryLimit($payload['business_id'])) {
                return response()->json(['message' => 'Has alcanzado el límite de categorías para tu plan.'], 422);
            }

            $slug = $this->uniqueSlug($payload['business_id'], $payload['slug'] ?: $payload['name']);

            $cat = Category::create([
                'business_id' => $payload['business_id'],
                'parent_id'   => $payload['parent_id'] ?? null,
                'name'        => $payload['name'],
                'slug'        => $slug,
                'position'    => $payload['position'] ?? 0,
            ]);

            return response()->json($cat->fresh(), 201);
        }

        // ---- Administrador: fuerza business_id a su negocio ----
        $bizId = $user->active_business_id;
        if (!$bizId) {
            return response()->json(['message' => 'No tienes un negocio asignado.'], 422);
        }

        $payload = $request->validate([
            // business_id no se acepta del cliente
            'parent_id'   => ['nullable','integer','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'slug'        => ['nullable','string','max:255'],
            'position'    => ['nullable','integer','min:0'],
        ]);

        if (!empty($payload['parent_id'])) {
            $parent = Category::find($payload['parent_id']);
            if (!$parent || $parent->business_id !== (int) $bizId) {
                return response()->json(['message' => 'La categoría padre debe pertenecer a tu negocio.'], 422);
            }
        }

        if (!$this->checkCategoryLimit($bizId)) {
            return response()->json(['message' => 'Has alcanzado el límite de categorías para tu plan.'], 422);
        }

        $slug = $this->uniqueSlug($bizId, $payload['slug'] ?: $payload['name']);

        $cat = Category::create([
            'business_id' => $bizId,
            'parent_id'   => $payload['parent_id'] ?? null,
            'name'        => $payload['name'],
            'slug'        => $slug,
            'position'    => $payload['position'] ?? 0,
        ]);

        return response()->json($cat->fresh(), 201);
    }

    /**
     * @OA\Patch( path="/api/admin/categories/{category}", ... )
     */
    public function update(Request $request, Category $category)
    {
        $user = $request->user();
        // authorizeResource ya validó policy->update()

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
                // Chequeo de ciclo
                $cursor = $parent;
                while ($cursor) {
                    if ($cursor->id === $category->id) {
                        return response()->json(['message' => 'Asignación inválida: ciclo en la jerarquía.'], 422);
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

        return response()->json($category->fresh()->loadCount(['children','products']));
    }

    /**
     * @OA\Delete( path="/api/admin/categories/{category}", ... )
     */
    public function destroy(Category $category)
    {
        // authorizeResource -> policy->delete
        $category->delete();
        return response()->json(null, 204);
    }

    // ========================
    // Helpers
    // ========================

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

        if (!$exists($slug)) return $slug;

        for ($i = 2; $i <= 5000; $i++) {
            $candidate = Str::limit($slug, 240, '') . '-' . $i;
            if (!$exists($candidate)) return $candidate;
        }
        return $slug . '-' . Str::random(5);
    }

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

        if (is_null($limit)) return true;

        $count = Category::where('business_id', $businessId)->count();
        return $count < (int) $limit;
    }
}