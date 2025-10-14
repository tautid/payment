<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | List of all drivers supported by this package.
    |
    | Supported: moota-transaction
    |
    | * [moota-transaction] : handle transaction validation also create
    |                         checkout in moota and able to use VA and QRIS.
    */

    'drivers' => [
        'moota-transaction',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transitions
    |--------------------------------------------------------------------------
    |
    | You can customize and add extra process from every state of payment
    |
    | Available State: ToPending, ToDue, ToCanceled, ToCompleted
    */

    'transitions_namespace' => 'App\\Transitions\\Payment',

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | All required credentials
    |
    */

    'moota_transaction_api_token' => null,
    'moota_transaction_webhook_secret' => null,
];
