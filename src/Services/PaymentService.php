<?php

namespace TautId\Payment\Services;

use Carbon\Carbon;
use TautId\Payment\Models\Payment;
use Spatie\LaravelData\DataCollection;
use TautId\Payment\Enums\PaymentStatusEnum;
use TautId\Payment\Data\Payment\PaymentData;
use TautId\Payment\Traits\FilterServiceTrait;
use Spatie\LaravelData\PaginatedDataCollection;
use Illuminate\Database\RecordNotFoundException;
use TautId\Payment\Data\Payment\CreatePaymentData;
use TautId\Payment\Enums\PaymentMethodFeeTypeEnum;
use TautId\Payment\Data\Utility\FilterPaginationData;
use TautId\Payment\Factories\PaymentMethodDriverFactory;

class PaymentService
{
    use FilterServiceTrait;

    public function getAllPayments(): DataCollection
    {
        return new DataCollection(
            PaymentData::class,
            Payment::get()->map(fn ($record) => PaymentData::from($record))
        );
    }

    public function getPaginatedPayments(FilterPaginationData $data): PaginatedDataCollection
    {
        $query = $this->filteredQuery(Payment::class, $data);

        $pagination = $query->paginate($data->per_page, ['*'], 'page', $data->page);

        $transformedItems = $pagination->getCollection()->map(fn ($record) => PaymentData::from($record));

        $pagination->setCollection($transformedItems);

        return new PaginatedDataCollection(PaymentData::class, $pagination);
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
        $method = app(PaymentMethodService::class)->getPaymentMethodById($data->method_id);

        $driver = PaymentMethodDriverFactory::getDriver($method->driver);

        $fee = ($method->payment_fee_type == PaymentMethodFeeTypeEnum::Percent->value)
                ? $data->amount * ($method->payment_fee / 100)
                : $method->payment_fee;

        $record = Payment::create([
            'trx_id' => uniqid(),
            'method_id' => $method->id,
            'source_id' => $data->source->id,
            'source_type' => get_class($data->source),
            'method_name' => $method->name,
            'customer_name' => $data->customer_name,
            'customer_phone' => $data->customer_phone,
            'customer_email' => $data->customer_email,
            'status' => PaymentStatusEnum::Created->value,
            'amount' => $data->amount,
            'payment_fee' => $fee,
            'total' => $data->amount + $fee,
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

    public function changePaymentToFailed(string $payment_id): void
    {
        $record = Payment::find($payment_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        if (
            ! in_array($record->status, [
                PaymentStatusEnum::Created->value,
                PaymentStatusEnum::Pending->value,
            ])
        ) {
            throw new \InvalidArgumentException('This current payment status is not created or pending');
        }

        $record->update([
            'status' => PaymentStatusEnum::Failed->value,
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

    public function updateDueAt(string $payment_id, Carbon $date): void
    {
        $record = Payment::find($payment_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment not found');
        }

        $record->update([
            'due_at' => $date,
        ]);
    }
}
