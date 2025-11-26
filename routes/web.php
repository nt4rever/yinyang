<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// Stripe Checkout
Route::get('checkout', [CheckoutController::class, 'checkout'])->name('checkout');
Route::get('checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
Route::get('subscription-checkout', [CheckoutController::class, 'subscriptionCheckout'])->name('subscription-checkout');
Route::get('latest-invoice', [CheckoutController::class, 'latestInvoice'])->name('latest-invoice');
