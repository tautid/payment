<?php

namespace TautId\Payment\Services;

use Illuminate\Database\RecordNotFoundException;
use Spatie\LaravelData\DataCollection;
use TautId\Payment\Data\Payment\CreatePaymentData;
use TautId\Payment\Data\Payment\PaymentData;
use TautId\Payment\Enums\PaymentStatusEnum;
use TautId\Payment\Factories\PaymentMethodDriverFactory;
use TautId\Payment\Models\Payment;

class PaymentService
{
    public function getAllPayments(): DataCollection
    {
        return new DataCollection(
            PaymentData::class,
            Payment::get()->map(fn ($record) => PaymentData::from($record))
        );
    }

    public function getPaymentById(string $payment_id): PaymentData
    {
        $record = Payment::find($payment_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        return PaymentData::from($record);
    }

    public function getPaymentByTrxId(string $payment_trx_id): PaymentData
    {
        $record = Payment::where('trx_id', $payment_trx_id)->first();

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        return PaymentData::from($record);
    }

    public function createPayment(CreatePaymentData $data): PaymentData
    {
        $driver = PaymentMethodDriverFactory::getDriver($data->method->driver);

        $record = Payment::create([
            'trx_id' => uniqid('PYM-'),
            'method_id' => $data->method->id,
            'source_id' => $data->source->id,
            'source_type' => get_class($data->source),
            'method_name' => $data->method->name,
            'customer_name' => $data->customer_name,
            'customer_phone' => $data->customer_phone,
            'customer_email' => $data->customer_email,
            'status' => PaymentStatusEnum::Created->value,
            'amount' => $data->amount,
            'total' => $data->amount,
            'date' => $data->date,
            'due_at' => $data->due_at,
        ]);

        $record->update(['status' => PaymentStatusEnum::Pending->value]);

        $result = PaymentData::from($record);

        $driver->createPayment($result);

        return $result;
    }

    public function changePaymentToDue(string $payment_id): void
    {
        $record = Payment::find($payment_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        if ($record->status != PaymentStatusEnum::Pending->value) {
            throw new \InvalidArgumentException('This current payment status is not pending');
        }

        $record->update([
            'status' => PaymentStatusEnum::Due->value,
        ]);
    }

    public function changePaymentToCompleted(string $payment_id): void
    {
        $record = Payment::find($payment_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        if ($record->status != PaymentStatusEnum::Pending->value) {
            throw new \InvalidArgumentException('This current payment status is not pending');
        }

        $record->update([
            'completed_at' => now(),
            'status' => PaymentStatusEnum::Completed->value,
        ]);
    }

    public function changePaymentToCanceled(string $payment_id): void
    {
        $record = Payment::find($payment_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        if ($record->status != PaymentStatusEnum::Pending->value) {
            throw new \InvalidArgumentException('This current payment status is not pending');
        }

        $record->update([
            'status' => PaymentStatusEnum::Canceled->value,
        ]);
    }

    public function updatePaymentPayload(string $payment_id, array $payload): void
    {
        $record = Payment::find($payment_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        $record->update([
            'payload' => $payload,
        ]);
    }

    public function updatePaymentResponse(string $payment_id, array $response): void
    {
        $record = Payment::find($payment_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        $record->update([
            'response' => $response,
        ]);
    }
}
