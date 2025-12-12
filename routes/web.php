<?php

use Illuminate\Support\Facades\Route;
use TautId\Payment\Controllers\TautPaymentController;

Route::get('taut/payment/{trx_id}/process', [TautPaymentController::class, 'show'])->name('taut.payment.process');

Route::webhooks(config('taut-payment.moota_callback_endpoint') ?? 'moota/taut-callback', 'moota-taut');
Route::webhooks(config('taut-payment.bayarind_callback_endpoint') ?? 'bayarind/taut-callback', 'bayarind-taut');
