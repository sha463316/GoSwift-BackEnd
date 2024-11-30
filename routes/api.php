<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
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
//Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


//Protected Routes needs Token
Route::group(['middleware' => ['auth:sanctum']], function () {

    //User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logoutAll', [AuthController::class, 'logoutAll']);
    Route::get('/profile', [AuthController::class, 'get_profile']);
    Route::post('/update-profile', [AuthController::class, 'update_profile']);


    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{storeId}/products', [StoreController::class, 'get_products']);
    Route::get('/stores/search', [StoreController::class, 'search']);


    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'get_product']);


});

// Admin Routes
Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {

    Route::post('/makeUserAdmin', [AuthController::class, 'makeUserAdmin']);
});
