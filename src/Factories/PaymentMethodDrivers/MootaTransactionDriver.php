<?php

namespace TautId\Payment\Factories\PaymentMethodDrivers;

use Illuminate\Support\Facades\Http;
use TautId\Payment\Abstracts\PaymentMethodDriverAbstract;
use TautId\Payment\Data\Payment\PaymentData;
use TautId\Payment\Services\PaymentService;

class MootaTransactionDriver extends PaymentMethodDriverAbstract
{
    private string $base_url = 'https://app.moota.co/api/v2/';

    public function __construct()
    {
        $this->has_unique_code = true;
    }

    private function getToken(): ?string
    {
        return config('taut-payment.moota_transaction_api_token');
    }

    private function getUrl(string $endpoint)
    {
        return "{$this->base_url}{$endpoint}";
    }

    public function channels(): array
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

    public function createPayment(PaymentData $data): void
    {
        if (empty($this->getToken())) {
            throw new \Exception('Unable to process because token is not initialize');
        }

        $payload = [
            'order_id' => $data->trx_id,
            'bank_account_id' => data_get($data->method->meta, 'moota_bank_id'),
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
            'redirect_url' => route('webhook-client-moota-taut'),
            'expired_in_minutes' => now()->diffInMinutes($data->due_at),
            'total' => $data->total,
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->withToken($this->getToken())
            ->post($this->getUrl('create-transaction'), $payload);

        if (! $response->successful()) {
            throw new \Exception($response->json('message'));
        }

        app(PaymentService::class)->updatePaymentPayload($data->id, $payload);
        app(PaymentService::class)->updatePaymentResponse($data->id, $response->collect()->toArray());
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
        if (empty(data_get($meta, 'moota_bank_id'))) {
            throw new \Exception('moota_bank_id is required');
        }
    }
}
