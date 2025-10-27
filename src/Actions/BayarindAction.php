<?php

namespace TautId\Payment\Actions;

use Illuminate\Contracts\View\View;
use TautId\Payment\Data\Payment\PaymentData;

class BayarindAction
{
    public function run(PaymentData $data): View
    {
        try {
            $result = match ((int) $data->method->service) {
                1085 => $this->handlingShopeepayView($data),
                1086 => $this->handlingOvoView($data),
                1084 => $this->handlingDanaView($data),
                1077 => $this->handlingLinkAjaView($data),
                1089 => $this->handlingQrisView($data),
                default => null,
            };

            if (empty($result)) {
                throw new \Exception('Route view is empty');
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    private function handlingLinkAjaView(PaymentData $data): View
    {
        return view('taut-payment::payment.bayarind.linkaja', [
            'redirectUrl' => data_get($data->response, 'redirectURL'),
            'redirectData' => data_get($data->response, 'redirectData.message'),
        ]);
    }

    private function handlingShopeepayView(PaymentData $data): View
    {
        return view('taut-payment::payment.bayarind.shopeepay', [
            'qrData' => data_get($data->response, 'redirectData.qr_content'),
        ]);
    }

    private function handlingOvoView(PaymentData $data): View
    {
        return view('taut-payment::payment.bayarind.ovo', [
            'redirectUrl' => data_get($data->response, 'redirectURL'),
        ]);
    }

    private function handlingDanaView(PaymentData $data): View
    {
        return view('taut-payment::payment.bayarind.dana', [
            'redirectUrl' => data_get($data->response, 'redirectURL'),
            'redirectData' => data_get($data->response, 'redirectData'),
        ]);
    }

    private function handlingQrisView(PaymentData $data): View
    {
        return view('taut-payment::payment.bayarind.qris', [
            'qrisUrl' => data_get($data->response, 'urlQris'),
        ]);
    }
}
