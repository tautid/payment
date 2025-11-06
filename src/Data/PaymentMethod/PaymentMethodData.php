<?php

namespace TautId\Payment\Data\PaymentMethod;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Payment\Models\PaymentMethod;
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
            meta: $record->meta,
            image_url: PaymentMethodDriverFactory::getDriver($record->driver)->serviceImageUrl($record->service),
            created_at: $record->created_at
        );
    }
}
