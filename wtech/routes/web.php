<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AdminProductController;
Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('login', [LoginController::class, 'store']);

    Route::get('admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('admin/login', [AdminLoginController::class, 'store'])->name('admin.login.store');

    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('register', [RegisterController::class, 'store']);
});

Route::post('logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

use App\Http\Controllers\CartController;

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/set-quantity/{productId}', [CartController::class, 'setQuantity'])->name('cart.setQuantity');

Route::get('/checkout/shipping', [CheckoutController::class, 'shipping'])->name('shipping');
Route::post('/checkout/shipping', [CheckoutController::class, 'storeShipping'])->name('shipping.store');
Route::get('/checkout/delivery', [CheckoutController::class, 'delivery'])->name('delivery');
Route::post('/checkout/delivery', [CheckoutController::class, 'storeDelivery'])->name('delivery.store');

// Admin routes
Route::middleware('auth', 'admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');
});