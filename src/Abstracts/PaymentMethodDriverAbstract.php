<?php

namespace TautId\Payment\Abstracts;

use Illuminate\Http\Request;
use TautId\Payment\Data\Payment\PaymentData;

abstract class PaymentMethodDriverAbstract
{
    abstract public function services(): array;

    abstract public function createPayment(PaymentData $data): void;

    abstract public function checkPayment(PaymentData $data): void;

    abstract public function cancelPayment(PaymentData $data): void;

    abstract public function metaValidation(array $meta): void;

    abstract public function checkSignature(Request $request): bool;
}
