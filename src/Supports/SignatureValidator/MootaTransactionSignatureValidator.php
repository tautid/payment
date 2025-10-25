<?php

namespace TautId\Payment\Supports\SignatureValidator;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;
use TautId\Payment\Factories\PaymentMethodDriverFactory;

class MootaTransactionSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return PaymentMethodDriverFactory::getDriver('moota-transaction')->checkSignature($request);
    }
}
