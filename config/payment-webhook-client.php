<?php

return [
    'configs' => [
        [
            'name' => 'moota-taut',
            'signing_secret' => env('MOOTA_SECRET'),
            'signature_header_name' => 'signature',
            'signature_validator' => \TautId\Payment\Supports\MootaTransactionSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => \TautId\Payment\Jobs\MootaTransactionWebhookReceiverJob::class,
        ],
    ],

    /*
     * The integer amount of days after which models should be deleted.
     *
     * It deletes all records after 30 days. Set to null if no models should be deleted.
     */
    'delete_after_days' => 30,

    /*
     * Should a unique token be added to the route name
     */
    'add_unique_token_to_route_name' => false,
];
