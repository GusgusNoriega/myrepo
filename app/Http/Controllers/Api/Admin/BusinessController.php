<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Admin - Businesses",
 *   description="Gestión de negocios (requiere rol admin con guard api)"
 * )
 */
class BusinessController extends Controller
{
    /**
     * Listado de negocios (paginado + filtros).
     *
     * Filtros:
     * - owner_user_id: int
     * - active: 1|0
     * - search: coincide con name/slug/domain/subdomain/contact_email
     * - per_page, page
     *
     * @OA\Get(
     *   path="/api/admin/businesses",
     *   tags={"Admin - Businesses"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="owner_user_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="active", in="query", @OA\Schema(type="boolean")),
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=20)),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "data": {{
     *           "id": 1,
     *           "owner_user_id": 10,
     *           "name": "Mi Tienda",
     *           "slug": "mi-tienda",
     *           "domain": "mitienda.com",
     *           "subdomain": "store",
     *           "country_code": "PE",
     *           "currency": "USD",
     *           "timezone": "America/Lima",
     *           "locale": "es_PE",
     *           "contact_name": "Juan",
     *           "contact_email": "soporte@mitienda.com",
     *           "settings": {"theme":"light"},
     *           "is_active": true,
     *           "created_at": "2025-08-01T12:00:00Z",
     *           "updated_at": "2025-08-01T12:00:00Z"
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
        $q = Business::query();

        if ($request->filled('owner_user_id')) {
            $q->where('owner_user_id', $request->integer('owner_user_id'));
        }
        if ($request->filled('active')) {
            $q->where('is_active', (int) (bool) $request->boolean('active'));
        }
        if ($s = $request->query('search')) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('slug', 'like', "%{$s}%")
                   ->orWhere('domain', 'like', "%{$s}%")
                   ->orWhere('subdomain', 'like', "%{$s}%")
                   ->orWhere('contact_email', 'like', "%{$s}%");
            });
        }

        $biz = $q->orderByDesc('created_at')->paginate(
            $request->integer('per_page', 20)
        )->appends($request->all());

        return response()->json([
            'data' => $biz->items(),
            'meta' => [
                'current_page' => $biz->currentPage(),
                'last_page'    => $biz->lastPage(),
                'per_page'     => $biz->perPage(),
                'total'        => $biz->total(),
            ],
        ]);
    }

    /**
     * Mostrar un negocio por ID.
     *
     * @OA\Get(
     *   path="/api/admin/businesses/{business}",
     *   tags={"Admin - Businesses"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="business", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "id": 1, "owner_user_id": 10, "name": "Mi Tienda", "slug": "mi-tienda",
     *         "domain": "mitienda.com", "subdomain":"store", "country_code":"PE",
     *         "currency":"USD", "timezone":"America/Lima", "locale":"es_PE",
     *         "contact_name":"Juan", "contact_email":"soporte@mitienda.com",
     *         "settings":{"theme":"light"}, "is_active":true,
     *         "created_at":"2025-08-01T12:00:00Z","updated_at":"2025-08-01T12:00:00Z"
     *       }
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Business $business)
    {
        return response()->json($business);
    }

    /**
     * Crear un negocio.
     *
     * Notas:
     * - Si no envías `slug`, se genera desde `name` y se garantiza unicidad.
     * - `domain`/`subdomain` se guardan en minúsculas.
     * - `currency`/`country_code` se normalizan en mayúsculas.
     *
     * @OA\Post(
     *   path="/api/admin/businesses",
     *   tags={"Admin - Businesses"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"name"},
     *       @OA\Property(property="owner_user_id", type="integer", nullable=true, example=10),
     *       @OA\Property(property="name", type="string", example="Mi Tienda"),
     *       @OA\Property(property="slug", type="string", nullable=true, example="mi-tienda"),
     *       @OA\Property(property="domain", type="string", nullable=true, example="mitienda.com"),
     *       @OA\Property(property="subdomain", type="string", nullable=true, example="store"),
     *       @OA\Property(property="country_code", type="string", nullable=true, example="PE"),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="timezone", type="string", nullable=true, example="America/Lima"),
     *       @OA\Property(property="locale", type="string", nullable=true, example="es_PE"),
     *       @OA\Property(property="contact_name", type="string", nullable=true, example="Juan"),
     *       @OA\Property(property="contact_email", type="string", nullable=true, example="soporte@mitienda.com"),
     *       @OA\Property(property="settings", type="object", nullable=true, example={"theme":"light"}),
     *       @OA\Property(property="is_active", type="boolean", example=true)
     *     )
     *   ),
     *   @OA\Response(
     *     response=201, description="Creado",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "id": 1, "owner_user_id": 10, "name": "Mi Tienda", "slug": "mi-tienda",
     *         "domain": "mitienda.com", "subdomain":"store", "country_code":"PE",
     *         "currency":"USD", "timezone":"America/Lima", "locale":"es_PE",
     *         "contact_name":"Juan", "contact_email":"soporte@mitienda.com",
     *         "settings":{"theme":"light"}, "is_active":true,
     *         "created_at":"2025-08-01T12:00:00Z","updated_at":"2025-08-01T12:00:00Z"
     *       }
     *     )
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            'owner_user_id' => ['nullable','integer','exists:users,id'],
            'name'          => ['required','string','max:255'],
            'slug'          => ['nullable','string','max:255', 'unique:businesses,slug'],
            'domain'        => ['nullable','string','max:255', 'unique:businesses,domain', 'regex:/^(?!https?:\/\/)[A-Za-z0-9.-]+$/'],
            'subdomain'     => ['nullable','string','max:255', 'unique:businesses,subdomain', 'regex:/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/'],
            'country_code'  => ['nullable','string','size:2'],
            'currency'      => ['nullable','string','size:3'],
            'timezone'      => ['nullable','string','max:64'],
            'locale'        => ['nullable','string','max:10'],
            'contact_name'  => ['nullable','string','max:255'],
            'contact_email' => ['nullable','email','max:255'],
            'settings'      => ['nullable','array'],
            'is_active'     => ['nullable','boolean'],
        ]);

        // Normalizar
        $domain    = isset($payload['domain'])    ? strtolower($payload['domain']) : null;
        $subdomain = isset($payload['subdomain']) ? strtolower($payload['subdomain']) : null;
        $currency  = strtoupper($payload['currency'] ?? 'USD');
        $cc        = isset($payload['country_code']) ? strtoupper($payload['country_code']) : null;

        $slug = $payload['slug'] ?? $payload['name'];
        $slug = $this->uniqueSlug($slug);

        $business = Business::create([
            'owner_user_id' => $payload['owner_user_id'] ?? null,
            'name'          => $payload['name'],
            'slug'          => $slug,
            'domain'        => $domain,
            'subdomain'     => $subdomain,
            'country_code'  => $cc,
            'currency'      => $currency,
            'timezone'      => $payload['timezone'] ?? null,
            'locale'        => $payload['locale'] ?? null,
            'contact_name'  => $payload['contact_name'] ?? null,
            'contact_email' => $payload['contact_email'] ?? null,
            'settings'      => $payload['settings'] ?? null,
            'is_active'     => (bool) ($payload['is_active'] ?? 1),
        ]);

        return response()->json($business->fresh(), 201);
    }

    /**
     * Actualizar un negocio.
     *
     * - Si envías `slug` vacío o null, se regenera desde `name` actual.
     * - Los `unique` ignoran el propio ID.
     *
     * @OA\Patch(
     *   path="/api/admin/businesses/{business}",
     *   tags={"Admin - Businesses"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="business", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="owner_user_id", type="integer", nullable=true, example=10),
     *       @OA\Property(property="name", type="string", example="Mi Tienda Perú"),
     *       @OA\Property(property="slug", type="string", nullable=true, example="mi-tienda-peru"),
     *       @OA\Property(property="domain", type="string", nullable=true, example="mitiendaperu.com"),
     *       @OA\Property(property="subdomain", type="string", nullable=true, example="store"),
     *       @OA\Property(property="country_code", type="string", nullable=true, example="PE"),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="timezone", type="string", nullable=true, example="America/Lima"),
     *       @OA\Property(property="locale", type="string", nullable=true, example="es_PE"),
     *       @OA\Property(property="contact_name", type="string", nullable=true, example="Juan"),
     *       @OA\Property(property="contact_email", type="string", nullable=true, example="soporte@mitienda.com"),
     *       @OA\Property(property="settings", type="object", nullable=true, example={"theme":"dark"}),
     *       @OA\Property(property="is_active", type="boolean", example=true)
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "id": 1, "owner_user_id": 10, "name": "Mi Tienda Perú", "slug": "mi-tienda-peru",
     *         "domain": "mitiendaperu.com", "subdomain":"store", "country_code":"PE",
     *         "currency":"USD", "timezone":"America/Lima", "locale":"es_PE",
     *         "contact_name":"Juan", "contact_email":"soporte@mitienda.com",
     *         "settings":{"theme":"dark"}, "is_active":true,
     *         "created_at":"2025-08-01T12:00:00Z","updated_at":"2025-08-02T12:00:00Z"
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
    public function update(Request $request, Business $business)
    {
        $payload = $request->validate([
            'owner_user_id' => ['sometimes','nullable','integer','exists:users,id'],
            'name'          => ['sometimes','string','max:255'],
            'slug'          => ['sometimes','nullable','string','max:255', Rule::unique('businesses','slug')->ignore($business->id)],
            'domain'        => ['sometimes','nullable','string','max:255', Rule::unique('businesses','domain')->ignore($business->id), 'regex:/^(?!https?:\/\/)[A-Za-z0-9.-]+$/'],
            'subdomain'     => ['sometimes','nullable','string','max:255', Rule::unique('businesses','subdomain')->ignore($business->id), 'regex:/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/'],
            'country_code'  => ['sometimes','nullable','string','size:2'],
            'currency'      => ['sometimes','nullable','string','size:3'],
            'timezone'      => ['sometimes','nullable','string','max:64'],
            'locale'        => ['sometimes','nullable','string','max:10'],
            'contact_name'  => ['sometimes','nullable','string','max:255'],
            'contact_email' => ['sometimes','nullable','email','max:255'],
            'settings'      => ['sometimes','nullable','array'],
            'is_active'     => ['sometimes','boolean'],
        ]);

        // Normalizar campos si están presentes
        if (array_key_exists('domain', $payload)) {
            $payload['domain'] = $payload['domain'] ? strtolower($payload['domain']) : null;
        }
        if (array_key_exists('subdomain', $payload)) {
            $payload['subdomain'] = $payload['subdomain'] ? strtolower($payload['subdomain']) : null;
        }
        if (array_key_exists('country_code', $payload)) {
            $payload['country_code'] = $payload['country_code'] ? strtoupper($payload['country_code']) : null;
        }
        if (array_key_exists('currency', $payload)) {
            $payload['currency'] = $payload['currency'] ? strtoupper($payload['currency']) : null;
        }

        // Slug: si se envía y viene vacío/null, regenerar desde name actual/entrante
        if (array_key_exists('slug', $payload)) {
            if (!$payload['slug']) {
                $nameForSlug = $payload['name'] ?? $business->name;
                $payload['slug'] = $this->uniqueSlug($nameForSlug, $business->id);
            } else {
                // Garantiza unicidad transformando si es necesario
                $payload['slug'] = $this->uniqueSlug($payload['slug'], $business->id);
            }
        }

        // Si no enviaron currency, mantener la actual; si nunca se seteo, default USD
        if (!array_key_exists('currency', $payload) && !$business->currency) {
            $payload['currency'] = 'USD';
        }

        $business->fill($payload);
        $business->save();

        return response()->json($business->fresh());
    }

    /**
     * Eliminar un negocio.
     *
     * Efectos por FKs:
     * - categories/products/subscriptions/media/etc. → CASCADE
     * - users.active_business_id → SET NULL
     *
     * @OA\Delete(
     *   path="/api/admin/businesses/{business}",
     *   tags={"Admin - Businesses"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="business", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Eliminado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Business $business)
    {
        $business->delete();
        return response()->json(null, 204);
    }

    // ========================
    // Helpers
    // ========================

    /**
     * Genera un slug único global para businesses.
     * Si $excludeId se pasa, ignora ese registro (para update).
     */
    private function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = Str::slug(Str::limit($base, 255, ''));
        $slug = $slug !== '' ? $slug : Str::random(8);

        $exists = function ($candidate) use ($excludeId) {
            return Business::where('slug', $candidate)
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
}