<?php

use App\Http\Controllers\AuthController;
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
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);


//Protected Routes needs Token
Route::group(['middleware' =>['auth:sanctum']] , function (){

    //User
    Route::post('/logout',[AuthController::class,'logout']);
    Route::put('/updateUser',[AuthController::class,'updateUser']);
    Route::get('/userProfile',[AuthController::class,'user']);
});

// Admin Routes
Route::group(['middleware' =>['auth:sanctum', 'admin']], function() {

    Route::post('/makeUserAdmin', [AuthController::class, 'makeUserAdmin']);
});
