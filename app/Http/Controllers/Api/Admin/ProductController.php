<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\MediaLink;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use OpenApi\Annotations as OA;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product', [
            'except' => ['show'],
        ]);
        $this->middleware('auth:api')->except(['show', 'publicIndexByBusiness']);

    }

    // ========== LIST ==========
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Product::query();

        // ---- Scoping para "administrador"
        if ($user->hasRole('administrador') && !$user->hasRole('admin')) {
            $bizId = (int) $user->active_business_id;
            if (!$bizId) {
                return response()->json([
                    'data' => [],
                    'meta' => ['current_page'=>1,'last_page'=>1,'per_page'=>$request->integer('per_page',20),'total'=>0],
                ]);
            }
            $q->where('business_id', $bizId);
        } else {
            // ---- Super admin: filtros completos
            if ($request->filled('business_id')) {
                $q->where('business_id', $request->integer('business_id'));
            }
        }

        if ($s = $request->query('search')) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name','like',"%{$s}%")
                   ->orWhere('sku','like',"%{$s}%")
                   ->orWhere('slug','like',"%{$s}%");
            });
        }

        $p = $q->orderByDesc('created_at')->paginate(
            $request->integer('per_page', 20)
        )->appends($request->all());

        $data = collect($p->items())->map(function (Product $product) {
            return $this->serializeProduct($product);
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'per_page'     => $p->perPage(),
                'total'        => $p->total(),
            ],
        ]);
    }

    // ========== SHOW ==========
    public function show(Product $product)
    {
        // authorizeResource ya validó policy->view()
        return response()->json($this->serializeProduct($product));
    }

    // ========== STORE ==========
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            // ---- Super admin: business_id viene en el payload
            $bizId = (int) $request->integer('business_id');
            $data  = $this->validatePayload($request, false, null, $bizId, true);
            $this->assertCategoryBelongsToBusiness($data['category_id'] ?? null, $bizId);
        } else {
            // ---- Administrador: fuerza business_id al suyo y NO acepta business_id del cliente
            $bizId = (int) $user->active_business_id;
            if (!$bizId) return response()->json(['message' => 'No tienes un negocio asignado.'], 422);

            // Clon seguro del request con business_id forzado
            $req = clone $request;
            $req->merge(['business_id' => $bizId]);
            $data = $this->validatePayload($req, false, null, $bizId, false);
            $this->assertCategoryBelongsToBusiness($data['category_id'] ?? null, $bizId);
        }

        return DB::transaction(function () use ($data, $bizId) {
            $product = Product::create(collect($data)->except([
                'featured_media_id','gallery_media_ids',
            ])->toArray());

            $this->syncFeatured($product, $data['featured_media_id'] ?? null);
            $this->syncGallery($product, $data['gallery_media_ids'] ?? null);

            $this->adjustProductsCounter($bizId, +1);

            return response()->json($this->serializeProduct($product->fresh()), 201);
        });
    }

    // ========== UPDATE ==========
    public function update(Request $request, Product $product)
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            // ---- Admin puede cambiar business_id
            $bizId = (int) ($request->integer('business_id') ?: $product->business_id);
            $data  = $this->validatePayload($request, true, $product, $bizId, true);
            $this->assertCategoryBelongsToBusiness($data['category_id'] ?? null, $bizId);
        } else {
            // ---- Administrador: prohibido cambiar business_id
            if ($request->filled('business_id') && (int)$request->integer('business_id') !== (int)$product->business_id) {
                return response()->json(['message' => 'No puedes cambiar el negocio del producto.'], 403);
            }
            $bizId = (int) $product->business_id;
            $req   = clone $request;
            $req->merge(['business_id' => $bizId]); // para reglas de unique
            $data  = $this->validatePayload($req, true, $product, $bizId, false);
            $this->assertCategoryBelongsToBusiness($data['category_id'] ?? null, $bizId);
        }

        return DB::transaction(function () use ($data, $product) {
            $oldBizId = (int) $product->getOriginal('business_id');

            $product->fill(collect($data)->except(['featured_media_id','gallery_media_ids'])->toArray());
            $product->save();

            if (array_key_exists('featured_media_id', $data)) {
                $this->syncFeatured($product, $data['featured_media_id']);
            }
            if (array_key_exists('gallery_media_ids', $data)) {
                $this->syncGallery($product, $data['gallery_media_ids']);
            }

            $this->pruneEmptyLinks($product);

            $newBizId = (int) $product->business_id;
            if ($newBizId !== $oldBizId) {
                $this->adjustProductsCounter($oldBizId, -1);
                $this->adjustProductsCounter($newBizId, +1);
            }

            return response()->json($this->serializeProduct($product->fresh()));
        });
    }

    // ========== DESTROY ==========
    public function destroy(Product $product)
    {
        // authorizeResource -> policy->delete
        DB::transaction(function () use ($product) {
            $bizId = (int) $product->business_id;
            $product->mediaLinks()->delete();
            $product->delete();
            $this->adjustProductsCounter($bizId, -1);
        });

        return response()->json(null, 204);
    }

    // -----------------------------
    // Helpers
    // -----------------------------

    /**
     * $bizId: negocio efectivo para las reglas de unicidad.
     * $allowBizInput: si true, el payload puede traer business_id (admin); si false, lo ignoramos (administrador).
     */
    private function validatePayload(Request $request, bool $isUpdate, ?Product $product, int $bizId, bool $allowBizInput): array
    {
        $id = $product?->id;

        // Si no permitimos input de business, validamos que NO cambie
        $businessRule = $allowBizInput
            ? ['required','integer','exists:businesses,id']
            : ['required','integer','in:'.$bizId]; // obliga al biz efectivo

        return $request->validate([
            'business_id' => $businessRule,
            'category_id' => ['nullable','integer','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'slug'        => [
                'required','string','max:255',
                Rule::unique('products','slug')->ignore($id)
                    ->where(fn($q)=>$q->where('business_id', $bizId)),
            ],
            'sku'         => [
                'required','string','max:255',
                Rule::unique('products','sku')->ignore($id)
                    ->where(fn($q)=>$q->where('business_id', $bizId)),
            ],
            'barcode'                => ['nullable','string','max:64'],
            'description'            => ['nullable','string'],
            'status'                 => ['required', Rule::in(['draft','active','archived'])],
            'has_variants'           => ['required','boolean'],
            'price_cents'            => ['nullable','integer','min:0'],
            'cost_cents'             => ['nullable','integer','min:0'],
            'compare_at_price_cents' => ['nullable','integer','min:0'],
            'currency'               => ['required','string','size:3'],
            'tax_included'           => ['required','boolean'],
            'attributes'             => ['nullable','array'],
            'weight_grams'           => ['nullable','integer','min:0'],
            'dimensions'             => ['nullable','array'],
            'published_at'           => ['nullable','date'],

            // Media
            'featured_media_id'  => ['nullable','integer','exists:media,id'],
            'gallery_media_ids'  => ['nullable'], // CSV "11,12,13"
        ]);
    }

    /**
     * category_id (si se manda) debe ser del mismo negocio.
     */
    private function assertCategoryBelongsToBusiness(?int $categoryId, int $bizId): void
    {
        if (!$categoryId) return;
        $ok = Category::where('id', $categoryId)->where('business_id', $bizId)->exists();
        if (!$ok) {
            abort(422, 'La categoría indicada no pertenece al negocio.');
        }
    }

    private function syncFeatured(Product $product, $mediaIdOrNull): void
    {
        $product->mediaLinks()->update(['is_featured' => false]);

        if (!$mediaIdOrNull) return;

        $media = Media::query()->findOrFail($mediaIdOrNull);

        // ---- Si Media tiene business_id, debe coincidir
        if (isset($media->business_id) && (int)$media->business_id !== (int)$product->business_id) {
            abort(422, 'La media seleccionada no pertenece a este negocio.');
        }

        $link = MediaLink::firstOrNew([
            'linkable_type' => Product::class,
            'linkable_id'   => $product->id,
            'media_id'      => $media->id,
        ]);
        $link->business_id = $product->business_id;
        $link->is_featured = true;
        $link->save();
    }

    private function parseGalleryIds($input)
    {
        if ($input === null) {
            return null;
        }
        if (is_array($input)) {
            $arr = $input;
        } else {
            $s = trim((string) $input);
            if ($s === '') {
                $arr = [];
            } elseif (str_starts_with($s, '[') && str_ends_with($s, ']')) {
                $decoded = json_decode($s, true);
                $arr = is_array($decoded) ? $decoded : [];
            } else {
                $arr = array_map('trim', explode(',', $s));
            }
        }
        return collect($arr)
            ->filter(fn($v) => $v !== '' && is_numeric($v))
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values();
    }

    private function syncGallery(Product $product, $raw): void
    {
        if ($raw === null) return;

        $product->mediaLinks()->update(['is_gallery' => false, 'position' => null]);

        $ids = $this->parseGalleryIds($raw);

        foreach ($ids as $i => $mediaId) {
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::query()->find($mediaId);
            if (!$media) continue;

            if (isset($media->business_id) && (int)$media->business_id !== (int)$product->business_id) {
                abort(422, 'Una de las medias de la galería no pertenece a este negocio.');
            }

            $link = \App\Models\MediaLink::firstOrNew([
                'linkable_type' => \App\Models\Product::class,
                'linkable_id'   => $product->id,
                'media_id'      => $media->id,
            ]);
            $link->business_id = $product->business_id;
            $link->is_gallery  = true;
            $link->position    = $i + 1;
            $link->save();
        }
    }

    private function pruneEmptyLinks(Product $product): void
    {
        $product->mediaLinks()
            ->where('is_featured', false)
            ->where('is_gallery', false)
            ->delete();
    }

    private function serializeProduct(Product $product): array
    {
        $product->loadMissing([
            'featuredMediaLink.media',
            'galleryMediaLinks.media',
        ]);

        $featured = optional($product->featuredMediaLink()->first()?->media, function (Media $m) {
            return ['id'=>$m->id,'name'=>$m->name,'url'=>$m->getFullUrl()];
        });

        $gallery = $product->galleryMediaLinks()->get()->map(function (MediaLink $link) {
            $m = $link->media;
            if (!$m) return null;
            return ['id'=>$m->id,'name'=>$m->name,'url'=>$m->getFullUrl(),'position'=>$link->position];
        })->filter()->values()->all();

        return array_merge($product->toArray(), [
            'featured_media' => $featured ?: null,
            'gallery_media'  => $gallery,
        ]);
    }

    // =========================
    // USAGE COUNTERS (tu lógica)
    // =========================
    private function ensureUsageRow(int $businessId): void
    {
        DB::table('usage_counters')->updateOrInsert(
            ['business_id' => $businessId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    private function adjustProductsCounter(int $businessId, int $delta): void
    {
        $this->ensureUsageRow($businessId);
        DB::table('usage_counters')
            ->where('business_id', $businessId)
            ->update([
                'products_count' => DB::raw('GREATEST(0, products_count + ('.(int)$delta.'))'),
                'updated_at'     => now(),
            ]);
    }

    /**
     * Listado PÚBLICO de productos por negocio (sin auth).
     * GET /api/public/businesses/{business}/products
     */
    public function publicIndexByBusiness(Request $request, int $business)
    {
        // Validación de filtros
        $request->validate([
            'per_page'         => ['nullable','integer','min:1','max:100'],
            'search'           => ['nullable','string'],
            'category_id'      => ['nullable','integer','exists:categories,id'],
            'price_cents_min'  => ['nullable','integer','min:0'],
            'price_cents_max'  => ['nullable','integer','min:0'],
            'sort'             => ['nullable', Rule::in(['newest','price_asc','price_desc','name_asc'])],
        ]);

        $q = Product::query()
            ->where('business_id', $business)
            ->where('status', 'active'); // evita exponer borradores/archivados

        if ($s = $request->query('search')) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name','like',"%{$s}%")
                ->orWhere('sku','like',"%{$s}%")
                ->orWhere('slug','like',"%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $q->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('price_cents_min')) {
            $q->where('price_cents', '>=', $request->integer('price_cents_min'));
        }
        if ($request->filled('price_cents_max')) {
            $q->where('price_cents', '<=', $request->integer('price_cents_max'));
        }

        // Ordenamiento
        switch ($request->query('sort')) {
            case 'price_asc':  $q->orderBy('price_cents', 'asc'); break;
            case 'price_desc': $q->orderBy('price_cents', 'desc'); break;
            case 'name_asc':   $q->orderBy('name', 'asc'); break;
            default:           $q->orderByDesc('created_at'); // newest
        }

        $p = $q->paginate($request->integer('per_page', 20))
            ->appends($request->all());

        $data = collect($p->items())->map(function (Product $product) {
            return $this->serializeProduct($product);
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'per_page'     => $p->perPage(),
                'total'        => $p->total(),
            ],
        ]);
    }
}