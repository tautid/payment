<?php

namespace TautId\Payment\Data\PaymentMethod;

use Spatie\LaravelData\Data;

class UpdatePaymentMethodData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $driver,
        public string $type,
        public ?array $meta
    )
    {

    }
}
