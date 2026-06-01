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

Route::redirect('/store', '/merchandise', 301);
Route::get('/store/{product_slug}', function (string $product_slug) {
    return redirect("/merchandise/{$product_slug}", 301);
});

// Cart routes
Route::middleware(['auth', 'throttle:cart'])->group(function () {
    Route::post('/cart', [CartController::class, 'addToCart'])->name('shopping-cart.add');
    Route::post('/getCartTotalPrice', [CartController::class, 'getCartTotalPrice']);
    Route::patch('/cart/items/{cartItemId}', [CartController::class, 'updateCartItem']);
    Route::delete('/cart/items/{cartItemId}', [CartController::class, 'deleteCartItem']);
});


Route::middleware(['auth', 'throttle:checkout'])->group(function () {
    Route::get('/api/checkout/search-destination', [App\Http\Controllers\CheckoutController::class, 'searchDestination'])
        ->name('checkout.search-destination');
    Route::post('/api/checkout/shipping-cost', [App\Http\Controllers\CheckoutController::class, 'getShippingCost'])
        ->name('checkout.shipping-cost');
});

Route::post('/api/checkout/create-invoice', [XenditController::class, 'createInvoice'])
    ->middleware(['auth', 'throttle:checkout'])
    ->name('checkout.create-invoice');

Route::post('/api/events/checkout/create-invoice', [XenditController::class, 'createEventInvoice'])
    ->middleware(['auth', 'throttle:checkout'])
    ->name('events.checkout.create-invoice');

// Payment status routes
Route::get('/payment/success/{order_id}', function ($orderId) {
    // Tampilkan pesan sukses sebentar, lalu redirect
    return redirect('/orders')->with('success', 'Pembayaran berhasil untuk Order #' . $orderId);
})->name('payment.success');

Route::get('/payment/failed/{order_id}', function ($orderId) {
    return view('payment.failed', ['orderId' => $orderId]);
})->name('payment.failed');

Route::get('/orders/{order}/print-invoice', [App\Http\Controllers\OrderInvoiceController::class, 'printInvoice'])
    ->middleware('auth')
    ->name('orders.print-invoice');

Route::get('/orders/{order}/print-delivery-order', [App\Http\Controllers\OrderInvoiceController::class, 'printDeliveryOrder'])
    ->middleware('auth')
    ->name('orders.print-delivery-order');

Route::get('/dashboard/events/{event}/ticket', [App\Http\Controllers\EventTicketController::class, 'showTicket'])
    ->middleware('auth')
    ->name('dashboard.events.ticket');

Route::get('/files/exports/{filename}', [App\Http\Controllers\PrivateFileController::class, 'downloadExport'])
    ->middleware('auth')
    ->name('files.export');


// Xendit webhook callback
Route::post('/api/xendit/callback', [XenditController::class, 'handleCallback'])
    ->name('xendit.callback');

// Explicitly override DevDojo Auth Folio routes to load them within KHI's marketing theme (with navigation and footer)
Route::middleware(['web', 'guest', 'throttle:login'])->group(function () {
    Route::get('auth/login', function () {
        return view('theme::pages.auth.login');
    });

    Route::get('auth/password/reset', function () {
        return view('theme::pages.auth.password.reset');
    })->name('auth.password.request');

    Route::get('auth/password/{token}', function (string $token) {
        return view('theme::pages.auth.password.[token]', compact('token'));
    })->name('password.reset');

    Route::get('auth/register', function () {
        return redirect('/join');
    });

    Route::get('auth/register-devdojo', function () {
        return redirect('/join');
    })->name('auth.register');
});
