<?php

return [
    'configs' => [
        [
            'name' => 'moota-taut',
            'signing_secret' => env('MOOTA_SECRET'),
            'signature_header_name' => 'signature',
            'signature_validator' => \TautId\Payment\Supports\SignatureValidator\MootaTransactionSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => \TautId\Payment\Jobs\MootaTransactionWebhookReceiverJob::class,
        ],
        [
            'name' => 'bayarind-taut',
            'signing_secret' => config('taut-payment.bayarind_webhook_secret'),
            'signature_header_name' => 'Signature',
            'signature_validator' => \TautId\Payment\Supports\SignatureValidator\BayarindSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \TautId\Payment\Supports\WebhookResponse\BayarindRespondTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => \TautId\Payment\Jobs\BayarindWebhookReceiverJob::class
        ]
    ],
];
