<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\Api\Admin\PlanController;
use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\AclUserController;

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
Route::middleware(['auth:api'])->group(function () {
    Route::get   ('/media',        [DocumentoController::class, 'mediaIndex']);
    Route::post  ('/media',        [DocumentoController::class, 'mediaStore']);
    Route::get   ('/media/usage',     [DocumentoController::class, 'mediaUsage']); // ← nuevo
    Route::get   ('/media/{id}',   [DocumentoController::class, 'mediaShow']);
    Route::patch ('/media/{id}',   [DocumentoController::class, 'mediaUpdate']);
    Route::delete('/media/{id}',   [DocumentoController::class, 'mediaDestroy']);
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:api', 'role:admin,api']) // <- importante el ,api
    ->group(function () {
        Route::apiResource('plans', PlanController::class);
    });

Route::prefix('auth')->group(function () {
    Route::post('login',  [AuthTokenController::class, 'login'])->middleware('throttle:10,1');
    Route::middleware('auth:api')->group(function () {
        Route::get('me',     [AuthTokenController::class, 'me']);
        Route::post('logout',[AuthTokenController::class, 'logout']);
    });
});

Route::prefix('admin')
    ->middleware(['auth:api','role:admin']) // solo admin API
    ->group(function () {

        // Roles
        Route::apiResource('roles', RoleController::class)->parameters(['roles' => 'role']);
        Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);

        // Permisos
        Route::apiResource('permissions', PermissionController::class)->parameters(['permissions' => 'permission']);

        // Asignaciones a usuarios
        Route::put('users/{user}/roles', [AclUserController::class, 'syncRoles']);
        Route::put('users/{user}/permissions', [AclUserController::class, 'syncPermissions']);
    });


