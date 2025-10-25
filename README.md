# Taut Payment

A comprehensive Laravel package for handling payment transactions with multiple payment gateways, customizable state transitions, and webhook integrations. Built on top of Spatie Laravel Data for robust data handling and type safety.

## Features

- **Multiple Payment Drivers**: Support for Moota Transaction, Bayarind, and offline payment methods
- **State Machine**: Built-in payment status transitions with customizable hooks
- **Webhook Integration**: Automatic webhook handling for payment gateway notifications
- **Type-Safe Data Layer**: Using Spatie Laravel Data for consistent data structures
- **Flexible Filtering**: Advanced pagination and filtering capabilities
- **Payment Gateway Integration**: Ready-to-use drivers for popular Indonesian payment gateways

## Installation

Install the package via composer:

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

Publish the webhook client config:

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

## Configuration

The package uses a configuration file `config/taut-payment.php` where you can configure:

- **Available drivers**: Enable/disable payment drivers
- **Transitions namespace**: Customize where transition classes are located
- **API credentials**: Set up payment gateway credentials (use environment variables)

## Core Concepts

### Payment Status Flow

The package uses a state machine for payment statuses:

1. **Created** → **Pending** → **Completed/Due/Canceled/Failed**

Available status transitions:
- `Created`: Initial payment state
- `Pending`: Payment awaiting completion
- `Due`: Payment has expired
- `Completed`: Payment successfully processed
- `Canceled`: Payment canceled by admin/system
- `Failed`: Payment processing failed

### Payment Drivers

The package supports multiple payment drivers:

1. **Offline Driver**: For cash payments and manual transactions
2. **Moota Transaction Driver**: Indonesian bank transfer integration with VA and QRIS support
3. **Bayarind Driver**: Multi-channel payment gateway supporting various e-wallets and VA

### Data Transfer Objects (DTOs)

All data in the package is handled through type-safe Data Transfer Objects using Spatie Laravel Data:

- **PaymentData**: Complete payment information
- **CreatePaymentData**: Data required to create a new payment
- **PaymentMethodData**: Payment method information
- **CreatePaymentMethodData**: Data for creating payment methods
- **UpdatePaymentMethodData**: Data for updating payment methods
- **FilterPaginationData**: Advanced filtering and pagination data

## Available Commands

This package provides powerful artisan commands:

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

```bash
php artisan taut-payment:make-transitions
```

## Payment Transitions System

The package uses a powerful state machine system for handling payment status changes. You can add custom business logic to each transition.

### Available Transitions

The system includes the following transitions:
- `ToPending` - When payment moves to pending status
- `ToCanceled` - When payment is canceled
- `ToCompleted` - When payment is successfully completed
- `ToDue` - When payment becomes overdue
- `ToFailed` - When payment processing fails

### Creating Custom Transitions

Generate custom transition files using the artisan command:

```bash
php artisan taut-payment:make-transitions
```

This creates transition files in your `app/Transitions/Payment/` directory that you can customize.

### Custom Transition Examples

```php
<?php

namespace App\Transitions\Payment;

use TautId\Payment\Abstracts\PaymentTransitionAbstract;
use TautId\Payment\Models\Payment;

class ToCompleted extends PaymentTransitionAbstract
{
    public function handle(Payment $record): void
    {
        // Your extra step
    }
}
```

### Configuration

Configure the transition namespace in `config/taut-payment.php`:

```php
'transitions_namespace' => 'App\\Transitions\\Payment',
```

## Core Services

The package provides comprehensive service classes for managing payments and payment methods with full type safety.

### PaymentService

The `PaymentService` class handles all payment operations throughout the payment lifecycle.

#### Retrieving Payments

```php
use TautId\Payment\Services\PaymentService;
use TautId\Payment\Data\Utility\FilterPaginationData;
use TautId\Payment\Data\Utility\ActiveFilterPaginationData;

$paymentService = app(PaymentService::class);

// Get all payments
$allPayments = $paymentService->getAllPayments();

// Advanced filtering with pagination
$filterData = FilterPaginationData::from([
    'page' => 1,
    'per_page' => 20,
    'sortBy' => 'created_at',
    'sortDirection' => 'desc',
    'searchable' => ['customer_name', 'customer_email', 'trx_id'],
    'searchTerm' => 'john@example.com',
    'active_filters' => [
        ActiveFilterPaginationData::from([
            'column' => 'status',
            'value' => 'completed'
        ]),
        ActiveFilterPaginationData::from([
            'column' => 'method.driver',  // Nested relationship filtering
            'value' => 'moota-transaction'
        ])
    ]
]);
$paginatedPayments = $paymentService->getPaginatedPayments($filterData);

// Get specific payments
$payment = $paymentService->getPaymentById('1');
$payment = $paymentService->getPaymentByTrxId('PYM-123456789');
```

