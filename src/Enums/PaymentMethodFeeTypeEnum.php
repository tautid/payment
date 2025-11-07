<?php

namespace TautId\Payment\Enums;

enum PaymentMethodFeeTypeEnum: string
{
    case Fixed = 'fixed';
    case Percent = 'percent';

    public static function toArray(): array
    {
        return collect(self::cases())->pluck('name', 'value')->toArray();
    }
}
