<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DocumentoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/documentos', [DocumentoController::class, 'store']);
Route::get('/documentos/{documento}', [DocumentoController::class, 'show']);

// NUEVAS RUTAS
Route::get('/documentos', [DocumentoController::class, 'index']);
Route::delete('/documentos/{id}', [DocumentoController::class, 'destroy']);




