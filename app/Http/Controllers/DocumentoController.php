<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Media as MediaModel;

class DocumentoController extends Controller
{
    /* =========================
     *  Helpers de contexto
     * ========================= */
    protected function currentBusinessId(Request $request): int
    {
        $bizId = (int) optional($request->user())->active_business_id;
        abort_if(!$bizId, 403, 'No hay negocio activo seleccionado.');
        return $bizId;
    }

    protected function isSuperAdmin($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    protected function isBizAdmin($user, int $bizId): bool
    {
        if (!$user) return false;
        $role = DB::table('memberships')
            ->where('business_id', $bizId)
            ->where('user_id', $user->id)
            ->where('state', 'active')
            ->value('role');

        return in_array($role, ['owner','admin'], true);
    }

    protected function mapMimeToType(?string $mime): string
    {
        if (!$mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        return 'document';
    }

    protected function fmtBytes(int $n): string
    {
        if ($n < 1024) return $n.' B';
        if ($n < 1048576) return round($n/1024,1).' KB';
        if ($n < 1073741824) return round($n/1048576,1).' MB';
        return round($n/1073741824,2).' GB';
    }

    protected function mediaToResource(MediaModel $m): array
    {
        $type   = $this->mapMimeToType($m->mime_type);
        $title  = $m->getCustomProperty('title') ?? pathinfo($m->file_name, PATHINFO_FILENAME);
        $alt    = $m->getCustomProperty('alt') ?? '';
        $tags   = $m->getCustomProperty('tags') ?? [];
        if (!is_array($tags)) {
            $tags = is_string($tags) ? array_filter(array_map('trim', explode(',', $tags))) : [];
        }

        return [
            'id'            => $m->id,
            'type'          => $type,
            'name'          => $m->file_name,
            'title'         => $title,
            'alt'           => $alt,
            'tags'          => array_values(array_unique($tags)),
            'size'          => (int) $m->size,
            'created_at'    => optional($m->created_at)->toIso8601String(),
            'url'           => $m->getFullUrl(),
            'mime'          => $m->mime_type,
            'owner_user_id' => $m->owner_user_id,
            'business_id'   => $m->business_id,
        ];
    }

    protected function applyMediaFilters(Request $request, $query)
    {
        if ($term = trim((string) $request->query('q', $request->query('search', '')))) {
            $like = '%'.$term.'%';
            $query->where(function($q) use ($like) {
                $q->where('file_name', 'like', $like)
                  ->orWhere('name', 'like', $like)
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.title')) LIKE ?", [$like])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.alt')) LIKE ?", [$like])
                  ->orWhereRaw("JSON_SEARCH(JSON_EXTRACT(custom_properties, '$.tags'), 'one', ?) IS NOT NULL", [$like]);
            });
        }

        if ($type = $request->query('type')) {
            $query->where(function($q) use ($type) {
                if ($type === 'image')   $q->where('mime_type', 'like', 'image/%');
                elseif ($type === 'video') $q->where('mime_type', 'like', 'video/%');
                elseif ($type === 'audio') $q->where('mime_type', 'like', 'audio/%');
                else $q->where(function($qq){
                    $qq->where('mime_type', 'like', 'application/%')
                       ->orWhere('mime_type', 'like', 'text/%');
                });
            });
        }

        $sort = $request->query('sort', 'date_desc');
        if ($sort === 'date_asc')      $query->orderBy('created_at', 'asc');
        elseif ($sort === 'name_asc')  $query->orderBy('file_name', 'asc');
        else                           $query->orderBy('created_at', 'desc');

        return $query;
    }

