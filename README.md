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

Run the migrations:

```bash
php artisan migrate
```
