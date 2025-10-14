<?php

namespace TautId\Payment\Services;

use Illuminate\Database\RecordNotFoundException;
use Spatie\LaravelData\DataCollection;
use TautId\Payment\Data\PaymentMethod\CreatePaymentMethodData;
use TautId\Payment\Data\PaymentMethod\PaymentMethodData;
use TautId\Payment\Data\PaymentMethod\UpdatePaymentMethodData;
use TautId\Payment\Enums\PaymentMethodTypeEnum;
use TautId\Payment\Factories\PaymentMethodDriverFactory;
use TautId\Payment\Models\PaymentMethod;

class PaymentMethodService
{
    public function getAllPaymentMethods(): DataCollection
    {
        return new DataCollection(
            PaymentMethodData::class,
            PaymentMethod::get()->map(fn ($record) => PaymentMethodData::from($record))
        );
    }

    public function getPaymentMethodById(string $method_id): PaymentMethodData
    {
        $record = PaymentMethod::find($method_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment method not found');
        }

        return PaymentMethodData::from($record);
    }

    public function getAllDrivers(): array
    {
        return PaymentMethodDriverFactory::getOptions();
    }

    public function getChannels(string $driver): array
    {
        $driver = PaymentMethodDriverFactory::getDriver($driver);

        return $driver->channels();
    }

    public function createPaymentMethod(CreatePaymentMethodData $data): PaymentMethodData
    {
        $drivers = PaymentMethodDriverFactory::getOptions();

        if (! in_array(strtolower($data->driver), $drivers)) {
            throw new \InvalidArgumentException('Invalid driver');
        }

        if (! in_array($data->type, PaymentMethodTypeEnum::toArray())) {
            throw new \InvalidArgumentException('Invalid type');
        }

        $driver = PaymentMethodDriverFactory::getDriver($data->driver);

        $driver->metaValidation($data->meta);

        $record = PaymentMethod::create([
            'name' => $data->name,
            'driver' => strtolower($data->driver),
            'type' => $data->type,
            'is_active' => true,
            'meta' => $data->meta,
        ]);

        return PaymentMethodData::from($record);
    }

    public function updatePaymentMethod(UpdatePaymentMethodData $data): PaymentMethodData
    {
        $record = PaymentMethod::find($data->id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment method not found');
        }

        if (! in_array($data->type, PaymentMethodTypeEnum::toArray())) {
            throw new \InvalidArgumentException('Invalid type');
        }

        $driver = PaymentMethodDriverFactory::getDriver($data->driver);

        $driver->metaValidation($data->meta);

        $record->update([
            'name' => $data->name,
            'driver' => strtolower($data->driver),
            'type' => $data->type,
            'meta' => $data->meta,
        ]);

        return PaymentMethodData::from($record);
    }

    public function PaymentMethodActivate(string $method_id): void
    {
        $record = PaymentMethod::find($method_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment method not found');
        }

        $record->update([
            'is_active' => false,
        ]);
    }

    public function PaymentMethodInactivate(string $method_id): void
    {
        $record = PaymentMethod::find($method_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment method not found');
        }

        $record->update([
            'is_active' => true,
        ]);
    }
}
