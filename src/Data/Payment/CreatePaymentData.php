<?php

namespace TautId\Payment\Data\Payment;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Illuminate\Database\Eloquent\Model;
use TautId\Payment\Data\PaymentMethod\PaymentMethodData;

class CreatePaymentData extends Data
{
    public function __construct(
        public Model $source,
        public PaymentMethodData $method,
        public string $customer_name,
        public ?string $customer_email,
        public ?string $customer_phone,
        public float $amount,
        public Carbon $date,
        public Carbon $due_at
    )
    {

    }
}
