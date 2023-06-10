<?php

use App\Http\Controllers\Aid\PackageController;
use App\Http\Controllers\Aid\PeopleAidController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Organization\OrganizationController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register/{role}', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('/users/me', 'getUserByToken');
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::apiResource('organizations', OrganizationController::class);

Route::apiResource('product-categories', ProductCategoryController::class);

Route::apiResource('products', ProductController::class);

Route::apiResource('people-aids', PeopleAidController::class);

Route::apiResource('packages', PackageController::class);
