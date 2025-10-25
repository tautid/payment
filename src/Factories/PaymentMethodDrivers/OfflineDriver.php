<?php

namespace TautId\Payment\Factories\PaymentMethodDrivers;

use Illuminate\Http\Request;
use TautId\Payment\Abstracts\PaymentMethodDriverAbstract;
use TautId\Payment\Data\Payment\PaymentData;

class OfflineDriver extends PaymentMethodDriverAbstract
{
    public function services(): array
    {
        return [
            'cash' => 'Cash',
        ];
    }

    public function createPayment(PaymentData $data): void
    {
        // no need to perform anything because the driver is offline
    }

    public function checkPayment(PaymentData $data): void
    {
        // no need to perform anything because the driver is offline
    }

    public function cancelPayment(PaymentData $data): void
    {
        // no need to perform anything because the driver is offline
    }

    public function metaValidation(array $meta): void
    {
        // no need to perform anything because the driver is offline
    }

    public function checkSignature(Request $request): bool
    {
        // no need to perform anything because the driver is offline
        return true;
    }
}
