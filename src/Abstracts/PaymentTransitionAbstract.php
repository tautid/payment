<?php

namespace TautId\Payment\Abstracts;

use TautId\Payment\Models\Payment;

abstract class PaymentTransitionAbstract
{
    abstract function handle(Payment $record): void;
}
