<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use Wave\Facades\Wave;

// Wave routes
Wave::routes();

Route::any('auth/setup', function () {
    abort(404); // Atau redirect atau response lainnya
});

// Cart routes
Route::middleware('auth')->group(function () {
    Route::post('/cart', [CartController::class, 'addToCart']);
    Route::post('/getCartTotalPrice', [CartController::class, 'getCartTotalPrice']);
    Route::patch('/cart/items/{cartItemId}', [CartController::class, 'updateCartItem']);
    Route::delete('/cart/items/{cartItemId}', [CartController::class, 'deleteCartItem']);
});
