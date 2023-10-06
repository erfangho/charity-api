<?php

use App\Http\Controllers\Aid\AidAllocationController;
use App\Http\Controllers\Aid\PackageAllocationController;
use App\Http\Controllers\Aid\PackageController;
use App\Http\Controllers\Aid\PackageItemController;
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
    Route::post('user/change-password', 'changePassword');
});

Route::prefix('users')->group(function () {
    Route::post('delete-multi', [UserController::class, 'destroyUsers']);
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::apiResource('organizations', OrganizationController::class);

Route::apiResource('product-categories', ProductCategoryController::class);

Route::apiResource('products', ProductController::class);
Route::post('products/delete-multi', [ProductController::class, 'destroyProducts']);

Route::apiResource('people-aids', PeopleAidController::class);
Route::post('people-aids/delete-multi', [PeopleAidController::class, 'destroyPeopleAids']);
Route::get('people-aids/abundance-chart/data', [PeopleAidController::class, 'abundanceChart']);
Route::post('aids/history', [PeopleAidController::class, 'getHelperAidHistory']);

Route::apiResource('packages', PackageController::class);
Route::post('packages/delete-multi', [PackageController::class, 'destroyPackages']);
Route::post('packages/create-package-with-items', [PackageController::class, 'createPackageWithItems']);
Route::put('packages/{id}/update-package-with-items', [PackageController::class, 'updatePackageWithItems']);


Route::apiResource('package-items', PackageItemController::class);

Route::apiResource('aid-allocations', AidAllocationController::class);
Route::post('aid-allocations/delete-multi', [AidAllocationController::class, 'destroyAidAllocations']);
Route::post('aid-allocations/assign-multi', [AidAllocationController::class, 'multiAssign']);
Route::get('aid-allocations/circle-chart/data', [AidAllocationController::class, 'chartData']);

Route::apiResource('package-allocations', PackageAllocationController::class);
Route::post('package-allocations/delete-multi', [PackageAllocationController::class, 'destroyPackageAllocations']);
Route::post('package-allocations/assign-multi', [PackageAllocationController::class, 'multiAssign']);

