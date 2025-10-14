<?php

namespace TautId\Payment\Data\PaymentMethod;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Payment\Models\PaymentMethod;

class PaymentMethodData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $driver,
        public string $type,
        public bool $is_active,
        public ?array $meta,
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
            type: $record->type,
            is_active: $record->is_active,
            meta: $record->meta,
            created_at: $record->created_at
        );
    }
}
