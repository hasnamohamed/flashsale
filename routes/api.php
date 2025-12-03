<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HoldController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;

Route::get('/products/{id}', [ProductController::class, 'show']);

Route::post('/holds', [HoldController::class, 'store']); // body: reservation → { product_id, qty } OR Success → { hold_id, expires_at }
Route::post('/orders', [OrderController::class, 'store']);      // body: hold_id

Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle']);

