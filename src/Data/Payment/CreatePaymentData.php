<?php

namespace TautId\Payment\Data\Payment;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

class CreatePaymentData extends Data
{
    public function __construct(
        public Model $source,
        public string $method_id,
        public string $customer_name,
        public ?string $customer_email,
        public ?string $customer_phone,
        public float $amount,
        public Carbon $date,
        public Carbon $due_at
    ) {}
}