#### Creating Payments

```php
use TautId\Payment\Data\Payment\CreatePaymentData;
use Carbon\Carbon;

$order = Order::find(1); // Any Eloquent model

$payment = $paymentService->createPayment(CreatePaymentData::from([
    'source' => $order,                    // Polymorphic relationship
    'method_id' => '1',                    // Payment method ID
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '628123456789',
    'amount' => 150000,                    // Amount in rupiah
    'date' => Carbon::now(),
    'due_at' => Carbon::now()->addHours(24) // Payment deadline
]));
```

#### Payment Status Management

```php
// Status transitions (automatically triggers transition classes)
$paymentService->changePaymentToDue('1');        // Pending → Due
$paymentService->changePaymentToCompleted('1');  // Pending → Completed  
$paymentService->changePaymentToCanceled('1');   // Pending → Canceled
$paymentService->changePaymentToFailed('1');     // Created/Pending → Failed

// Update payment metadata
$paymentService->updatePaymentPayload('1', [
    'gateway_transaction_id' => 'tx_abc123',
    'reference_number' => 'REF-789',
    'gateway_data' => ['channel' => 'bank_transfer']
]);

$paymentService->updatePaymentResponse('1', [
    'response_code' => '00',
    'response_message' => 'Transaction successful',
    'gateway_response' => $gatewayResponse
]);
```

### PaymentMethodService

Manages payment methods and their driver configurations.

#### Retrieving Payment Methods

```php
use TautId\Payment\Services\PaymentMethodService;

$methodService = app(PaymentMethodService::class);

// Get all payment methods
$allMethods = $methodService->getAllPaymentMethods();

// Paginated retrieval with filtering
$filterData = FilterPaginationData::from([
    'page' => 1,
    'per_page' => 15,
    'active_filters' => [
        ActiveFilterPaginationData::from(['column' => 'is_active', 'value' => true]),
        ActiveFilterPaginationData::from(['column' => 'driver', 'value' => 'moota-transaction'])
    ]
]);
$paginatedMethods = $methodService->getPaginatePaymentMethods($filterData);

// Get specific payment method
$method = $methodService->getPaymentMethodById('1');
$bayarindMethods = $methodService->getPaymentMethodByDriver('bayarind');
```

#### Driver Management

```php
// Get all available drivers
$drivers = $methodService->getAllDrivers();
// Returns: ['moota-transaction' => 'Moota-transaction', 'offline' => 'Offline', 'bayarind' => 'Bayarind']

// Get services for each driver
$offlineServices = $methodService->getServices('offline');
// Returns: ['cash' => 'Cash']

$mootaServices = $methodService->getServices('moota-transaction');  
// Returns dynamic bank accounts from Moota API

$bayarindServices = $methodService->getServices('bayarind');
// Returns: [1021 => 'BCA Virtual Account', 1085 => 'Shopee Pay', ...]
```

#### Creating Payment Methods

```php
use TautId\Payment\Data\PaymentMethod\CreatePaymentMethodData;

// Offline payment method
$offlineMethod = $methodService->createPaymentMethod(CreatePaymentMethodData::from([
    'name' => 'Cash Payment',
    'driver' => 'offline',
    'service' => 'cash', // getService by channel
    'type' => 'production',
    'meta' => []
]));

// Moota bank transfer
$mootaMethod = $methodService->createPaymentMethod(CreatePaymentMethodData::from([
    'name' => 'Bank Transfer BCA',
    'driver' => 'moota-transaction',
    'service' => 'BCA_BANK_ID', // getService by channel
    'type' => 'production',
    'meta' => []
]));

// Bayarind e-wallet
$bayarindMethod = $methodService->createPaymentMethod(CreatePaymentMethodData::from([
    'name' => 'Shopee Pay',
    'driver' => 'bayarind',
    'service' => '1085', // getService by channel
    'type' => 'sandbox',
    'meta' => [
        'bayarind_channel_id' => 'your_channel_id'
    ]
]));
```

