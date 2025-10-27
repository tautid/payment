<?php

use Illuminate\Support\Facades\Route;
use TautId\Payment\Controllers\TautPaymentController;

Route::get('taut/payment/{trx_id}/process', [TautPaymentController::class, 'show'])->name('taut.payment.process');

Route::webhooks('moota/taut-callback', 'moota-taut');
Route::webhooks('bayarind/taut-callback', 'bayarind-taut');
