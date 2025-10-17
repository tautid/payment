<?php

namespace TautId\Payment\Abstracts;

use TautId\Payment\Data\Payment\PaymentData;

abstract class PaymentMethodDriverAbstract
{
    abstract public function channels(): array;

    abstract public function createPayment(PaymentData $data): void;

    abstract public function checkPayment(PaymentData $data): void;

    abstract public function cancelPayment(PaymentData $data): void;

    abstract public function metaValidation(array $meta): void;
}
