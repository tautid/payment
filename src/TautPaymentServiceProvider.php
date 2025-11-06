<?php

namespace TautId\Payment;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TautId\Payment\Abstracts\PaymentTransitionAbstract;
use TautId\Payment\Commands\MakeTransitionsCommand;
use TautId\Payment\Commands\PaymentDueCommand;

class TautPaymentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('taut-payment')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasMigration('create_taut_payments_table')
            ->hasCommand(PaymentDueCommand::class)
            ->hasCommand(MakeTransitionsCommand::class);
    }

    public function boot()
    {
        parent::boot();

        $existing = config('webhook-client.configs', []);
        $mine = require __DIR__.'/../config/payment-webhook-client.php';

        config([
            'webhook-client.configs' => array_merge($existing, $mine['configs']),
        ]);

        $this->registerTransitionBindings();

        // Publish assets
        $this->publishes([
            __DIR__.'/../assets/images' => public_path('vendor/taut-payment/images'),
        ], 'taut-payment-assets');

        // Publish seeders
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/seeders/' => database_path('seeders/'),
            ], 'taut-payment-seeders');
        }
    }

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__.'/../config/payment-webhook-client.php',
            'webhook-client'
        );
    }

    public function registerTransitionBindings()
    {
        $namespace = config('taut-payment.transitions_namespace', 'App\\Transitions\\Payment');

        $transitions = [
            'ToPending',
            'ToCanceled',
            'ToCompleted',
            'ToDue',
        ];

        foreach ($transitions as $transition) {
            $userClass = "{$namespace}\\{$transition}";
            $packageClass = "TautId\\Payment\\Transitions\\{$transition}";

            if (class_exists($userClass) && is_subclass_of($userClass, PaymentTransitionAbstract::class)) {
                $this->app->bind($packageClass, $userClass);
            } else {
                $this->app->bind($packageClass, $packageClass);
            }
        }
    }
}
