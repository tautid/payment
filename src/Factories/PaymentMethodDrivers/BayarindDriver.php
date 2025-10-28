<?php

namespace TautId\Payment\Factories\PaymentMethodDrivers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use TautId\Payment\Abstracts\PaymentMethodDriverAbstract;
use TautId\Payment\Data\Payment\PaymentData;
use TautId\Payment\Enums\PaymentMethodTypeEnum;
use TautId\Payment\Services\PaymentService;

class BayarindDriver extends PaymentMethodDriverAbstract
{
    private string $sandbox_url = 'https://paytest.bayarind.id/PaymentRegister/';

    private string $production_url = 'https://pay.sprintasia.net/PaymentRegister/';

    public function services(): array
    {
        return [
            1021 => 'BCA Virtual Account',
            1085 => 'Shopee Pay',
            1084 => 'Dana',
            1077 => 'Link Aja',
            1086 => 'OVO',
            1089 => 'QRIS',
        ];
    }

    private function getToken(): string
    {
        return config('taut-payment.bayarind_secret');
    }

    private function getBaseUrl(string $endpoint, bool $is_production = false): string
    {
        $base_url = $is_production ? $this->production_url : $this->sandbox_url;

        return "{$base_url}{$endpoint}";
    }

    private function getCompanyId(): string
    {
        return config('taut-payment.bayarind_company_id');
    }

    private function getCustomerAccount(int $number): string
    {
        $number = (($number) ? substr($number, 2) : mt_rand(10000000000, 99999999999));

        return $this->getCompanyId().$number;
    }

    private function getRedirectUrl(PaymentData $data): string
    {
        $callbackUrl = config('taut-payment.redirect_url');

        if (empty($callbackUrl)) {
            throw new \Exception(
                'Bayarind callback URL is not configured. Please set "redirect_url" in taut-payment config or BAYARIND_CALLBACK_URL environment variable.'
            );
        }

        $callbackUrl = str_replace(['{id}', '{trx_id}'], [$data->id, $data->trx_id], $callbackUrl);

        return $callbackUrl;
    }

    public function createPayment(PaymentData $data): void
    {
        $transactionNo = $data->trx_id;
        $transactionAmount = intval($data->total);
        $channelId = data_get($data->method->meta, 'bayarind_channel_id');
        $secretKey = $this->getToken();
        $authCode = hash(
            'sha256',
            $transactionNo.$transactionAmount.$channelId.$secretKey
        );

        $payload = [
            'authCode' => $authCode,
            'channelId' => $channelId,
            'serviceCode' => $data->method->service,
            'currency' => 'IDR',
            'transactionNo' => $transactionNo,
            'transactionAmount' => $transactionAmount,
            'transactionDate' => $data->created_at->format('Y-m-d H:i:s'),
            'transactionExpire' => $data->due_at->format('Y-m-d H:i:s'), // in GMT +7
            'description' => "Payment {$data->trx_id}",
            'customerAccount' => $this->getCustomerAccount($data->customer_phone), // BCAVA
            'customerName' => $data->customer_name,
            'callbackURL' => $this->getRedirectUrl($data),
        ];

        $extraPayload = match ((int) $data->method->service) {
            1085 => $this->handlingShopeepay($data),
            1086 => $this->handlingOVO($data),
            1084 => $this->handlingDana($data),
            1077 => $this->handlingLinkAja($data),
            1089 => $this->handlingQris($data),
            default => [],
        };

        $payload = array_merge($payload, $extraPayload);

        $response = Http::asForm()->post(
            $this->getBaseUrl(
                endpoint: '',
                is_production: $data->method->type == PaymentMethodTypeEnum::Production->value
            ), $payload);

        app(PaymentService::class)->updatePaymentPayload($data->id, $payload);
        app(PaymentService::class)->updatePaymentResponse($data->id, $response->collect()->toArray());

        if ($response->json('insertStatus') != '00') {
            throw new \Exception($response->json('insertMessage'));
        }

        app(PaymentService::class)->updateDueAt(
            $data->id,
            Carbon::parse(data_get($payload, 'transactionExpire'))
        );
    }

    private function handlingShopeepay(PaymentData $data): array
    {
        return [
            'customerEmail' => $data->customer_email,
            'customerPhone' => $data->customer_phone,
            'transactionFee' => 0,
            'transactionExpire' => now()->addMinutes(59)->format('Y-m-d H:i:s'), // in GMT +7
        ];
    }

    private function handlingOVO(PaymentData $data): array
    {
        return [
            'customerPhone' => $data->customer_phone,
            'transactionExpire' => now()->addMinutes(59)->format('Y-m-d H:i:s'), // in GMT +7
        ];
    }

    private function handlingDana(PaymentData $data): array
    {
        return [
            'customerEmail' => $data->customer_email,
            'customerPhone' => $data->customer_phone,
            'transactionFee' => 0,
            'transactionExpire' => now()->addMinutes(59)->format('Y-m-d H:i:s'), // in GMT +7
        ];
    }

    private function handlingLinkAja(PaymentData $data): array
    {
        return [
            'transactionFee' => 0,
            'transactionExpire' => now()->addMinutes(59)->format('Y-m-d H:i:s'), // in GMT +7
        ];
    }

    private function handlingQris(PaymentData $data): array
    {
        return [
            'itemDetails' => [
                [
                    'itemName' => "Payment {$data->trx_id}",
                    'quantity' => 1,
                    'price' => intval($data->amount),
                ],
            ],
            'transactionExpire' => now()->addMinutes(59)->format('Y-m-d H:i:s'), // in GMT +7
        ];
    }

    public function checkPayment(PaymentData $data): void
    {
        //
    }

    public function cancelPayment(PaymentData $data): void
    {
        //
    }

    public function metaValidation(array $meta): void
    {
        if (empty(data_get($meta, 'bayarind_channel_id'))) {
            throw new \Exception('bayarind_channel_id is required');
        }
    }

    public function checkSignature(Request $request): bool
    {
        $secretKey = $this->getToken();

        if (empty($secretKey)) {
            throw InvalidConfig::signingSecretNotSet();
        }

        $signature = $request->get('authCode');
        if (empty($signature)) {
            return false;
        }

        $transactionNo = $request->get('transactionNo');
        $transactionAmount = $request->get('transactionAmount');
        $channelId = $request->get('channelId');
        $transactionStatus = $request->get('transactionStatus');
        $insertId = $request->get('insertId');

        $computed_signature = hash(
            'sha256',
            $transactionNo.$transactionAmount.$channelId.$transactionStatus.$insertId.$secretKey
        );

        return hash_equals($signature, $computed_signature);
    }
}
