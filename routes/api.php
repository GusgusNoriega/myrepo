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
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\MembershipController;
use App\Http\Controllers\Api\Admin\SubscriptionController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\BusinessController;
use App\Http\Controllers\Api\Admin\UserAdminController;


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

Route::prefix('admin')->group(function () {
    Route::apiResource('products', ProductController::class)->only('show');
});


// PÚBLICA
Route::prefix('public')->group(function () {
    Route::get('businesses/{business}/products', [ProductController::class, 'publicIndexByBusiness'])
        ->name('public.products.byBusiness');
});

Route::middleware('auth:api')
    ->prefix('admin')
    ->group(function () {
        Route::apiResource('products', ProductController::class)->except('show');
    });

Route::middleware(['auth:api']) // agrega tu middleware/permiso de admin aquí
    ->prefix('admin')
    ->group(function () {
        Route::apiResource('memberships', MembershipController::class)
            ->only(['index','show','store','update','destroy']);
    });

Route::middleware(['auth:api']) // agrega tus middlewares/permiso admin
    ->prefix('admin')
    ->group(function () {
        Route::apiResource('subscriptions', SubscriptionController::class)
            ->only(['index','show','store','update','destroy']);
    });

Route::middleware(['auth:api']) // agrega tus middlewares (role:admin) si corresponde
    ->prefix('admin')
    ->group(function () {
        Route::apiResource('categories', CategoryController::class)
            ->only(['index','show','store','update','destroy']);
    });

Route::middleware(['auth:api'])
    ->prefix('admin')
    ->group(function () {
        Route::get('my-business', [BusinessController::class, 'myBusiness']);
        Route::patch('my-business', [BusinessController::class, 'updateMyBusiness']);
        Route::apiResource('businesses', BusinessController::class)
            ->only(['index','show','store','update','destroy']);
    });

Route::middleware(['auth:api']) // ajusta a tu middleware real
    ->prefix('admin')->group(function () {
        Route::apiResource('users', UserAdminController::class)
            ->only(['index','show','store','update','destroy']);
        Route::patch('users/{user}/active-business', [UserAdminController::class, 'setActiveBusiness']);
    });





