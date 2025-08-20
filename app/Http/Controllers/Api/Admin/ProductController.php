<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\MediaLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Admin - Products",
 *   description="Gestión de productos (requiere token y permisos de admin)."
 * )
 */
class ProductController extends Controller
{
    /**
     * Listar productos (paginado + filtros).
     *
     * @OA\Get(
     *   path="/api/admin/products",
     *   operationId="AdminProductsIndex",
     *   tags={"Admin - Products"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="business_id",
     *     in="query",
     *     required=false,
     *     description="Filtra por negocio",
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="search",
     *     in="query",
     *     required=false,
     *     description="Buscar por name/sku/slug",
     *     @OA\Schema(type="string", example="camiseta")
     *   ),
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     required=false,
     *     description="Tamaño de página (default 20)",
     *     @OA\Schema(type="integer", example=20)
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     required=false,
     *     description="Página a consultar",
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Listado paginado de productos con featured y galería (name/url).",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="business_id", type="integer"),
     *           @OA\Property(property="category_id", type="integer", nullable=true),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="slug", type="string"),
     *           @OA\Property(property="sku", type="string"),
     *           @OA\Property(property="barcode", type="string", nullable=true),
     *           @OA\Property(property="description", type="string", nullable=true),
     *           @OA\Property(property="status", type="string", enum={"draft","active","archived"}),
     *           @OA\Property(property="has_variants", type="boolean"),
     *           @OA\Property(property="price_cents", type="integer", nullable=true),
     *           @OA\Property(property="cost_cents", type="integer", nullable=true),
     *           @OA\Property(property="compare_at_price_cents", type="integer", nullable=true),
     *           @OA\Property(property="currency", type="string", example="USD"),
     *           @OA\Property(property="tax_included", type="boolean"),
     *           @OA\Property(property="attributes", type="object", nullable=true),
     *           @OA\Property(property="weight_grams", type="integer", nullable=true),
     *           @OA\Property(property="dimensions", type="object", nullable=true),
     *           @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *           @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *           @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *           @OA\Property(
     *             property="featured_media",
     *             type="object",
     *             nullable=true,
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="url", type="string")
     *           ),
     *           @OA\Property(
     *             property="gallery_media",
     *             type="array",
     *             @OA\Items(
     *               type="object",
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="name", type="string"),
     *               @OA\Property(property="url", type="string"),
     *               @OA\Property(property="position", type="integer", nullable=true)
     *             )
     *           )
     *         )
     *       ),
     *       @OA\Property(
     *         property="meta",
     *         type="object",
     *         @OA\Property(property="current_page", type="integer", example=1),
     *         @OA\Property(property="last_page", type="integer", example=3),
     *         @OA\Property(property="per_page", type="integer", example=20),
     *         @OA\Property(property="total", type="integer", example=45)
     *       )
     *     )
     *   )
     * )
     */
    // ========== LIST ==========
    public function index(Request $request)
    {
        $q = Product::query();

        if ($request->filled('business_id')) {
            $q->where('business_id', $request->integer('business_id'));
        }
        if ($s = $request->query('search')) {
               $q->where(function ($qq) use ($s) {
                    $qq->where('name','like',"$s%")
                    ->orWhere('sku','like',"$s%")
                    ->orWhere('slug','like',"$s%");
                });
        }

        $p = $q->orderByDesc('created_at')->paginate(
            $request->integer('per_page', 20)
        )->appends($request->all());

        // Mapear medias “ligeras”
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

    /**
     * Mostrar un producto por ID (incluye featured y galería).
     *
     * @OA\Get(
     *   path="/api/admin/products/{product}",
     *   operationId="AdminProductsShow",
     *   tags={"Admin - Products"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="product", in="path", required=true, description="ID del producto",
     *     @OA\Schema(type="integer", example=15)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Producto con featured y galería (name/url).",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="business_id", type="integer"),
     *       @OA\Property(property="category_id", type="integer", nullable=true),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="slug", type="string"),
     *       @OA\Property(property="sku", type="string"),
     *       @OA\Property(property="barcode", type="string", nullable=true),
     *       @OA\Property(property="description", type="string", nullable=true),
     *       @OA\Property(property="status", type="string", enum={"draft","active","archived"}),
     *       @OA\Property(property="has_variants", type="boolean"),
     *       @OA\Property(property="price_cents", type="integer", nullable=true),
     *       @OA\Property(property="cost_cents", type="integer", nullable=true),
     *       @OA\Property(property="compare_at_price_cents", type="integer", nullable=true),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="tax_included", type="boolean"),
     *       @OA\Property(property="attributes", type="object", nullable=true),
     *       @OA\Property(property="weight_grams", type="integer", nullable=true),
     *       @OA\Property(property="dimensions", type="object", nullable=true),
     *       @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(
     *         property="featured_media",
     *         type="object",
     *         nullable=true,
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="url", type="string")
     *       ),
     *       @OA\Property(
     *         property="gallery_media",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="url", type="string"),
     *           @OA\Property(property="position", type="integer", nullable=true)
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    // ========== SHOW ==========
    public function show(Product $product)
    {
        return response()->json($this->serializeProduct($product));
    }

    /**
     * Crear producto (acepta featured_media_id y gallery_media_ids como IDs).
     *
     * @OA\Post(
     *   path="/api/admin/products",
     *   operationId="AdminProductsStore",
     *   tags={"Admin - Products"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"business_id","name","slug","sku","status","has_variants","currency","tax_included"},
     *       @OA\Property(property="business_id", type="integer", example=1),
     *       @OA\Property(property="category_id", type="integer", nullable=true, example=10),
     *       @OA\Property(property="name", type="string", example="Camiseta básica"),
     *       @OA\Property(property="slug", type="string", example="camiseta-basica"),
     *       @OA\Property(property="sku", type="string", example="TSH-001"),
     *       @OA\Property(property="barcode", type="string", nullable=true, example="1234567890123"),
     *       @OA\Property(property="description", type="string", nullable=true, example="Camiseta 100% algodón"),
     *       @OA\Property(property="status", type="string", enum={"draft","active","archived"}, example="active"),
     *       @OA\Property(property="has_variants", type="boolean", example=false),
     *       @OA\Property(property="price_cents", type="integer", nullable=true, example=1299),
     *       @OA\Property(property="cost_cents", type="integer", nullable=true, example=700),
     *       @OA\Property(property="compare_at_price_cents", type="integer", nullable=true, example=1499),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="tax_included", type="boolean", example=true),
     *       @OA\Property(property="attributes", type="object", nullable=true, example={"color":"negro","talla":"M"}),
     *       @OA\Property(property="weight_grams", type="integer", nullable=true, example=200),
     *       @OA\Property(property="dimensions", type="object", nullable=true, example={"w":20,"h":2,"l":30}),
     *       @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="featured_media_id", type="integer", nullable=true, example=10),
     *       @OA\Property(property="gallery_media_ids", type="string", nullable=true, example="11,12,13")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Creado",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="business_id", type="integer"),
     *       @OA\Property(property="category_id", type="integer", nullable=true),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="slug", type="string"),
     *       @OA\Property(property="sku", type="string"),
     *       @OA\Property(property="barcode", type="string", nullable=true),
     *       @OA\Property(property="description", type="string", nullable=true),
     *       @OA\Property(property="status", type="string", enum={"draft","active","archived"}),
     *       @OA\Property(property="has_variants", type="boolean"),
     *       @OA\Property(property="price_cents", type="integer", nullable=true),
     *       @OA\Property(property="cost_cents", type="integer", nullable=true),
     *       @OA\Property(property="compare_at_price_cents", type="integer", nullable=true),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="tax_included", type="boolean"),
     *       @OA\Property(property="attributes", type="object", nullable=true),
     *       @OA\Property(property="weight_grams", type="integer", nullable=true),
     *       @OA\Property(property="dimensions", type="object", nullable=true),
     *       @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(
     *         property="featured_media",
     *         type="object",
     *         nullable=true,
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="url", type="string")
     *       ),
     *       @OA\Property(
     *         property="gallery_media",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="url", type="string"),
     *           @OA\Property(property="position", type="integer", nullable=true)
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    // ========== STORE ==========
    public function store(Request $request)
    {
        $data = $this->validatePayload($request, false);

        return DB::transaction(function () use ($data) {
            $product = Product::create(collect($data)->except([
                'featured_media_id',
                'gallery_media_ids',
            ])->toArray());

            // Sincronizar medias
            $this->syncFeatured($product, $data['featured_media_id'] ?? null);
            $this->syncGallery($product, $data['gallery_media_ids'] ?? '');

            // ++ USAGE COUNTERS: +1 producto para este negocio
            $this->adjustProductsCounter($product->business_id, +1);

            return response()->json($this->serializeProduct($product->fresh()), 201);
        });
    }

    /**
     * Actualizar un producto (reemplaza featured/galería si se envían).
     *
     * @OA\Patch(
     *   path="/api/admin/products/{product}",
     *   operationId="AdminProductsUpdate",
     *   tags={"Admin - Products"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="product", in="path", required=true, description="ID del producto",
     *     @OA\Schema(type="integer", example=15)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"business_id","name","slug","sku","status","has_variants","currency","tax_included"},
     *       @OA\Property(property="business_id", type="integer", example=1),
     *       @OA\Property(property="category_id", type="integer", nullable=true, example=10),
     *       @OA\Property(property="name", type="string", example="Camiseta básica"),
     *       @OA\Property(property="slug", type="string", example="camiseta-basica"),
     *       @OA\Property(property="sku", type="string", example="TSH-001"),
     *       @OA\Property(property="barcode", type="string", nullable=true, example="1234567890123"),
     *       @OA\Property(property="description", type="string", nullable=true, example="Camiseta 100% algodón"),
     *       @OA\Property(property="status", type="string", enum={"draft","active","archived"}, example="active"),
     *       @OA\Property(property="has_variants", type="boolean", example=false),
     *       @OA\Property(property="price_cents", type="integer", nullable=true, example=1299),
     *       @OA\Property(property="cost_cents", type="integer", nullable=true, example=700),
     *       @OA\Property(property="compare_at_price_cents", type="integer", nullable=true, example=1499),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="tax_included", type="boolean", example=true),
     *       @OA\Property(property="attributes", type="object", nullable=true, example={"color":"negro","talla":"M"}),
     *       @OA\Property(property="weight_grams", type="integer", nullable=true, example=200),
     *       @OA\Property(property="dimensions", type="object", nullable=true, example={"w":20,"h":2,"l":30}),
     *       @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="featured_media_id", type="integer", nullable=true, example=10),
     *       @OA\Property(property="gallery_media_ids", type="string", nullable=true, example="11,12,13")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Actualizado",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="business_id", type="integer"),
     *       @OA\Property(property="category_id", type="integer", nullable=true),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="slug", type="string"),
     *       @OA\Property(property="sku", type="string"),
     *       @OA\Property(property="barcode", type="string", nullable=true),
     *       @OA\Property(property="description", type="string", nullable=true),
     *       @OA\Property(property="status", type="string", enum={"draft","active","archived"}),
     *       @OA\Property(property="has_variants", type="boolean"),
     *       @OA\Property(property="price_cents", type="integer", nullable=true),
     *       @OA\Property(property="cost_cents", type="integer", nullable=true),
     *       @OA\Property(property="compare_at_price_cents", type="integer", nullable=true),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="tax_included", type="boolean"),
     *       @OA\Property(property="attributes", type="object", nullable=true),
     *       @OA\Property(property="weight_grams", type="integer", nullable=true),
     *       @OA\Property(property="dimensions", type="object", nullable=true),
     *       @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(
     *         property="featured_media",
     *         type="object",
     *         nullable=true,
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="url", type="string")
     *       ),
     *       @OA\Property(
     *         property="gallery_media",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string"),
     *           @OA\Property(property="url", type="string"),
     *           @OA\Property(property="position", type="integer", nullable=true)
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=422, description="Validación fallida"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    // ========== UPDATE ==========
    public function update(Request $request, Product $product)
    {
        $data = $this->validatePayload($request, true, $product);

        return DB::transaction(function () use ($data, $product) {

            // 1) Capturar negocio original ANTES de modificar
            $oldBizId = (int) $product->getOriginal('business_id');

            $product->fill(collect($data)->except([
                'featured_media_id',
                'gallery_media_ids',
            ])->toArray());
            $product->save();

            // featured_media_id presente -> reemplaza featured
            if (array_key_exists('featured_media_id', $data)) {
                $this->syncFeatured($product, $data['featured_media_id']);
            }

            // gallery_media_ids presente -> reemplaza galería
            if (array_key_exists('gallery_media_ids', $data)) {
                $this->syncGallery($product, $data['gallery_media_ids']);
            }

            // limpia filas sin flags
            $this->pruneEmptyLinks($product);

            // ++ USAGE COUNTERS: si cambió de negocio, mueve el conteo
            $newBizId = (int) $product->business_id;
            if ($newBizId !== $oldBizId) {
                $this->adjustProductsCounter($oldBizId, -1);
                $this->adjustProductsCounter($newBizId, +1);
            }

            return response()->json($this->serializeProduct($product->fresh()));
        });
    }

    /**
     * Eliminar producto (borra también vínculos de media).
     *
     * @OA\Delete(
     *   path="/api/admin/products/{product}",
     *   operationId="AdminProductsDestroy",
     *   tags={"Admin - Products"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="product", in="path", required=true, description="ID del producto",
     *     @OA\Schema(type="integer", example=15)
     *   ),
     *   @OA\Response(response=204, description="Eliminado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    // ========== DESTROY ==========
    public function destroy(Product $product)
    {
       DB::transaction(function () use ($product) {
            // ++ USAGE COUNTERS: tomar negocio antes de borrar
            $bizId = (int) $product->business_id;

            $product->mediaLinks()->delete();
            $product->delete();

            // -- productos del negocio
            $this->adjustProductsCounter($bizId, -1);
        });

        return response()->json(null, 204);
    }

    // -----------------------------
    // Helpers
    // -----------------------------

    private function validatePayload(Request $request, bool $isUpdate, ?Product $product = null): array
    {
        $id = $product?->id;

        return $request->validate([
            'business_id' => ['required','integer','exists:businesses,id'],
            'category_id' => ['nullable','integer','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'slug'        => [
                'required','string','max:255',
                Rule::unique('products','slug')->ignore($id)->where(fn($q)=>$q->where('business_id', $request->integer('business_id')))
            ],
            'sku'         => [
                'required','string','max:255',
                Rule::unique('products','sku')->ignore($id)->where(fn($q)=>$q->where('business_id', $request->integer('business_id')))
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

            // NUEVOS:
            'featured_media_id'  => ['nullable','integer','exists:media,id'],
            'gallery_media_ids'  => ['nullable','string'], // CSV "11,12,13"
        ]);
    }

    private function syncFeatured(Product $product, $mediaIdOrNull): void
    {
        // Desactivar featured actual
        $product->mediaLinks()->update(['is_featured' => false]);

        if (!$mediaIdOrNull) {
            return;
        }

        $media = Media::query()->findOrFail($mediaIdOrNull);

        /** @var MediaLink $link */
        $link = MediaLink::firstOrNew([
            'linkable_type' => Product::class,
            'linkable_id'   => $product->id,
            'media_id'      => $media->id,
        ]);
        $link->business_id = $product->business_id;
        $link->is_featured = true;
        // conservar is_gallery si ya lo era
        $link->save();
    }

