<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


    class DocumentoController extends Controller
    {
     /**
     * @OA\Post(
     *   path="/api/documentos",
     *   tags={"Documentos"},
     *   summary="Subir archivo",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"titulo"},
     *         @OA\Property(property="titulo",  type="string"),
     *         @OA\Property(property="descripcion", type="string"),
     *         @OA\Property(property="ruta", type="string", example="/storage/archivos/123/contrato.pdf"),
     *         @OA\Property(property="imagen",  type="file", format="binary"),
     *         @OA\Property(property="archivo", type="file", format="binary")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Creado")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen'      => 'nullable|image|mimes:jpeg,png|max:2048',
            'archivo'     => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        // Determinar tipo
        $tipo = 'otro';
        if ($request->hasFile('imagen')) {
            $tipo = 'imagen';
        } elseif ($request->hasFile('archivo')) {
            $tipo = match ($request->file('archivo')->getMimeType()) {
                'application/pdf'  => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                default            => 'otro',
            };
        }

       

        $doc = Documento::create([
            'titulo'      => $request->input('titulo'),
            'descripcion' => $request->input('descripcion'),
            'tipo'        => $tipo,
        ]);

        if ($request->hasFile('imagen')) {
            $media = $doc->addMediaFromRequest('imagen')
                     ->preservingOriginal()
                     ->toMediaCollection('imagenes');
                
             $doc->update(['ruta' => parse_url($media->getFullUrl(), PHP_URL_PATH)]);
        }

        if ($request->hasFile('archivo')) {
            $media = $doc->addMediaFromRequest('archivo')
                     ->preservingOriginal()
                     ->toMediaCollection('archivos');

            $doc->update(['ruta' => parse_url($media->getFullUrl(), PHP_URL_PATH)]);
        }

        return response()->json([
            'message'   => 'Documento guardado correctamente',
            'documento' => [
                'id'          => $doc->id,
                'titulo'      => $doc->titulo,
                'tipo'        => $doc->tipo,
                'ruta'        => $doc->ruta,   
                'imagen_url'  => $doc->getFirstMediaUrl('imagenes'),
                'archivo_url' => $doc->getFirstMediaUrl('archivos'),
            ]
        ], 201);
    }
/**
     * @OA\Get(
     *   path="/api/documentos",
     *   tags={"Documentos"},
     *   summary="Listar documentos paginados",
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=10)),
     *   @OA\Parameter(name="buscar",    in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="tipo",      in="query", @OA\Schema(type="string", enum={"imagen","pdf","doc","docx","otro"})),
     *   @OA\Parameter(name="fecha_desde", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="fecha_hasta", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="ordenar",     in="query", @OA\Schema(type="string", enum={"asc","desc"}, default="desc")),
     *   @OA\Response(response=200, description="Lista paginada de documentos")
     * )
     */
    public function index(Request $request)
    {
        $query = Documento::query();

        // --- filtros ---
        if ($buscar = $request->query('buscar')) {
            $query->where(fn($q) =>
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%")
            );
        }

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($fecha = $request->query('fecha_desde')) {
            $query->whereDate('created_at', '>=', $fecha);
        }

        if ($fecha = $request->query('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $fecha);
        }

        $ordenar = $request->query('ordenar', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $ordenar);

        $perPage    = (int) $request->query('per_page', 10);
        $documentos = $query->paginate($perPage)
                            ->appends($request->all());

        return response()->json([
            'data' => $documentos->items(),
            'meta' => [
                'current_page'  => $documentos->currentPage(),
                'last_page'     => $documentos->lastPage(),
                'per_page'      => $documentos->perPage(),
                'total'         => $documentos->total(),
                'next_page_url' => $documentos->nextPageUrl(),
                'prev_page_url' => $documentos->previousPageUrl(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/documentos/{id}",
     *   tags={"Documentos"},
     *   summary="Mostrar un documento",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Documento encontrado"),
     *   @OA\Response(response=404, description="Documento no encontrado")
     * )
     */
    public function show(Documento $documento)
    {
        return response()->json([
            'id' => $documento->id,
            'titulo' => $documento->titulo,
            'tipo'        => $documento->tipo,
            'imagen_url' => $documento->getFirstMediaUrl('imagenes'),
            'archivo_url' => $documento->getFirstMediaUrl('archivos'),
        ]);
    }

    /**
     * @OA\Delete(
     *   path="/api/documentos/{id}",
     *   tags={"Documentos"},
     *   summary="Eliminar documento",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=204, description="Documento eliminado"),
     *   @OA\Response(response=404, description="Documento no encontrado")
     * )
     */
    public function destroy($id)
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json(['error' => 'Documento no encontrado'], 404);
        }

        // Eliminar archivos asociados
        $documento->clearMediaCollection('imagenes');
        $documento->clearMediaCollection('archivos');

        $documento->delete();

        return response()->json(null, 204);
    }
    
}