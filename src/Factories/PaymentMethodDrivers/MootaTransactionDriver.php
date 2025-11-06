<?php

namespace TautId\Payment\Factories\PaymentMethodDrivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use TautId\Payment\Abstracts\PaymentMethodDriverAbstract;
use TautId\Payment\Data\Payment\PaymentData;
use TautId\Payment\Services\PaymentService;

class MootaTransactionDriver extends PaymentMethodDriverAbstract
{
    private string $base_url = 'https://app.moota.co/api/v2/';

    private function getToken(): ?string
    {
        return config('taut-payment.moota_transaction_api_token');
    }

    private function getUrl(string $endpoint)
    {
        return "{$this->base_url}{$endpoint}";
    }

    public function services(): array
    {
        try {
            if (empty($this->getToken())) {
                throw new \Exception('Unable to process because token is not initialize');
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->withToken($this->getToken())
                ->get($this->getUrl('accounts/index?per_page=50'));

            if (! $response->successful()) {
                throw new \Exception($response->json('message'));
            }

            $options = $response->collect('data')
                ->mapWithKeys(function ($data) {
                    $bank_id = data_get($data, 'bank_id');
                    $username = data_get($data, 'username');

                    return [$bank_id => $username];
                });

            return $options->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getServiceImageFilename(string $service): string
    {
        $image_filename = match (strtolower($service)) {
            default => 'moota.png'
        };

        return $image_filename;
    }

    public function createPayment(PaymentData $data): void
    {
        try {
            if (empty($this->getToken())) {
                throw new \Exception('Unable to process because token is not initialize');
            }

            $payload = [
                'order_id' => $data->trx_id,
                'bank_account_id' => $data->method->service,
                'customers' => [
                    'name' => $data->customer_name,
                    'email' => $data->customer_email,
                    'phone' => $data->customer_phone,
                ],
                'items' => [
                    [
                        'name' => "Payment #{$data->trx_id}",
                        'description' => null,
                        'qty' => 1,
                        'price' => $data->total,
                    ],
                ],
                'description' => null,
                'note' => null,
                'redirect_url' => null,
                'expired_in_minutes' => now()->diffInMinutes($data->due_at),
                'total' => $data->total,
            ];

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->withToken($this->getToken())
                ->post($this->getUrl('create-transaction'), $payload);

            app(PaymentService::class)->updatePaymentPayload($data->id, $payload);
            app(PaymentService::class)->updatePaymentResponse($data->id, $response->collect()->toArray());

            if (! $response->successful()) {
                throw new \Exception($response->json('message'));
            }
        } catch (\Exception $e) {
            app(PaymentService::class)->changePaymentToFailed($data->id);
            throw new \Exception($e->getMessage());
        }

    }

    public function checkPayment(PaymentData $data): void
    {
        // no need to perform check payment, already handled by WebhookMootaTransactionReceiverJob
    }

    public function cancelPayment(PaymentData $data): void
    {
        // moota mutation-tracking/cancel deprecated
    }

    public function metaValidation(array $meta): void
    {
        //
    }

    public function checkSignature(Request $request): bool
    {
        $signature = $request->header('signature');

        if (! $signature) {
            return false;
        }

        $signingSecret = config('taut-payment.moota_transaction_webhook_secret');

        if (empty($signingSecret)) {
            throw InvalidConfig::signingSecretNotSet();
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), $signingSecret);

        return hash_equals($computedSignature, $signature);
    }
}
