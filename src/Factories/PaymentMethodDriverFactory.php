<?php

namespace TautId\Payment\Factories;

use TautId\Payment\Abstracts\PaymentMethodDriverAbstract;
use TautId\Payment\Factories\PaymentMethodDrivers\MootaDriver;
use TautId\Payment\Factories\PaymentMethodDrivers\MootaTransactionDriver;

class PaymentMethodDriverFactory
{
    public static function getDriver(string $driverName): PaymentMethodDriverAbstract
    {
        $driver = match(strtolower($driverName))
                {
                    "moota-transaction" => new MootaTransactionDriver(),
                    default => null
                };

        if(empty($driver))
            throw new \Exception('Driver not found');

        if(!in_array($driverName,config('taut-payment.drivers')))
            throw new \Exception("{$driverName} is disabled from config");

        return $driver;
    }

    public static function getOptions(): array
    {
        $options = collect(config('taut-payment.drivers'))
                    ->mapWithKeys(fn($item) => [$item => ucfirst($item)])
                    ->toArray();

        return $options;
    }
}
