<?php

namespace TautId\Payment\Actions;

use Illuminate\Contracts\View\View;
use TautId\Payment\Data\Payment\PaymentData;

class MootaTransactionAction
{
    public function run(PaymentData $data): View
    {
        return view('taut-payment::payment.moota-transaction.payment', [
            'redirectUrl' => data_get($data->response, 'data.payment_url'),
        ]);
    }
}
