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
    | * [offline] : offline payment such as cash
    */

    'drivers' => [
        'moota-transaction',
        'offline',
        'bayarind',
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
    | Sandbox Credentials
    |--------------------------------------------------------------------------
    |
    | All required sandbox credentials
    |
    | note: replace using env() for safety
    */

    'sandbox_bayarind_secret' => null,
    'sandbox_bayarind_company_id' => null,

    /*
    |--------------------------------------------------------------------------
    | Production Credentials
    |--------------------------------------------------------------------------
    |
    | All required production credentials
    |
    | note: replace using env() for safety
    */

    'moota_transaction_api_token' => null,
    'moota_transaction_webhook_secret' => null,
    'production_bayarind_secret' => null,
    'production_bayarind_company_id' => null,

    /*
    |--------------------------------------------------------------------------
    | Redirect URLs
    |--------------------------------------------------------------------------
    |
    | Configure where payment redirect to from vendor
    | Use {id} or {trx_id} as placeholders for dynamic payment identifiers
    |
    | Examples:
    | - Dynamic with payment ID: 'https://yourdomain.com/payment/{id}'
    | - Dynamic with payment TRX ID: 'https://yourdomain.com/payment/{trx_id}'
    | - Static URL: 'https://yourdomain.com/payment/bayarind/callback'
    |
    | note: replace using env() for best practice
    */

    'redirect_url' => null,
    'moota_callback_endpoint' => 'moota/taut-callback',
    'bayarind_callback_endpoint' => 'bayarind/taut-callback',
];
