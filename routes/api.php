<?php

use App\Http\Controllers\Admin\AdminController;
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
    Route::post('/make-admin', [AdminController::class, 'make_user_admin']);
    Route::post('/create-store', [AdminController::class, 'create_store']);
    Route::post('/update-store/{id}', [AdminController::class, 'edit_store']);
    Route::delete('/delete-store/{id}', [AdminController::class, 'delete_store']);


    Route::post('/store/{id}/create-product/', [AdminController::class, 'create_product']);
    Route::post('/update-product/{id}', [AdminController::class, 'edit_products']);
    Route::delete('/delete-product/{id}', [AdminController::class, 'delete_product']);

});
