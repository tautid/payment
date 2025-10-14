<?php

namespace TautId\Payment\Facades;

use Illuminate\Support\Facades\Facade;

class TautPayment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TautId\Payment\TautPayment::class;
    }
}
