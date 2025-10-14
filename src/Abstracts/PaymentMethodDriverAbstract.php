<?php

namespace TautId\Payment\Abstracts;

use TautId\Payment\Data\Payment\PaymentData;

abstract class PaymentMethodDriverAbstract
{
    protected bool $has_unique_code = false;

    public function hasUniqueCode()
    {
        return $this->has_unique_code;
    }

    abstract public function channels(): array;

    abstract public function createPayment(PaymentData $data): void;

    abstract public function checkPayment(PaymentData $data): void;

    abstract public function cancelPayment(PaymentData $data): void;

    abstract public function metaValidation(array $meta): void;
}
