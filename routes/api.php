<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update', [AuthController::class, 'updateUser']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::get('/user-info', [\App\Http\Controllers\ActionController::class, 'info']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/user'

], function ($router) {
    Route::get('/info', [\App\Http\Controllers\ActionController::class, 'info']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/product'

], function ($router) {
    Route::post('/create', [\App\Http\Controllers\ActionController::class, 'createProduct']);
    Route::get('/get/{id}', [\App\Http\Controllers\ActionController::class, 'getProduct']);
    Route::post('/upload/images', [\App\Http\Controllers\ActionController::class, 'uploadImages']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/chat'

], function ($router) {
    Route::post('/send', [\App\Http\Controllers\ActionController::class, 'sendChat']);
    Route::get('/get/{id}', [\App\Http\Controllers\ActionController::class, 'getChat']);
    Route::get('/all', [\App\Http\Controllers\ActionController::class, 'getChats']);
});


Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/order'

], function ($router) {
    Route::post('/create', [\App\Http\Controllers\ActionController::class, 'newRequest']);
});
