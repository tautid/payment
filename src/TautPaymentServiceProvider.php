<?php

namespace TautId\Payment;

use Spatie\LaravelPackageTools\Package;
use TautId\Payment\Commands\PaymentDueCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TautId\Payment\Abstracts\PaymentTransitionAbstract;

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
            ->hasCommand(PaymentDueCommand::class);
    }

    public function boot()
    {
        parent::boot();

        $this->mergeConfigFrom(__DIR__.'./../config/payment-webhook-client.php', 'webhook-client');

        // if (!Schema::hasTable('settings')) {
        //     throw new RuntimeException(
        //         "The 'settings' table was not found. Please run:\n".
        //         "php artisan vendor:publish --tag=settings-migrations\n".
        //         "php artisan migrate"
        //     );
        // }
    }

    public function register()
    {
        parent::register();

        $this->registerTransitionBindings();
    }

    public function registerTransitionBindings()
    {
        $namespace = config('taut-payment.transitions_namespace', 'App\\Transitions');

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
