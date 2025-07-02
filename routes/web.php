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
use App\Http\Controllers\XenditController;
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


Route::post('/api/checkout/create-invoice', [XenditController::class, 'createInvoice'])
    ->name('checkout.create-invoice');

// Payment status routes
Route::get('/payment/success/{order_id}', function ($orderId) {
    // Tampilkan pesan sukses sebentar, lalu redirect
    return redirect()->route('orders.index')->with('success', 'Pembayaran berhasil untuk Order #' . $orderId);
})->name('payment.success');

Route::get('/payment/failed/{order_id}', function ($orderId) {
    return view('payment.failed', ['orderId' => $orderId]);
})->name('payment.failed');

Route::get('/orders/{order}/print-invoice', [App\Http\Controllers\OrderInvoiceController::class, 'printInvoice'])->name('orders.print-invoice');


// Xendit webhook callback
Route::post('/api/xendit/callback', [XenditController::class, 'handleCallback'])
    ->name('xendit.callback');
