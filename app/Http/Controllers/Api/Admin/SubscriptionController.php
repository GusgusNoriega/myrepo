<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Admin - Subscriptions",
 *   description="Gestión de suscripciones de negocios a planes (requiere rol admin con guard api)"
 * )
 */
class SubscriptionController extends Controller
{
    /**
     * Listado de suscripciones (paginado + filtros).
     *
     * Filtros soportados:
     * - business_id: int
     * - plan_id: int
     * - status: trialing|active|past_due|canceled
     * - customer: string (coincide con external_customer_id LIKE)
     * - ref: string (coincide con external_ref LIKE)
     * - active_on: date-time (ISO) → suscripciones activas en esa fecha
     * - date_from / date_to: acota por rango de current_period_start / end
     * - per_page, page
     *
     * @OA\Get(
     *   path="/api/admin/subscriptions",
     *   tags={"Admin - Subscriptions"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="business_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="plan_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"trialing","active","past_due","canceled"})),
     *   @OA\Parameter(name="customer", in="query", description="external_customer_id LIKE", @OA\Schema(type="string")),
     *   @OA\Parameter(name="ref", in="query", description="external_ref LIKE", @OA\Schema(type="string")),
     *   @OA\Parameter(name="active_on", in="query", description="ISO date-time", @OA\Schema(type="string", format="date-time")),
     *   @OA\Parameter(name="date_from", in="query", description="ISO date-time", @OA\Schema(type="string", format="date-time")),
     *   @OA\Parameter(name="date_to", in="query", description="ISO date-time", @OA\Schema(type="string", format="date-time")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=20)),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "data": {{
     *           "id": 101, "business_id": 1, "plan_id": 3, "status": "active",
     *           "current_period_start": "2025-08-01T00:00:00Z",
     *           "current_period_end":   "2025-08-31T23:59:59Z",
     *           "trial_ends_at": null, "canceled_at": null, "cancel_at_period_end": false,
     *           "external_ref": "sub_ABC", "external_customer_id": "cus_123",
     *           "payment_method": {"brand":"visa","last4":"4242"},
     *           "created_at": "2025-08-01T12:00:00Z", "updated_at": "2025-08-01T12:00:00Z",
     *           "business": {"id":1,"name":"Mi Negocio"},
     *           "plan": {"id":3,"code":"starter","name":"Starter","price_usd":"9.00","billing_interval":"month"}
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
        $q = Subscription::query()->with([
            'business:id,name',
            'plan:id,code,name,price_usd,billing_interval',
        ]);

        if ($request->filled('business_id')) {
            $q->where('business_id', $request->integer('business_id'));
        }
        if ($request->filled('plan_id')) {
            $q->where('plan_id', $request->integer('plan_id'));
        }
        if ($request->filled('status')) {
            $q->where('status', $request->get('status'));
        }
        if ($customer = $request->query('customer')) {
            $q->where('external_customer_id', 'like', "%{$customer}%");
        }
        if ($ref = $request->query('ref')) {
            $q->where('external_ref', 'like', "%{$ref}%");
        }
        if ($activeOn = $request->query('active_on')) {
            $q->whereIn('status', ['trialing','active'])
              ->where('current_period_start', '<=', $activeOn)
              ->where('current_period_end',   '>=', $activeOn)
              ->where(function ($qq) use ($activeOn) {
                  $qq->whereNull('canceled_at')
                     ->orWhere('canceled_at', '>', $activeOn);
              });
        }
        if ($from = $request->query('date_from')) {
            $q->where('current_period_end', '>=', $from);
        }
        if ($to = $request->query('date_to')) {
            $q->where('current_period_start', '<=', $to);
        }

        $subs = $q->orderByDesc('created_at')->paginate(
            $request->integer('per_page', 20)
        )->appends($request->all());

        return response()->json([
            'data' => $subs->items(),
            'meta' => [
                'current_page' => $subs->currentPage(),
                'last_page'    => $subs->lastPage(),
                'per_page'     => $subs->perPage(),
                'total'        => $subs->total(),
            ],
        ]);
    }

    /**
     * Ver una suscripción por ID.
     *
     * @OA\Get(
     *   path="/api/admin/subscriptions/{subscription}",
     *   tags={"Admin - Subscriptions"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="subscription", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "id": 101, "business_id": 1, "plan_id": 3, "status":"active",
     *         "current_period_start":"2025-08-01T00:00:00Z","current_period_end":"2025-08-31T23:59:59Z",
     *         "trial_ends_at": null, "canceled_at": null, "cancel_at_period_end": false,
     *         "external_ref":"sub_ABC","external_customer_id":"cus_123",
     *         "payment_method":{"brand":"visa","last4":"4242"},
     *         "created_at":"2025-08-01T12:00:00Z","updated_at":"2025-08-01T12:00:00Z",
     *         "business":{"id":1,"name":"Mi Negocio"},
     *         "plan":{"id":3,"code":"starter","name":"Starter","price_usd":"9.00","billing_interval":"month"}
     *       }
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Subscription $subscription)
    {
        return response()->json(
            $subscription->load(['business:id,name', 'plan:id,code,name,price_usd,billing_interval'])
        );
    }

    /**
     * Crear una suscripción.
     *
     * Reglas:
     * - `current_period_end` debe ser > `current_period_start`
     * - Evitar solapamiento con otra suscripción trialing/active del mismo negocio.
     *
     * @OA\Post(
     *   path="/api/admin/subscriptions",
     *   tags={"Admin - Subscriptions"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"business_id","plan_id","status","current_period_start","current_period_end"},
     *       @OA\Property(property="business_id", type="integer", example=1),
     *       @OA\Property(property="plan_id", type="integer", example=3),
     *       @OA\Property(property="status", type="string", enum={"trialing","active","past_due","canceled"}, example="active"),
     *       @OA\Property(property="current_period_start", type="string", format="date-time", example="2025-08-01T00:00:00Z"),
     *       @OA\Property(property="current_period_end",   type="string", format="date-time", example="2025-08-31T23:59:59Z"),
     *       @OA\Property(property="trial_ends_at", type="string", format="date-time", nullable=true, example=null),
     *       @OA\Property(property="canceled_at",   type="string", format="date-time", nullable=true, example=null),
     *       @OA\Property(property="cancel_at_period_end", type="boolean", example=false),
     *       @OA\Property(property="external_ref", type="string", example="sub_ABC"),
     *       @OA\Property(property="external_customer_id", type="string", example="cus_123"),
     *       @OA\Property(property="payment_method", type="object", example={"brand":"visa","last4":"4242"})
     *     )
     *   ),
     *   @OA\Response(
     *     response=201, description="Creado",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "id": 201, "business_id": 1, "plan_id": 3, "status":"active",
     *         "current_period_start":"2025-08-01T00:00:00Z","current_period_end":"2025-08-31T23:59:59Z",
     *         "trial_ends_at": null, "canceled_at": null, "cancel_at_period_end": false,
     *         "external_ref":"sub_ABC","external_customer_id":"cus_123",
     *         "payment_method":{"brand":"visa","last4":"4242"},
     *         "created_at":"2025-08-01T12:00:00Z","updated_at":"2025-08-01T12:00:00Z",
     *         "business":{"id":1,"name":"Mi Negocio"},
     *         "plan":{"id":3,"code":"starter","name":"Starter","price_usd":"9.00","billing_interval":"month"}
     *       }
     *     )
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(
     *     response=409, description="Conflicto de solapamiento",
     *     @OA\JsonContent(type="object", example={"message":"Existe otra suscripción activa/trial que se solapa en el mismo periodo."})
     *   ),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            'business_id'           => ['required','integer','exists:businesses,id'],
            'plan_id'               => ['required','integer','exists:plans,id'],
            'status'                => ['required', Rule::in(['trialing','active','past_due','canceled'])],
            'current_period_start'  => ['required','date'],
            'current_period_end'    => ['required','date','after:current_period_start'],
            'trial_ends_at'         => ['nullable','date'],
            'canceled_at'           => ['nullable','date'],
            'cancel_at_period_end'  => ['nullable','boolean'],
            'external_ref'          => ['nullable','string'],
            'external_customer_id'  => ['nullable','string'],
            'payment_method'        => ['nullable','array'],
        ]);

        // Validación de solape si la nueva suscripción es trialing/active
        if (in_array($payload['status'], ['trialing','active'], true)) {
            $overlap = Subscription::where('business_id', $payload['business_id'])
                ->whereIn('status', ['trialing','active'])
                ->where(function ($qq) use ($payload) {
                    $qq->where('current_period_start', '<=', $payload['current_period_end'])
                       ->where('current_period_end',   '>=', $payload['current_period_start']);
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'message' => 'Existe otra suscripción activa/trial que se solapa en el mismo periodo.'
                ], 409);
            }
        }

        $sub = Subscription::create($payload);

        return response()->json(
            $sub->fresh()->load(['business:id,name', 'plan:id,code,name,price_usd,billing_interval']),
            201
        );
    }

    /**
     * Actualizar una suscripción.
     *
     * Puede cambiar plan, estado y fechas. Se valida que no haya solape
     * trial/active con otras suscripciones del mismo negocio.
     *
     * @OA\Patch(
     *   path="/api/admin/subscriptions/{subscription}",
     *   tags={"Admin - Subscriptions"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="subscription", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="plan_id", type="integer", example=4),
     *       @OA\Property(property="status", type="string", enum={"trialing","active","past_due","canceled"}, example="past_due"),
     *       @OA\Property(property="current_period_start", type="string", format="date-time", example="2025-09-01T00:00:00Z"),
     *       @OA\Property(property="current_period_end",   type="string", format="date-time", example="2025-09-30T23:59:59Z"),
     *       @OA\Property(property="trial_ends_at", type="string", format="date-time", nullable=true, example=null),
     *       @OA\Property(property="canceled_at",   type="string", format="date-time", nullable=true, example=null),
     *       @OA\Property(property="cancel_at_period_end", type="boolean", example=false),
     *       @OA\Property(property="external_ref", type="string", example="sub_DEF"),
     *       @OA\Property(property="external_customer_id", type="string", example="cus_456"),
     *       @OA\Property(property="payment_method", type="object", example={"brand":"mastercard","last4":"5555"})
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(type="object",
     *       example={
     *         "id": 101, "business_id": 1, "plan_id": 4, "status":"past_due",
     *         "current_period_start":"2025-09-01T00:00:00Z","current_period_end":"2025-09-30T23:59:59Z",
     *         "trial_ends_at": null, "canceled_at": null, "cancel_at_period_end": false,
     *         "external_ref":"sub_DEF","external_customer_id":"cus_456",
     *         "payment_method":{"brand":"mastercard","last4":"5555"},
     *         "created_at":"2025-08-01T12:00:00Z","updated_at":"2025-09-01T12:00:00Z",
     *         "business":{"id":1,"name":"Mi Negocio"},
     *         "plan":{"id":4,"code":"pro","name":"Pro","price_usd":"29.00","billing_interval":"month"}
     *       }
     *     )
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(
     *     response=409, description="Conflicto de solapamiento",
     *     @OA\JsonContent(type="object", example={"message":"Existe otra suscripción activa/trial que se solapa en el mismo periodo."})
     *   ),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(Request $request, Subscription $subscription)
    {
        $payload = $request->validate([
            'plan_id'               => ['sometimes','integer','exists:plans,id'],
            'status'                => ['sometimes', Rule::in(['trialing','active','past_due','canceled'])],
            'current_period_start'  => ['sometimes','date'],
            'current_period_end'    => ['sometimes','date','after:current_period_start'],
            'trial_ends_at'         => ['sometimes','nullable','date'],
            'canceled_at'           => ['sometimes','nullable','date'],
            'cancel_at_period_end'  => ['sometimes','boolean'],
            'external_ref'          => ['sometimes','nullable','string'],
            'external_customer_id'  => ['sometimes','nullable','string'],
            'payment_method'        => ['sometimes','nullable','array'],
        ]);

        // Pre-evaluar datos finales para validación de solape
        $businessId = $subscription->business_id;
        $status     = $payload['status']               ?? $subscription->status;
        $start      = $payload['current_period_start'] ?? optional($subscription->current_period_start)->toISOString();
        $end        = $payload['current_period_end']   ?? optional($subscription->current_period_end)->toISOString();

        if ($status && in_array($status, ['trialing','active'], true) && $start && $end) {
            $overlap = Subscription::where('business_id', $businessId)
                ->where('id', '!=', $subscription->id)
                ->whereIn('status', ['trialing','active'])
                ->where(function ($qq) use ($start, $end) {
                    $qq->where('current_period_start', '<=', $end)
                       ->where('current_period_end',   '>=', $start);
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'message' => 'Existe otra suscripción activa/trial que se solapa en el mismo periodo.'
                ], 409);
            }
        }

        $subscription->fill($payload);
        $subscription->save();

        return response()->json(
            $subscription->fresh()->load(['business:id,name', 'plan:id,code,name,price_usd,billing_interval'])
        );
    }

    /**
     * Eliminar una suscripción.
     *
     * @OA\Delete(
     *   path="/api/admin/subscriptions/{subscription}",
     *   tags={"Admin - Subscriptions"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="subscription", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Eliminado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return response()->json(null, 204);
    }
}
