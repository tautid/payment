<?php

namespace TautId\Payment\Transitions;

use TautId\Payment\Abstracts\PaymentTransitionAbstract;

class ToCompleted extends PaymentTransitionAbstract
{
    public function handle(\TautId\Payment\Models\Payment $record): void
    {
        //
    }
}
