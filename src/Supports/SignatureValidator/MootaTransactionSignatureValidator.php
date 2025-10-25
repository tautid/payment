<?php

namespace TautId\Payment\Supports\SignatureValidator;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use TautId\Payment\Factories\PaymentMethodDriverFactory;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;

class MootaTransactionSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return PaymentMethodDriverFactory::getDriver('moota-transaction')->checkSignature($request);
    }
}
