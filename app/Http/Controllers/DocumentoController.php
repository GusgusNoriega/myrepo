<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;

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
 *         @OA\Property(property="imagen",  type="file", format="binary"),
 *         @OA\Property(property="archivo", type="file", format="binary")
 *       )
 *     )
 *   ),
 *   @OA\Response(response=201, description="Creado")
 * )
 */
class DocumentoController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/documentos",
     *   tags={"Documentos"},
     *   summary="Listar documentos paginados",
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     description="Cantidad de documentos por página",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Lista paginada de documentos")
     * )
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $documentos = Documento::paginate($perPage)->appends(['per_page' => $perPage]);

        return response()->json([
            'data' => $documentos->items(),
            'meta' => [
                'current_page' => $documentos->currentPage(),
                'last_page' => $documentos->lastPage(),
                'per_page' => $documentos->perPage(),
                'total' => $documentos->total(),
                'next_page_url' => $documentos->nextPageUrl(),
                'prev_page_url' => $documentos->previousPageUrl(),
                'links' => $documentos->getUrlRange(
                    max($documentos->currentPage() - 2, 1),
                    min($documentos->currentPage() + 2, $documentos->lastPage())
                )
            ]
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

    public function store(Request $request)
    {
        $request->validate([
            'titulo'  => 'required|string|max:255',
            'imagen'  => 'nullable|image|mimes:jpeg,png|max:2048',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $doc = Documento::create($request->only('titulo'));

        if ($request->hasFile('imagen')) {
            $doc->addMediaFromRequest('imagen')
                ->preservingOriginal()
                ->toMediaCollection('imagenes');
        }

        if ($request->hasFile('archivo')) {
            $doc->addMediaFromRequest('archivo')
                ->preservingOriginal()
                ->toMediaCollection('archivos');
        }

        return response()->json([
            'message' => 'Documento guardado correctamente',
            'documento' => [
                'id' => $doc->id,
                'titulo' => $doc->titulo,
                'imagen_url' => $doc->getFirstMediaUrl('imagenes'),
                'archivo_url' => $doc->getFirstMediaUrl('archivos'),
            ]
        ], 201);
    }
}