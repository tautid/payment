<?php

namespace TautId\Payment\Jobs;

use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use TautId\Payment\Services\PaymentService;

class BayarindWebhookReceiverJob extends ProcessWebhookJob
{
    public function handle()
    {
        try {
            $payload = $this->webhookCall->payload;

            $payment = app(PaymentService::class)->getPaymentByTrxId(data_get($payload, 'transactionNo'));

            switch ((string) data_get($payload, 'transactionStatus')) {
                case '04': // Expired
                    app(PaymentService::class)->changePaymentToDue($payment->id);
                    break;
                case '00': // Completed
                    app(PaymentService::class)->changePaymentToCompleted($payment->id);
                    break;
                case '05': // Canceled
                    app(PaymentService::class)->changePaymentToCanceled($payment->id);
                    break;
                default: // Failed
                    app(PaymentService::class)->changePaymentToFailed($payment->id);
            }
        } catch (\InvalidArgumentException $e) {
            //
        }
    }
}
