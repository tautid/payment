<?php

namespace TautId\Payment\Data\PaymentMethod;

use Spatie\LaravelData\Data;

class CreatePaymentMethodData extends Data
{
    public function __construct(
        public string $name,
        public string $driver,
        public string $type,
        public ?array $meta
    ) {}
}
