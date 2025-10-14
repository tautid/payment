<?php

namespace TautId\Payment\Abstracts;

use TautId\Payment\Models\Payment;

abstract class PaymentTransitionAbstract
{
    abstract public function handle(Payment $record): void;
}
