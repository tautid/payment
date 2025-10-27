<?php

namespace TautId\Payment\Supports\WebhookResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;
use Symfony\Component\HttpFoundation\Response;
use TautId\Payment\Enums\PaymentStatusEnum;
use TautId\Payment\Factories\PaymentMethodDriverFactory;
use TautId\Payment\Services\PaymentMethodService;
use TautId\Payment\Services\PaymentService;

class BayarindRespondTo implements RespondsToWebhook
{
    private function findChannels(string $channelId): Collection
    {
        $methods = app(PaymentMethodService::class)->getPaymentMethodByDriver('bayarind');

        $channels = collect($methods)->filter(fn($method) => data_get($method,'meta.bayarind_channel_id') == $channelId);

        return $channels;
    }

    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response
    {
        $channels = $this->findChannels($request->get('channelId'));

        // Invalid channelId
        if ($channels->count() <= 0) {
            return $this->invalidChannel($request);
        }

        // Invalid transactionNo
        try {
            $payment = app(PaymentService::class)->getPaymentByTrxId($request->get('transactionNo'));
        } catch (\Exception $e) {
            return $this->invalidTransactionNumber($request);
        } catch (\Illuminate\Database\RecordNotFoundException $e) {
            return $this->invalidTransactionNumber($request);
        } catch (\InvalidArgumentException $e) {
            return $this->invalidTransactionNumber($request);
        }

        // Invalid transactionAmount
        if ($payment->total != $request->get('transactionAmount')) {
            return $this->invalidTransactionAmount($request);
        }

        // Invalid insertId
        if (data_get($payment->response, 'insertId') != $request->get('insertId')) {
            return $this->invalidInsertId($request);
        }

        // Invalid Auth Code
        if (! PaymentMethodDriverFactory::getDriver('bayarind')->checkSignature($request)) {
            return $this->invalidAuthCode($request);
        }

        // Invalid currency
        if ($request->get('currency') != 'IDR') {
            return $this->invalidCurrency($request);
        }

        // Invalid transactionStatus
        if ($request->get('transactionStatus') != '00') {
            return $this->invalidTransactionStatus($request);
        }

        // Invalid customerAccount (VA only)
        if (
            $request->get('customerAccount') &&
            (data_get($payment->payload, 'customerAccount') != $request->get('customerAccount'))
        ) {
            return $this->invalidCustomerAccount($request);
        }

        if ($payment->status == PaymentStatusEnum::Completed->value) {
            return $this->doublePayment($request);
        }

        if ($payment->status == PaymentStatusEnum::Canceled->value) {
            return $this->cancelByAdmin($request);
        }

        if ($payment->status == PaymentStatusEnum::Due->value) {
            return $this->expired($request);
        }

        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => $request->get('transactionStatus'),
            'paymentMessage' => $request->get('transactionMessage'),
            'flagType' => $request->get('flagType'),
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function invalidChannel(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '01',
            'paymentMessage' => 'Invalid channelId',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function invalidTransactionNumber(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '01',
            'paymentMessage' => 'Invalid transactionNo',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function invalidTransactionAmount(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '01',
            'paymentMessage' => 'Invalid Transaction Amount',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function invalidInsertId(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '01',
            'paymentMessage' => 'Invalid insertId',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function invalidAuthCode(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '01',
            'paymentMessage' => 'Invalid AuthCode',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function invalidCurrency(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => $request->get('currency'),
            'paymentStatus' => '01',
            'paymentMessage' => 'Invalid Currency',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function invalidTransactionStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '01',
            'paymentMessage' => 'Invalid Transaction Status',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function invalidCustomerAccount(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '01',
            'paymentMessage' => 'Invalid VA Number',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function doublePayment(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '02',
            'paymentMessage' => 'Transaction has been paid',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function cancelByAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '05',
            'paymentMessage' => 'Transaction has been canceled',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }

    private function expired(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'channelId' => $request->get('channelId'),
            'currency' => 'IDR',
            'paymentStatus' => '04',
            'paymentMessage' => 'Transaction has been expired',
            'flagType' => '11',
            'paymentReffId' => $request->get('paymentReffId'),
        ]);
    }
}
