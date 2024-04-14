<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
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

    Route::middleware('auth:sanctum')->group(function () {
        Route::put('change-password', 'App\Http\Controllers\Auth\ChangePasswordController@changePassword');
        Route::post('logout', 'App\Http\Controllers\Auth\LogoutController@logout');
        Route::resource('/categories', CategoryController::class);
        Route::resource('/brands', BrandController::class);
        Route::put('/products/restore/{product}', [ProductController::class, 'restore']);
        Route::resource('/products', ProductController::class);
        Route::resource('/carts', CartController::class);

        Route::prefix('profile')->group(function () {
            Route::get('profile-detail/{profile}', 'App\Http\Controllers\Profiles\ProfileController@show');
            Route::put('profile-update/{profile}', 'App\Http\Controllers\Profiles\ProfileController@update');
        });

        Route::get('shipping-address/trash', 'App\Http\Controllers\Address\ShippingAddressController@trash');
        Route::post('shipping-address/restore/{id}', 'App\Http\Controllers\Address\ShippingAddressController@restore');
        Route::resource('shipping-address', 'App\Http\Controllers\Address\ShippingAddressController')->except(['create', 'edit']);
        Route::resource('wishlists', 'App\Http\Controllers\WishlistController');
    });
});