#### Updating and Managing Methods

```php
use TautId\Payment\Data\PaymentMethod\UpdatePaymentMethodData;

// Update payment method
$methodService->updatePaymentMethod(UpdatePaymentMethodData::from([
    'id' => '1',
    'name' => 'Updated Payment Method',
    'driver' => 'offline',
    'service' => 'cash', 
    'type' => 'production',
    'meta' => ['updated' => 'metadata']
]));

// Activate/deactivate methods
$methodService->activatePaymentMethod('1');
$methodService->deactivatePaymentMethod('1');
```

## Data Structures

### Enums

#### PaymentStatusEnum

```php
use TautId\Payment\Enums\PaymentStatusEnum;

PaymentStatusEnum::Created;      // 'created'
PaymentStatusEnum::Pending;      // 'pending'
PaymentStatusEnum::Due;          // 'due'
PaymentStatusEnum::Canceled;     // 'canceled'
PaymentStatusEnum::Completed;    // 'completed'
PaymentStatusEnum::Failed;       // 'failed'

// Get all statuses as array
PaymentStatusEnum::toArray();
```

#### PaymentMethodTypeEnum

```php
use TautId\Payment\Enums\PaymentMethodTypeEnum;

PaymentMethodTypeEnum::Sandbox;     // 'sandbox'
PaymentMethodTypeEnum::Production;  // 'production'
```

## Advanced Usage Examples

### Complete E-commerce Flow

```php
use TautId\Payment\Services\{PaymentService, PaymentMethodService};
use TautId\Payment\Data\Payment\CreatePaymentData;
use TautId\Payment\Enums\PaymentStatusEnum;

class CheckoutController extends Controller
{
    public function processCheckout(Request $request)
    {
        $order = Order::create($request->validated());
        
        // Get active payment methods
        $methodService = app(PaymentMethodService::class);
        $paymentMethods = $methodService->getAllPaymentMethods()
            ->filter(fn($method) => $method->is_active);
            
        return view('checkout', compact('order', 'paymentMethods'));
    }
    
    public function createPayment(Request $request, Order $order)
    {
        $paymentService = app(PaymentService::class);
        
        $payment = $paymentService->createPayment(CreatePaymentData::from([
            'source' => $order,
            'method_id' => $request->payment_method_id,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'amount' => $order->total_amount,
            'date' => now(),
            'due_at' => now()->addHours(24)
        ]));
        
        return redirect()->route('payment.show', $payment->id);
    }
}
```

### Custom Filtering and Search

```php
use TautId\Payment\Data\Utility\{FilterPaginationData, ActiveFilterPaginationData};

// Advanced payment search
$filterData = FilterPaginationData::from([
    'page' => 1,
    'per_page' => 25,
    'sortBy' => 'total',
    'sortDirection' => 'desc',
    'searchable' => ['customer_name', 'customer_email', 'trx_id'],
    'searchTerm' => 'john',
    'active_filters' => [
        // Filter by date range
        ActiveFilterPaginationData::from([
            'column' => 'created_at',
            'value' => [now()->subDays(7), now()]
        ]),
        // Filter by payment method driver
        ActiveFilterPaginationData::from([
            'column' => 'method.driver', 
            'value' => 'moota-transaction'
        ]),
        // Filter by status
        ActiveFilterPaginationData::from([
            'column' => 'status',
            'value' => [PaymentStatusEnum::Completed->value, PaymentStatusEnum::Pending->value]
        ])
    ]
]);

$payments = $paymentService->getPaginatedPayments($filterData);
```

### Error Handling Best Practices

```php
use Illuminate\Database\RecordNotFoundException;
use TautId\Payment\Services\PaymentService;

class PaymentApiController extends Controller
{
    public function show($id)
    {
        try {
            $paymentService = app(PaymentService::class);
            $payment = $paymentService->getPaymentById($id);
            
            return response()->json($payment);
            
        } catch (RecordNotFoundException $e) {
            return response()->json([
                'error' => 'Payment not found'
            ], 404);
            
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Invalid request: ' . $e->getMessage()
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Payment retrieval failed', [
                'payment_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
```
