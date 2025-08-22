<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class BusinessController extends Controller
{
    public function __construct()
    {
        // Aplica la BusinessPolicy automáticamente en show/update/destroy, etc.
        $this->authorizeResource(Business::class, 'business');
    }

    /**
     * @OA\Get(
     *   path="/api/admin/businesses",
     *   tags={"Admin - Businesses"},
     *   security={{"bearerAuth":{}}}, ...
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Business::query();

        // ---- Scoping para el rol "administrador" (NO super admin) ----
        if ($user->hasRole('administrador') && !$user->hasRole('admin')) {
            // fuerza a su business asignado e ignora filtros
            $activeId = $user->active_business_id;
            if (!$activeId) {
                return response()->json([
                    'data' => [],
                    'meta' => ['current_page'=>1,'last_page'=>1,'per_page'=>20,'total'=>0],
                ]);
            }
            $q->where('id', $activeId);
        } else {
            // ---- El super admin mantiene filtros completos ----
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

    public function show(Business $business)
    {
        // authorizeResource ya llamó a la policy->view()
        return response()->json($business);
    }

    public function store(Request $request)
    {
        // Solo super admin (policy->create) — si llega aquí, está autorizado
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

    public function update(Request $request, Business $business)
    {
        $user = $request->user();
        // authorizeResource ya llamó a policy->update()

        // ---- Super admin: validación completa (tal como tenías) ----
        if ($user->hasRole('admin')) {
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
        } else {
            // ---- Administrador: SOLO puede tocar campos "seguros" (whitelist) ----
            // Ajusta esta lista a tu gusto
            $payload = $request->validate([
                'name'          => ['sometimes','string','max:255'],
                'timezone'      => ['sometimes','nullable','string','max:64'],
                'locale'        => ['sometimes','nullable','string','max:10'],
                'contact_name'  => ['sometimes','nullable','string','max:255'],
                'contact_email' => ['sometimes','nullable','email','max:255'],
                'settings'      => ['sometimes','nullable','array'],
                // NO: owner_user_id, slug, domain, subdomain, country_code, currency, is_active
            ]);
        }

        // Normalizaciones comunes
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

        // Slug único (solo si super admin lo envía o si se regenerase)
        if (array_key_exists('slug', $payload)) {
            if (!$payload['slug']) {
                $nameForSlug = $payload['name'] ?? $business->name;
                $payload['slug'] = $this->uniqueSlug($nameForSlug, $business->id);
            } else {
                $payload['slug'] = $this->uniqueSlug($payload['slug'], $business->id);
            }
        }

        // Default USD si nunca se seteó (conservamos tu lógica)
        if (!array_key_exists('currency', $payload) && !$business->currency && $user->hasRole('admin')) {
            $payload['currency'] = 'USD';
        }

        $business->fill($payload)->save();

        return response()->json($business->fresh());
    }

    public function destroy(Business $business)
    {
        // authorizeResource -> policy->delete
        $business->delete();
        return response()->json(null, 204);
    }

    // ---------------------------
    // Endpoints opcionales "mi negocio"
    // ---------------------------

    /**
     * @OA\Get(
     *   path="/api/admin/my-business",
     *   tags={"Admin - Businesses"},
     *   security={{"bearerAuth":{}}}
     * )
     */
    public function myBusiness(Request $request)
    {
        $user = $request->user();
        $bizId = $user->active_business_id;
        if (!$bizId) {
            return response()->json(['message' => 'No tiene un negocio asignado.'], 404);
        }
        $business = Business::findOrFail($bizId);
        $this->authorize('view', $business);

        return response()->json($business);
    }

    /**
     * @OA\Patch(
     *   path="/api/admin/my-business",
     *   tags={"Admin - Businesses"},
     *   security={{"bearerAuth":{}}}
     * )
     */
    public function updateMyBusiness(Request $request)
    {
        $user = $request->user();
        $bizId = $user->active_business_id;
        if (!$bizId) {
            return response()->json(['message' => 'No tiene un negocio asignado.'], 404);
        }
        $business = Business::findOrFail($bizId);
        $this->authorize('update', $business);

        // MISMA whitelist que en update para "administrador"
        $payload = $request->validate([
            'name'          => ['sometimes','string','max:255'],
            'timezone'      => ['sometimes','nullable','string','max:64'],
            'locale'        => ['sometimes','nullable','string','max:10'],
            'contact_name'  => ['sometimes','nullable','string','max:255'],
            'contact_email' => ['sometimes','nullable','email','max:255'],
            'settings'      => ['sometimes','nullable','array'],
        ]);

        $business->fill($payload)->save();

        return response()->json($business->fresh());
    }

    // Helpers...
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
