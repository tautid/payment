# Taut Payment

A Laravel package for handling payment transactions with customizable state transitions and webhook integrations.

## Installation

You can install the package via composer:

```bash
composer require tautid/payment
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="taut-payment-config"
```

Publish the seeder file (optional):

```bash
php artisan vendor:publish --tag="taut-payment-seeders"
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

## Available Commands

This package provides two artisan commands:

### 1. Payment Due Command
Automatically changes pending payments to due status when they exceed their due date:

```bash
php artisan taut-payment:due
```

You can schedule this command to run periodically in your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('taut-payment:due')->hourly();
}
```

### 2. Make Transitions Command
Generates custom transition files for handling payment state changes:

## Customizing Payment Transitions

You can add custom business logic to payment state changes by creating your own transition classes. This allows you to:

- Send notifications when payments are completed
- Update related models when payment status changes
- Log payment activities for audit trails
- Integrate with third-party services
- Execute custom business rules

### Creating Custom Transitions

Use the provided command to generate transition files:

```bash
php artisan taut-payment:make-transitions
```

This will create the following transition files in your `app/Transitions/Payment/` directory:
- `ToCanceled.php` - Executed when payment is canceled
- `ToCompleted.php` - Executed when payment is completed
- `ToDue.php` - Executed when payment becomes due
- `ToPending.php` - Executed when payment becomes pending
- `ToFailed.php` - Executed when payment becomes failed

### Example Custom Transition

Each transition file extends the `PaymentTransitionAbstract` class. Here's an example of customizing the `ToCompleted` transition:

```php
<?php

namespace App\Transitions\Payment;

use TautId\Payment\Abstracts\PaymentTransitionAbstract;
use TautId\Payment\Models\Payment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ToCompleted extends PaymentTransitionAbstract
{
    public function handle(Payment $record): void
    {
        // Your extra step
    }
}
```

### Configuration

The transition namespace can be configured in your `config/taut-payment.php`:

```php
'transitions_namespace' => 'App\\Transitions\\Payment',
```

This allows you to organize your transitions in a different namespace if needed.

## Using the Services

The Taut Payment package provides two main service classes for managing payments and payment methods programmatically.

### PaymentService

The `PaymentService` class provides comprehensive functionality for managing payments throughout their lifecycle.

#### Retrieving Payments

```php
use TautId\Payment\Services\PaymentService;
use TautId\Payment\Data\Utility\FilterPaginationData;

$paymentService = app(PaymentService::class);

// Get all payments
$allPayments = $paymentService->getAllPayments();

// Get paginated payments with filtering
$filterData = FilterPaginationData::from([
    'page' => 1,
    'per_page' => 10,
    'search' => 'customer name', // Optional search term
    'filters' => ['status' => 'completed'] // Optional filters
]);
$paginatedPayments = $paymentService->getPaginatedPayments($filterData);

// Get payment by ID
$payment = $paymentService->getPaymentById('1');

// Get payment by transaction ID
$payment = $paymentService->getPaymentByTrxId('PYM-123456789');
```

#### Creating Payments

```php
use TautId\Payment\Data\Payment\CreatePaymentData;
use Carbon\Carbon;

// Assuming you have an order model or similar
$order = Order::find(1);

$createData = CreatePaymentData::from([
    'source' => $order, // Any Eloquent model
    'method_id' => '1', // Payment method ID
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '+1234567890',
    'amount' => 100.50,
    'date' => Carbon::now(),
    'due_at' => Carbon::now()->addDays(3)
]);

$payment = $paymentService->createPayment($createData);
```

#### Managing Payment Status

```php
// Change payment to due (when payment period expires)
$paymentService->changePaymentToDue('1');

// Mark payment as completed
$paymentService->changePaymentToCompleted('1');

// Cancel payment
$paymentService->changePaymentToCanceled('1');

// Mark payment as failed
$paymentService->changePaymentToFailed('1');
```

#### Updating Payment Data

```php
// Update payment payload (external payment gateway data)
$paymentService->updatePaymentPayload('1', [
    'gateway_transaction_id' => 'tx_123456',
    'gateway_reference' => 'REF-789'
]);

// Update payment response (gateway response data)
$paymentService->updatePaymentResponse('1', [
    'status_code' => 200,
    'message' => 'Payment processed successfully',
    'gateway_response' => ['...']
]);
```

### PaymentMethodService

