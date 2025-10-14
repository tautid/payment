<?php

namespace TautId\Payment\Transitions;

use TautId\Payment\Abstracts\PaymentTransitionAbstract;

class ToCanceled extends PaymentTransitionAbstract
{
    public function handle(\TautId\Payment\Models\Payment $record): void
    {
        //
    }
}
