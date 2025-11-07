<?php

namespace TautId\Payment\Data\PaymentMethod;

use Spatie\LaravelData\Data;

class UpdatePaymentMethodData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $driver,
        public string $service,
        public ?string $payment_fee_type,
        public ?float $payment_fee,
        public string $type,
        public ?array $meta
    ) {}
}