The `PaymentMethodService` class handles the management of payment methods and their configurations.

#### Retrieving Payment Methods

```php
use TautId\Payment\Services\PaymentMethodService;

$methodService = app(PaymentMethodService::class);

// Get all payment methods
$allMethods = $methodService->getAllPaymentMethods();

// Get paginated payment methods
$filterData = FilterPaginationData::from([
    'page' => 1,
    'per_page' => 10,
    'filters' => ['is_active' => true]
]);
$paginatedMethods = $methodService->getPaginatePaymentMethods($filterData);

// Get payment method by ID
$method = $methodService->getPaymentMethodById('1');
```

#### Working with Drivers

```php
// Get all available drivers
$availableDrivers = $methodService->getAllDrivers();
// Returns: ['moota-transaction' => 'Moota-transaction', 'offline' => 'Offline']

// Get channels supported by a specific driver
$offlineServices = $methodService->getServices('offline');
// For offline driver returns: ['cash']

$mootaServices = $methodService->getServices('moota-transaction');
// Returns available channels for Moota driver

$bayarindServices = $methodService->getServices('bayarind');
// Returns available channels for Bayarind driver
```

#### Creating Payment Methods

```php
use TautId\Payment\Data\PaymentMethod\CreatePaymentMethodData;

// Create a cash payment method
$createData = CreatePaymentMethodData::from([
    'name' => 'Cash Payment',
    'driver' => 'offline',
    'service' => 'cash',
    'type' => 'production', // or 'sandbox'
    'meta' => [
        'description' => 'Pay with cash at our counter',
        'instructions' => 'Please bring exact change',
        'channels' => ['cash']
    ]
]);

$paymentMethod = $methodService->createPaymentMethod($createData);

// Create a Moota payment method
$mootaData = CreatePaymentMethodData::from([
    'name' => 'Bank Transfer - BCA',
    'driver' => 'moota-transaction',
    'type' => 'production',
    'meta' => [
        'bank_code' => 'BCA',
        'account_number' => '1234567890',
        'account_name' => 'Your Company Name'
    ]
]);

$bankTransferMethod = $methodService->createPaymentMethod($mootaData);
```

#### Updating Payment Methods

```php
use TautId\Payment\Data\PaymentMethod\UpdatePaymentMethodData;

$updateData = UpdatePaymentMethodData::from([
    'id' => '1',
    'name' => 'Updated Cash Payment',
    'driver' => 'offline',
    'service' => 'cash',
    'type' => 'production',
    'meta' => [
        'description' => 'Updated description',
        'instructions' => 'Updated instructions'
    ]
]);

$updatedMethod = $methodService->updatePaymentMethod($updateData);
```

#### Managing Payment Method Status

```php
// Activate a payment method
$methodService->activatePaymentMethod('1');

// Deactivate a payment method
$methodService->deactivatePaymentMethod('1');
```

### Practical Usage Examples

#### Complete Payment Flow

```php
use TautId\Payment\Services\{PaymentService, PaymentMethodService};
use TautId\Payment\Data\Payment\CreatePaymentData;

// 1. Get available payment methods for user selection
$methodService = app(PaymentMethodService::class);
$activeMethods = $methodService->getAllPaymentMethods()
    ->filter(fn($method) => $method->is_active);

// 2. Create payment when user selects a method
$paymentService = app(PaymentService::class);
$payment = $paymentService->createPayment(CreatePaymentData::from([
    'source' => $order,
    'method_id' => $selectedMethodId,
    'customer_name' => $request->customer_name,
    'customer_email' => $request->customer_email,
    'amount' => $order->total,
    'date' => Carbon::now(),
    'due_at' => Carbon::now()->addHours(24)
]));

// 3. Handle payment completion (webhook or manual)
if ($paymentConfirmed) {
    $paymentService->changePaymentToCompleted($payment->id);
}
```

#### Error Handling

All service methods throw appropriate exceptions that you should handle:

```php
use Illuminate\Database\RecordNotFoundException;

try {
    $payment = $paymentService->getPaymentById('invalid-id');
} catch (RecordNotFoundException $e) {
    // Handle payment not found
    return response()->json(['error' => 'Payment not found'], 404);
} catch (\InvalidArgumentException $e) {
    // Handle invalid arguments (e.g., wrong status transition)
    return response()->json(['error' => $e->getMessage()], 400);
}
```

This will create cash payment methods for both production and sandbox environments.