    private function syncGallery(Product $product, string $csvIds): void
    {
        // Resetear flags de galería
        $product->mediaLinks()->update(['is_gallery' => false, 'position' => null]);

        $ids = collect(explode(',', $csvIds))
            ->map(fn($v)=>trim($v))
            ->filter(fn($v)=>$v !== '')
            ->map(fn($v)=>(int)$v)
            ->unique()->values();

        foreach ($ids as $i => $mediaId) {
            $media = Media::query()->find($mediaId);
            if (!$media) continue;

            $link = MediaLink::firstOrNew([
                'linkable_type' => Product::class,
                'linkable_id'   => $product->id,
                'media_id'      => $media->id,
            ]);
            $link->business_id = $product->business_id;
            $link->is_gallery  = true;
            $link->position    = $i + 1;
            // conservar is_featured si ya lo era
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

        // Featured
        $featured = optional($product->featuredMediaLink()->first()?->media, function (Media $m) {
            return [
                'id'   => $m->id,
                'name' => $m->name,
                'url'  => $m->getFullUrl(),
            ];
        });

        // Galería
        $gallery = $product->galleryMediaLinks()->get()->map(function (MediaLink $link) {
            $m = $link->media;
            if (!$m) return null;
            return [
                'id'       => $m->id,
                'name'     => $m->name,
                'url'      => $m->getFullUrl(),
                'position' => $link->position,
            ];
        })->filter()->values()->all();

        // Serial básico del producto + medias ligeras
        return array_merge($product->toArray(), [
            'featured_media'  => $featured ?: null,
            'gallery_media'   => $gallery,
        ]);
    }

    // =========================
    // USAGE COUNTERS (NUEVO)
    // =========================
    /**
     * Asegura que exista la fila en usage_counters para el negocio.
     */
    private function ensureUsageRow(int $businessId): void
    {
        DB::table('usage_counters')->updateOrInsert(
            ['business_id' => $businessId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    /**
     * Aplica un delta al contador de productos (usa GREATEST para no bajar de 0).
     */
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
}