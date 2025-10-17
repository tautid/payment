<?php

namespace TautId\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use TautId\Payment\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Cash Payment',
                'driver' => 'offline',
                'type' => 'production',
                'is_active' => true,
                'meta' => [
                    'description' => 'Cash payment method for offline transactions',
                    'channels' => ['cash'],
                    'instructions' => 'Please pay with cash at the counter',
                ]
            ],
            [
                'name' => 'Cash Payment (Sandbox)',
                'driver' => 'offline',
                'type' => 'sandbox',
                'is_active' => true,
                'meta' => [
                    'description' => 'Cash payment method for testing purposes',
                    'channels' => ['cash'],
                    'instructions' => 'Test cash payment method',
                ]
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                [
                    'driver' => $method['driver'],
                    'type' => $method['type'],
                ],
                $method
            );
        }
    }
}