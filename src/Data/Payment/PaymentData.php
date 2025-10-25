<?php

namespace TautId\Payment\Data\Payment;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;
use TautId\Payment\Data\PaymentMethod\PaymentMethodData;
use TautId\Payment\Models\Payment;

class PaymentData extends Data
{
    public function __construct(
        public string $id,
        public string $trx_id,
        public PaymentMethodData $method,
        public Model $source,
        public string $customer_name,
        public ?string $customer_email,
        public ?string $customer_phone,
        public string $status,
        public float $amount,
        public float $total,
        public ?array $payload,
        public ?array $response,
        public Carbon $date,
        public Carbon $due_at,
        public ?Carbon $completed_at,
        public Carbon $created_at
    ) {}

    public static function fromModel(Payment $record): self
    {
        return new self(
            id: $record->id,
            trx_id: $record->trx_id,
            method: PaymentMethodData::from($record->method),
            source: $record->source,
            customer_name: $record->customer_name,
            customer_email: $record->customer_email,
            customer_phone: $record->customer_phone,
            status: $record->status,
            amount: $record->amount,
            total: $record->total,
            payload: $record->payload,
            response: $record->response,
            date: $record->date,
            due_at: $record->due_at,
            completed_at: $record->completed_at,
            created_at: $record->created_at
        );
    }
}