    /* =========================
     *  Helpers de cuota/uso
     * ========================= */
    protected function getBusinessQuota(int $bizId): array
    {
        $sub = DB::table('subscriptions as s')
            ->join('plan_features as pf', 'pf.plan_id', '=', 's.plan_id')
            ->leftJoin('plans as p', 'p.id', '=', 's.plan_id')
            ->where('s.business_id', $bizId)
            ->whereIn('s.status', ['active','trialing'])
            ->where('s.current_period_start', '<=', now())
            ->where('s.current_period_end',   '>=', now())
            ->orderByDesc('s.id')
            ->selectRaw('s.id as subscription_id, s.status, s.current_period_start, s.current_period_end,
                         pf.storage_limit_bytes, pf.asset_limit, p.name as plan_name')
            ->first();

        return [
            'has_active_subscription' => (bool) $sub,
            'plan_name'               => $sub->plan_name ?? null,
            'limit_bytes'             => isset($sub->storage_limit_bytes) ? (int)$sub->storage_limit_bytes : null,
            'asset_limit'             => isset($sub->asset_limit) ? (int)$sub->asset_limit : null,
            'period'                  => $sub ? [
                'start' => (string) $sub->current_period_start,
                'end'   => (string) $sub->current_period_end,
            ] : null,
        ];
    }

    protected function getUsageSnapshot(int $bizId): array
    {
        $row = DB::table('usage_counters')->where('business_id', $bizId)->first();

        if (!$row) {
            $agg = DB::table('media')
                ->where('business_id', $bizId)
                ->selectRaw('COALESCE(SUM(size),0) as bytes, COUNT(*) as cnt')
                ->first();

            DB::table('usage_counters')->insert([
                'business_id'   => $bizId,
                'products_count'=> 0,
                'assets_count'  => (int) $agg->cnt,
                'storage_bytes' => (int) $agg->bytes,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            return ['used_bytes' => (int)$agg->bytes, 'assets_count' => (int)$agg->cnt];
        }

        return [
            'used_bytes'   => (int) $row->storage_bytes,
            'assets_count' => (int) $row->assets_count,
        ];
    }

    /* =========================
     *  Endpoints API
     * ========================= */

    // GET /api/media
    public function mediaIndex(Request $request)
    {
        $perPage = (int) $request->query('per_page', 18);
        $user    = $request->user();

        // Admin global: puede ver todo, opcionalmente filtrar por ?business_id
        if ($this->isSuperAdmin($user)) {
            $query = MediaModel::query();

            if ($request->filled('business_id')) {
                $query->where('business_id', (int) $request->query('business_id'));
            }

            $this->applyMediaFilters($request, $query);
            $p = $query->paginate($perPage)->appends($request->all());

            return response()->json([
                'data' => $p->getCollection()->map(fn($m) => $this->mediaToResource($m))->values(),
                'meta' => [
                    'current_page'  => $p->currentPage(),
                    'last_page'     => $p->lastPage(),
                    'from'          => $p->firstItem() ?? 0,
                    'to'            => $p->lastItem() ?? 0,
                    'per_page'      => $p->perPage(),
                    'total'         => $p->total(),
                    'next_page_url' => $p->nextPageUrl(),
                    'prev_page_url' => $p->previousPageUrl(),
                ],
            ]);
        }

        // No admin: siempre por negocio activo
        $bizId = $this->currentBusinessId($request);

        // Autorización por Policy (viewAny con $bizId)
        $this->authorize('viewAny', [MediaModel::class, $bizId]);

        $query = MediaModel::query()->where('business_id', $bizId);

        // Si NO es admin de negocio, limitar a sus propios archivos
        if (!$this->isBizAdmin($user, $bizId)) {
            $query->where('owner_user_id', $user->id);
        }

        $this->applyMediaFilters($request, $query);
        $p = $query->paginate($perPage)->appends($request->all());

        return response()->json([
            'data' => $p->getCollection()->map(fn($m) => $this->mediaToResource($m))->values(),
            'meta' => [
                'current_page'  => $p->currentPage(),
                'last_page'     => $p->lastPage(),
                'from'          => $p->firstItem() ?? 0,
                'to'            => $p->lastItem() ?? 0,
                'per_page'      => $p->perPage(),
                'total'         => $p->total(),
                'next_page_url' => $p->nextPageUrl(),
                'prev_page_url' => $p->previousPageUrl(),
            ],
        ]);
    }

    // GET /api/media/usage
    public function mediaUsage(Request $request)
    {
        $bizId = $this->currentBusinessId($request);
        $quota = $this->getBusinessQuota($bizId);
        $usage = $this->getUsageSnapshot($bizId);

        $limit = $quota['limit_bytes'];
        $remaining = is_int($limit) && $limit > 0
            ? max(0, $limit - $usage['used_bytes'])
            : null;

        $assetLimit = $quota['asset_limit'];
        $assetsRemaining = is_int($assetLimit)
            ? max(0, $assetLimit - $usage['assets_count'])
            : null;

        return response()->json([
            'business_id' => $bizId,
            'plan' => [
                'active'      => $quota['has_active_subscription'],
                'name'        => $quota['plan_name'],
                'period'      => $quota['period'],
                'limit_bytes' => $limit,
                'asset_limit' => $assetLimit,
            ],
            'usage' => [
                'used_bytes'        => $usage['used_bytes'],
                'used_human'        => $this->fmtBytes($usage['used_bytes'] ?? 0),
                'remaining_bytes'   => $remaining,
                'remaining_human'   => is_null($remaining) ? null : $this->fmtBytes($remaining),
                'percent'           => (is_int($limit) && $limit > 0)
                    ? round(($usage['used_bytes'] / max(1,$limit)) * 100, 2)
                    : null,
                'assets_count'      => $usage['assets_count'],
                'assets_remaining'  => $assetsRemaining,
            ],
        ]);
    }

    // POST /api/media
    public function mediaStore(Request $request)
    {
        $request->validate([
            'file'      => 'nullable|file|max:51200', // 50MB
            'files'     => 'nullable|array',
            'files.*'   => 'file|max:51200',
        ]);

        $user  = $request->user();

        // Admin puede subir hacia un negocio específico (?business_id)
        if ($this->isSuperAdmin($user) && $request->filled('business_id')) {
            $request->validate(['business_id' => 'required|integer|exists:businesses,id']);
            $bizId = (int) $request->input('business_id');
        } else {
            $bizId = $this->currentBusinessId($request);
        }

        // Determinar owner
        $ownerId = (int) optional($user)->id;
        if ($this->isSuperAdmin($user) && $request->filled('owner_user_id')) {
            $request->validate(['owner_user_id' => 'required|integer|exists:users,id']);
            $ownerId = (int) $request->input('owner_user_id');
        }

        // Policy: create (admin de negocio o dueño==user)
        $this->authorize('create', [MediaModel::class, $bizId, $ownerId]);

        // Normaliza a arreglo de UploadedFile
        $files = [];
        if ($request->hasFile('files'))       $files = $request->file('files');
        elseif ($request->hasFile('file'))    $files = [$request->file('file')];
        elseif ($request->hasFile('imagen'))  $files = [$request->file('imagen')];
        elseif ($request->hasFile('archivo')) $files = [$request->file('archivo')];

        if (empty($files)) {
            return response()->json(['message' => 'No se recibieron archivos.'], 422);
        }

        // Chequeo de cuota
        $quota   = $this->getBusinessQuota($bizId);
        if (!$quota['has_active_subscription']) {
            return response()->json(['message' => 'No hay suscripción activa para este negocio.'], 402);
        }

        $usage   = $this->getUsageSnapshot($bizId);
        $incomingBytes = array_sum(array_map(fn($f) => (int) $f->getSize(), $files));
        $incomingCnt   = count($files);

        if (is_int($quota['limit_bytes']) && $quota['limit_bytes'] > 0) {
            if (($usage['used_bytes'] + $incomingBytes) > $quota['limit_bytes']) {
                return response()->json([
                    'message'        => 'Límite de almacenamiento excedido por tu plan.',
                    'limit_bytes'    => $quota['limit_bytes'],
                    'used_bytes'     => $usage['used_bytes'],
                    'incoming_bytes' => $incomingBytes,
                ], 422);
            }
        }

        if (is_int($quota['asset_limit'])) {
            if (($usage['assets_count'] + $incomingCnt) > $quota['asset_limit']) {
                return response()->json([
                    'message'       => 'Límite de cantidad de archivos excedido por tu plan.',
                    'asset_limit'   => $quota['asset_limit'],
                    'assets_count'  => $usage['assets_count'],
                    'incoming_cnt'  => $incomingCnt,
                ], 422);
            }
        }

        $uploaded = [];
        $bytesUploaded = 0;

        foreach ($files as $idx => $file) {
            $tipo = $this->mapMimeToType($file->getMimeType());

            // Crea contenedor Documento con negocio/owner
            $doc = Documento::create([
                'business_id'   => $bizId,
                'owner_user_id' => $ownerId,
                'titulo'        => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'descripcion'   => null,
                'tipo'          => $tipo === 'image' ? 'imagen' : ($tipo === 'document' ? 'pdf' : $tipo),
            ]);

            $collection = $tipo === 'image' ? 'imagenes' : 'archivos';

            $media = $doc->addMedia($file)
                ->preservingOriginal()
                ->toMediaCollection($collection);

            if (is_null($media->business_id)) {
                $media->business_id = $bizId;
            }
            $media->owner_user_id = $ownerId;
            $media->save();

            // Metadatos opcionales
            $title = $request->input(is_array($request->input('title')) ? "title.$idx" : 'title');
            $alt   = $request->input(is_array($request->input('alt'))   ? "alt.$idx"   : 'alt');
            $tags  = $request->input(is_array($request->input('tags'))  ? "tags.$idx"  : 'tags');

            if ($title !== null) $media->setCustomProperty('title', $title);
            if ($alt   !== null) $media->setCustomProperty('alt',   $alt);
            if ($tags  !== null) {
                $tagsArr = is_array($tags) ? $tags : array_filter(array_map('trim', explode(',', (string) $tags)));
                $media->setCustomProperty('tags', $tagsArr);
            }
            $media->save();

            // Compatibilidad con documentos
            $doc->update(['ruta' => parse_url($media->getFullUrl(), PHP_URL_PATH)]);

            $uploaded[]     = $this->mediaToResource($media);
            $bytesUploaded += (int) $file->getSize();
        }

        // Actualiza usage_counters
        DB::table('usage_counters')->updateOrInsert(
            ['business_id' => $bizId],
            ['created_at' => now(), 'updated_at' => now()]
        );

        DB::table('usage_counters')->where('business_id', $bizId)->update([
            'storage_bytes' => DB::raw('storage_bytes + '.(int)$bytesUploaded),
            'assets_count'  => DB::raw('assets_count + '.count($uploaded)),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Archivo(s) subido(s) correctamente',
            'items'   => $uploaded,
        ], 201);
    }

    // GET /api/media/{id}
    public function mediaShow(Request $request, $id)
    {
        $user = $request->user();

        if ($this->isSuperAdmin($user)) {
            $media = MediaModel::findOrFail($id);
            $this->authorize('view', $media);
            return response()->json($this->mediaToResource($media));
        }

        $bizId = $this->currentBusinessId($request);
        $media = MediaModel::where('business_id', $bizId)->findOrFail($id);

        // Policy: ver
        $this->authorize('view', $media);

        return response()->json($this->mediaToResource($media));
    }

    // PATCH /api/media/{id}
    public function mediaUpdate(Request $request, $id)
    {
        $user = $request->user();

        if ($this->isSuperAdmin($user)) {
            $media = MediaModel::findOrFail($id);
        } else {
            $bizId = $this->currentBusinessId($request);
            $media = MediaModel::where('business_id', $bizId)->findOrFail($id);
        }

        $this->authorize('update', $media);

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'title' => 'sometimes|nullable|string|max:255',
            'alt'   => 'sometimes|nullable|string|max:255',
            'tags'  => 'sometimes|nullable',
        ]);

        if ($request->filled('name')) {
            $media->file_name = (string) $request->string('name');
            $media->name      = pathinfo($media->file_name, PATHINFO_FILENAME);
        }
        if ($request->exists('title')) $media->setCustomProperty('title', $request->input('title'));
        if ($request->exists('alt'))   $media->setCustomProperty('alt',   $request->input('alt'));

        if ($request->exists('tags')) {
            $tags    = $request->input('tags');
            $tagsArr = is_array($tags) ? $tags : array_filter(array_map('trim', explode(',', (string) $tags)));
            $media->setCustomProperty('tags', $tagsArr);
        }

        $media->save();

        return response()->json([
            'message' => 'Metadatos actualizados',
            'item'    => $this->mediaToResource($media),
        ]);
    }

    // DELETE /api/media/{id}
    public function mediaDestroy(Request $request, $id)
    {
        $user = $request->user();

        if ($this->isSuperAdmin($user)) {
            $media = MediaModel::findOrFail($id);
        } else {
            $bizId = $this->currentBusinessId($request);
            $media = MediaModel::where('business_id', $bizId)->findOrFail($id);
        }

        $this->authorize('delete', $media);

        $bytes = (int) $media->size;

        $owner = $media->model; // Documento
        $media->delete();

        if ($owner instanceof Documento && $owner->fresh() && $owner->media()->count() === 0) {
            $owner->delete();
        }

        // Actualiza usage_counters restando
        $bizIdForCounters = (int) ($media->business_id ?? ($user?->active_business_id ?? 0));
        if ($bizIdForCounters) {
            DB::table('usage_counters')->updateOrInsert(
                ['business_id' => $bizIdForCounters],
                ['created_at' => now(), 'updated_at' => now()]
            );

            DB::table('usage_counters')->where('business_id', $bizIdForCounters)->update([
                'storage_bytes' => DB::raw('GREATEST(0, storage_bytes - '. $bytes .')'),
                'assets_count'  => DB::raw('GREATEST(0, assets_count - 1)'),
                'updated_at'    => now(),
            ]);
        }

        return response()->json(null, 204);
    }
}