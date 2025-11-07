<?php

namespace TautId\Payment\Data\PaymentMethod;

use Spatie\LaravelData\Data;

class CreatePaymentMethodData extends Data
{
    public function __construct(
        public string $name,
        public string $driver,
        public string $service,
        public ?string $payment_fee_type = 'fixed',
        public ?float $payment_fee = 0,
        public string $type,
        public ?array $meta
    ) {}
}
