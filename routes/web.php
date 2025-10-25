<?php

use Illuminate\Support\Facades\Route;

Route::webhooks('moota/taut-callback', 'moota-taut');
Route::webhooks('bayarind/taut-callback', 'bayarind-taut');
