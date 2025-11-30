<?php

namespace TautId\Payment\Services;

use Illuminate\Database\RecordNotFoundException;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use TautId\Payment\Data\PaymentMethod\CreatePaymentMethodData;
use TautId\Payment\Data\PaymentMethod\PaymentMethodData;
use TautId\Payment\Data\PaymentMethod\UpdatePaymentMethodData;
use TautId\Payment\Data\Utility\FilterPaginationData;
use TautId\Payment\Enums\PaymentMethodFeeTypeEnum;
use TautId\Payment\Enums\PaymentMethodTypeEnum;
use TautId\Payment\Factories\PaymentMethodDriverFactory;
use TautId\Payment\Models\PaymentMethod;
use TautId\Payment\Traits\FilterServiceTrait;

class PaymentMethodService
{
    use FilterServiceTrait;

    public function getAllPaymentMethods(): DataCollection
    {
        return new DataCollection(
            PaymentMethodData::class,
            PaymentMethod::get()->map(fn ($record) => PaymentMethodData::from($record))
        );
    }

    public function getPaginatePaymentMethods(FilterPaginationData $data): PaginatedDataCollection
    {
        $query = $this->filteredQuery(PaymentMethod::class, $data);

        $pagination = $query->paginate($data->per_page, ['*'], 'page', $data->page);

        $transformedItems = $pagination->getCollection()->map(fn ($record) => PaymentMethodData::from($record));

        $pagination->setCollection($transformedItems);

        return new PaginatedDataCollection(PaymentMethodData::class, $pagination);
    }

    public function getPaymentMethodById(string $method_id): PaymentMethodData
    {
        $record = PaymentMethod::find($method_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment method not found');
        }

        return PaymentMethodData::from($record);
    }

    public function getPaymentMethodByDriver(string $driver): DataCollection
    {
        return new DataCollection(
            PaymentMethodData::class,
            PaymentMethod::where('driver', $driver)->get()->map(fn ($record) => PaymentMethodData::from($record))
        );
    }

    public function getAllDrivers(): array
    {
        return PaymentMethodDriverFactory::getOptions();
    }

    public function getServices(string $driver): array
    {
        $driver = PaymentMethodDriverFactory::getDriver($driver);

        return $driver->services();
    }

    public function getAllAvailablePaymentMethods(): DataCollection
    {
        return new DataCollection(
            PaymentMethodData::class,
            PaymentMethod::where('is_active', true)->get()->map(fn ($item) => PaymentMethodData::from($item))
        );
    }

    public function createPaymentMethod(CreatePaymentMethodData $data): PaymentMethodData
    {
        $drivers = PaymentMethodDriverFactory::getOptions();

        if (! in_array(strtolower($data->driver), array_keys($drivers))) {
            throw new \InvalidArgumentException('Invalid driver');
        }

        if (! in_array($data->type, array_keys(PaymentMethodTypeEnum::toArray()))) {
            throw new \InvalidArgumentException('Invalid type');
        }

        if (! in_array($data->payment_fee_type, array_keys(PaymentMethodFeeTypeEnum::toArray()))) {
            throw new \InvalidArgumentException('Invalid fee type');
        }

        if ($data->payment_fee < 0) {
            throw new \InvalidArgumentException('Unable to fill fee lower than 0');
        }

        if ($data->payment_fee_type == PaymentMethodFeeTypeEnum::Percent->value && $data->payment_fee > 100) {
            throw new \InvalidArgumentException('Unable to fill fee greater than 100% for type percent');
        }

        $driver = PaymentMethodDriverFactory::getDriver($data->driver);

        if (! in_array($data->service, array_keys($driver->services()))) {
            throw new \InvalidArgumentException('Invalid service id');
        }

        $driver->metaValidation($data->meta);

        $record = PaymentMethod::create([
            'name' => $data->name,
            'driver' => strtolower($data->driver),
            'service' => $data->service,
            'payment_fee_type' => $data->payment_fee_type,
            'payment_fee' => $data->payment_fee,
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

        $drivers = PaymentMethodDriverFactory::getOptions();

        if (! in_array(strtolower($data->driver), array_keys($drivers))) {
            throw new \InvalidArgumentException('Invalid driver');
        }

        if (! in_array($data->type, array_keys(PaymentMethodTypeEnum::toArray()))) {
            throw new \InvalidArgumentException('Invalid type');
        }

        if (! in_array($data->payment_fee_type, array_keys(PaymentMethodFeeTypeEnum::toArray()))) {
            throw new \InvalidArgumentException('Invalid fee type');
        }

        if ($data->payment_fee < 0) {
            throw new \InvalidArgumentException('Unable to fill fee lower than 0');
        }

        if ($data->payment_fee_type == PaymentMethodFeeTypeEnum::Percent->value && $data->payment_fee > 100) {
            throw new \InvalidArgumentException('Unable to fill fee greater than 100% for type percent');
        }

        $driver = PaymentMethodDriverFactory::getDriver($data->driver);

        if (! in_array($data->service, array_keys($driver->services()))) {
            throw new \InvalidArgumentException('Invalid service id');
        }

        $driver->metaValidation($data->meta);

        $record->update([
            'name' => $data->name,
            'driver' => strtolower($data->driver),
            'service' => $data->service,
            'payment_fee_type' => $data->payment_fee_type,
            'payment_fee' => $data->payment_fee,
            'type' => $data->type,
            'meta' => $data->meta,
        ]);

        return PaymentMethodData::from($record);
    }

    public function activatePaymentMethod(string $method_id): void
    {
        $record = PaymentMethod::find($method_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment method not found');
        }

        $record->update([
            'is_active' => true,
        ]);
    }

    public function deactivatePaymentMethod(string $method_id): void
    {
        $record = PaymentMethod::find($method_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Payment method not found');
        }

        $record->update([
            'is_active' => false,
        ]);
    }
}
