<?php

use Illuminate\Support\Facades\Route;
use Creative2llc\LunarPaypal\Http\Controllers\OrdersController;
use Creative2llc\LunarPaypal\Http\Controllers\WebhookController;

Route::prefix('lunar-paypal')->group(function () {
    Route::post('/orders', [OrdersController::class, 'create'])->name('lunar-paypal.orders.create');

    Route::post('/orders/{order_id}/capture', [OrdersController::class, 'capture'])->name('lunar-paypal.orders.capture');

    Route::post('/webhook', [WebhookController::class, 'handle']);
});
