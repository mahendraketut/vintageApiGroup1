<?php

use App\Http\Controllers\ProductController;
use App\Models\Wishlist;
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

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    //Route test connection
    Route::get('test', function () {
        return response()->json(['message' => 'API is working']);
    });

    Route::post('register', 'App\Http\Controllers\Auth\RegisterController@register');
    Route::post('login', 'App\Http\Controllers\Auth\LoginController@login');

    Route::get('categories', 'App\Http\Controllers\CategoryController@index');
    Route::get('brands', 'App\Http\Controllers\BrandController@index');

    Route::get('products', 'App\Http\Controllers\ProductController@index');

    Route::middleware('auth:sanctum')->group(function () {
        Route::put('change-password', 'App\Http\Controllers\Auth\ChangePasswordController@changePassword');
        Route::post('logout', 'App\Http\Controllers\Auth\LogoutController@logout');

        Route::put('/products/restore', 'App\Http\Controllers\ProductController@restore');
        Route::resource('/products', 'App\Http\Controllers\ProductController');

        Route::resource('wishlists', 'App\Http\Controllers\WishlistController');
    });
});
