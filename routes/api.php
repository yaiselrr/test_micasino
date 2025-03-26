<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');
Route::post('/payment/webhook/super-walletz', [PaymentController::class, 'handleSuperWalletzWebhook'])
    ->name('payment.webhook.super_walletz');
