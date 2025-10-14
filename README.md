# Taut Payment

## Installation

You can install the package via composer:

```bash
composer require taut-id/payment
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="taut-payment-config"
```

Publish the webhook client migrations:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"
```

Publish the payment migrations:

```bash
php artisan vendor:publish --tag="taut-payment-migrations"
```

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-config"
```

Run the migrations:

```bash
php artisan migrate
```

> [!IMPORTANT]
> **Remove the default config in webhook-client after publishing.**
> 
> This step is crucial to ensure proper configuration of the webhook client for your application.

## Customizing Payment Transitions

You can customize payment transitions by creating your own transition classes. Use the provided command to generate transition files:

```bash
php artisan taut-payment:make-transitions
```

This will create the following transition files in your `app/Transitions/Payment/` directory:
- `ToCanceled.php`
- `ToCompleted.php` 
- `ToDue.php`
- `ToPending.php`

The transition files will have the proper `App\Transitions\Payment` namespace and extend the `PaymentTransitionAbstract` class. You can then implement your custom logic in the `handle()` method of each transition.
