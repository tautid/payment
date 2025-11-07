<?php

namespace TautId\Payment\Data\PaymentMethod;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Payment\Models\PaymentMethod;
use TautId\Payment\Enums\PaymentMethodFeeTypeEnum;
use TautId\Payment\Factories\PaymentMethodDriverFactory;

class PaymentMethodData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $driver,
        public string $service,
        public string $type,
        public bool $is_active,
        public string $payment_fee_type,
        public float $payment_fee,
        public ?array $meta,
        public string $image_url,
        public Carbon $created_at
    ) {
        //
    }

    public static function fromModel(PaymentMethod $record): self
    {
        return new self(
            id: $record->id,
            name: $record->name,
            driver: $record->driver,
            service: $record->service,
            type: $record->type,
            is_active: $record->is_active,
            payment_fee_type: $record->payment_fee_type ?? PaymentMethodFeeTypeEnum::Fixed->value,
            payment_fee: $record->payment_fee ?? 0,
            meta: $record->meta,
            image_url: PaymentMethodDriverFactory::getDriver($record->driver)->serviceImageUrl($record->service),
            created_at: $record->created_at
        );
    }
}
