<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentoController;
use L5Swagger\Http\Controllers\SwaggerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/seccion-1-1',  [DemoController::class, 'seccion11'])->name('seccion11');
Route::get('/seccion-1-2',  [DemoController::class, 'seccion12'])->name('seccion12');
Route::get('/seccion-2-1',  [DemoController::class, 'seccion21'])->name('seccion21');
Route::get('/seccion-2-2',  [DemoController::class, 'seccion22'])->name('seccion22');

Route::get ('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users',         [UserController::class, 'store' ])->name('users.store');

/* ---------- Invitados (no logueados) ---------- */
Route::middleware('guest')->group(function () {
    // Login
    Route::get ('/login',    [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login',    [AuthenticatedSessionController::class, 'store']);

    // Registro (quítalo si no lo necesitas)
    Route::get ('/register', [RegisteredUserController::class,     'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class,     'store']);
    
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
     ->name('logout')           //  ➜ route('logout')
     ->middleware('auth');      //  Solo usuarios logueados

// routes/web.php
Route::middleware('web')->get('/admin/acl', function () {
    return view('admin.acl'); // resources/views/admin/acl.blade.php
})->name('admin.acl');






