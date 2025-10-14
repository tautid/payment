<?php

namespace TautId\Payment\Enums;

enum PaymentStatusEnum: string
{
    case Created = 'created';
    case Pending = 'pending';
    case Due = 'due';
    case Canceled = 'canceled';
    case Completed = 'completed';

    public static function toArray() : array
    {
        return collect(self::cases())->pluck('name','value')->toArray();
    }
}
