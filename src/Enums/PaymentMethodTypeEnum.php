<?php

namespace TautId\Payment\Enums;

enum PaymentMethodTypeEnum: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';

    public static function toArray(): array
    {
        return collect(self::cases())->pluck('name', 'value')->toArray();
    }
}
